<?php
//****************************************************
// Auteur: CraftMyCMS (Kévin GUIOT)
// CraftMyCMS PERSO 4.0.2.2
// Copyright © 2012 - 2015
// Sortie: 26 avril 2015 à 19h30
// Contact: contact@craftmycms.fr
//
// http://www.craftmycms.fr/
// http://developpeur.craftmycms.fr/changelog/cms/#zsl7zgdmzs
//****************************************************

$titre_page = "Gestion des news";
include("../include/init.php");

$site = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$site = str_replace('www.', null, $site);
$site = str_replace('admin/news.php?add', null, $site);

if(!empty($_POST)) {
	//Ajout d'une news
	if(strstr($_SERVER['REQUEST_URI'], 'add')) {
		
		if($permissions[1] == true) {
			
			if(empty($_POST['titre'])) {
				$sujet = false;
			} else {
				$sujet = secure($_POST['titre']);
			}
			
			if(!empty($_POST['news'])) {				
				if(empty($_POST['small'])) {
					$small = false;
				} else {
					$small = true;
				}
				
				if(!empty($_POST['site'])) {
					$req = $connexion->prepare("INSERT INTO ".$prefixe."news (titre, content, image, date, heure, user, small) VALUES (:titre_news,:news,:image,:date,:heure,:user, :small)");
					$req->execute(
						array(
							'titre_news' => $_POST['titre'],
							'image' => $_POST['image'],
							'news' => secure($_POST['news']),
							'date' => $date,
							'heure' => $heure,
							'user' => USER_PSEUDO,
							'small' => $small
						)
					);
				} 
				
				if(!empty($_POST['mail'])) {
					$req_selectMembres = $connexion->query('SELECT * FROM '.$prefixe.'membres');
					while($selectMembre = $req_selectMembres->fetch()) {
						$headers ='From: '.$titresite.'<'.$mail_admin.'>'."\n"; 
						$headers .='Reply-To: '.$mail_admin."\n"; 
						$headers .='Content-Type: text/html; charset="utf-8"'."\n"; 
						$headers .='Content-Transfer-Encoding: 8bit';
						$message = '<style>hr {background: #d9d9d9;border-width: 0;color: #d9d9d9;height: 1px;margin-top: 15px;margin-bottom: 20px;}a {font-weight:bold; color:black}a:hover {text-decoration:underline;}</style>';
						$message .= $_POST['news'].'<hr><span style="font-size: small; color: grey">Il s\'agit d\'un mail automatique envoyé par <a href="http://'.$site.'">'.$titresite.'</a>, merci de ne pas y répondre.</span>';
						
						$mail = mail($selectMembre['email'], utf8_decode($sujet), $message, $headers);
					}
				}
				header("location: ?msg=ajoutnews:true"); exit;
			} else {
				header("location: ?msg=ajoutnews:false"); exit;
			}
		}
	}
	
	//Modifier une news
	elseif(strstr($_SERVER['REQUEST_URI'], '?edit')) {
		if($permissions[2]) {
			if(empty($_POST['small'])) {
				$small = false;
			} else {
				$small = true;
			}
			$update = $connexion->prepare("UPDATE ".$prefixe."news SET titre = :titre, content = :content, image = :image, small=:small WHERE id = :idnews");
			$update->execute(
				array(
					'titre' => $_POST['titre'],
					'content' => $_POST['news'],
					'image' => $_POST['image'],
					'idnews' => $_POST['idnews'],
					'small' => $small
				)
			);
			header("location: ?msg=editnews:true");
		}
	} else {
		if($permissions[3]) {
			//Supprimer avec la sélection
			if(!empty($_POST['selection'])) {
				foreach($_POST['selection'] as $selection) {
					$deleteNews = $connexion->prepare('DELETE FROM '.$prefixe.'news WHERE id=:id');
					$deleteNews->execute(array('id' => $selection));
				}
				header("location: ?msg=supprnews:true");
				exit;
			}
		}
	}
	exit;
}

//Suppresion d'une news
if(!empty($_GET['delete']) && !empty($_GET['token'])) {
	if($permissions[3]) {
		$id = $_GET['delete'];
		if(is_numeric($id)) {
			$connexion->exec("DELETE FROM ".$prefixe."news WHERE id=$id");
			header("location: ?msg=supprnew:true");
		}
	}
}

include("header.php");
?>

<div id="content" class="news">

	<?php
	msg("Votre news a bien été envoyée.", 'v', 'get', 'ajoutnews:true', 10, 'close');
	msg("Votre news a bien été éditée.", 'v', 'get', 'editnews:true', 10, 'close');
	msg("Les news sélectionnées ont bien été supprimée", 'v', 'get', 'supprnews:true', 10, 'close');
	msg("La news sélectionnée a bien été supprimée.", 'v', 'get', 'supprnew:true', 10, 'close');
	msg("Il est impossible d'envoyer un mail via votre site.<br>Veuillez consulter le support de votre hébergeur pour activer la fonction mail().", 'r', 'get', 'email:false', 10, 'close');
	msg("Veuillez saisir le contenu de votre news.", 'r', 'get', 'ajoutnews:false', 10, 'close');
	
	if(!empty($_GET['edit'])) {
		echo "<a href='news.php'>&#8617; Afficher toutes les news</a>";
		$id = $_GET['edit'];
		if(is_numeric($id)) {
			$resultats=$connexion->query("SELECT * FROM ".$prefixe."news WHERE id='$id'");
			$resultats->setFetchMode(PDO::FETCH_OBJ);
			$lignecount = $resultats->rowCount();
			if($lignecount!="0") {
				$ligne = $resultats->fetch(); 
				if($permissions[2]) { ?>
					<h3>Modifier une news</h3>
					<form method="post" action="?edit">
						<table class="table table-bordered table-striped">
							<tr>
								<td>Titre de votre news</td>
								<td><input type="text" name="titre" placeholder="Titre..." value="<?php echo $ligne->titre; ?>"></td>
							</tr>
							<tr>
								<td>Image de votre news (facultatif)<br>
								<small>(taille min &amp; max: 80px)</small></td>
								<td><input type="text" name="image" placeholder="Lien de l'image..." value="<?php echo $ligne->image; ?>"></td>
							</tr>
							<tr>
								<td colspan="2"><textarea placeholder="Contenu de votre news (du code HTML peut être mis en place)" name="news" rows="5"><?php echo $ligne->content; ?></textarea></td>
							</tr>
							<tr>
								<td colspan="2"><input type="submit"> <label for="small"><input type="checkbox" name="small" id="small" <?php if($ligne->small == true) { echo "checked"; }?>> Montrer que 270 mots sur la page d'accueil</small></td>
							</tr>
						</table>
						<input type="hidden" name="idnews" value="<?php echo $ligne->id; ?>">
					</form>
				<?php } else {
					msg("Vous n'avez pas les permissions nécessaires pour modifier cette news.", 'r');
				}
			} else {
				msg("Cette news n\'existe pas.", 'r');
			}
		} else {
			msg("Cette news n\'existe pas.", 'r');
		}
	} else { ?>
		<h3>Ajouter une news</h3>
		<?php if($permissions[1]) { ?>
			<form method="post" action="?add">
				<table class="table table-bordered table-striped">
					<tr>
						<td>Titre de votre news (facultatif)</td>
						<td><input type="text" name="titre" placeholder="Titre..." value="<?php if(!empty($titre_news)) { echo $titre_news; } ?>"></td>
					</tr>
					<tr>
						<td>Image de votre news (facultatif)<br>
						<small>(taille min &amp; max: 80px)</small></td>
						<td><input type="text" name="image" placeholder="Lien de l'image..." value="<?php if(!empty($image)) { echo $image; } ?>"></td>
					</tr>
					<tr>
						<td colspan="2">
							<textarea placeholder="Contenu de votre news (du code HTML peut être mis en place)" name="news" rows="5"><?php if(!empty($news)) { echo $news; } ?></textarea>
							<label for="site"><input type="checkbox" name="site" id="site" checked> Envoyer cette news sur votre site</label><br>
							<label for="mail"><input type="checkbox" name="mail" id="mail"> Envoyer cette news par mail à vos utilisateurs</label>
						</td>
					</tr>
					<tr>
						<tr>
							<td colspan="2"><input type="submit" value="Ajouter une news"><label for="small"> <input type="checkbox" name="small" id="small"> Montrer que 270 mots sur la page d'accueil (HTML désactivé)</small></td>
						</tr>
					</tr>
				</table>
			</form>
		<?php } else {
			msg("Vous n'avez pas les permissions nécessaires pour ajouter une news.", 'r');
		}
		?>
		
		<h3>Gérer vos news</h3>
		<div id="affiche_news">
			<?php
			$sql = $connexion->query("SELECT * FROM ".$prefixe."news ORDER BY id DESC");
			$nbrNews = $sql->rowCount();
			
			if($nbrNews > 0) {
				$sql->setFetchMode(PDO::FETCH_OBJ);
				echo '<form method="post" id="selectionForm">';
				while($req = $sql->fetch()) {
					$content = $req->content;
					$content = str_replace("[retour]", "<br>", $content);
					$content = str_replace('[a href=&quot;', '<a href="', $content);
					$content = str_replace('[/a]', '</a>', $content);
					$content = str_replace('&quot;]', '">', $content);
					$content = htmlspecialchars_decode($content);
					
					$content = nl2br($content);
					?>
					<div class="news" id="<?php echo $req->id; ?>">
					
						<?php
						
						//Ajout de la case pour cocher.
						if($permissions[3]) {
							echo '<input name="selection[]" value="'.$req->id.'" type="checkbox" style="float:right;">';
						}
	
						if(!empty($req->titre)) { ?>
							<div class="titre"><?php echo $req->titre; ?></div>
							<hr class="hrdashed">
						<?php }
						if(!empty($req->image)) { ?>
							<img src="<?php echo $req->image; ?>" class="imageNew" alt="news<?php echo $req->id; ?>">
							<?php echo $content; ?>
						<?php } else { ?>
							<span class="content">
							<?php echo $content; ?>
							</span>
						<?php } ?>
						<hr class="hrdashed" style="clear:both;">
							
						<?php
						if($permissions[2] && $permissions[3]) {
							echo '<a href="?edit='.$req->id.'">Modifier</a> &bull; <a href="?delete='.$req->id.'&token='.$_SESSION['token'].'">Supprimer</a> &bull;';
						} elseif($permissions[2]) {
							echo '<a href="?edit='.$req->id.'">Modifier</a> &bull;';
						} elseif($permissions[3]) {
							echo '<a href="?delete='.$req->id.'&token='.$_SESSION['token'].'">Supprimer</a> &bull;';
						}
						?>
						Ajouté le <?php echo $req->date." à ".$req->heure; ?>
					</div>    
				<?php
				}
				echo '</form>';
	
				if($permissions[3]) { ?>
					<div class="news last">
						<select id="selectNews" onchange="confirmSelect()">
							<option value="rien">Pour la séléction...</option>
							<option value="delete">Supprimer</option>
						</select>	
						<input type="checkbox" style="float:right;" id="selectionAll">
					</div>
				<?php
				}
			} else {
				msg("Vous n'avez pas encore publié de news.", 'b', '', '', 0);
			}
			?>
		</div>
	</div>
	<?php
		}
	?>
</div>
<?php include("footer.php"); ?>
<script>
$(document).ready(function() {
    $('#selectionAll').click(function() { 
        var cases = $("#affiche_news").find(':checkbox');
        if(this.checked) {
            cases.attr('checked', true);
        } else {
            cases.attr('checked', false);
        }
    });
});

function confirmSelect() {
	var selectNews = document.getElementById("selectNews");
	var selectNews = selectNews.options[selectNews.selectedIndex].value;
	
	if (selectNews == "delete") {
		confirmMsg = confirm("Voulez-vous vraiment supprimer les news sélectionnées ?");
		if (confirmMsg == true) {
			document.getElementById('selectionForm').submit()
		} else {
			
		}
	} else {
	}
}
</script>