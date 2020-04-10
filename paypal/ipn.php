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

date_default_timezone_set('Europe/Paris');
$date = date("d/m/Y");
$heure = date("H:i:s");
include('../include/config/config.inc.php');

try {
    @$connexion = new PDO('mysql:host='.$serveur.';dbname='.$base.';charset=utf8', $user, $mdp, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
} catch (PDOException $e) {
    $erreur = "5";
}

if($_POST && (!empty($adressePaypal) || $adressePaypal!=null)) {
    $req = 'cmd=_notify-validate';
    foreach ($_POST as $key => $value) {
        $value = urlencode(stripslashes($value));
        $req .= "&$key=$value";
    }
    
    $header = "POST /cgi-bin/webscr HTTP/1.0\n";
    $header .= "Host: www.paypal.com\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\n";
    $header .= "Content-Length: " . strlen($req) . "\n\n";
    
    parse_str($_POST['custom'],$custom);
    $id = $custom['id'];
    $txn_id = $_POST['txn_id'];
    
    //Ouverture du socket
    $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
    
    //Ajout des logs
    $f = fopen($txn_id.'.txt', 'w+');
    
    $logs = print_r($_POST, true);
    if(!empty($errstr)) {
        $logs = "ERREUR: ".$errstr."\n\n".$logs;
    }
    
    fputs($f, $logs);
    fclose($f);
    
    if (!$fp) {
        $addPaiement = $connexion->prepare('INSERT INTO '.$prefixe.'commandes SET nb=:id_paiement, pseudo=:pseudo, date=:date, heure=:heure');
        $addPaiement->execute(array(
            'id_paiement' => $txn_id." (PayPal)<br><span style='color:#AA0303;'>ERREUR SOCKET: <strong>".$errstr,
            'pseudo' => $id,
            'date' => $date,
            'heure' => $heure
        ));
    } else {
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];

        fputs ($fp, $header . $req);
        while (!feof($fp)) {
            $res = fgets ($fp, 1024);
            if (strcmp ($res, "VERIFIED") == 0) {
                if ($payment_status == "Completed") {
                    if($adressePaypal == $receiver_email) {                       
                        $offrePaypalPrix = array(
                            '0' => $offrePaypal[0],
                            '2' => $offrePaypal[2],
                            '4' => $offrePaypal[4],
                        );
                        
                        if(in_array($payment_amount, $offrePaypalPrix)) {
                            $key = array_search($payment_amount, $offrePaypalPrix);
                            
                            $addPaiement = $connexion->prepare('INSERT INTO '.$prefixe.'commandes SET nb=:id_paiement, pseudo=:pseudo, date=:date, heure=:heure');
                            $addPaiement->execute(array(
                                'id_paiement' => $txn_id." (PayPal)",
                                'pseudo' => $id,
                                'date' => $date,
                                'heure' => $heure
                            ));
                            
                            $updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token + :token WHERE pseudo=:pseudo');
                            $updateMembre->execute(array(
                                'token' => $offrePaypal[$key+1],
                                'pseudo' => $id
                            ));
                        }
                        exit;
                    } else if (strcmp ($res, "INVALID") == 0) {}
                }
                fclose ($fp);
            }
        }
    }
}
?>