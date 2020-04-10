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

if(defined('AUTH_ID') && !defined('SERVEUR_ID')) {

    //Liste des serveurs
    $req_selectServeurs = $connexion->query('SELECT * FROM '.$prefixe.'serveurs WHERE etat = "1" ORDER BY nom ASC LIMIT 1');
    $nbr_selectServeurs = $req_selectServeurs->rowCount();
    
    if($nbr_selectServeurs > 0) {
        //Se connecte au serveur par défaut
        $sql_selectServeur = 'SELECT * FROM '.$prefixe.'serveurs WHERE etat = "1" ORDER BY nom ASC LIMIT 1';
        
        if(empty($_COOKIE['serveur'])) {
            $req_selectServeur = $connexion->query($sql_selectServeur);
        } else {
            $req_selectServeur = $connexion->prepare('SELECT * FROM '.$prefixe.'serveurs WHERE id=:id AND etat = 1');
            $req_selectServeur->execute(array(
                'id' => $_COOKIE['serveur'],
            ));
            $nbr_selectServeur = $req_selectServeur->rowCount();
            
            if($nbr_selectServeur == 0) {
                $req_selectServeur = $connexion->query($sql_selectServeur);
            }
        }
        
        $selectServeur = $req_selectServeur->fetch();
        DEFINE('SERVEUR_ID', $selectServeur['id']);
        
        if($activeJSONAPI == true) {
            //On se connecte au serveur via JSONAPI.
            $api = new JSONAPI(
                $selectServeur['external_ip'],
                $selectServeur['port'],
                $selectServeur['user'],
                $selectServeur['password'],
                $selectServeur['salt']
            ); 
            
            //On détermine si la connexion est possible.
            $getPlayerCount = $api->call('getPlayerCount');
            
            if($getPlayerCount['result'] == 'success') {
                $etatJSONAPI = true;
                $playerlimit = $api->call("getPlayerLimit");
                
                $playercount = $getPlayerCount["success"];
                $playerlimit = $playerlimit["success"];
                
                $players = $api->call("getPlayerNames");
            } else {
                $etatJSONAPI = false;
            }
        } else {
            $etatJSONAPI = false;
        }
    } else {
        $etatJSONAPI = false;
    }
}
?>