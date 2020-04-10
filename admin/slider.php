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

$titre_page = "Gestion du slider";
include("../include/init.php");
if(!empty($_POST)) {
	$req = $connexion->prepare("UPDATE ".$prefixe."slider SET slider=:slider, titre=:titre, content=:content WHERE id=1");
	$req->execute(
		array(
			'slider' => $_POST['slider1'],
			'titre' => $_POST['titre1'],
			'content' => $_POST['content1'],
		)
	);
	
	$req = $connexion->prepare("UPDATE ".$prefixe."slider SET slider=:slider, titre=:titre, content=:content WHERE id=2");
	$req->execute(
		array(
			'slider' => $_POST['slider2'],
			'titre' => $_POST['titre2'],
			'content' => $_POST['content2'],
		)
	);
	
	$req = $connexion->prepare("UPDATE ".$prefixe."slider SET slider=:slider, titre=:titre, content=:content WHERE id=3");
	$req->execute(
		array(
			'slider' => $_POST['slider3'],
			'titre' => $_POST['titre3'],
			'content' => $_POST['content3'],
		)
	);
	
	$req = $connexion->prepare("UPDATE ".$prefixe."slider SET slider=:slider, titre=:titre, content=:content WHERE id=4");
	$req->execute(
		array(
			'slider' => $_POST['slider4'],
			'titre' => $_POST['titre4'],
			'content' => $_POST['content4'],
		)
	);

	header('location: ?msg=slider:true');
}

include("header.php");
?>
<div id="content" class="slider">
	<p>Pour supprimer un slider, mettez "false" dans le titre de celui-ci.</p>
	<form method="post">
		<?php
		msg("Votre slider a bien été modifié.", 'v', 'get', 'slider:true');
		
		$sql = $connexion->query("SELECT * FROM ".$prefixe."slider");
		$sql->setFetchMode(PDO::FETCH_OBJ);
		while($req = $sql->fetch()) {
			$slider = $req->slider;
			$titre = $req->titre;
			$content = $req->content;
			?>
			<div class="slider" style="background: url(<?php echo $slider; ?>) no-repeat; ">
				<div class="input">
					<input type="text" placeholder="Lien de l'image de votre slider" value="<?php echo $slider; ?>" name="slider<?php echo $req->id; ?>"><br>
					<input type="text" placeholder="Titre de votre slider" value="<?php echo $titre; ?>"name="titre<?php echo $req->id; ?>"><br>
					<textarea placeholder="Description de votre slider" name="content<?php echo $req->id; ?>" rows="3"><?php echo $content; ?></textarea>
				</div>
			</div>
			<?php
			}
		?>
		<input type="submit" value="Enregister vos nouveaux sliders">
	</form>
</div>

<?php
include("footer.php");
?>