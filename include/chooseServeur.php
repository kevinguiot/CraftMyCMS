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

//On change le serveur désiré
if(!empty($_GET['serveur'])) {
    include('init.php');
    
    $req_selectServeur = $connexion->prepare('SELECT * FROM '.$prefixe.'serveurs WHERE id=:id');
    $req_selectServeur->execute(array(
        'id' => $_GET['serveur']
    ));
    $nbr_selectServeur = $req_selectServeur->rowCount();
    
    if($nbr_selectServeur == 1) {
        //On définit le serveur à charger
        setcookie('serveur', $_GET['serveur'], time()+259200, '/', $site);
        
        die('success');
    }
}
?>