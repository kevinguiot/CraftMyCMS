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

$titre_page = "Règlement";
include("include/init.php");
include("include/header.php");
?>
<div id="content">
    <?php
    if($reglement == true) {
        echo file_get_contents('include/config/reglement.php');
    } else {
        msg("Le réglement est désactivé", 'r');
    } ?>
</div>
<?php include("include/footer.php"); ?>