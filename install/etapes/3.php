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

if(!empty($_POST)) {
	if(empty($_POST['identifiant'])) { $erreurv = "Vous n'avez pas indiqué votre identifiant"; }
	elseif(empty($_POST['email'])) { $erreurv = "Vous n'avez pas indiqué votre adresse email"; }
	elseif($_POST['identifiant'] == 'admin') { $erreurv = "Le pseudo <u>admin</u> n'a pas le droit d'être utilisé."; }
	elseif(!preg_match("#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#",$_POST['email'])) { $erreurv = "L'adresse email n'est pas valide"; }
	elseif(empty($_POST['password'])) { $erreurv = "Vous n'avez pas indiqué votre mot de passe"; }
	else {
		$email = $_POST['email'];
		$pseudo = secure($_POST['identifiant']);
		
		$req_selectMembre = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE pseudo=:pseudo OR email=:email');
		$req_selectMembre->execute(array(
			'pseudo' => $pseudo,
			'email' => $email
		));
		$nbr_selectMembre = $req_selectMembre->rowCount();
		
		if($nbr_selectMembre == 1) {
			$deleteMembre = $connexion->prepare('DELETE FROM '.$prefixe.'membres WHERE pseudo=:pseudo OR email=:email');
			$deleteMembre->execute(array(
				'pseudo' => $pseudo,
				'email' => $_POST['email'],
			));
		}
		
		$session = md5(rand());

		$insertMembre = $connexion->prepare('INSERT INTO '.$prefixe.'membres SET session=:session, rang="3", pseudo=:pseudo, email=:email, nom=:nom, prenom=:prenom, passe=:passe, date=:date, heure=:heure') or die(print_r($connexion->errorInfo(), true));
		$insertMembre->execute(array(
			'session' => $session,
			'pseudo' => $pseudo,
			'nom' => $_POST['nom'],
			'prenom' => $_POST['prenom'],
			'passe' => md5($_POST['password']),
			'email' => $email,
			'date' => $date,
			'heure' => $heure,
		));
		
		$_SESSION['session'] = $session;
		$_SESSION['emailAdmin'] = $email;
		$_SESSION['token'] = md5(time() * rand());

		$connexion->query('UPDATE '.$prefixe.'etape_temp SET etape3="1"');
		header('location: index.php');
		exit;
	}
}

include("header.php");
?>

<div id="content" class="admin">
    <?php
    if(!empty($erreurv)) {
        echo '<div class="warning_r">'.$erreurv.'</div>';
    }
    ?>
	<p><strong>Vous devez configurer le premier administrateur de votre CMS.</strong><br>
	Evitez les pseudos "Admin", "Administrateur", "admin", et "administrateur".</p>
	<form method="post">
        <table class="table table-bordered table-striped">
			<tr>
				<td>Votre identifiant</td>
				<td><input type="text" name="identifiant" value="<?php if(isset($_POST['identifiant'])) { echo $_POST['identifiant']; } ?>"></td>
			</tr>
			<tr>
				<td>Votre adresse e-mail</td>
				<td>
					<input type="text" name="email"  value="<?php if(isset($_POST['email'])) { echo $_POST['email']; } ?>">
				</td>
			</tr>
			<tr>
				<td>Votre mot de passe</td>
				<td><input type="password" name="password" autocomplete="off"></td>
			</tr>
			<tr>
				<td>Votre prénom</td>
				<td><input type="text" name="prenom" value="<?php if(isset($_POST['prenom'])) { echo $_POST['prenom']; } ?>"></td>
			</tr>
			<tr>
				<td>Votre nom</td>
				<td><input type="text" name="nom" value="<?php if(isset($_POST['nom'])) { echo $_POST['nom']; } ?>"></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Créer un nouvel administrateur"></td>
			</tr>
		</table>
	</form>
</div>
<?php include("footer.php"); ?>