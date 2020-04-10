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

$titre = $titresite;

if(defined('titre_page')) {
	$titre_page = titre_page;
}

$maj_etat = file_get_contents("../include/config/maj.txt");
if(strstr($maj_etat, 'false')) {
	$maj_etat = false;
} else {
	$maj_etat = true;
}

?>
<!doctype html>
<html lang="fr">
    <head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php if(!empty($titre_page) AND ($titre_page!="accueil")) { echo $titre_page.' - '; } ?>Gérer votre CMS</title> 
        <meta name="robots" content="noindex, nofollow">

		<link rel="stylesheet" href="../style/<?php echo $theme; ?>/reset.css" media="screen">			
		<link rel="stylesheet" href="../style/<?php echo $theme; ?>/admin.css" media="screen">
		<link type="text/css" href="../style/jquery.jscrollpane.css" rel="stylesheet" media="all" />
		<link rel="icon" href="../images/favicon.ico">

		<script type="text/javascript" src="../script/jquery.min.js"></script>
		<script type="text/javascript" src="../script/jquery-ui.min.js"></script>
	
		<script type="text/javascript" src="../script/reset.js"></script>
		<script type="text/javascript" src="../script/jquery.jscrollpane.min.js"></script>
		<script type="text/javascript" src="../script/jquery.mousewheel.js"></script>
		
		<script type="text/javascript">
		$(function() {
			var element = $('a.selected').position();
			var top = element.top;
			var pane = $('#sidebar');
			
			pane.jScrollPane();
			var api = pane.data('jsp');

			api.scrollBy(0, top);
			return false;
		});
		</script>
		
		<?php
		if($rang == 2) {
			echo '<style>#sidebar a.selected:after { left: 208px; } </style>';
		}
		?>
	</head>
	
	<body>
		<div id="header">
			<div style="float:left; padding-top:12px;">
				<h1><?php echo $titre; ?></h1>
				<?php
				if($rang == "3") { echo '<h2>Panel d\'administration</h2>'; }
				if($rang == "2") { echo '<h2>Panel de modération</h2>'; }
				?>
			</div>
		</div>
		
        <div id="sidebar">
			<?php
			//Accès aux liens du menu
			$menu[0] = array('Revenir sur le site', '../', '../images/admin/sidebar/retour.png');
			$menu[1] = array('accueil', 'index.php', '../images/admin/sidebar/accueil.png');
			
			if($rang == 3 || ($rang == 2 && in_array('update.php', $pageModo))) {
				$menu[2] = array('Mise à jours', 'update.php', '');
			}
			
			if($rang == 3 || ($rang == 2 && in_array('news.php', $pageModo))) {
				$menu[3] = array('Gestion des news', 'news.php', '../images/admin/sidebar/news.png');
			}
			
			if($rang == 3) {
				$menu[4] = array('Gestion des serveurs', 'serveurs.php', '../images/admin/sidebar/serveurs.png');
				$menu[5] = array('Gestion des membres', 'membres.php', '../images/admin/sidebar/membres.png');
			}
			
			if($rang == 3 || ($rang == 2 && in_array('boutique.php', $pageModo))) {
				$menu[6] = array('Gestion de la boutique', 'boutique.php', '../images/admin/sidebar/boutique.png');
			}
			
			if($rang == 3) {
				$menu[7] = array('Gestion du slider', 'slider.php', '../images/admin/sidebar/slider.png');
				$menu[8] = array('Gestion des plugins', 'plugins.php', '../images/admin/sidebar/plugins.png');
				$menu[9] = array('Gestion des thèmes', 'themes.php', '../images/admin/sidebar/themes.png');
				$menu[10] = array('Paramètre du site', 'site.php', '../images/admin/sidebar/parametres.png');
				$menu[11] = array('Paramètre des pages', 'pages.php', '../images/admin/sidebar/parametres.png');
				$menu[12] = array('Paramètre de la BDD', 'bdd.php', '../images/admin/sidebar/bdd.png');
				$menu[13] = array('Apparence du CMS', 'apparence.php', '../images/admin/sidebar/themes.png');
				$menu[14] = array('Editer le règlement', 'reglement.php', '../images/admin/sidebar/reglement.png');
				$menu[15] = array('Editer les modules', 'modules.php', '../images/admin/sidebar/modules.png');
				$menu[16] = array('Maintenance', 'maintenance.php', '../images/admin/sidebar/maintenance.png');
				$menu[17] = array('Logs de connexion', 'log_connexion.php', '../images/admin/sidebar/news.png');
				$menu[18] = array('Ajouter une page', 'ajoutpage.php', '../images/admin/sidebar/ajout_page.png');
			}
			
			//Création du menu
			$i = 0;

			for($i = 0; $i<19; $i++) {
				if(!empty($menu[$i])) {
					if($i == 3 || $i == 10 || $i == 13 || $i == 8) {
						echo '<hr>';
					}				
					
					echo '<a href="'.$menu[$i][1].'"';
					if($titre_page == $menu[$i][0]) {
						echo 'class="selected"';
					}
					echo '>';
					
					if($i == 2) {
						if($maj_etat == false) {
							echo '<img src="../images/admin/sidebar/maj_false.png">';
						} else {
							echo '<img src="../images/admin/sidebar/maj_true.png">';
						}
					} else {
						echo '<img src="'.$menu[$i][2].'">';
					}
					
					if($menu[$i][0] == 'accueil') {
						$menu[$i][0] = 'Tableau de bord';
					}
					
					echo $menu[$i][0].'</a>';
				}
			}
			?>
	    </div>