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

$titre_page = "Mon compte";
include("include/init.php");

if(!connect()) {
	header('location: index.php');
	exit;
}

$req_selectUser = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE id=:user_id');
$req_selectUser->execute(array('user_id' => USER_ID));
$selectUser = $req_selectUser->fetch();

if(!empty($_POST) && empty($_GET['sk'])) {
	$_POST = array_map('secure', $_POST);
	
    if((is_numeric($_POST['birthday'])) && ($_POST['birthday'] > '0') && ($_POST['birthday'] <= '31')) {
        $birthday = $_POST['birthday'];
    } else {
        $erreur = "Votre jour de naissance est incorrect.";
    }
    
    if((is_numeric($_POST['birthmonth'])) && ($_POST['birthmonth'] > '0') && ($_POST['birthmonth'] <= '12')) {
        $birthmonth = $_POST['birthmonth'];
    } else {
        $erreur = "Votre mois de naissance est incorrect.";
    }    
    
    if((is_numeric($_POST['birthyear'])) && ($_POST['birthyear'] >= '1900') && ($_POST['birthyear'] <= '2014')) {
        $birthyear = $_POST['birthyear'];
    } else {
        $erreur = "Votre date de naissance est incorrecte.";
    }
	
	if(!verifmail($_POST['email'])) {
		$erreur = "Votre adresse e-mail est incorrecte.";
	}

	if(empty($erreur)) {
		$updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET prenom=:prenom, nom=:nom, email=:email, travail=:travail, naissance=:naissance, localisation=:localisation, web=:web WHERE id=:id');
		$updateMembre->execute(array(
			'prenom' => $_POST['prenom'],
			'nom' => $_POST['nom'],
			'email' => $_POST['email'],
			'travail' => $_POST['travail'],
			'naissance' => $birthday.'/'.$birthmonth.'/'.$birthyear,
			'localisation' => $_POST['localisation'],
			'web' => $_POST['web'],
			'id' => USER_ID,
		));
		
		header('location: compte.php?msg=update:ok');
		exit;
	}
}

if(!empty($_POST) && @$_GET['sk'] == "password") {
	if($_POST['new_pass'] != "" && $_POST['new_pass_verif'] != "") {
		if(md5($_POST['old_pass']) == $selectUser['passe']) {
			if($_POST['new_pass'] == $_POST['new_pass_verif']) {
				
				$newPass = md5($_POST['new_pass']);
				
				$updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET passe=:newpass WHERE id=:id');
				$updateMembre->execute(array(
					'newpass' => $newPass,
					'id' => USER_ID,
				));
				header('location: compte.php?msg=password:ok');
				exit;
				
			} else {
				$erreur = "Vos deux nouveaux mots de passe ne correspondent pas.";
			}
		} else {
			$erreur = "Votre mot de passe actuel n'est pas correct.";
		}
	} else {
		$erreur = "Vous n'avez pas mentionné votre nouveau mot de passe.";
	}}

include("include/header.php");

if(!empty($_GET['sk']) && $_GET['sk'] == 'password') { ?>
	<div id="content" class="mon_compte">
		
		<?php
		if(!empty($erreur)) {
			msg($erreur, 'r');
		}
		?>

		<h3>Modification du mot de passe</h3>
		<form method="post">
			<table class="table table-bordered table-striped infos">
				<tr>
					<td>Votre mot de passe actuel</td>
					<td><input type="password" name="old_pass"></td>
				</tr>
				
				<tr>
					<td>Votre nouveau mot de passe</td>
					<td><input type="password" name="new_pass"></td>
				</tr>
				
				<tr>
					<td>Confirmer votre nouveau mot de passe</td>
					<td><input type="password" name="new_pass_verif"></td>
				</tr>
				
				<tr>
					<td colspan="2"><input type="submit" value="Modifier votre mot de passe"> <a href="compte.php">Revenir en arrière</a></td>
				</tr>
			</table>
		</form>
	</div>
	<?php
	include('include/footer.php');
	exit;
}
?>
	
<div id="content" class="mon_compte">
	<?php
	if(!empty($erreur)) {
		msg($erreur, 'r');		
	} else {
		msg("Les informations de votre profil ont bien été modifiées.", 'v', 'get', 'update:ok');
	}
	
	msg("Votre article a bien été reçu sur le jeu.",'v', 'get', "receive:true");
	msg("Impossible de récupérer votre article en jeu.",'r', 'get', "receive:false");
	msg("Le mot de passe de votre compte a bien été modifié.",'v', 'get', "password:ok");
	
	if(empty($_POST)) {
		$prenom = $selectUser['prenom'];
		$nom = $selectUser['nom'];
		$email = $selectUser['email'];
		$travail = $selectUser['travail'];
		$localisation = $selectUser['localisation'];
		$web = $selectUser['web'];
	} else {
		$prenom = $_POST['prenom'];
		$nom = $_POST['nom'];
		$email = $_POST['email'];
		$travail = $_POST['travail'];
		$localisation = $_POST['localisation'];
		$web = $_POST['web'];
	}
	
	$naissance = explode('/', $selectUser['naissance']);
	
	if(empty($naissance[0])) {
		$birthday = "1";
	} else {
		$birthday = $naissance[0];
	}
	
	if(empty($naissance[1])) {
		$birthmonth = "1";
	} else {
		$birthmonth = $naissance[1];
	}
	
	if(empty($naissance[2])) {
		$birthyear = "1900";
	} else {
		$birthyear = $naissance[2];
	}
	?>
	<h3>Votre profil</h3>
	<form method="post">
		<table class="table table-bordered table-striped infos">
			<tr>
				<td>Votre prénom</td>
				<td><input type="text" value="<?php echo $prenom; ?>" name="prenom"></td>
			</tr>
			<tr>
				<td>Votre nom</td>
				<td><input type="text" value="<?php echo $nom; ?>" name="nom"></td>
			</tr>
			<tr>
				<td>Votre adresse e-mail</td>
				<td><input type="text" value="<?php echo $email; ?>" name="email"></td>
			</tr>
			<tr>
				<td>Votre date de naissance</td>
				<td>
					<select name="birthday">
						<?php
						for($i = 1; $i < 32; $i++) {
							if(strlen($i) == 1) {
								$i = '0'.$i;
							}
							
							$selected = null;
							
							if($i == $birthday) {
								$selected = 'selected';
							}
							
							echo '<option  value="'.$i.'" '.$selected.'>'.$i.'</option>';
						}
						?>
					</select>
					
					<select name="birthmonth">
						<?php
						$mois_01 = "Janvier";
						$mois_02 = "Février";
						$mois_03 = "Mars";
						$mois_04 = "Avril";
						$mois_05 = "Mai";
						$mois_06 = "Juin";
						$mois_07 = "Juillet";
						$mois_08 = "Août";
						$mois_09 = "Septembre";
						$mois_10 = "Octobre";
						$mois_11 = "Novembre";
						$mois_12 = "Décembre";
						
						for($i = 1; $i < 13; $i++) {
							if(strlen($i) == 1) {
								$i = '0'.$i;
							}
							
							$selected = null;
							
							if($i == $birthmonth) {
								$selected = 'selected';
							}
							
							echo '<option  value="'.$i.'" '.$selected.'>'.${"mois_".$i}.'</option>';
						}						
						?>
					</select>
					
					<select name="birthyear">
						<?php
						for($i = 2014; $i >= 1900; $i--) {
							if(strlen($i) == 1) {
								$i = '0'.$i;
							}

							$selected = null;
							
							if($i == $birthyear) {
								$selected = 'selected';
							}
							
							echo '<option  value="'.$i.'" '.$selected.'>'.$i.'</option>';
						}						
						?>
					</select>
                </td>
			</tr>
			<tr>
				<td>Votre travail<br><small>Correspond à votre travail, ou vos études.</small></td>
				<td><input type="text" name="travail" placeholder="Exemple: Élève de BTS SN (IR), etc..." value="<?php echo $travail; ?>"></td>
			</tr>
			<tr>
				<td>Votre localisation<br><small>Correspond à la ville où vous habitez.</small></td>
				<td><input type="text" name="localisation" placeholder="Exemple: France, Paris" value="<?php echo $localisation; ?>"></td>
			</tr>
			<tr>
				<td>Votre site internet<br><small>URL de votre site personnel.</small></td>
				<td><input type="text" name="web" placeholder="Exemple: http://www.craftmycms.fr/" value="<?php echo $web; ?>"></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Modifier votre profil"> <a href="?sk=password">Cliquez ici pour modifier votre mot de passe</a></td>
			</tr>
		</table>
	</form><br>
	
	<h3>Votre liste d'achat</h3>
	<?php
	$sql = $connexion->prepare("SELECT * FROM ".$prefixe."boutique_liste WHERE pseudo=:pseudo ORDER BY id DESC");
	$sql->execute(array(
		'pseudo' => $pseudo
	));
	$nbrAchat = $sql->rowCount();
	if($nbrAchat == "0") {
		msg("Vous n'avez pas encore acheté d'article sur la boutique.", "b");
	} else { ?>
		<table class="table table-bordered table-striped liste_achat">
			<tr>
				<td></td>
				<td>Article</td>
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
				
				if($req['etat'] == true) {
					$etat = '<img src="images/true.png" alt="true" title="Article bien reçu en jeu.">';
				} elseif(!empty($reqboutique['article'])) {
					$etat = '<a href="boutique.php?receive='.$req['id'].'"><img src="images/sand-clock.png" alt="waiting" title="Article en attente de confirmation de votre part."></a>';				
				} else {
					$etat = '<img src="images/false.png" alt="false" title="Vous n\'avez pas reçu votre article en jeu.">';
				}
				
				echo '<tr>';
				echo '<td>'.$etat.'</td>';
				
				echo '<td>'.$article;
				if($req['etat'] == false && !empty($reqboutique['article'])) {
					echo '<br><a href="boutique.php?receive='.$req['id'].'">Recevoir votre article en jeu</a>';
				}
				echo '</td>';
				
				echo '<td>'.$req['date'].'</td>';
				echo '<td>'.$req['heure'].'</td>';
				echo '</tr>';
			}
			?>
		</table>
	<?php } ?><br>
	
	<h3>Vos commandes</h3>
	<?php
	$sql = $connexion->prepare("SELECT * FROM ".$prefixe."commandes WHERE pseudo=:pseudo ORDER BY id DESC");
	$sql->execute(array(
		'pseudo' => $pseudo
	));
	$nbrAchat = $sql->rowCount();
	if($nbrAchat == "0") {
		msg("Vous n'avez pas encore passé de commande sur ce site.", "b");
	} else { ?>
		<table class="table table-bordered table-striped commandes">
			<tr>
				<td>Numéro de commande</td>
				<td>Date</td>
				<td>Heure</td>
			</tr>
			<?php
			while($req = $sql->fetch()) {
				?>
				<tr>
					<td><?php echo $req['nb']; ?></td>
					<td><?php echo $req['date']; ?></td>
					<td><?php echo $req['heure']; ?></td>
				</tr>
				<?php	
				}
			?>
		</table>
	<?php } ?><br>
	
	<h3>Vos billets sur le support</h3>
	<?php
	$req_listeBillets = $connexion->prepare('SELECT * FROM '.$prefixe.'support WHERE pseudo=:pseudo');
	$req_listeBillets->execute(array(
		'pseudo' => $pseudo
	));
	$nbrBillet = $req_listeBillets->rowCount();
	if($nbrBillet == '0') {
		msg("Vous n'avez pas encore posté de billet sur le support.", 'b');
	} else { ?>
		<table class="table table-bordered table-striped support">
			<tr>
				<td></td>
				<td>Sujet</td>
				<td>Date</td>
				<td>Heure</td>
			</tr>
			<?php
			while($listeBillet = $req_listeBillets->fetch()) {
				if($listeBillet['etat']=="0") {
					$etat = '<img src="images/false.png" alt="false" title="Non-résolu">';
				} else {
					$etat = '<img src="images/true.png" alt="true" title="Résolu">';
				}
				echo '<tr>';
				echo '<td>'.$etat.'</td>';
				echo '<td><a href="support.php?id='.$listeBillet['id'].'">'.$listeBillet['sujet'].'</a></td>';
				echo '<td>'.$listeBillet['date'].'</td>';
				echo '<td>'.$listeBillet['heure'].'</td>';
				echo '</tr>';
			}
			?>
		</table>
	<?php } ?>
</div>
<?php include("include/footer.php"); ?>