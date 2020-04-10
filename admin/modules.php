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

$titre_page = "Editer les modules";
include("../include/init.php");

if(!empty($_POST)) {
	if(!empty($_POST['modules'])) {
		$data = $_POST['modules'];
		$fp = fopen('../include/config/modules.php', 'w+');
	}
	if(fwrite($fp, $data)) {
		fclose($fp);
		header("location: ?msg=true");
	} else {
		header("location: ?msg=false");
	}
}

include("header.php");
?>
<div id="content" class="onglet">
	<?php
	msg("Vos modules ont bien été modifiés.", 'v', 'get', 'true');
	msg("Impossible de réécrire sur le fichier include/config/modules.php.<br>Veuillez regarder les droits du fichier.", 'r', 'get', 'false');
	?>
	<h3>Modifier vos modules:</h3><br>
	<form method="post">
		<textarea name="modules" rows="10">
			<?php echo file_get_contents("../include/config/modules.php"); ?>
		</textarea>
		<input type="submit"> Vous devez passer par cette page pour modifier les modules.<br>
		Vous ne devez pas éditer le fichier manuellement.
	</form>	
</div>
<?php include("footer.php"); ?>