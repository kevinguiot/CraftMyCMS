<!doctype html>
<html lang="fr">
    <head>
		<title>Installation de CraftMyCMS version Perso</title>
		
		<meta name="Description" content="Installation de CraftMyCMS version Perso">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="robots" content="noindex, nofollow">

		<link rel="stylesheet" href="../style/<?php echo $theme; ?>/reset.css" media="screen">
		<link rel="stylesheet" href="../style/<?php echo $theme; ?>/install.css" media="screen">
		<link rel="icon" href="../images/favicon.ico">

		<script type="text/javascript" src="../script/jquery.min.js"></script>
		<script type="text/javascript" src="../script/jquery-ui.min.js"></script>
	</head>

	<body>
		<div id="header">
			<h1>Installation de CraftMyCMS</h1>
			<?php
			echo '<h2>';
			
			if($etapePrevious == true) {
				echo '<span><a href="?force=previous">Etape précédente</a></span>';
			} else {
				echo '<span class="strike">Etape précédente</span>';
			}

			echo ' &bull; <strong>Etape n&deg;'.$etape.' - '.$listeEtapeArray[$etape].'</strong> &bull; ';

			if($etapeNext == true) {
				echo '<span><a href="?force=next">Etape suivante</a></span>';
			} else {
				echo '<span class="strike">Etape suivante</span>';
			}

			echo '</h2>';
			?>
		</div>
        <div id="sidebar">
			<?php
			$i = 1;
			foreach($listeEtapeArray as $listeEtape) {
				echo '<span';
				if($etape == $i) {
					echo ' class="selected"';
				}
				echo '>';
				
				echo $listeEtape.'</span>';
				$i++;
			}
			?>
		</div>