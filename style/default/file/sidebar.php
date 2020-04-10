<div id="sidebar">
<?php
listePlugins('modules', 'first');
if(!connect()) { ?>
	<div class="block">
		<h3><a href="login.php">Connexion</a></h3>
		<div id="connexion" style="font-weight:bold;">
			<form method="post" action="login.php">
				<img src="images/icone/user.png"> Pseudo<br>
				<input type="text" name="pseudo"><br>
				<img src="images/icone/bullet_key.png"> Mot de passe<br>
				<input type="password" name="passe"><br>
				<span class="center"><input type="submit" value="Connexion"><a href="inscription.php">Inscription</a></span>
			</form>
		</div>
	</div>
<?php } else { ?>
	<div class="block">
		<h3>Espace membre</h3>	
		<div id="espace_membre">
			<span style="float:left; margin-left:7px; "><img src="skin.php?pseudo=<?php echo $pseudo ?>" alt="skin"></span>
			<span style="float:left; margin:1px 0 0 10px;">Bienvenue <br><strong><?php
			if($rang == "3") { echo '<span style="color:#AA0303;">'; }
			elseif($rang == "2") { echo '<span style="color:green;">'; }
			else { echo '<span style="color:black;">'; }
			echo $pseudo.'</span></strong>';
			?></span>
			<p style="clear:both;"></p>
			<a href="membre.php?pseudo=<?php echo $pseudo; ?>">Mon profil</a>
			<a href="messagerie.php">Mes conversations</a>
			<a href="compte.php">Mon compte<?php if(!empty($notification)) { echo $notification; } ?></a>
			<a href="login.php?logout">Déconnexion</a>
			<?php
			if($rang=="3") { ?>
				<br>
				<a href="admin/index.php" class="admin">Administration</a>
				<?php
				$maj = file_get_contents("include/config/maj.txt");
				if(strstr($maj, 'false')) {
					$maj = false;
				}
				?>
				<a href="admin/update.php" class="admin">Mises à jour<?php if($maj == false) { ?> <span class="notification">!</span><?php } else { echo '<img style="vertical-align:center; left:4px; top:2px; position:relative;" src="images/true.png">'; } ?></a>
			<?php } if($rang == "2") {
				echo '<br>';
				echo '<a href="admin/index.php" class="admin">Modération</a>';
			}
			?>
		</div>
	</div>
	<?php }
	
	if($etatJSONAPI == TRUE) { ?>
	<div class="block">
		<h3>Joueurs en ligne</h3>
		<?php
		if($playercount>0) {
			echo '<div id="onlinePlayers">';
			$players = $players["success"];
			
			foreach($players as $player) {
				$player = secure($player);
				echo '<a title="Afficher le profil de '.$player.'" href="membre.php?pseudo='.$player.'"><img src="skin.php?pseudo='.$player.'" alt="'.$player.'"';
				
				$req_selectMembre = $connexion->prepare('SELECT rang FROM '.$prefixe.'membres WHERE pseudo=:pseudo');
				$req_selectMembre->execute(array(
					'pseudo' => $player,
				));
				$selectMembre = $req_selectMembre->fetch();
				
				if($selectMembre[0] == "3") {
					echo ' class="admin"';
				}
				
				if($selectMembre[0] == "2") {
					echo ' class="moderateur"';
				}
				
				echo '></a>';
			}
			echo '</div>';
		} else {
			echo '<div id="onlinePlayers" class="aucun">Aucun joueur connecté</div>';
		}
		?>
	</div>
	<?php }
	
	if($activeBlocInfos == TRUE && $etatJSONAPI == TRUE) {
		echo '<div class="block">';
		echo '<h3>Informations</h3>';
		echo '<div id="newsletter" class="center">';

		$server = $api->call("getServer");
		
		if(!empty($server['success'])) {
			$date1 = $server["success"]["worlds"]["0"]["time"];
			$weather = $server["success"]["worlds"]["0"]["hasStorm"];
			$isThundering = $server["success"]["worlds"]["0"]["isThundering"];
			
			if($date1>"12499" AND $date1<"24000") { $temps = "nuit"; }
			$heure = true;
		}
		
		$version = $api->call("getBukkitVersion");
		$version = $version['success'];
		$properties = $api->call("getPropertiesFile", array("server"));
		$version2 = $properties["success"]["online-mode"];
		$whitelist = $properties["success"]["white-list"];
		
		if(!empty($heure) && $heure == true) {
			echo "Heure: "; if(!empty($temps) AND $temps=="nuit") {
				echo '<img src="images/moon.png" alt="lune" title="Il fait nuit.">';
			} else {
				echo '<img src="images/sun.png" alt="sun" title="Il fait jour.">';
			}
			
			echo ' Temps: ';
			
			if($isThundering=="true") {
				echo '<img src="images/cloud-rain-thunder.png" alt="eclair" style="padding-left:5px;" title="Temps orageux (avec éclairs).">';
			} elseif($weather == true) {
					echo '<img src="images/cloud-rain.png" alt="rain" style="padding-left:5px" title="Temps pluvieux.">';
			} else {
				echo '<img src="images/sun.png" alt="sun" title="Temps ensoleillé.">';
			}
			
			echo '<br>';
		}
		
		echo "Version CB: $version";
		echo "<br>Joueurs: "; if($version2=="false") { echo "Non-officiels"; } else { echo "Officiels"; }
		echo "<br>White-list: "; if($whitelist=="false") { echo "Désactivée"; } else { echo "Activée"; }

		echo '</div></div>';
	}
	
	if($activeBlocStats == true) { ?>
		<div class="block">
			<h3>Statistiques</h3>
			<div class="contenu stats">
				<ul>
					<li><strong>Membres inscrits:</strong><br><?php echo "$nbrMembreA aujourd'hui / $nbrMembre totals"; ?></li>
					<?php echo '<li><strong>Dernier inscrit: </strong><br><a href="membre.php?pseudo='.$lastMembre.'" title="Aller sur le profil de '.$lastMembre.'"><img src="skin.php?pseudo='.$lastMembre.'&size=24">'.$lastMembre.'</a></li>'; ?>
					<li><strong>Visites:</strong><br><?php echo "$nbrVisiteA aujourd'hui / $nbrVisiteT totals"; ?></li>
				</ul>
			</div>
		</div>
	<?php }
	include('include/config/modules.php');
	listePlugins('modules', null);
	?>
</div>