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

$titre_page = "Récupération du mot de passe";
include('include/init.php');

if(connect()) {
	header('location: index.php');
	exit;
}

if(!empty($_POST)) {
	if(!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['key'])) {
		$req_selectKey = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE email=:email AND oublie=:key');
		$req_selectKey->execute(array(
			'email' => $_POST['email'],
			'key' => $_POST['key']
		));
		$nbr_selectKey = $req_selectKey->rowCount();
		
		if($nbr_selectKey == 1) {
			$updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET passe=:passe, oublie=:oublie WHERE email=:email');
			$updateMembre->execute(array(
				'passe' => md5($_POST['password']),
				'oublie' => null,
				'email' => $_POST['email']
			));
			header('location: login.php?msg=change');
			exit;
		}
	}
	
	if(!empty($_POST['pseudo']) && !empty($_POST['email'])) {
		
		if($captcha == true) {
			if(!empty($_POST['captcha'])) {
				$captchaCode = strtoupper($_POST['captcha']);
				$captchaCode = md5($captchaCode);

				if($captchaCode == $_SESSION['captcha']) {
					$useCaptcha = true;
				} else {
					$useCaptcha = false;
				}
			} else {
				$useCaptcha = null;
			}
		} else {
			$useCaptcha = true;
		}

		if($useCaptcha == true) {	
		
		
		//if(!empty($_POST['captcha'])) {
			//$captchaCode = strtoupper($_POST['captcha']);
			//$captchaCode = md5($captchaCode);
			//
			//if($captchaCode == $_SESSION['captcha']) {
				$req_selectMembre = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE pseudo=:pseudo AND email=:email');
				$req_selectMembre->execute(array(
					'pseudo' => $_POST['pseudo'],
					'email' => $_POST['email']
				));
				$nbr_selectMembre = $req_selectMembre->rowCount();
				
				if($nbr_selectMembre == 1) {
					$key = sha1(rand());
					
					$selectMembre = $req_selectMembre->fetch();
					
					$updateMembre = $connexion->prepare("UPDATE ".$prefixe."membres SET oublie=:oublie WHERE email=:email");
					$updateMembre->execute(array(
						'oublie' => $key,
						'email' => $selectMembre['email']
					));
					
					$headers ='From: '.$titresite.'<'.$mail_admin.'>'."\n"; 
					$headers .='Reply-To: '.$mail_admin.''."\n"; 
					$headers .='Content-Type: text/html; charset="utf-8"'."\n"; 
					$headers .='Content-Transfer-Encoding: 8bit'; 
					$message ='Voici le code de sécurité: <strong>'.$key.'</strong><br>
					Vous devez entrer ce code pour pouvoir modifier votre mot de passe.<br><br>
					<a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?email='.$selectMembre['email'].'&key='.$key.'">Cliquez ici pour modifier votre mot de passe</a>.'; 
					if(mail($selectMembre['email'], 'Nouveau mot de passe', $message, $headers)) {
						$mailEnvoye = true;
						$msg = "Veuillez consulter votre boîte mail, un message avec un code de confirmation a été envoyé. Saisissez-le ci-dessous pour continuer la réinitialisation de votre mot de passe.";
					} else  { 
						$erreur = 'Ce site ne peut ne pas envoyer de mail. Veuillez contacter l\'administrateur du site.';
					}
				} else {
					$erreur = "Le pseudo et l'email ne correspondent pas.";
				}
		//	} else {
		//		$erreur = "Le code captcha est incorrect, veuillez réessayer.";
		//	}
		//} else {
		//	$erreur = "Le code captcha est incorrect, veuillez réessayer.";
		//}
		} else {
			$erreur = "Le code captcha est incorrect, veuillez réessayer.";
		}
	} else {
		$erreur = "Vous devez renseigner votre pseudo et votre adresse e-mail.";
	}

}

include('include/header.php');
?>

<div id="content" class="login">
	<?php
	if(!empty($erreur)) { msg($erreur, 'r'); }
	if(!empty($msg)) { echo msg($msg, 'v'); }
	?>
	
	<form method="post">
		<table>
			<tr>
				<td colspan="2" style="text-align: center;">
					<strong>Procédure de récupération de mot de passe</strong><br>
					Un code vous sera envoyé dans votre boîte mail.
				</td>
			</tr>
			
			<?php
			if((!empty($mailEnvoye) && $mailEnvoye == true) || (!empty($_GET['email']) && !empty($_GET['key']))) {
				if(!empty($_POST['email'])) {
					$email = $_POST['email'];
				} elseif(!empty($_GET['email'])) {
					$email = $_GET['email'];
				} else {
					$email = null;
				}
				
				if(!empty($_GET['key'])) {
					$key = $_GET['key'];
				} else {
					$key = false;
				}
				?>
				<tr class="input">
					<td>
						<img src="images/icone/user.png"> Votre adresse e-mail<br>
						<input type="text" name="email" value="<?php echo $email; ?>" autocomplete="off">
					</td>
					<td>
						<img src="images/icone/bullet_key.png"> Clef de sécurité envoyée<br>
						<input type="text" name="key" value="<?php echo $key; ?>" autocomplete="off">
					</td>
				</tr>
				
				<tr class="input captcha">
					<td style="text-align: left;">
						<img src="images/icone/bullet_key.png"> Nouveau mot de passe<br>
						<input type="password" name="password" autocomplete="off">
					</td>
					<td style="vertical-align: middle;">
						<input type="submit" value="Réinitialiser le mot de passe">
					</td>
				</tr>
				<?php
			} else {
				if(!empty($_POST['email'])) {
					$email = $_POST['email'];
				} else {
					$email = null;
				}
				
				
				if(!empty($_POST['pseudo'])) {
					$pseudo = $_POST['pseudo'];
				} else {
					$pseudo = null;
				}				
				?>
				<tr class="input">
					<td>
						<img src="images/icone/user.png"> Votre pseudo<br>
						<input type="text" name="pseudo" value="<?php echo $pseudo; ?>">
					</td>
					<td>
						<img src="images/icone/email.png"> Votre adresse e-mail<br>
						<input type="email" name="email" value="<?php echo $email; ?>">
					</td>
				</tr>
				
				<?php
				if($captcha == true) { ?>
					<tr class="input captcha">
						<td>
							<img src="include/class/captcha.php" alt="Captcha" id="captcha" title="Captcha">
							<img src="images/reloadc.png" alt="Recharger l'image" title="Recharger l'image" style="cursor:pointer;position:relative;top:-7px;" onclick="document.images.captcha.src='include/class/captcha.php?id='+Math.round(Math.random(0)*1000)">
						</td>
						<td>
							<input type="text" name="captcha" placeholder="Recopiez le code ci-contre (captcha)"><br>
							<input type="submit" value="Continuer...">
						</td>
					</tr>
				<?php } else { ?>
					<tr>
						<td colspan="2">
							<input type="submit" value="Continuer...">
						</td>
					</tr>
				<?php }
			} ?>			
		</table>
	</form>	
</div>
<?php include('include/footer.php'); ?>