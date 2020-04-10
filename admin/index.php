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

$titre_page = "accueil";
$needserveur = TRUE;
include("../include/init.php");

//Charge les serveurs actifs.
if(strstr($_SERVER['REQUEST_URI'], 'reqServeurs')) {
	$serveurActif = 0;

	$req_selectServeurs = $connexion->query('SELECT * FROM '.$prefixe.'serveurs');
	$nbr_selectServeurs = $req_selectServeurs->rowCount();

	while($selectServeurs = $req_selectServeurs->fetch(PDO::FETCH_ASSOC)) {
		//Se connecte au serveur via JSONAPI
		$api = new JSONAPI(
			$selectServeurs['external_ip'],
			$selectServeurs['port'],
			$selectServeurs['user'],
			$selectServeurs['password'],
			$selectServeurs['salt']
		);
		$getPlayerCount = $api->call('getPlayerCount');

		if($getPlayerCount['result'] == 'success') {
			$serveurActif++;
		}
	}

	echo $serveurActif.'/'.$nbr_selectServeurs;
	exit;
}

include("header.php");
?>

<div id="content" class="index">
	<?php
	msg("N'oubliez pas d'adapter vos paramètres après chaque mises à jours de votre CMS.", 'b');

	$req_nbrMembre = $connexion->query('SELECT * FROM '.$prefixe.'membres');
	$nbrMembre = $req_nbrMembre->rowCount();

	$req_nbrAchat = $connexion->query("SELECT * FROM ".$prefixe."boutique_liste");
	$nbrAchat = $req_nbrAchat->rowCount();

	$req_nbrNews = $connexion->query('SELECT * FROM '.$prefixe.'news');
	$nbrNews = $req_nbrNews->rowCount();

	$req_nbrTickets = $connexion->query('SELECT * FROM '.$prefixe.'support');
	$nbrTickets = $req_nbrTickets->rowCount();

	$req_nbrVisiteT = $connexion->query('SELECT * FROM '.$prefixe.'visites');
	$nbrVisiteT = $req_nbrVisiteT->rowCount();

	$req_nbrVisiteA = $connexion->prepare('SELECT * FROM '.$prefixe.'visites WHERE date=:date');
	$req_nbrVisiteA->execute(array(
		'date' => $date
	));
	$nbrVisiteA = $req_nbrVisiteA->rowCount();
	?>

	<h3>Informations de votre site</h3>

	<?php
	if($permissions[0]) { ?>
		<table class="stats">
			<tr>
				<td>
					<img src="../images/admin/bars-chart.png">
					<p>
						<span class="h1">Visites aujourd'hui</span>
						<span class="h2"><?php echo $nbrVisiteA; ?> visite(s)</span>
					</p>
				</td>
				<td>
					<img src="../images/admin/user.png">
					<p>
						<span class="h1">Membres</span>
						<span class="h2"><a href="membres.php"><?php echo $nbrMembre; ?> membre(s) inscrit(s)</a></span>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<img src="../images/admin/pie-chart.png">
					<p>
						<span class="h1">Visites totales</span>
						<span class="h2"><?php echo $nbrVisiteT; ?> visite(s)</span>
					</p>
				</td>
				<td>
					<img src="../images/admin/shopping-backet.png">
					<p>
						<span class="h1">Liste des achats</span>
						<span class="h2"><a href="boutique.php#listeAchat"><?php echo $nbrAchat; ?> article(s) vendu(s)</a></span>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<img src="../images/admin/notes.png">
					<p>
						<span class="h1">News</span>
						<span class="h2"><a href="news.php"><?php echo $nbrNews; ?> new(s) publiée(s)</a></span>
					</p>
				</td>
				<td>
					<img src="../images/admin/screwdriver.png">
					<p>
						<span class="h1">Support</span>
						<span class="h2"><a href="../support.php"><?php echo $nbrTickets; ?> ticket(s) envoyé(s)</a></span>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<img src="../images/admin/sign-info.png">
					<p>
						<span class="h1">Mise à jour</span>
						<?php
						if($maj_etat=="1") {
							echo '<span class="h2" style="color:green;">Votre CMS est à jour</span>';
						} else {
							echo '<span class="h2" style="color:#AA0303;">Votre CMS n\'est pas à jour</span>';
						}
						?>
					</p>
				</td>
				<td class="jsonapi">
					<img src="../images/admin/globe.png">
					<p>
						<span class="h1">JSONAPI</span>
						<span class="h2"></span>
					</p>
				</td>
			</tr>
		</table>
	<?php } else {
		msg("Vous n'avez pas les droits nécessaires pour afficher les statistiques.", 'r', null, null);
	}
	?>

	<h3>Restez à l'actualité de CraftMyCMS</h3>
	<table class="stats actu">
		<tr>
			<td>
				<a target="_blank" href="https://www.facebook.com/craftmycms" title="Accéder à la page Facebook"><img src="../images/admin/facebook.png" alt="facebook"></a>
				<a target="_blank" href="https://twitter.com/CraftMyCMS" title="Accéder à la page Twitter"><img src="../images/admin/twitter.png" alt="twitter">
				<a target="_blank" href="http://www.craftmycms.fr/support/" title="Accéder au support de CraftMyCMS"><img src="../images/admin/Support.png" alt="support"></a>
				<a target="_blank" href="http://forum.craftmycms.fr/?forum=suggestions" title="Suggestions pour le CMS"><img src="../images/admin/Flag.png" alt="suggestion">
			</td>
			<td>
				Il est important d'être à l'actualité de CraftMyCMS en cas de bug important du CMS.<br>Vous pouvez aussi envoyer vos suggestions.
			</td>
		</tr>
	</table>
</div>
<?php include("footer.php"); ?>

<script>
var etatJSONAPI = $('td.jsonapi .h2');

etatJSONAPI.html("Chargement en cours...");
jQuery(window).load(function(){
	$.get('index.php?reqServeurs', function(html) {
		etatJSONAPI.html(html + " sont opérationnels.");
	});
});
</script>
