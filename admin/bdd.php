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

$titre_page = "Paramètre de la BDD";
$needserveur = false;
include("../include/init.php");

if(!empty($_POST['host'])) {
    try {
        $newConnexion = new PDO('mysql:host='.$_POST['host'].';dbname='.$_POST['db'].';charset=utf8', $_POST['user'], $_POST['mdp']) or die("test");
        
        modifConfig('serveur', secure($_POST['host']));
        modifConfig('base', secure($_POST['db']));
        modifConfig('user', secure($_POST['user']));
        modifConfig('mdp', secure($_POST['mdp']));
        modifConfig('prefixe', secure($_POST['prefixe']));
        
        $newConnexion = null;
        
        session_unset();
        session_destroy();

        header("location: ../index.php");
    } catch (PDOException $erreur) {
        header("location: ?msg=bdd:false");
    }
    exit;
}

include('header.php');
?>

<div id="content" class="parametre_site">
    
    <?php
    msg("Les identifiants pour se connecter à la base de donnée ont bien été modifiés.", 'v', 'get', 'bdd:true');
    msg("Les identifiants pour se connecter à la base de donnée ne sont pas corrects.", 'r', 'get', 'bdd:false');
    ?>
    
    <h3>Paramètre de la base de donnée</h3>
    <form method="post">
        <table class="table table-bordered table-striped">
            <tr>
                <td>Serveur de la base de donnée</td>
                <td><input type="text" name="host" value="<?php echo $serveur; ?>"></td>
            </tr>
            <tr>
                <td>Nom d'utilisateur</td>
                <td><input type="text" name="user" value="<?php echo $user; ?>"></td>
            </tr>
            <tr>
                <td>Mot de passe</td>
                <td><input type="password" name="mdp"></td>
            </tr>
            <tr>
                <td>Nom de la base de donnée</td>
                <td><input type="text" name="db" value="<?php echo $base; ?>"></td>
            </tr>
            <tr>
                <td>Préfixe de la base de donnée<br><small>Permet d'enregistrer toutes les tables du CMS sur le prefixe ci-contre.</small></td>
                <td><input type="text" name="prefixe" value="<?php echo $prefixe; ?>"></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Modifier les paramètres"> <small>Attention, vous serez déconnecté après la modification de la base de donnée.</small></td>
            </tr>
        </table>
    </form>
</div>

<?php include('footer.php'); ?>