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

$titre_page = "Maintenance";
include("../include/init.php");
if(!empty($_POST['maintenance'])) {
    if($_POST['maintenance'] != "false") {
		modifConfig('maintenance', secure($_POST['maintenance']));
    } else {
		echo modifConfig('maintenance');
    }
	header("location: ?msg=true");
}
include("header.php");
?>

<div id="content" class="maintenance">
	<?php
	msg("La maintenance de votre CMS a bien été ajoutée.", 'v', 'get', 'true');
	?>
	
	<h3>Ajoutez un message de maintenance:</h3>
	<form method="post">
		<table class="table table-bordered table-striped">
			<tr>
				<td colspan="2">
					<input type="text" placeholder="Raison de la maintenance" name="maintenance" value="<?php echo $maintenance; ?>"><br>
					"false" pour enlever la maintenance.<br>
					
				</td>
			</tr>
		</table>
		<div style="text-align: center;"><input type="submit" value="Enregistrer"></div>
	</form><br>
	<p class="alert">
		<strong>Attention: </strong>Vous devez être connecté en tant qu'administrateur pour que la maintenance ne soit pas affichée ! Si vous êtes déconnecté de votre site, rendez-vous sur la page "login.php" et connectez-vous. C'est la seule page qui ne sera pas en maintenance.	</p>
</div>
<?php include("footer.php"); ?>