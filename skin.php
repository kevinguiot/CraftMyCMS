<?php
//****************************************************
// Auteur: CraftMyCMS (Kévin GUIOT)
// CraftMyCMS PERSO 4.0.2.2
// Copyright © 2012 - 2015
// Sortie: 26 avril 2015 à 19h30
// Contact: contact@craftmycms.fr
//
// http://www.craftmycms.fr/
// http://developpeur.craftmycms.fr/changelog/cms.php#k0345han1l
//****************************************************

include('include/config/config.inc.php');

if(!empty($_GET['pseudo'])) {
	$pseudo = $_GET['pseudo'];

	if(strstr($pseudo, '/')) {
		$pseudo = explode('/', $pseudo);
		$size = (int) trim($pseudo[1]);
		$pseudo = trim($pseudo[0]);
	} else {
		if(!empty($_GET['size'])) {
			$size = $_GET['size'];
			if(!is_numeric($size)) {
				$size = '64';
			}
		} else {
			$size = '64';
		}
	}
} else {
	$pseudo = 'char.png';
	$size = '64';
}

header('Content-Type: image/png');

$provenanceSkin2D = str_replace('{PSEUDO}', $pseudo, $provenanceSkin2D);
$provenanceSkin2D = str_replace('{SIZE}', $size, $provenanceSkin2D);

if($activeSkin == true) {
	$skin = imagecreatefromstring(file_get_contents($provenanceSkin2D));
	if ($skin == false) {
		$filename = 'images/skin.png';
		$skinDefault = imagecreatefrompng($filename);
		
		$skin = imagecreatetruecolor($size,$size);
		imagecopyresampled($skin,$skinDefault,0,0,8,8,$size,$size,8,8);
	}
} else {
	$filename = 'images/skin.png';
	$skinDefault = imagecreatefrompng($filename);
	
	$skin = imagecreatetruecolor($size,$size);
	imagecopyresampled($skin,$skinDefault,0,0,8,8,$size,$size,8,8);

}

imagepng($skin);
imagedestroy($skin);
?>