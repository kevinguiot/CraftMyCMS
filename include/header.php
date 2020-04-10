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

if(!defined('AUTH_ID') OR AUTH_ID != true) {
	exit;
}

//On définit le titre du site
$titre = $titresite;

//On gère les visites, et si il y a un nouveau visiteur, on incrémente dans la table visites.
$req_nbVisite = $connexion->prepare('SELECT * FROM '.$prefixe.'visites WHERE date=:date AND ip=:ip');
$req_nbVisite->execute(array(
	'date' => $date,
	'ip' => $_SERVER['REMOTE_ADDR']
));
$nbVisite = $req_nbVisite->rowCount();

if($nbVisite == "0") {
	$addVisite = $connexion->prepare("INSERT INTO ".$prefixe."visites SET date=:date, ip=:ip");
	$addVisite->execute(array(
		'date' => $date,
		'ip' => $_SERVER['REMOTE_ADDR']
	));
} 

if(empty($hideHeader) || $hideHeader != true) {
	//Permet d'établir le serveur actuel.
	include('class/getServeur.php');
} else {
	$nbr_selectServeurs = 0;
	$etatJSONAPI = false;
}

//Gère les requêtes SQL pour la sidebar.
$req_nbrMembre = $connexion->query('SELECT * FROM '.$prefixe.'membres ORDER BY id DESC');
$nbrMembre = $req_nbrMembre->rowCount();
$lastMembre = $req_nbrMembre->fetch();
$lastMembre = $lastMembre['pseudo'];

$req_nbrMembreA = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE date=:date ORDER BY id DESC');
$req_nbrMembreA->execute(array(
	'date' => $date
));

$nbrMembreA = $req_nbrMembreA->rowCount();
$req_nbrVisiteT = $connexion->query('SELECT * FROM '.$prefixe.'visites');
$nbrVisiteT = $req_nbrVisiteT->rowCount();
$req_nbrVisiteA = $connexion->prepare('SELECT * FROM '.$prefixe.'visites WHERE date=:date');
$req_nbrVisiteA->execute(array(
	'date' => $date
));
$nbrVisiteA = $req_nbrVisiteA->rowCount();

if(connect()) {
	$req_selectAchat = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_liste WHERE pseudo=:pseudo AND etat = 0');
	$req_selectAchat->execute(array(
		'pseudo' => USER_PSEUDO,
	));
	$nbr_selectAchat = $req_selectAchat->rowCount();
	if($nbr_selectAchat > 0) {
		$notification = ' <span class="notification">'.$nbr_selectAchat.'</span>';
	}
}

//Inclus le fichier header & sidebar du thème.
include('style/'.$theme.'/file/header.php');

if(empty($hideSidebar)) {
	include('style/'.$theme.'/file/sidebar.php');
}
?>