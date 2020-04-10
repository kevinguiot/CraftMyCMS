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

$titre_page = "Ajouter une page";
include("../include/init.php");
if(!empty($_POST['page'])) {
	$name = $_POST['name'];
	$page = $_POST['page'];
	$fp = fopen("../$name.php","w+");
	fwrite($fp, $page);
	fclose($fp);
	header("location: ../$name.php");
}
include("header.php");
?>

<div id="content" class="ajoutpage">
	<?php
	if(!empty($_GET['msg'])) {
		$msg = $_GET['msg'];
		if(!empty($msg)) {
			if($msg == "slider:true") { echo '<div class="warning_v">Votre page a bien été créée</div>'; }
		}
	}
	?>
	<form method="post">
		Nom du fichier: <input type="text" name="name" value="votrepage">.php
		<textarea rows="20" name="page">
<?php
echo '<?php
$titre_page = "le titre de votre page";
include("include/init.php");
include("include/header.php");
?>

<div id="content">
Le contenu de votre page.
</div>

<?php include("include/footer.php"); ?>'
; ?>
		</textarea>
		<input type="submit"> Faite attention de ne pas supprimer un fichier déjà existant.
	</form>
</div>
<?php include("footer.php"); ?>