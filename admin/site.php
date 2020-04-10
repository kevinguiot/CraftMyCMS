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

$titre_page = "Paramètre du site";
include("../include/init.php");

if(!empty($_POST)) {
	$form = array('titresite', 'slogan', 'description', 'keywords', 'facebook', 'twitter', 'youtube');
	
	if(strlen($_POST['description']) > 180) {
		header("location: ?msg=description:false"); 
	} else {
		foreach($form as $value) {
			$_POST[$value] = str_replace("\n", " ", $_POST[$value]);
			$_POST[$value] = str_replace("\r", " ", $_POST[$value]);		
	
			modifConfig($value, secure($_POST[$value]));
		}
	
		if(!empty($_POST['mail'])) {
			if(verifmail($_POST['mail'])) {
				modifConfig('email_contact', secure($_POST['mail']));
			} else {
				header("location: ?msg=parametre:false"); 
				exit;
			}
		}
		
		if(!empty($_POST['background'])) {
			if(@!copy($_POST['background'], "../images/background.jpg")) {
				header("location: ?msg=image:false");
				exit;
			} else {
				modifConfig('background', secure($_POST['background']));
			}
		} else {
			modifConfig('background', null);
		}
		
		if(!empty($_POST['favicon'])) {
			if(@!copy($_POST['favicon'], "../images/favicon.ico")) {
				header("location: ?msg=image:false");
				exit;
			} else {
				modifConfig('favicon', secure($_POST['favicon']));
			}
		} else {
			modifConfig('favicon', null);
		}
		
		if(!empty($_POST['logo'])) {
			if(@!copy($_POST['logo'], "../images/logo.jpg")) {
				header("location: ?msg=image:false");
				exit;
			} else {
				modifConfig('logo', secure($_POST['logo']));
			}
		} else {
			modifConfig('logo', null);
		}
		
		header("location: ?msg=parametre:ok");
	}
	exit;
}

include("header.php");
?>

<div id="content" class="parametre_site">
	
	<?php
	msg("La description de votre site ne doit pas dépasser 180 caractères.", 'r', 'get', 'description:false');
	msg("Les paramètres du site ont bien été modifiées.", 'v', 'get', "parametre:ok");
	msg("Vous avez saisi une adresse mail incorrecte.", 'r', 'get', "parametre:false");
	msg("Impossible de changer ce paramètre. Veuillez vérifier les droits sur le dossier images.<br>Verifiez aussi que l'url de l'image soit correct.", 'r', 'get', "image:false");
	?>
	
	<form method="post">
		<h3>Paramètre généraux</h3>
		<table class="table table-bordered table-striped">
			<tr>
				<td>Le nom du site<sup>*</sup></td>
				<td><input type="text" name="titresite" value="<?php if(!empty($titresite)) { echo $titresite; } ?>"></td>
			</tr>
			<tr>
				<td>Le slogan du site*</td>
				<td><input type="text" name="slogan" value="<?php if(!empty($slogan)) { echo $slogan; }?>"></td>
			</tr>
			<tr>
				<td>
					La description du site<sup>*</sup><br>
					<small>Les retours à la ligne ne seront pas considérés.<br>Ne doit pas dépasser 180 caractères.</small>
				</td>
				<td>
					<textarea name="description" rows="4"><?php if(!empty($description)) { echo $description; }?></textarea>
				</td>
			</tr>
			<tr>
				<td>
					Adresse mail du site<sup>*</sup>
					<br><small>Utilisée pour envoyer des mails aux membres.</small>
				</td>
				<td><input type="text" value="<?php if(!empty($email_contact)) { echo $email_contact; } ?>" name="mail"></td>
			</tr>
			<tr>
				<td>
					Mots-clés du site<br>
					<small>Séparez vos mots-clés par une virgule.</small>
				</td>
				<td>
					<input type="text" name="keywords" value="<?php if(!empty($keywords)) { echo $keywords; }?>"><br>
				</td>
			</tr>
			<tr>
				<td>Background du site<br>
				<small><a href="http://www.craftmycms.fr/background/" target="_blank">Cliquez ici</a> pour trouver des backgrounds</small></td>
				<td><input type="text" name="background" placeholder="Url de l'image du background" value="<?php echo $background; ?>"></td>
			</tr>
			<tr>
				<td>Logo du site</td>
				<td><input type="text" name="logo" placeholder="Url de l'image du logo" value="<?php echo $logo; ?>"></td>
			</tr>
			<tr>
				<td>Icône du site (favicon)</td>
				<td><input type="text" name="favicon" placeholder="Url de l'image du favicon" value="<?php echo $favicon; ?>"></td>
			</tr>
			<tr>
				<td>Réseaux sociaux</td>
				<td>
					<input type="text" name="facebook" value="<?php if(!empty($facebook)) { echo $facebook; }?>" placeholder="Lien de votre page Facebook..."><br>
					<input type="text" name="twitter" value="<?php if(!empty($twitter)) { echo $twitter; }?>" placeholder="Lien de votre page Twitter..."><br>
					<input type="text" name="youtube" value="<?php if(!empty($youtube)) { echo $youtube; }?>" placeholder="Lien de votre chaîne Youtube..."><br>
				</td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Modifier les paramètres"></td>
			</tr>
		</table>
	</form>
</div>
<?php include("footer.php"); ?>