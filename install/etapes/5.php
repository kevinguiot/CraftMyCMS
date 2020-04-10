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
	$email = $_POST['email'];
	$nom_serveur = secure($_POST['nom_serveur']);
	$slogan_site = secure($_POST['slogan_site']);
	$keywords = secure($_POST['keywords']);
	
	$description = str_replace("\n", " ", $_POST['description']);
	$description = str_replace("\r", " ", $description);

	if(empty($nom_serveur) OR empty($slogan_site) OR empty($description)) {
		$erreurv = 'Le nom du serveur, slogan du site et la description doivent obligatoirement être remplis.';
	}
	elseif(!verifmail($email)) {
		$erreurv = 'Vous devez saisir une adresse e-mail valide.';
	}
	elseif(strlen($description) > 180) {
		$erreurv = 'La description de votre site ne doit pas dépasser 180 caractères.';
	}
	else {
		$description = secure($_POST['description']);
		
		$background = $_POST['background'];
		$favicon = $_POST['favicon'];
		$logo = $_POST['logo'];
		$twitter = secure($_POST['twitter']);
		$facebook = secure($_POST['facebook']);
		$youtube = secure($_POST['youtube']);
		if(!empty($background)) {
			if(!copy($_POST['background'], "../images/background.jpg")) {
				$erreurv = 'Impossible de changer le background. Veuillez vérifier les droits sur le dossier images.';
			}
		}
		if(!empty($favicon)) {
			if(!copy($_POST['favicon'], "../images/favicon.jpg")) {
				$erreurv = 'Impossible de changer le favicon. Veuillez vérifier les droits sur le dossier images.';
			}
		}
		if(!empty($logo)) {
			if(!copy($_POST['logo'], "../images/logo.jpg")) {
				$erreurv = 'Impossible de changer le logo. Veuillez vérifier les droits sur le dossier images.';
			}
		}
	}
	if(empty($erreurv)) {		
		$data ='<?php
$email_contact = "'.$email.'";
$titresite = "'.$nom_serveur.'";
$slogan = "'.$slogan_site.'";
$description = "'.$description.'";
$keywords = "'.$keywords.'";
$facebook = "'.$facebook.'";
$twitter = "'.$twitter.'";
$youtube = "'.$youtube.'";
$background = "'.$background.'";
$logo = "'.$logo.'";
$favicon = "'.$favicon.'";
?>';
		$fp = fopen("temp/site.php","w+");
		fputs($fp, $data);
		fclose($fp);
		$connexion->query("UPDATE ".$prefixe."etape_temp SET etape5='1'");
		header("location: index.php");
	}
}
include("header.php");
?>

<div id="content" class="parametre_site">
    <?php
	if(!empty($erreurv)) {
		echo '<div class="warning_r">'.$erreurv.'</div>';
	}
    ?>
	<p>Vous devez désormais configurer quelques informations de votre futur site.<br>Ces données pourront être modifiées par la suite via le panel d'administration.</p>
	<form method="post">
        <table class="table table-bordered table-striped">
			<tr>
				<td>
					Le nom du site<sup>*</sup><br>
				</td>
				<td>
					<input type="text" name="nom_serveur" value="<?php if(!empty($nom_serveur)) { echo $nom_serveur; }?>">
				</td>
			</tr>
			<tr>
				<td>Le slogan du site<sup>*</sup></td>
				<td><input type="text" name="slogan_site" value="<?php if(!empty($slogan_site)) { echo $slogan_site; }?>"></td>
			</tr>
			<tr>
				<td>
					Description du site<sup>*</sup><br>
					<small>Les retours à la ligne ne seront pas considérés.<br>Ne doit pas dépasser 180 caractères.</small>
				</td>
				<td>
					<textarea name="description"><?php if(!empty($description)) { echo $description; }?></textarea>
				</td>
			</tr>
			<tr>
				<td>Adresse mail du site<sup>*</sup><br><small>Utilisée pour envoyer des mails aux membres.</small></td>
				<td><input type="text" name="email" value="<?php if(!empty($_SESSION['emailAdmin'])) { echo $_SESSION['emailAdmin']; } ?>"></td>
			</tr>
			<tr>
				<td>
					Mots-clés du site<br>
					<small>
						Séparez vos mots-clés par une virgule.
					</small>
				</td>
				<td>
					<input type="text" name="keywords" value="<?php if(!empty($keywords)) { echo $keywords; }?>">
				</td>
			</tr>
			<tr>
				<td>Background du site (URL)<br>
				<small><a href="http://www.craftmycms.fr/background/" target="_blank">Cliquez ici</a> pour trouver des backgrounds</small>.</td>
				<td><input type="text" name="background"></td>
			</tr>
			<tr>
				<td>Logo site (URL)</td>
				<td><input type="text" name="logo"></td>
			</tr>
			<tr>
				<td>Icone du site (URL)</td>
				<td><input type="text" name="favicon"></td>
			</tr>
			<tr>
				<td>Réseaux sociaux</td>
				<td>
					<input type="text" name="facebook" placeholder="Lien de votre page Facebook..."><br>
					<input type="text" name="twitter" placeholder="Lien de votre page Twitter..."><br>
					<input type="text" name="youtube" placeholder="Lien de votre chaîne Youtube..."><br>
				</td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Enregistrer ces nouveaux paramètres"></td>
			</tr>
		</table>
	</form>
</div>
<?php include("footer.php"); ?>