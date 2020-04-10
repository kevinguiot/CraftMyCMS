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

$site = $_SERVER["SERVER_NAME"];
$www = "www.";
$pattern = "#(?:^(".$www.")[^0-9])|(?:[^0-9](".$www.")[^0-9])|(?:[^0-9](".$www.")$)#";
if(preg_match_all($pattern,$site,$matches)) {
  $site = $_SERVER["SERVER_NAME"];
  $site = str_replace("www.", "", $site);
}

//Défini l'identifiant de la MAJ
DEFINE('MAJ_CMS', '3ea1a7505194e632a33246e1c7a1a0ee');

function sendToCraftMyCMS($method, $array = null, $error = false) {
  $erreurSystem = 'r5tevk6qjg';

  //On retourne l'erreur
  $response = $erreurSystem;

  //On traite l'erreur
  if(empty($result) || $error == true) {
    $result = 'error=>'.$response;
  }

  //On peut retourner la réponse
  return $result;
}

function getMessage($error) {
  $error = str_replace('error=>', null, $error);

  if(strstr($error, 'msg:')) {
    $error = strstr($error, 'msg:');
    $error = str_replace('msg:', null, $error);
  } else {

    switch ($error) {
      case 'r5tevk6qjg':
      $error = "Les services de CraftMyCMS sont actuellement indisponibles, veuillez réessayer ultérieurement.<br>Vous pouvez aussi contacter le support de CraftMyCMS: <a target=\"_blank\" href=\"http://www.craftmycms.fr/support/\">http://www.craftmycms.fr/support/</a>.";
      break;
      case '5tufc63jk2':
      $error = "Votre site n'arrive pas à se connecter aux services de CraftMyCMS (CraftMyCMS.fr), veuillez vérifier que votre connexion internet soit bien établie.";
      break;
      case '2yrslevgdu':
      $error = "Les services proposés par CraftMyCMS sont actuellement en maintenance.<br>Veuillez réessayer dans quelques minutes.";
      break;
      case 'false':
      $error = "Vous n'avez pas les droits nécessaires pour accéder à ce contenu.<br>Merci de contacter le support de CraftMyCMS.fr: <a target=\"_blank\" href=\"http://www.craftmycms.fr/support/\">http://www.craftmycms.fr/support/</a>.";
      break;
      default:
      $error = "Votre requête a échoué, veuillez réessayer ultérieuement.<br>Vous pouvez aussi contacter le support de CraftMyCMS: <a target=\"_blank\" href=\"http://www.craftmycms.fr/support/\">http://www.craftmycms.fr/support/</a>.";
      break;
    }
  }

  return $error;
}
?>
