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

$titre_page = "Gestion de la boutique";
$needserveur = false;
include("../include/init.php");

//Suppresion d'un article
if($permissions[8] == true) {
	if(!empty($_GET['suppr']) && !empty($_GET['token'])) {
		$req_selectArticle = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id');
		$req_selectArticle->execute(array('id' => $_GET['suppr']));
		$selectArticle = $req_selectArticle->rowCount();
		if($selectArticle > 0) {
			$deleteArticle = $connexion->prepare('DELETE FROM '.$prefixe.'boutique WHERE id=:id');
			$deleteArticle->execute(array('id' => $_GET['suppr']));
			header('location: boutique.php?msg=suppr');
			exit;
		}
		
	}
}

//Ajout / Modification d'un article
if(strstr($_SERVER['REQUEST_URI'], 'form') && $_POST) {
	if($permissions[6] || $permissions[7]) {
		if($_GET['form'] == 'ajout') {
			$envoi = true;
		} elseif($_GET['form'] == 'update') {
			
			$req_selectArticle = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id');
			$req_selectArticle->execute(array(
				'id' => $_GET['update'],
			));
			$nbr_selectArticle = $req_selectArticle->rowCount();
			
			if($nbr_selectArticle == 1) {
				$envoi = true;
			}
		} else {
			$envoi = false;
		}
		
		if($envoi == false) {
			header('location: boutique.php?msg=ajoutboutique:false');
			exit;
		}
		
		//On traite la valeur IG
		if(empty($_POST['valeur_ig'])) {
			$valeurIG = "";
		} else {
			$valeurIG = $_POST['valeur_ig'];
		}
		
		//On traite l'article requis
		if($_POST['requis'] == "0") {
			$requis = "";
		} else{
			$requis = $_POST['requis'];
		}
		
		//On traite si l'article est limité ou non
		if(empty($_POST['limite'])) {
			$limite = "0";
		} else {
			$limite = "1";
		}
		
		//On traite les commandes utilisée.
		$commande = $_POST['commande'];
		$commande = implode('[{NEW}]', $commande);
	
		//On traite les serveurs
		$serveur = $_POST['serveur'];
		
		//On ajoute ou modifie dans la base de donnée.
		if($_GET['form'] == 'ajout') {
			
			if($permissions[6]) {
				$insertArticle = $connexion->prepare("INSERT INTO ".$prefixe."boutique SET article=:article, description=:description, categorie=:categorie, valeur=:valeur, valeur_ig=:valeur_ig, commande=:commande,image=:image, date=:date, heure=:heure, requis=:requis, limite=:limite, serveur=:serveur") or die(print_r($connexion->errorInfo(), true));
				$insertArticle->execute(array(
					'article' => $_POST['article'],
					'description' => $_POST['description'],
					'categorie' => $_POST['categorie'],
					'valeur' => $_POST['valeur'],
					'valeur_ig' => $valeurIG,
					'commande' => $commande,
					'image' => $_POST['image'],
					'date' => $date,
					'heure' => $heure,
					'requis' => $requis,
					'limite' => $limite,
					'serveur' => $serveur,
				));
				
				header('location: boutique.php?msg=ajoutboutique:ok');
			}
		} else {
			if($permissions[7]) {
				$updateArticle = $connexion->prepare('UPDATE '.$prefixe.'boutique SET article=:article, description=:description, categorie=:categorie, valeur=:valeur, valeur_ig=:valeur_ig, commande=:commande,image=:image, requis=:requis, limite=:limite, serveur=:serveur WHERE id=:id');
				$updateArticle->execute(array(
					'article' => $_POST['article'],
					'description' => $_POST['description'],
					'categorie' => $_POST['categorie'],
					'valeur' => $_POST['valeur'],
					'valeur_ig' => $valeurIG,
					'commande' => $commande,
					'image' => $_POST['image'],
					'requis' => $requis,
					'limite' => $limite,
					'serveur' => $serveur,
					'id' => $_GET['update'],
				));
				
				header('location: boutique.php?update='.$_GET['update'].'&msg=modifier:ok');
			}
		}
		exit;
	}
}

if($rang == 3) {
	if(!empty($_GET['vider']) && !empty($_GET['token'])) {
		if($_GET['vider']=="achats") {
			$connexion->query('DELETE FROM '.$prefixe.'boutique_liste');
		}
		if($_GET['vider']=="commandes") {
			$connexion->query('DELETE FROM '.$prefixe.'commandes');
		}
		if($_GET['vider']=="tokens") {
			$connexion->query('UPDATE '.$prefixe.'membres SET token=0');
		}
		header('location: ?msg=vider:ok');
		exit;
	}
	
	if(strstr($_SERVER['REQUEST_URI'], 'gererCredit') && $_POST) {
		$cPseudo = $_POST['pseudo'];
		$cNbr = $_POST['credit'];
		$cAction = $_POST['action'];
		if(is_numeric($cNbr)) {
			if(empty($_POST['all'])) {
				$req_selectMembre = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE pseudo=:pseudo');
				$req_selectMembre->execute(array(
					'pseudo' => $cPseudo
				));
				$nbr_selectPseudo = $req_selectMembre->rowCount();
				if($nbr_selectPseudo>0) {
					if($cAction == "1") {$updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token + :token WHERE pseudo = :pseudo');}
					if($cAction == "2") { $updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token - :token WHERE pseudo = :pseudo'); }
					if($cAction == "3") { $updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = :token WHERE pseudo = :pseudo'); }
					$updateMembre->execute(array(
						'token' => $cNbr,
						'pseudo' => $cPseudo,
					));
				}
			} else {
				if($cAction == "1") { $updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token + :token'); }
				if($cAction == "2") { $updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token - :token'); }
				if($cAction == "3") { $updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = :token'); }
				$updateMembre->execute(array('token' => $cNbr));
			}
			header('location: boutique.php?msg=credit:ok');
			exit;
		}
	}	
	
	if(!empty($_GET['supprcat'])) {
		$sql = $connexion->query("SELECT * FROM ".$prefixe."boutique_cat");
		$sql->setFetchMode(PDO::FETCH_OBJ);
		$count = $sql->rowCount();
		if($count=="1") {
			header("location: ?msg=supprcat:false");
			exit;
		} else { 
			$id = $_GET['supprcat'];
			$connexion->exec("DELETE FROM ".$prefixe."boutique_cat WHERE id=$id");
			header("location: ?msg=supprcat:true");
			exit;
		}
	}
	
	if(!empty($_POST['categorie2'])) {
		$req = $connexion->prepare("INSERT INTO ".$prefixe."boutique_cat (categorie) VALUES (:categorie)");
		$req->execute(
			array(
				'categorie' => $_POST['categorie2'],
			)
		);
		header("location: ?msg=ajoutcategorie:ok");
		exit;
	}
}
    
include("header.php");
?>

<div id="content" class="boutique">
    <?php
	msg("Votre article a bien été supprimé.", 'v', 'get', 'suppr');
	msg("Votre catégorie a bien été supprimée.", 'v', 'get', 'supprcat:true');
	msg("Vous devez laisser au moins une catégorie.", 'r', 'get', 'supprcat:false');
	msg("Votre categorie a bien été ajoutée.", 'v', 'get', 'ajoutcategorie:ok');
	msg("Votre article a bien été modifié.", 'v', 'get', 'modifier:ok');
	msg("Les crédits ont bien été envoyés.", 'v', 'get', 'ajoutcredit:true');
	msg("Les crédits ont bien été supprimés.", 'v', 'get', 'supprcredit:true');
	msg("Votre objet a bien été ajouté à la boutique.", 'v', 'get', 'ajoutboutique:ok');
	msg("Les crédits ont bien été modifiés pour ce membre.", 'v', 'get', 'credit:ok');
	msg("Votre action a bien été effectuée.", 'v', 'get', 'vider:ok');
	
	if(empty($_POST)) {
		if(!empty($_GET['update'])) {
			if($permissions[7] == true) {
				$req_selectArticle = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id');
				$req_selectArticle->execute(array(
					'id' => $_GET['update']
				));
				$selectArticle = $req_selectArticle->fetch();
				if(!empty($selectArticle)) {
					$titre = $selectArticle['article'];
					$description = $selectArticle['description'];
					$valeur = $selectArticle['valeur'];
					$valeur_ig = $selectArticle['valeur_ig'];
					$commande = $selectArticle['commande'];
					$image = $selectArticle['image'];
					$serveur = $selectArticle['serveur'];
				} else {
					$titre=$description=$valeur=$valeur_ig=$commande=$image=null;
				}
			} else {
				msg("Vous n'avez pas les permissions nécessaires pour afficher cette page.", 'r');
				echo '</div>';
				include('footer.php');
				exit;
			}
		} else {
			if($permissions[6] == true) {
				$serveur=$titre=$description=$valeur=$valeur_ig=$commande=$image=null;
			}
		}
	} else {
		$titre = $_POST['article'];
		$description = $_POST['description'];
		$valeur = $_POST['valeur'];
		
		if(empty($_POST['valeur_ig'])) {
			$valeur_ig = false;
		} else {
			$valeur_ig = $_POST['valeur_ig'];
		}
		
		$commande = $_POST['commande'];
		$image = $_POST['image'];
	}
	?>
	
	<h3>
		<?php if(empty($selectArticle)) { ?>Ajouter un article<?php } else { ?>Modifier: <?php echo $selectArticle['article']; } ?>
	</h3>
	<?php
	if((empty($_GET['update']) && $permissions[6] == true) OR (!empty($_GET['update']) && $permissions[7])) {
		if(!empty($selectArticle)) {
			echo '<form method="post" action="boutique.php?form=update&update='.$selectArticle['id'].'">';
		} else { 
			echo '<form method="post" action="boutique.php?form=ajout">';
		}
		?>
		
		<table class="table table-bordered table-striped ajout_article">
			<tr>
				<td>Titre de votre article</td>
				<td><input type="text" name="article" placeholder="Titre..." value="<?php echo $titre; ?>"></td>
			</tr>
			<tr>
				<td>Description de votre article<br><small>Les retours à la ligne ne seront pas considérés.</small></td>
				<td>
					<textarea rows="3" name="description" placeholder="Description..."><?php echo $description; ?></textarea>
				</td>
			</tr>
			<tr>
				<td>Catégorie de votre article</td>
				<td>
					<select name="categorie">
						<?php
						$sql = $connexion->query("SELECT * FROM ".$prefixe."boutique_cat");
						$sql->setFetchMode(PDO::FETCH_OBJ);
						while($categorie1 = $sql->fetch()) {
							if(!empty($selectArticle)) {
								if($categorie1->id == $selectArticle['categorie']) {
									echo "<option value=".$categorie1->id." selected>".$categorie1->categorie."</option>";
								} else {
									echo "<option value=".$categorie1->id.">".$categorie1->categorie."</option>";
								}
							} else {
								echo "<option value=".$categorie1->id.">".$categorie1->categorie."</option>";
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Valeur de votre article<br><small>Laisser vide pour ne pas activer le moyen de paiement en question.</small></td>
				<td>
					<input type="text" name="valeur"  style="width:10%;" maxlength="15" value="<?php echo $valeur; ?>"> <?php echo '<strong>'.$monnaie_site.'</strong> (monnaie site)'; ?>
					<?php if($activeMoneyIG == true) { ?><br><input type="text" name="valeur_ig" style="width:10%;" maxlength="15" value="<?php echo $valeur_ig; ?>"> <?php echo '<strong>'.$monnaie_serveur.'</strong> (monnaie serveur)'; } ?>
				</td>
			</tr>
			<tr>
				<td>Commande console de votre article<br><small><strong>Exemple:<br></strong>- give $player 256 64</small></td>
				<td>
					
					<?php
					if(empty($commande)) {
						echo '<input type="text" placeholder="Commande..." name="commande[]">';
					} else {
						$commande = explode('[{NEW}]', $commande);
						foreach($commande as $commande) {
							if(!empty($commande)) {
								echo '<input type="text" placeholder="Commande..." value="'.$commande.'" name="commande[]">';
							}
						}
					}
					?>

					<img src="../images/admin/plus.png" class="plus" title="Ajouter une nouvelle commande...">
					<br><small><strong>$player</strong> = Pseudo du joueur<br>
				</td>
			</tr>									
			<tr>
				<td>Image de votre article<br><small>Lien de votre image</small></td>
				<td><input type="text" placeholder="Lien de l'image..." name="image" value="<?php echo $image; ?>"></td>
			</tr>
			<tr>
				<td>
					Achat nécessaire pour acheter cet article<br>
					<small>Si un joueur n'achète pas l'article ci-contre séléctionné, alors il ne peut pas acheter l'article que vous allez enregistrer.</small>
				</td>
				<td>
					<select name="requis">
						<?php
						$req_selectAllArticle = $connexion->query('SELECT * FROM '.$prefixe.'boutique');
						if(empty($selectArticle)) {
							echo '<option value="0" selected>Aucun</option>';
							while($selectAllArticle = $req_selectAllArticle->fetch()) {
								echo '<option value="'.$selectAllArticle['id'].'">'.$selectAllArticle['article'].'</option>';
							}
						} else {
							if($selectArticle['requis'] == null) {
								echo '<option value="0" selected>Aucun</option>';
							} else {
								echo '<option value="0">Aucun</option>';
							}
							while($selectAllArticle = $req_selectAllArticle->fetch()) {
								if($selectAllArticle['id'] != $selectArticle['id']) {
									if($selectArticle['requis'] == $selectAllArticle['id']) {
										echo '<option value="'.$selectAllArticle['id'].'" selected>'.$selectAllArticle['article'].'</option>';
									} else {
										echo '<option value="'.$selectAllArticle['id'].'">'.$selectAllArticle['article'].'</option>';
									}	
								}
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Achat limité<br><small>En activant cette option, votre joueur ne pourra acheter qu'une fois votre article.</small></td>
				<td style="text-align: center;"><input name="limite" type="checkbox" <?php if(!empty($selectArticle) && $selectArticle['limite']=="1") { echo 'checked'; }; ?>></td>
			</tr>
			<tr>
				<td>Serveur Minecraft<br><small>Sélectionnez le serveur sur lequel vous voulez ajouter votre article.</small></td>
				<td>
					<?php
					$req_selectServeurs = $connexion->query('SELECT * FROM '.$prefixe.'serveurs ORDER BY nom ASC');
					$nbr_selectServeurs = $req_selectServeurs->rowCount();
					
					if($nbr_selectServeurs > 0) {
						$i = 0;
						
						while($selectServeurs = $req_selectServeurs->fetch()) {
							if(empty($serveur) && $i == 0) {
								$checked = ' checked';
							} else {
								if($selectServeurs['id']  == $serveur) {
									$checked = ' checked';
								} else {
									$checked = "";
								}
							}
							
							echo '<input type="radio" name="serveur" id="serveur'.$selectServeurs['id'].'" value="'.$selectServeurs['id'].'"'.$checked.'> <label for="serveur'.$selectServeurs['id'].'">'.$selectServeurs['nom'].'</label><br>';
							$i++;
						}
					} else {
						echo "<div style='text-align:center;'>Vous n'avez pas encore de serveur.</div>";
					}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php
					if(empty($selectArticle)) {
						echo '<input type="submit" value="Ajouter un article"></td>'; 
					} else {
						echo '<input type="submit" value="Modifier cet article"> <a href="boutique.php">Revenir en arrière</a></td>';
					}
					?>
				</td>
			</tr>
		</table>
		
		<?php
		echo '</form>';
	} else {
		msg("Vous n'avez pas les permissions nécessaires pour afficher cette page", 'r');
	}
	
	if($rang == 3) { ?>
		<h3>Autres paramètres</h3>
		<div class="credits">
			<div class="ajout_suppr ajout2">
				<h2>Gérer les cagégories</h2>
				<div style="padding:10px;">
					<table class="table table-bordered table-striped gerer_cat">
						<tr>
							<td>Ajouter</td>
							<td>
								<form method="post">
								<input type="text" name="categorie2" placeholder="Nom de votre catégorie">
								<input type="submit" value="OK">
								</form>
							</td>
						</tr>
						<tr>
							<td>Supprimer</td>
							<td>
								<form method="get">
									<select name="supprcat">
										<?php
										$req_categorie = $connexion->query('SELECT * FROM '.$prefixe.'boutique_cat');
										while($categorie = $req_categorie->fetch()) {
											echo '<option value="'.$categorie['id'].'">'.$categorie['categorie'].'</option>';
										}
										?>
									</select>
									<input type="submit" value="OK">
								</form>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="ajout_suppr retirer" id="gererCreditForm">
				<h2>Ajouter/Retirer des crédits</h2>
				<form method="post" action="?gererCredit">
					<?php
					if(!empty($_GET['pseudocredit'])) {
						echo '<input type="text" name="pseudo" value='.$_GET['pseudocredit'].' placeholder="Pseudo du joueur">';
					} else {
						echo '<input type="text" name="pseudo" placeholder="Pseudo du joueur">';
					}
					?>
					<label for="all"><input type="checkbox" id="all" name="all">Tous les joueurs</label><br>
					<input type="text" name="credit" placeholder="Nombre de crédit">
					<select name="action">
						<option value="3">Définir</option>
						<option value="1">Ajouter</option>
						<option value="2">Retirer</option>
					</select><br>
					<input type="submit" value="Envoyer"><br>
				</form>
			</div>
			<p style="clear: both;"></p>
		</div>
		
		<div class="credits">
			<div class="liste_tokens">
				<h2>Derniers acheteurs</h2>
				<div style="padding:10px;">
					<?php
					$req_listeAchat = $connexion->query('SELECT * FROM '.$prefixe.'boutique_liste LIMIT 12');
					$i = '0';
					while($listeAchat = $req_listeAchat->fetch()) {
						echo '<img title="Le '.$listeAchat['date'].' à '.$listeAchat['heure'].' par '.$listeAchat['pseudo'].'" src="../skin.php?pseudo='.$listeAchat['pseudo'].'">';
						$i++;
					}
					if($i == '0') {
						echo msg("Aucun achat n'a été effectué.", 'b');
					}
					?>
				</div>
			</div>
			
			<div class="liste_tokens informations">
				<h2>Informations</h2>
				<div style="padding:10px;">
					<a onclick='confirmListe("Etes-vous sûr de vouloir vider la liste des achats", "?vider=achats")'>Vider la liste des achats</a><br>
					<a onclick='confirmListe("Etes-vous sûr de vouloir vider la liste des commandes effectuées", "?vider=commandes")'>Vider la liste des commandes</a><br>
					<a onclick='confirmListe("Etes-vous sûr de vouloir vider la liste des tokens de vos membres", "?vider=tokens")'>Vider la liste des tokens</a><br>
				</div>
			</div>		
			<p style="clear: both;"></p>
		</div>
	<?php } ?>
	
	<h3>Informations sur vos articles</h3>
	<?php
	$sql = $connexion->query("SELECT * FROM ".$prefixe."boutique ORDER BY id DESC");
	$nbrArticle = $sql->rowCount();
	if($nbrArticle > 0) {
		echo '<table id="listeArticle">';
		while($article = $sql->fetch()) {
			$req_selectCategorie = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_cat WHERE id=:id');
			$req_selectCategorie->execute(array('id' => $article['categorie']));
			$selectCategorie = $req_selectCategorie->fetch();
			
			if(empty($article['image'])) {
				$article['image'] = "../images/empty_article.png";
			}
		
			if(empty($selectCategorie[1])) {
				$categorie = "inconnu";
			} else {
				$categorie = $selectCategorie[1];
			}
			
			$commande = $article['commande'];
			$commande = explode('[{NEW}]', $commande);
			$commandeArticle = null;
			
			foreach($commande as $commande) {
				if(!empty($commande)) {
					$commandeArticle .= '<br>- '.$commande;
				}
			}
			
			
			$req_selectAchats = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_liste WHERE id_boutique=:id');
			$req_selectAchats->execute(array('id' => $article['id']));
			$selectAchat = $req_selectAchats->rowCount();
			$descriptionTitle = str_replace('<br>', null, $article['description']);
			if($article['valeur_ig']==null) { $valeur_ig = "0"; } else { $valeur_ig = $article['valeur_ig']; }
			
			echo '<tr>';
			echo '<td>';
			echo '<div class="article">';
			
			if($activeMoneyIG == true) { echo '<div class="nbrCreditIG">'.$valeur_ig.'</div>';}
			
			echo '<div class="nbrCredit">'.$article['valeur'].'</div><p style="clear:both;">';
			echo '<img src="'.$article['image'].'" alt="'.$article['article'].'"><br>';
			echo stripcslashes($article['article']);
			
			if($permissions[8]) {
				echo '<div><img onclick=\'confirmListe("Etes-vous sûr de vouloir supprimer cet article: '.secure($article['article']).'", "?suppr='.$article['id'].'&token='.$_SESSION['token'].'")\' class="option" alt="delete" title="Supprimer cet article" src="../images/admin/deleteArticle.png"></div>';
			}
			
			if($permissions[7]) {
				echo '<div><a style="float:right;" href="?update='.$article['id'].'"><img class="option" alt="Modifier cet article" alt="edit" src="../images/admin/updateArticle.png"></a></div>';
			}
			
			
			echo '<p style="clear:both;"></p>';
			echo '</div>';
			echo '</td><td>';
			echo '<h3>'.$article['article'].'</h3>';
			echo $article['description'];
			echo '<br><br>';
			echo '&bull; Catégorie: <strong>'.$categorie.'</strong><br>';
			echo '&bull; Création le: <strong>'.$article['date'].' à '.$article['heure'].'</strong><br>';
			echo '&bull; Commande: <strong>'.$commandeArticle.'</strong><br>';
			echo '&bull; Achat: <strong>'.$selectAchat.'</strong>';
			echo '</td></tr>';
		}
	echo '</table>';
	} else {
		msg("Vous n'avez pas encore ajouté d'article.", 'b');
	}
	?>

	<?php if($rang == 3) {?>
		<h3 id="listeAchat">Liste des achats</h3>
		<?php
		$sql = $connexion->query("SELECT * FROM ".$prefixe."boutique_liste ORDER BY id DESC");
		$nbrAchat = $sql->rowCount();
		if($nbrAchat == "0") {
			msg("Vous n'avez pas encore acheter d'article sur la boutique.", "b");
		} else { ?>
		<table class="table table-bordered table-striped liste_achat">
			<tr>
				<td>Article</td>
				<td>Pseudo</td>
				<td>Date</td>
				<td>Heure</td>
			</tr>
			<?php
			while($req = $sql->fetch()) {
				$sqlboutique = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id_boutique');
				$sqlboutique->execute(array('id_boutique' => $req['id_boutique']));
				$reqboutique = $sqlboutique->fetch();
				if(empty($reqboutique['article'])) {
					$article  = "Introuvable (l'article a été supprimé).";
				} else {
					$article = $reqboutique['article'];
				}
				?>
				<tr>
					<td><?php echo $article; ?></td>
					<td><?php echo $req['pseudo']; ?></td>
					<td><?php echo $req['date']; ?></td>
					<td><?php echo $req['heure']; ?></td>
				</tr>
				<?php	
				}
			?>
		</table>
		<?php } ?>
	
		<h3 id="listeCommande">Liste des commandes</h3>
		<?php
		$req_selectCommandes = $connexion->query('SELECT * FROM '.$prefixe.'commandes ORDER BY id DESC');
		$nbrCommandes = $req_selectCommandes->rowCount();
		if($nbrCommandes>0) { ?>
			<table class="table table-bordered table-striped liste_achat">
				<tr>
					<td>Numéro de commande</td>
					<td>Pseudo</td>
					<td>Date</td>
					<td>Heure</td>
				</tr>
			<?php
			$req_selectCommandes = $connexion->query('SELECT * FROM '.$prefixe.'commandes ORDER BY id DESC');
			while($selectCommandes = $req_selectCommandes->fetch()) {
				echo '<tr>';
				echo '<td>'.$selectCommandes['nb'].'</td>';
				echo '<td>'.$selectCommandes['pseudo'].'</td>';
				echo '<td>'.$selectCommandes['date'].'</td>';
				echo '<td>'.$selectCommandes['heure'].'</td>';
				echo '</tr>';
			}
			?>
			</table>
		<?php } else {
			msg("Aucune commande n'a encore été faite.", 'b');
		}
	}
	?>
</div>
<?php include("footer.php"); ?>
<script>
$('#all').change(function () {
	if ($(this).attr("checked")) {
		$("input[name=pseudo]").attr("disabled","disabled");
		$("input[name=pseudo]").css('background', '#E6E6E6');
	} else {
		$("input[name=pseudo]").removeAttr("disabled");
		$("input[name=pseudo]").css('background', 'white');
	}
});

function confirmListe(msg,a) {
	var r=confirm(msg + ' ?');
	if (r==true) {
		window.location = a + '&token=<?php echo $_SESSION['token']; ?>';
	}
}

$('img.plus').click(function() {
	$('<input type="text" name="commande[]" placeholder="Commande...">').insertAfter("input[name='commande[]']:last-of-type");
});

</script>