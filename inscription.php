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

$titre_page = "Inscription";
include("include/init.php");

if(connect()) {
	header('location: index.php');
	exit;
}

@$session_captcha = $_SESSION['captcha'];

if($_POST) {
	if($captcha == "1") {
		$code = strtoupper($_POST['captcha']);
	}
    $email = $_POST['email'];
    $pseudo = secure($_POST['identifiant']);
	$pseudo = preg_replace("#[^a-zA-Z-0-9_-]#", "?", $pseudo);
    $passe = $_POST['passe'];
    $vpasse = $_POST['vpasse'];

	if(empty($email) OR empty($pseudo) OR empty($passe) OR empty($vpasse)) {
		$erreur = "Veuillez remplir les champs obligatoires";
	} else {
		if(verifmail($email)) {
			$reponse_mail = $connexion->query("SELECT email FROM ".$prefixe."membres WHERE email='$email'");
			$count_mail = $reponse_mail->rowCount();
			
			if($pseudo != 'admin') {
				$reponse_pseudo = $connexion->query("SELECT pseudo FROM ".$prefixe."membres WHERE pseudo='$pseudo'");
				$count_pseudo = $reponse_pseudo->rowCount();
			} else {
				$count_pseudo = '1';
			}

			if(empty($pseudo)) { $erreur = "Vous n'avez pas indiqué votre pseudo"; }
			elseif(verifmail($email) == false) { $erreur = "Vous n'avez pas entrer une adresse mail valide"; }
			elseif($passe=="") { $erreur = "Vous n'avez pas indiqué votre mot de passe"; }
			elseif($vpasse=="") { $erreur = "Vous n'avez pas répéter votre mot de passe"; }
			elseif($passe!=$vpasse) { $erreur = "Vous n'avez pas indiqué les mêmes mots de passe"; }
			elseif($count_mail == 1) { $erreur = "Cet e-mail existe déjà "; }
			elseif($count_pseudo == 1) { $erreur = "Ce pseudo existe déjà "; }
			elseif(md5($code)!=$session_captcha && $captcha == "1") { $erreur = "Vous n'avez pas recopier le bon code anti-robot"; }
			
			//Si le membre doit être connecté sur le serveur en même temps
			if($connect_serveur == true) {
	
				//Permet d'établir le serveur actuel.
				include('include/class/getServeur.php');
	
				if($etatJSONAPI == true) {
					$getPlayer = $api->call("getPlayer", array($pseudo));

					if($getPlayer['result'] == 'error' || (!empty($getPlayer['success']) && $getPlayer['success']['ip'] == 'offline')) {
						$erreur = "Vous devez être connecté sur le serveur pour pouvoir vous inscrire.";
					}
				} else {
					$erreur = "Le serveur est fermé. Vous ne pouvez pas encore vous inscrire sur le site, car vous devez être connecté sur le serveur.";
				}
			}

			
			if(empty($erreur)) {
				$nom = secure($_POST['nom']);
				$prenom = secure($_POST['prenom']);
				
				$passemd5 = md5($passe);
				$session5 = md5(rand());
				$req = $connexion->prepare("INSERT INTO ".$prefixe."membres (session, pseudo, prenom, nom, passe, email, date, heure, ip) VALUES (:session, :pseudo, :prenom, :nom, :passe, :email, :date, :heure, :ip)");
				$req->execute(
					array(
						'session' => $session5,
						'pseudo' => $pseudo,
						'prenom' => $prenom,
						'nom' => $nom,			
						'passe' => $passemd5,			
						'email' => $email,
						'date' => $date,
						'heure' => $heure,
						'ip' => $_SERVER['REMOTE_ADDR']
					)
				);
				
				$_SESSION['session'] = $session5;
				$_SESSION['token'] = md5(time() * rand());
		
				header('Location:index.php');
				exit;
			}
		} else {
			$erreur = "Votre adresse mail n'est pas valide";
		}
	}
}
include("include/header.php");
?>

<div id="content" class="inscription">
	<?php
	if($_POST && isset($erreur)) {
		echo '<div class="warning_r">'.$erreur.'</div>';
	}
	?>
	<form method="post" action="inscription.php">
		<table>
			<tr><td colspan="2">Inscrivez-vous sur le site <strong><?php echo $titre; ?></strong></td></tr>
			<tr>
				<td><input type="text" name="identifiant" <?php if(isset($_POST['identifiant'])) { echo 'value="'.$_POST['identifiant'].'" '; } ?>placeholder="Votre pseudonyme Minecraft.net*"></td>
				<td><input type="text" name="email" <?php if(isset($_POST['email'])) { echo 'value="'.$_POST['email'].'" '; } ?>placeholder="Votre adresse e-mail*"></td>
			</tr>
			<tr>
				<td><input type="password" name="passe" placeholder="Votre mot de passe*"></td>
				<td><input type="password" name="vpasse" placeholder="Répétez votre mot de passe*"></td>
			</tr>
			<tr>
				<td><input type="text" name="prenom" <?php if(isset($_POST['prenom'])) { echo 'value="'.$_POST['prenom'].'" '; } ?>placeholder="Votre prénom"></td>
				<td><input type="text" name="nom" <?php if(isset($_POST['nom'])) { echo 'value="'.$_POST['nom'].'" '; } ?>placeholder="Votre nom"></td>
			</tr>
			<?php
			if($captcha == "1") { ?>
				<tr>
					<td style="vertical-align: top;">
						<img src="include/class/captcha.php" alt="Captcha" id="captcha">
						<img src="images/reloadc.png" alt="Recharger l'image" title="Recharger l'image" style="cursor:pointer;position:relative;top:-7px;" onclick="document.images.captcha.src='include/class/captcha.php?id='+Math.round(Math.random(0)*1000)" />
					</td>
					<td style="vertical-align: top;">
						<input type="text" class="input_connexion2" name="captcha" placeholder="Recopiez le code ci-contre"><br>
						<input style="width:auto; font-weight:bold; padding:5px; color:#5C5C5C; " type="submit" value="Inscrivez-vous !">
					</td>
				</tr>
			<?php } else { ?>
				<tr>
					<td colspan="2"><input style="width:auto; font-weight:bold; padding:5px; color:#5C5C5C; " type="submit" value="Inscrivez-vous"></td>
				</tr>
			<?php }
			if($connect_serveur == true) {
			?>
			<tr>
				<td colspan="2">
					Attention, vous devez être connecté sur le serveur pour pouvoir vous inscrire sur le site (avec le même pseudo).
				</td>
			</tr>
			<?php } ?>
		</table>		
	</form>
</div>
<?php include("include/footer.php"); ?>