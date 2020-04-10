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

$connexion->query("DELETE FROM ".$prefixe."etape_temp");

$addLOCK = fopen('LOCK', 'w+');
fwrite($addLOCK, null);
fclose($addLOCK);

include('temp/bdd.php');
include('temp/site.php');
include('temp/page.php');

$config = '<?php
/* Ce fichier ne doit pas être édité manuellement. */

/* CONFIGURATION DE LA BASE DE DONNÉE */
$serveur = "'.$serveur.'"; 
$user = "'.$user.'"; 
$mdp = "'.$mdp.'"; 
$base = "'.$base.'"; 
$prefixe = "'.$prefixe.'";

/* CONFIGURATION DU SITE */
$titresite = "'.$titresite.'";
$slogan = "'.$slogan.'";
$description = "'.$description.'";
$keywords = "'.$keywords.'";
$background = "'.$background.'";
$favicon = "'.$favicon.'";
$logo = "'.$logo.'";
$facebook = "'.$facebook.'";
$twitter = "'.$twitter.'";
$youtube = "'.$youtube.'";
$email_contact = "'.$email_contact.'";
$theme = "default";
$connect_serveur = "'.$connect_serveur.'";
$banlist = "'.$banlist.'";
$reglement = "'.$reglement.'";
$captcha = "'.$captcha.'";
$maintenance = false;
$activeBlocInfos = true;
$activeBlocStats = true;
$activeJSONAPI = true;
$activeInventaire = false;
$skinHelm = true;
$activePlugins = true;

/* CONFIGURATION DE LA BOUTIQUE */
$activeStarpass = true;
$monnaie_site = "'.$monnaie_site.'";
$monnaie_serveur = "'.$monnaie_serveur.'";
$valeur = "'.$valeur.'";
$idp = "'.$idp.'";
$idd = "'.$idd.'";
$activeMoneyIG = false;
$activePaypal = false;
$adressePaypal = null;
$offrePaypal = array(null,null,null,null,null,null);

/* AUTRES PARAMETRES */
$permissionsModo = array(false,false,false,false,false,false,false,false,false,false);
$newsParPage = "0";
?>';
	
$addConfig = fopen('../include/config/config.inc.php', 'w+');
fwrite($addConfig, $config);
fclose($addConfig);

unlink('temp/site.php');
unlink('temp/bdd.php');
unlink('temp/page.php');

header("location: ../admin/finalisation.php");
?>