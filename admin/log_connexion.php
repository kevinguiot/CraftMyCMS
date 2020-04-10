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

$titre_page = "Logs de connexion";
include("../include/init.php");
include("header.php");

if(strstr($_SERVER['REQUEST_URI'], 'remove')) {
	if(!empty($_GET['token'])) {
		$deleteLogs = $connexion->query('DELETE FROM '.$prefixe.'log_connexion');
		header('location: log_connexion.php');
		exit;
	}
}
?>

<div id="content" class="log_connexion">
    <?php
    $req_selectLog = $connexion->query('SELECT * FROM '.$prefixe.'log_connexion WHERE etat = 1');
    $nbr_selectLog = $req_selectLog->rowCount();
    
    if($nbr_selectLog > 0) {
    ?>
        <a onClick="confirmRemove()">Cliquez-ici pour vider les logs de connexions</a>
    
        <table class="table table-bordered table-striped">
            <tr>
                <td>Pseudo</td>
                <td>Navigateur utilisé</td>
                <td>Adresse IP</td>
                <td>Date</td>
                <td>Essais</td>
            </tr>
            <?php
            while($selectLog = $req_selectLog->fetch()) {
                $tentative = $selectLog['tentative'] + 1;
                
                echo '<tr>';
                echo '<td><a title="Accéder au profil de '.$selectLog['pseudo'].'" href="../membre.php?pseudo='.$selectLog['pseudo'].'">'.getUserInfos('pseudo', $selectLog['pseudo'], 'color').'</a></td>';
                echo '<td>'.$selectLog['navigateur'].'</td>';
                echo '<td>'.$selectLog['ip'].'</td>';
                echo '<td>'.$selectLog['date'].'</td>';
                echo '<td>'.$tentative.'</td>';
                echo '</tr>';
            }
            ?>
        </table>
    <?php } else {
        msg("Les logs de connexions sont actuellement vides.", 'b');
    }
    ?>
</div>

<?php include('footer.php'); ?>

<script>
function confirmRemove(msg,a) {
	var r= confirm("Etes-vous sûr de vouloir vider les logs de connexions ?");
	if (r==true) {
		window.location = 'log_connexion.php?remove&token=<?php echo $_SESSION['token']; ?>';
	}
}
</script>