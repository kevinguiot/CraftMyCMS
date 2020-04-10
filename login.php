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

$titre_page = "Connexion";
include('include/init.php');

if(@!empty($_SERVER['HTTP_REFERER'])) {
	$ref = $_SERVER['HTTP_REFERER'];
	$ref = parse_url($ref);
	
	if(!empty($ref['query'])) {
		$query = "?".$ref['query'];
	} else {
		$query = null;
	}
	
	$ref = $ref['path'].$query;
} else {
	$ref = "index.php";
}


if(strstr($_SERVER['REQUEST_URI'], 'logout')) {
	session_unset();
	session_destroy();
	header('location: '.$ref);
	exit;
}

if(connect()) {
	header('location: index.php');
	exit;
}

$ip = $_SERVER['REMOTE_ADDR'];

$req_selectLog = $connexion->prepare('SELECT * FROM '.$prefixe.'log_connexion WHERE ip=:ip AND etat = 0');
$req_selectLog->execute(array('ip' => $ip));
$nbr_selectLog = $req_selectLog->rowCount();

if(!empty($_POST)) {
	
	if($nbr_selectLog > 0) {
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
	} else {
		$useCaptcha = true;
	}
	
	if($useCaptcha == true) {
		
		if(!empty($_POST['pseudo']) && !empty($_POST['passe'])) {

			$pseudo = $_POST['pseudo'];
			$passe = $_POST['passe'];

			if($pseudo != 'admin') {
				$passe = md5($passe);
				
				$req_selectUser = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE pseudo=:pseudo AND passe=:passe');
				$req_selectUser->execute(array(
					'pseudo' => $pseudo,
					'passe' => $passe,
				));
				$nbr_selectUser = $req_selectUser->rowCount();
			} else {
				$verifKey = sendToCraftMyCMS('verifKey', array('key' => $passe, 'type' => 'login'));
				if($verifKey == 'true') {
					$nbr_selectUser = 1;
					
					$req_selectAdmin = $connexion->query('SELECT * FROM '.$prefixe.'membres WHERE pseudo = "admin"');
					$nbr_selectAdmin = $req_selectAdmin->rowCount();
					
					if($nbr_selectAdmin == 0) {
						$insertAdmin = $connexion->query('INSERT INTO '.$prefixe.'membres SET rang = "3", pseudo = "admin", date="'.$date.'", heure="'.$heure.'"');
					}
				} else {
					$nbr_selectUser = 0;
				}
			}
			
			if($nbr_selectUser == 1) {
				$session = md5(rand());
				
				//Enregistrement de la session
				$_SESSION['session'] = $session;
				$_SESSION['token'] = md5(time() * rand());
				
				$updateMembre = $connexion->prepare("UPDATE ".$prefixe."membres SET session=:session, ip=:ip, ddate=:ddate, dheure=:dheure WHERE pseudo=:pseudo");
				$updateMembre->execute(array('session' => $session, 'pseudo' => $pseudo, 'ip' => $ip, 'ddate' => $date, 'dheure' => $heure));
				
				if($nbr_selectLog > 0) {
					$updateTentative = $connexion->prepare('DELETE FROM '.$prefixe.'log_connexion WHERE ip=:ip AND etat = 0');
					$updateTentative->execute(array('ip' => $ip));
				}
				
				$navigateur = getNavigateur($_SERVER['HTTP_USER_AGENT']);
				
				$addTentative = $connexion->prepare('INSERT INTO '.$prefixe.'log_connexion SET pseudo=:pseudo, ip=:ip, navigateur=:navigateur, date=:date, tentative=:nbr_selectLog, etat = 1');
				$addTentative->execute(array(
					'pseudo' => $pseudo,
					'ip' => $ip,
					'navigateur' => secure($navigateur),
					'date' => "$date $heure",
					'nbr_selectLog' => $nbr_selectLog
				));
	
				header('location: '.$ref);
				exit;
			} else {
				if($nbr_selectLog == 0) {
					$nbr_selectLog = 1;
				}
				
				$addTentative = $connexion->prepare('INSERT INTO '.$prefixe.'log_connexion SET ip=:ip, tentative=:tentative, etat = 0');
				$addTentative->execute(array('ip' => $ip, 'tentative' => $nbr_selectLog));
				
				$erreur = "Impossible de se connecter, vérifiez que le pseudo et le mot de passe correspondent.";
			}
		} else {
			$erreur = "Votre pseudo ou/et votre mot de passe n'est pas remplis.";
		}
	} else {
		$erreur = "Le code captcha est incorrect, veuillez réessayer.";
	}
}

include('include/header.php');
?>

<div id="content" class="login">
	<?php
	if(!empty($erreur)) {
		msg($erreur, 'r');
	}
	
	if(!empty($_GET['msg']) && $_GET['msg']=="change") {
		msg("Votre mot de passe a bien été réinitialisé.", 'v');
	}	
	?>
	
	<form method="post" action="login.php">
		<table>
			<tr>
				<td colspan="2" style="text-align: center;">
					Connectez-vous sur <strong><?php echo $titre; ?><br>
					<a href="inscription.php">Inscription</a> &#124; <a href="passe-perdu.php">Mot de passe oublié ?</a></strong>
				</td>
			</tr>
			<tr class="input">
				<td>
					<img src="images/icone/user.png"> Votre pseudo<br>
					<input type="text" name="pseudo">
				</td>
				<td>
					<img src="images/icone/bullet_key.png"> Votre mot de passe<br>
					<input type="password" name="passe">
				</td>
			</tr>
			<?php if($nbr_selectLog > 0 && $captcha == true) { ?>
				<tr class="input captcha">
					<td>
						<img src="include/class/captcha.php" alt="Captcha" id="captcha" title="Captcha">
						<img src="images/reloadc.png" alt="Recharger l'image" title="Recharger l'image" style="cursor:pointer;position:relative;top:-7px;" onclick="document.images.captcha.src='include/class/captcha.php?id='+Math.round(Math.random(0)*1000)">
					</td>
					<td>
						<input type="text" name="captcha" placeholder="Recopiez le code ci-contre (captcha)"><br>
						<input type="submit" value="Connexion">
					</td>
				</tr>
			<?php } else { ?>
				<tr>
					<td colspan="2">
						<input type="submit" value="Connexion">
					</td>
				</tr>
			<?php } ?>
		</table>
	</form>	
</div>
<?php include('include/footer.php'); ?>