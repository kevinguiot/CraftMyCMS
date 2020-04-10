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

$titre_page = "Editer le règlement";
include("../include/init.php");
if(!empty($_POST['reglement'])) {
	$addRegement = fopen('../include/config/reglement.php', 'w+');
	if(fwrite($addRegement, $_POST['reglement'])) {
		fclose($addRegement);
		header("location: ?msg=reglement:true");
		exit;
	} else {
		header("location: ?msg=reglement:false");
		fclose($addRegement);
	}
}
include("header.php");
?>
<div id="content" class="reglement">
	
	<?php
	msg("Votre règlement a bien été modifié.", 'v', 'get', 'reglement:true');
	msg("Impossible de modifier le règlement.", 'r', 'get', 'reglement:false');
	?>
	
    <p>Editez le réglement de votre CMS via cette page<br>Le code HTML est autorisé</p><br>
    <form method="post">
        <textarea rows="15" name="reglement"><?php echo file_get_contents('../include/config/reglement.php'); ?></textarea>
        <input type="submit">
    </form>
 </div>
</div>
<?php include("footer.php"); ?>