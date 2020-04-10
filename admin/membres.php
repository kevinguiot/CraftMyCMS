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

$titre_page = "Gestion des membres";
include("../include/init.php");
if(!empty($_GET['deletemembre']) && !empty($_GET['token'])) {
	$id = $_GET['deletemembre'];
	$sql = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE id=:id');
	$sql->execute(array('id' => $_GET['deletemembre']));
    $sql->setFetchMode(PDO::FETCH_OBJ);
    $req = $sql->fetch();
	if($req->pseudo != $pseudo) {
		if(empty($req)) {
			header("location: ?msg=membre:inexistant");
		} else {
			if($req->rang=="1") {
				$req = $connexion->prepare("DELETE FROM ".$prefixe."membres WHERE id=:id");
				$req->execute(
					array(
						'id' => $id,
					)
				);
				header("location: ?msg=membre:suppr");
			} else {
				$sql = $connexion->query("SELECT * FROM ".$prefixe."membres WHERE rang=3");
				$sql->setFetchMode(PDO::FETCH_OBJ);
				$num = $sql->rowCount();
				if($num=="1") {
					header("location: ?msg=membre:admin");
				} else {
					$req = $connexion->prepare("DELETE FROM ".$prefixe."membres WHERE id=:id");
					$req->execute(
						array(
							'id' => $id,
						)
					);
					header("location: ?msg=membre:suppr");
				}
			}
		}
	} else {
		header("location: ?msg=membre:false");
	}
	exit;
}

if(!empty($_POST['updatemembre'])) {
	$id = $_POST['id'];
	if(!empty($_POST['mail'])) {
        $req = $connexion->prepare("UPDATE ".$prefixe."membres SET email=:mail WHERE id=:id");
        $req->execute(
            array(
                'mail' => secure($_POST['mail']),
                'id' => $_POST['id'],
            )
        );
    }
	if(!empty($_POST['prenom'])) {
        $req = $connexion->prepare("UPDATE ".$prefixe."membres SET prenom=:prenom WHERE id=:id");
        $req->execute(
            array(
                'prenom' => secure($_POST['prenom']),
                'id' => $_POST['id'],
            )
        );        
	}
	if(!empty($_POST['nom'])) {
        $req = $connexion->prepare("UPDATE ".$prefixe."membres SET nom=:nom WHERE id=:id");
        $req->execute(
            array(
                'nom' => secure($_POST['nom']),
                'id' => $_POST['id'],
            )
        );        
	}
	if(is_numeric($_POST['rang'])) {
		$sql = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE id=:id');
		$sql->execute(array('id' => $id));
		$sql->setFetchMode(PDO::FETCH_OBJ);
		$req = $sql->fetch();
		if($req->pseudo != $pseudo) {
			if($_POST['rang']=="1") {
				$sql = $connexion->query("SELECT * FROM ".$prefixe."membres WHERE rang=3");
				$sql->setFetchMode(PDO::FETCH_OBJ);
				$num = $sql->rowCount();
				if($num < "1") {
					header("location: ?msg=membre:admin");
				} else {
					$req = $connexion->prepare("UPDATE ".$prefixe."membres SET rang=:rang WHERE id=:id");
					$req->execute(
						array(
							'rang' => $_POST['rang'],
							'id' => $_POST['id'],
						)
					);
					header("location: ?msg=membre:change");
				}
			} else {
				$req = $connexion->prepare("UPDATE ".$prefixe."membres SET rang=:rang WHERE id=:id");
				$req->execute(
					array(
						'rang' => $_POST['rang'],
						'id' => $_POST['id'],
					)
				);
				header("location: ?msg=membre:change");
			}
		} else {
			header("location: ?msg=membre:false");
		}
	}
}
include("header.php");
?>
<div id="content" class="membres">
	<?php
	if(!empty($_GET['msg'])) {
		$msg = $_GET['msg'];
		if(!empty($msg)) {
			if($msg == "membre:false") { echo '<div class="warning_r">Vous ne pouvez pas supprimer/modifier votre propre profil.</div>'; }
			if($msg == "membre:inexistant") { echo '<div class="warning_r">Ce membre n\'existe pas.</div>'; }
			if($msg == "membre:suppr") { echo '<div class="warning_v">Ce membre a bien été supprimé.</div>'; }
			if($msg == "membre:admin") { echo '<div class="warning_r">Vous ne pouvez pas supprimer le dernier administrateur.</div>'; }
			if($msg == "membre:change") { echo '<div class="warning_v">Les informations de ce membre ont bien été changées.</div>'; }
		}
	}
    if(!empty($_GET['modifier'])) {
        $id = (int) $_GET['modifier'];
        $sql = $connexion->prepare("SELECT * FROM ".$prefixe."membres WHERE id=:id LIMIT 1");
		$sql->execute(array('id' => $id));
		$nbr_sql = $sql->rowCount();
		
		if($nbr_sql > 0) {
			$sql->setFetchMode(PDO::FETCH_OBJ);
			$req = $sql->fetch(); ?>
			<form method="post">
				<input type="hidden" name="updatemembre" value="true">
				<input type="hidden" name="id" value="<?php echo $req->id; ?>">
				<p>Changer les informations pour <strong><?php echo $req->pseudo; ?></strong></p>
				<table class="table table-bordered table-striped modif_membres">
					<tr>
						<td>Pseudo</td>
						<td><input type="text" value="<?php echo $req->pseudo; ?>" disabled></td>
					</tr>
					<tr>
						<td>Adresse e-mail</td>
						<td><input type="text" value="<?php echo $req->email; ?>" name="mail"></td>
					</tr>
					<tr>
						<td>Nom</td>
						<td><input type="text" name="nom" value="<?php echo $req->nom; ?>"></td>
					</tr>
					<tr>
						<td>Prénom</td>
						<td><input type="text" name="prenom" value="<?php echo $req->prenom; ?>"></td>
					</tr>
					<tr>
						<td>Rang</td>
						<td>
							<select name="rang">
								<option value="1" <?php if($req->rang=="1") { echo "selected"; } ?>>Utilisateur</option>
								<option value="2" <?php if($req->rang=="2") { echo "selected"; } ?>>Modérateur</option>
								<option value="3" <?php if($req->rang=="3") { echo "selected"; } ?>>Administrateur</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>Adresse IP</td>
						<td><input type="text" value="<?php echo $req->ip; ?>" disabled></td>
					</tr>
					<tr>
						<td>Tokens</td>
						<td><?php echo $req->token; ?></td>
					</tr>
					<tr>
						<td>Inscription</td>
						<td><?php echo $req->date.' à '.$req->heure; ?></td>
					</tr>
					<tr>
						<td>Dernière connexion sur le site</td>
						<td>
							<?php
							if(!empty($req->ddate) && !empty($req->dheure)) {
								echo $req->ddate.' à '.$req->dheure; 
							} else {
								echo 'Non renseigné';
							}
							?>
						</td>
					</tr>
					<td colspan="2"><input type="submit"></td>
				</table>
			</form>
		<?php
			} else { msg("Ce membre n'existe pas. <a href='membres.php'>Cliquez-ici pour revenir en arrière</a>.", 'r');} 
		} else { ?>
			<table class="table table-bordered table-striped membres">
				<tr>
					<td name="pseudo">Pseudo</td>
					<td>Rang</td>
					<td name="action">Action</td>
				</tr>
				<?php
				$sql = $connexion->query("SELECT * FROM ".$prefixe."membres ORDER BY rang DESC");
				$sql->setFetchMode(PDO::FETCH_OBJ);
				while($req = $sql->fetch()) { ?>
				<tr>
					<td name="pseudo"><strong><a href="../membre.php?pseudo=<?php echo $req->pseudo; ?>"><?php echo $req->pseudo; ?></a></strong></td>
					<td>
						<?php
						if($req->rang=="3") { echo "Administrateur"; }
						elseif($req->rang == "2") { echo "Modérateur"; }
						else { echo "Utilisateur"; }
						?>
					</td>
					<td>
						<a title="Modifier ce compte" href="?modifier=<?php echo $req->id; ?>"><img src="../images/page_white_edit.png"></a>
						<a title="Envoyer des crédits à ce membre" href="boutique.php?pseudocredit=<?php echo $req->pseudo; ?>#gererCreditForm"><img src="../images/coins.png"></a>
						<a title="Supprimer ce membre" href="?deletemembre=<?php echo $req->id; ?>&token=<?php echo $_SESSION['token']; ?>"><img src="../images/false.png"></a>
					</td>
				</tr>
				<?php } ?>
			</table>
    <?php } ?>
</div>
<?php include("footer.php"); ?>