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

if(!empty($_POST['host'])) {
    $host = $_POST['host'];
    $user = $_POST['user'];
    $mdp = $_POST['mdp'];
    $db = $_POST['db'];
    $prefixe = $_POST['prefixe'];
    if(!empty($host) AND !empty($user)) {
        if(!empty($db)) {
            try {
                $dbh = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $mdp) or die("test");
                $data = '<?php $serveur = "'.$host.'"; $user = "'.$user.'"; $mdp = "'.$mdp.'"; $base = "'.$db.'"; $prefixe = "'.$prefixe.'"; ?>';
                $fp = fopen('temp/bdd.php' ,"w+");
                
                if(fwrite($fp, $data)) {
                    fclose($fp);
                    include ("sql.php");
                    $dbh->exec("INSERT INTO ".$prefixe."etape_temp (etape1, etape2) VALUES ('1', '1')");
                    header("location: index.php");
                    exit;
                } else {
                    $erreurv = "Impossible d'écrire les données dans votre FTP.<br>Veuillez mettre les droits 777 sur tous les fichiers et dossiers de votre FTP.";
                }
                fclose($fp);
                $dbh = null;
            } catch (PDOException $erreur) {
                $erreurv = "Impossible de se connecter à la base de donnée.";
            }
        } else {
            $erreurv = "Vous devez entrer le nom de votre base de donnée.";
        }
    } else {
        $erreurv = "Vous devez entrer l'url de votre base de donnée ainsi que votre nom d'utilisateur.";
    }
}

include('header.php');
?>

<div id="content" class="bdd">
    <?php
    if(!empty($_GET['msg']) && $_GET['msg'] == 'cms:true') {
        msg("Votre CMS est désormais activé.", 'v');
    }
    
    if(!empty($erreurv)) {
        msg($erreurv, 'r');
    }
    ?>
    
    <p class="bdd">
        La base de donnée permet de stocker les données de votre site.<br>
        Vous pourrez retrouver les informations demandées sur le panel de votre hébergeur web.
    </p>
    
    <form method="post">
        <table class="table table-bordered table-striped">
            <tr>
                <td>Serveur de la base de donnée</td>
                <td><input type="text" name="host" value="<?php if(!empty($_POST['host'])) { echo $_POST['host']; } ?>"></td>
            </tr>
            <tr>
                <td>Nom d'utilisateur</td>
                <td><input type="text" name="user" value="<?php if(!empty($_POST['user'])) { echo $_POST['user']; } ?>"></td>
            </tr>
            <tr>
                <td>Mot de passe</td>
                <td><input type="password" name="mdp"></td>
            </tr>
            <tr>
                <td>Nom de la base de donnée</td>
                <td><input type="text" name="db" value="<?php if(!empty($_POST['db'])) { echo $_POST['db']; } ?>"></td>
            </tr>
            <tr>
                <td>Préfixe de la base de donnée<br><small>Permet d'enregistrer toutes les tables du CMS sur le prefixe ci-contre.</small></td>
                <td><input type="text" name="prefixe" value="<?php if(!empty($_POST['prefixe'])) { echo $_POST['prefixe']; } else { echo "cmc_"; }?>"></td>
            </tr>
            <tr>
                <td colspan="2"><input class="button_stone" type="submit" value="Enregistrer ces informations"></td>
            </tr>
        </table>
    </form>
</div>

<?php include('footer.php'); ?>