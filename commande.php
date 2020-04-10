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

$ident=$idp=$ids=$idd=$codes=$code1=$code2=$code3=$code4=$code5=$datas='';
$titre_page = "Commande";
include("include/init.php");

if(strstr($_SERVER['REQUEST_URI'], 'verif')) {
    if(connect()) {
        $ident=$idp.";".$ids.";".$idd;
        if(isset($_POST['code1'])) $code1 = $_POST['code1'];
        if(isset($_POST['code2'])) $code2 = ";".$_POST['code2'];
        if(isset($_POST['code3'])) $code3 = ";".$_POST['code3'];
        if(isset($_POST['code4'])) $code4 = ";".$_POST['code4'];
        if(isset($_POST['code5'])) $code5 = ";".$_POST['code5'];
        $codes=$code1.$code2.$code3.$code4.$code5;
        if(isset($_POST['DATAS'])) $datas = $_POST['DATAS'];
        $ident=urlencode($ident);
        $codes=urlencode($codes);
        $datas=urlencode($datas);
        $get_f=@file("http://script.starpass.fr/check_php.php?ident=$ident&codes=$codes&DATAS=$datas");
        if(!$get_f) {
            exit("Votre serveur n'a pas accès au serveur de StarPass, merci de contacter votre hébergeur.");
        }
        $tab = explode("|",$get_f[0]);
        if(!$tab[1]) $url = "http://script.starpass.fr/error.php";
        else $url = $tab[1];
        $pays = $tab[2];
        $palier = urldecode($tab[3]);
        $id_palier = urldecode($tab[4]);
        $type = urldecode($tab[5]);
        if(substr($tab[0],0,3) != "OUI") {
            header("Location: commande.php?msg=code:false");
            exit;
        } else {
            $ajoutCommande = $connexion->prepare('INSERT INTO '.$prefixe.'commandes SET nb=:nb, pseudo=:pseudo, date=:date, heure=:heure');
            $ajoutCommande->execute(array(
                'nb' => rand(1000000000, 9999999999),
                'pseudo' => $pseudo,
                'date' => $date,
                'heure' => $heure
            ));
            $updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token + :token WHERE pseudo=:pseudo');
            $updateMembre->execute(array(
                'token' => $valeur,
                'pseudo' => $pseudo,
            ));
            header("Location: boutique.php?msg=code:true");
            exit;
        }
    exit;
    }
}
$site = "http://".$site."/";
include("include/header.php");
?>

<div id="content" class="commande">
    <?php
    if(connect()) {
        msg('Votre code est incorrect, veuillez réessayer.', 'r', 'get', 'code:false');
        
        if($activePaypal == true) {
            if(!empty($adressePaypal)) { ?>
    
            <h3>Payer avec PayPal</h3>
            <div id="PayPal">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="paypal">
                    <select name="amount">
                        <option selected>Séléctionnez l'offre que vous désirez...</option>
                        <?php
                        for($i = 0; $i<6; $i++) {
                            if($i%2) {
                                echo $offrePaypal[$i].' '.$monnaie_site.'</option>';
                            } else {
                                echo '<option value="'.$offrePaypal[$i].'">'.$offrePaypal[$i].'€ pour ';
                            }
                        }
                        ?>
                    </select>
                    <input name="currency_code" type="hidden" value="EUR">
                    <input name="shipping" type="hidden" value="0.00">
                    <input name="tax" type="hidden" value="0.00">
                    <input name="return" type="hidden" value="<?php echo $site; ?>boutique.php?msg=paypal:true">
                    <input name="cancel_return" type="hidden" value="<?php echo $site; ?>">
                    <input name="notify_url" type="hidden" value="<?php echo $site; ?>paypal/ipn.php">
                    <input name="cmd" type="hidden" value="_xclick">
                    <input name="business" type="hidden" value="<?php echo $adressePaypal; ?>">
                    <input name="item_name" type="hidden" value="Commande de <?php echo $monnaie_site." sur ".$titre; ?>">
                    <input name="no_note" type="hidden" value="1">
                    <input name="lc" type="hidden" value="FR">
                    <input name="bn" type="hidden" value="PP-BuyNowBF">
                    <input name="custom" type="hidden" value="id=<?php echo $pseudo; ?>">
                    <input type="submit" value="Continuer" name="submit">
                </form>
            </div><br>
            
            <?php } else {
                msg("Il est impossible de payer par PayPal pour le moment.", 'b');
            }
        }
        if($activeStarpass == true) { ?>

            <h3>Payer avec Starpass</h3>
            <?php echo '<p style="text-align:center; font-weight:bold;">1 code acheté = '.$valeur.' '.$monnaie_site.'</p>'; ?>
            <div class="starpassBox" id="starpass_<?php echo $idd; ?>"><p style="text-align: center;font-size: small;">Chargement du script Starpass en cours...</p></div>
            <script type="text/javascript" src="http://script.starpass.fr/script.php?idd=<?php echo $idd; ?>&amp;verif_en_php=1&amp;datas="></script>
            <noscript>Veuillez activer le Javascript de votre navigateur s'il vous pla&icirc;t.<br />
                <a href="http://www.starpass.fr/">Micro Paiement StarPass</a>
            </noscript>
            
        <?php }
        
        if($activeStarpass == false && $activePaypal == false) {
            msg("Vous ne pouvez pas encore acheter de <u>$monnaie_site</u> car aucun moyen de paiement n'est encore défini.", 'b');
        }
        
    } else {
        msg("Vous devez être connecté pour acheter des $monnaie_site.", 'b');
    }
    ?>
</div>
<?php include ("include/footer.php"); ?>