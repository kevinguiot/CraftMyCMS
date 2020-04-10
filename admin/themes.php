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

$titre_page = "Gestion des thèmes";
include('../include/init.php');
$themeActuel = $theme;

//Ajout d'un addon au CMS
if(!empty($_GET['add'])) {
    if(addAddon($_GET['add']) == true) {
        header('location: themes.php?msg=ajout:true');
    } else {
        header('location: themes.php?msg=ajout:false');
    }
	exit;
}

//Téléchargement d'un addon
if(!empty($_GET['download'])) {
    if(downloadAddon('theme', $_GET['download']) == true) {
		header('location: themes.php?msg=telechargement:true');
	} else {
		header('location: themes.php?msg=telechargement:false');
	}
	exit;
}

//Appliquer un thème
if(!empty($_GET['appliquer'])) {
	if(is_dir('../style/'.$_GET['appliquer'])) {
		modifConfig('theme', $_GET['appliquer']);
		header('location: themes.php?msg=appliquer:true');
	} else {
		header('location: themes.php?msg=appliquer:true');
	}
	exit;
}

//Appliquer un thème via le formulaire
if(!empty($_POST['theme'])) {
	modifConfig('theme', secure($_POST['theme']));
	header('location: themes.php?msg=appliquer:true');
	exit;
}

//Achat d'un addon
if(!empty($_GET['buy'])) {
	$buyAddon = buyAddon($_GET['buy']);

	if($buyAddon != true) {
		header('location: plugins.php?msg=buy:false');
	} else {
		header('location: http://www.craftmycms.fr/membre/'.$buyAddon);
	}
	exit;
}

include('header.php');
?>

<div id="content" class="themes">
	<?php
	msg('Cet addon a bien été ajouté à votre CMS.', 'v', 'get', 'ajout:true');
	msg("Il est impossible d'ajouter cet addon à votre CMS.", 'r', 'get', 'ajout:false');
	msg("Le téléchargement de cet addon a bien été effectué.", 'v', 'get', 'telechargement:true');
	msg("Le téléchargement de cet addon a echoué.", 'r', 'get', 'telechargement:false');
	msg("Le thème suivant a bien été appliqué.", 'v', 'get', 'appliquer:true');
	msg("Impossible d'appliquer le thème suivant.", 'r', 'get', 'appliquer:false');
	?>

	<h3>Configuration des thèmes</h3>
	<div style="padding: 15px; background-color: #E6E6E6; border-radius: 3px; border: 1px solid E4E4E4; margin: 10px;">
		<a href="?appliquer=default">Cliquez-ici pour remettre le thème par défaut</a>.<hr>

		<form method="post">

			<strong>Modifier manuellement le nom de configuration du thème utilisé (optionnel):</strong><br>
			<input type="text" name="theme" value="<?php echo $theme; ?>">
			<input type="submit" value="Enregistrer"><br>
		</form>
	</div>
	<br>

	<h3>Magasin des thèmes</h3>
	<?php
	//Récupération des thèmes
	$listeThemes = getAddons('getThemes', $themeActuel);

	if(!strstr($listeThemes[0], 'error=>') ) {
		foreach($listeThemes as $theme) {
			list(
				$infosTheme['id_addon'],
				$infosTheme['user_id'],
				$infosTheme['nom'],
				$infosTheme['description'],
				$infosTheme['image'],
				$infosTheme['payant'],
				$infosTheme['date'],
				$infosTheme['heure'],
				$infosTheme['udate'],
				$infosTheme['uheure'],
				$infosTheme['isDownload'],
				$infosTheme['isMaj'],
				$infosTheme['nomConfig'],
				$infosTheme['version'],
				$infosTheme['maj'],
				$infosTheme['isDev'],
			) = explode('%', $theme);

			//Gestion du prix
			$prix = $infosTheme['payant'];
			if($prix == '0') {
				$prix = 'proposé gratuitement';
			} else {
				$prix = 'proposé pour la somme de <strong>'.$prix.'&#8364;</strong>';
			}

			//Gestion de l'image
			$image = $infosTheme['image'];
			if($image == "") {
				$image = "../images/empty_image.png";
			}
			?>

			<div class="theme">
				<div class="left">
					<div class="preview">
						<img class="previewTheme" src="<?php echo $image; ?>">
						<div class="actions">
							<?php
							if($infosTheme['isDownload'] == true) {
								if($themeActuel == $infosTheme['nomConfig']) {
									echo '<span style="float:left;"><img src="../images/admin/archive-checkmark.png"></span>';
								} else {
									echo '<span style="float:left;"><a href="?appliquer='.$infosTheme['nomConfig'].'"><img src="../images/admin/archive-empty.png"></a></span>';
								}
							}

							echo '<span style="float:right;">';
							if($infosTheme['isDownload'] == '0') {
								if($infosTheme['payant'] == '0') {
									echo '<a href="?add='.$infosTheme['id_addon'].'"><img src="../images/admin/circle-plus_16.png"></a>';
								} else {
									echo '<a href="?buy='.$infosTheme['id_addon'].'"><img src="../images/admin/cart-arrow-down_white.png"></a>';
								}
							} else {
								echo '<a href="?download='.$infosTheme['id_addon'].'">';
								if($infosTheme['isMaj'] == 'true') {
									echo '<img src="../images/admin/arrow-download_green.png">';
								} else {
									echo '<img src="../images/admin/arrow-download_red.png">';

								}
								echo '</a>';
							}

							echo '<a href="#?w=700" rel="screen_'.$infosTheme['id_addon'].'" class="poplight"><img title="Cliquez ici pour agrandir l\'image" src="../images/admin/loop.png"></a>';
							echo '</span>';
							echo '<p style="clear:both;"></p>';
							?>
						</div>
					</div>
				</div>
				<div class="right">
					<?php
					$page = 'http://www.craftmycms.fr/addons/'.$infosTheme['nomConfig'].'/';

					echo '<h3><a href="'.$page.'">';

					if($infosTheme['isDev'] == 'true') {
						echo '<span style="color:#AA0303;">[DEV]</span> ';
					}

					echo $infosTheme['nom'].'</a></h3>';

					echo '<small>Envoyé par <a href="http://www.craftmycms.fr/profil/'.$infosTheme['user_id'].'" target="_blank">'.$infosTheme['user_id'].'</a>, '.$prix.'.<br>
					<a href="'.$page.'">Cliquez-ici pour afficher la page de ce thème.</a></small>';
					echo $infosTheme['description'];
					?>
				</div>
				<p style="clear: both;"></p>

				<?php
				echo '<div id="screen_'.$infosTheme['id_addon'].'" class="popup_block viewTheme" style="width:auto; text-align:center;"><img src="'.$image.'"></div>';
				?>
			</div>
			<?php
		}
	} else {
		$message = getMessage($listeThemes[0]);
		msg($message, 'r', null, null, 10);
	}
	?>
</div>
<?php include('footer.php'); ?>
<script type="text/javascript" src="../script/fade.js"></script>
