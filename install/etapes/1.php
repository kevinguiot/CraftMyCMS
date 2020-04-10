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

if($_POST) {
	$validActivation = sendToCraftMyCMS('validActivation', array('mail' => $_POST['mail'], 'clef' => $_POST['clef']));

	switch ($validActivation) {
		case '0':
			$erreurv = "Cette clef d'activation n'existe pas, veuillez réessayer.<br>Vérifiez aussi que votre clef d'activation corresponde au fichier d'activation du CMS (CMS_INFO).";
			break;
		case '1':
			header('location: index.php?msg=cms:true');
			break;
		case '2':
			$erreurv = "Cette clef d'activation est déjà utilisée. Pour qu'elle soit de nouveau utilisable, veuillez réinitialiser le CMS correspondant à cette clef, via votre espace membre, sur CraftMyCMS.fr.";
			break;
		default:
			$erreurv = getMessage($validActivation);
			break;
	}
}

include('header.php');
?>

<div id="content">
	
	<?php
	if(!empty($erreurv)) {
		msg($erreurv, 'r');
	}
	?>

	<p class="thanks">Merci à vous d'avoir acheté <strong>CraftMyCMS</strong> version Perso. Nous espérons que ce CMS vous sera très utile pour votre serveur. En cas de soucis, merci de nous le signaler en passant sur le <a href="http://www.craftmycms.fr/support/">support du CMS</a>.</p><br>
	<h3>Compatibilité de votre hébergeur</h3>
	<table class="table table-bordered table-striped compatibilite">
		<tr>
			<td>Version de PHP</td>
			<td>Extention cURL</td>
			<td>Extension ionCube</td>
			<td>Fonction allow_url_fopen</td>
			<td>Droits d'accès aux fichiers</td>
		</tr>
		<tr>
			<td><?php if(PHP_VERSION>="5.3") { echo $compatible; $version = true; } else { echo $non_compatible; $version = false; } ?></td>
			<td><?php if(iscurlinstalled()== true) { echo $compatible; $curl = true; } else { echo $non_compatible; $curl = false; } ?> </td>
			<td><?php if(isioncubeinstalled()== true) { echo $compatible; $ioncube = true; } else { echo $non_compatible; $ioncube = false; } ?> </td>
			<td><?php $allow = @file_get_contents('http://www.craftmycms.fr/index.php');
				if($allow) { echo $compatible; $fonction = true; } else { echo $non_compatible; $fonction = false; } ?>
			</td>
			<td><?php
				if(is_writable("../") AND is_writable("../include/config/") AND is_writable("../images/")) {
					echo $compatible;
					$is_writable = true;
				} else {
					echo $non_compatible;
					$is_writable = false;
				}
				?>
			</td>
		</tr>
	</table><br>
	<h3>Activation de votre CMS</h3>
	<?php
	if(strstr($getActivation, 'error')) {
		$erreurv = getMessage($getActivation);
		msg($erreurv, 'r');
	} else {
		if($is_writable == true &&
		   $curl == true &&
		   $ioncube == true &&
		   $fonction == true &&
		   $version == TRUE) {
			?>
			<form method="post">
				<table class="table table-bordered table-striped">
					<tr>
						<td style="width:30%;">Votre adresse mail d'activation</td>
						<td style="width:70%;"><input type="text" name="mail" value="<?php if(!empty($_POST['mail'])) { echo $_POST['mail']; } ?>"></td>
					</tr>
					<tr>
						<td>Votre clef d'activation</td>
						<td><input type="text" name="clef" placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" value="<?php if(!empty($_POST['clef'])) { echo $_POST['clef']; } ?>"></td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" class="button_stone" value="Activer votre CMS" ></td>
					</tr>
				</table>
			</form>
			<?php
		} else {
			msg("Vous ne pouvez pas installer CraftMyCMS, veillez à ce que votre hébergeur web soit compatible.", 'r');
		}
	}
	?>
</div>