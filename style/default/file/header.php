<!doctype html>
<html lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?php if($titre_page == "accueil") { echo $titre; } else { echo $titre_page." - ".$titre; } ?></title> 
		<meta name="description" content="<?php echo $description; ?>">
		<meta name="keywords" content="<?php echo $keywords; ?>, Serveur, Minecraft, CraftMyCMS">
		<meta name="author" content="CraftMyCMS, Kévin Guiot">
		<meta name="robots" content="index, follow">

		<link rel="stylesheet" href="style/<?php echo $theme; ?>/reset.css" media="screen">
		<link rel="stylesheet" href="style/<?php echo $theme; ?>/style.css" media="screen">
		<link rel="stylesheet" href="style/custom_style.css" media="screen">
		<link rel="icon" href="images/favicon.ico">

		<style>
		<?php if($titre_page == "accueil") {
			echo '#content {overflow: hidden;}';
			echo '#footer {margin-top: 0;}';
		} else {
			echo '#content {background-color:white; padding:5px; overflow: hidden;}';
		}
		?>
		</style>

		<script type="text/javascript" src="script/jquery.min.js"></script>
		<script type="text/javascript" src="script/jquery-ui.min.js"></script>
		<script type="text/javascript" src="script/reset.js"></script>
		<?php
		if($titre_page == "accueil") { ?>
			<script type="text/javascript" src="script/jquery-1.3.2.min.js"></script>
			<script type="text/javascript" src="script/jquery-ui-1.7.2.custom.min.js"></script>
			<script type="text/javascript">
			$(document).ready(function(){
				$("#featured > ul").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);
			});
			$(document).ready(function(){
				$("#featured").tabs({fx:[{opacity: "toggle", duration: 'slow'}, {opacity: "toggle", duration: 'normal'}],
					show: function(event, ui){
						$('#featured .ui-tabs-panel .info').hide();
						var infoheight=$('.info', ui.panel).height();
						$('.info', ui.panel).css('height', '0px').animate({ 'height': infoheight }, 500);
					}
				}).tabs("rotate", 5000, true);
				$('#featured').hover(
					function(){ $('#featured').tabs('rotate', 0, true); },
					function(){ $('#featured').tabs('rotate', 5000, true); }
				);
			});
			</script>
		<?php } ?>
		<script type="text/javascript">
		$(document).ready(function(){
			
			//On gère l'affichage des onglets
			sfHover = function() {
				var sfEls = document.getElementById("navigation").getElementsByTagName("li");
				for (var i=0; i<sfEls.length; i++) {
					sfEls[i].onmouseover = function() {
						this.className = this.className.replace(new RegExp(" sfhover"), "");
						this.className += " sfhover";
					}
					sfEls[i].onmouseout = function() {
						this.className = this.className.replace(new RegExp(" sfhover"), "");
					}
				}
			}
			if (window.attachEvent) window.attachEvent("onload", sfHover);
			
			//On gère l'affichage des serveurs
			var listeServeurs = $('#listeServeurs');			
				$('a.affServeurs').click(function() {
				if (listeServeurs.css('display') == 'none') {
					//listeServeurs.fadeIn();
					listeServeurs.css('display', 'block');
				} else {
					//listeServeurs.fadeOut();
					listeServeurs.css('display', 'none');
				}
				
				return false;
			});
			
			//On change le serveur désiré
			$('a.chooseServeur').click(function() {
				$('#showServeurs').attr('src', 'images/loading.gif');

				var serveur = $(this).attr('id').replace('serveur', '');
				
				$.ajax({
					url: 'include/chooseServeur.php',
					type: 'get',
					data: 'serveur=' + serveur,
					success: function(html) {
						window.location.reload();
						listeServeurs.css('display', 'none');
					}
					
				});
			});
		});
		</script>
	</head>	
	<body>
		
		<div id="header">
			<div class="header">
				<a href="index.php" title="Retourner à l'accueil de <?php echo $titre; ?>"><img style="float: left;" class="logo" src="images/logo.jpg" alt="logo"></a>
				<h1><?php echo $titre; ?></h1>
				<h2><?php echo $slogan; ?></h2>
			</div>
			<?php
			if($nbr_selectServeurs > 0 OR (!empty($hideHeader) && $hideHeader == true)) {
				
				echo '<div class="informations">';
				
				//Si on souhaite se connecter via l'header.
				if(!empty($hideHeader)) {
					
					//Si on est pas connecté.
					if(!connect()) { ?>
					
						<form method="post" action="login.php">
							<img src="images/icone/user.png"> <input type="text" name="pseudo" placeholder="Votre pseudo..."><br>
							<img src="images/icone/bullet_key.png"> <input type="password" name="passe" placeholder="Votre mot de passe...">
							<input type="submit" value="OK">
						</form>

					<?php
					} else {
						
						if(USER_RANG == 3) {
							$colorRang = "#AA0303";
						} elseif(USER_RANG == 2) {
							$colorRang = 'green';
						} else {
							$colorRang = 'black';
						}
						
						echo '<span style="float:left;"><img src="skin.php?pseudo='.USER_PSEUDO.'" alt="skin"></span>';
						echo '<span style="float:left; margin:1px 0 0 10px; font-weight:bold;">Bienvenue <span style="color:'.$colorRang.'">'.USER_PSEUDO.'</span>';
						echo '<br><small>';
						
						echo '<a href="membre.php?pseudo='.USER_PSEUDO.'">Profil</a> &#124; ';
						echo '<a href="messagerie.php">Conversations</a> &#124; ';
						echo '<a href="compte.php">Compte</a><br>';
						
						echo '<a href="login.php?logout">Déconnexion</a>';
						
						if(USER_RANG == 3) {
							echo ' &#124; <a href="admin/index.php">Administration</a>';
						}
						
						echo '</small>';
					}
				} else {

					if($etatJSONAPI == true) {
						echo '<img src="images/icone/online.png" style="margin-right:5px;">';
					} else {
						echo '<img src="images/icone/offline.png" style="margin-right:5px;">';
					}
					
					//Affichage de la liste entière des serveurs
					$req_selectServeurs = $connexion->prepare('SELECT * FROM '.$prefixe.'serveurs WHERE id != :id AND etat = 1');
					$req_selectServeurs->execute(array(
						'id' => $selectServeur['id'],
					));
					$nbr_selectServeurs = $req_selectServeurs->rowCount();
					
					if($nbr_selectServeurs > 0) {
						echo '<a class="affServeurs" href="#">'.$selectServeur['nom'];
						echo '<img id="showServeurs" src="images/arrow_chooseServeur.png" style="margin-left: 5px; float:right; position: relative; top: 2px;"></a>';
						
						echo '<div id="listeServeurs">';
						while($selectServeurs = $req_selectServeurs->fetch()) {
							echo '<a class="chooseServeur" id="serveur'.$selectServeurs['id'].'" style="margin-left: 17px;">'.$selectServeurs['nom'].'</a><br>';
						}
						echo '</div>';
					} else {
						echo '<strong>'.$selectServeur['nom'].'</strong>';
					}
					
					echo '<br>IP: <strong>'.$selectServeur['ip'].'</strong><br>';
					
					if($etatJSONAPI == true) {
						echo 'Joueurs en ligne: <strong>'.$playercount.' / '.$playerlimit.'</strong><br>';
					}
				}
				echo '</div>';
			}
			?>
		</div>
		
		<div id="navigation">
            <ul class="sf-menu">
				<?php
				//listePlugins('header', 'first');
				
				$req_selectOnglets = $connexion->query('SELECT * FROM '.$prefixe.'onglets WHERE niv = "main" OR niv = "nav" ORDER BY pos ASC');
				while($selectOnglets = $req_selectOnglets->fetch()) {
					if($selectOnglets['new'] == '1') {
						$newOnglet = 'target="_blank"';
					} else {
						$newOnglet = null;
					}
					
					echo '<li>';
					
					if($selectOnglets['niv'] == "main") {
						echo '<a '.$newOnglet.' class="nav nm" title="'.$selectOnglets['nom'].'" href="'.$selectOnglets['href'].'">'.$selectOnglets['nom'].'</a>';
					}
					
					if($selectOnglets['niv'] == "nav") {
						echo '<a class="nav nm" title="'.$selectOnglets['nom'].'" href="">'.$selectOnglets['nom'].' <img class="arrow" src="images/arrow_down.png"></a>';
						echo '<ul class="niveau2" style="left:0px; top:40px;">';
							
							$req_selectOngletsNav = $connexion->query('SELECT * FROM '.$prefixe.'onglets WHERE niv = "'.$selectOnglets['id'].'" ORDER BY pos ASC');
							while($selectOngletsNav = $req_selectOngletsNav->fetch()) {
								if($selectOngletsNav['new'] == '1') {
									$newOnglet = 'target="_blank"';
								} else {
									$newOnglet = null;
								}
								
								echo '<li>';
								echo '<a '.$newOnglet.' class="nav autre" title="'.$selectOngletsNav['nom'].'" href="'.$selectOngletsNav['href'].'">'.$selectOngletsNav['nom'].'</a>';
								echo '</li>';
							}
							
						
						echo '</ul>';
					}
					echo '</li>';
				}
				
				//include("custom/onglets.php");
				//listePlugins('header', null);
				?>
			</ul>
			<span class="reseaux">
				<?php
				if(!empty($twitter)) { echo '<a href="'.$twitter.'" target="_blank"><img src="images/twitter.png" alt="twitter" title="Twitter"></a>'; }
				if(!empty($facebook)) { echo '<a href="'.$facebook.'" target="_blank"><img src="images/facebook.png" alt="facebook" title="Facebook"></a>'; }
				if(!empty($youtube)) { echo '<a href="'.$youtube.'" target="_blank"><img src="images/youtube.png" alt="youtube" title="YouTube"></a>'; }
				?>
			</span>
		</div>
		<?php
		if($titre_page=="accueil") { ?>
		<div id="slider">
			<div id="featured" >
				<ul class="ui-tabs-nav">
				<?php
				$sql= $connexion->query("SELECT * FROM ".$prefixe."slider");
				$sql->setFetchMode(PDO::FETCH_OBJ);
				$sql1= $connexion->query("SELECT * FROM ".$prefixe."slider");
				$sql1->setFetchMode(PDO::FETCH_OBJ);
				$i = 0;
				while($req = $sql->fetch()) {
					$slider = secure($req->slider);
					$titre = secure($req->titre);
					$i++;
					if($titre != 'false') {
					?>
					<li class="ui-tabs-nav-item" id="nav-fragment-<?php echo $i; ?>"><a href="#fragment-<?php echo $i; ?>"><img src="<?php echo $slider; ?>" alt="" /><span><?php echo $titre; ?></span></a></li>
				<?php } } ?>
				</ul>
				<?php
				$i = 0;
				while($req = $sql1->fetch()) {
					$slider = secure($req->slider);
					$titre = secure($req->titre);
					$content = secure($req->content);
					$i++;
					if($titre != 'false') {
					?>
					<div id="fragment-<?php echo $i; ?>" class="ui-tabs-panel" style="">
						<img src="<?php echo $slider; ?>" alt="">
						<div class="info" >
							<h2><a href="#"><?php echo $titre; ?></a></h2>
							<p><?php echo $content; ?></p>
						</div>
					</div>
				<?php } } ?>
			</div>
		</div>
		<?php } ?>