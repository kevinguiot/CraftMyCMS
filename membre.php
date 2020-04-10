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

$titre_page = "Membres";
include("include/init.php");

//Si on recherche le pseudo d'une personne
if(!empty($_GET['searchPseudo'])) {
	$i = 1;
	$pseudo = strip_tags(substr($_GET['searchPseudo'],0, 100));
	if(isset($pseudo)) {
		$string = null;
		$req_selectMembre = $connexion->prepare("SELECT * FROM ".$prefixe."membres WHERE pseudo LIKE :pseudo");
		$req_selectMembre->execute(array(':pseudo' => $pseudo.'%'));
		$nbr_selectMembre = $req_selectMembre->rowCount();
		if ($nbr_selectMembre > 0) {
			$string = '<table>';
			while($selectMembre = $req_selectMembre->fetch()){
				$string .= '<tr class="searchListeMembre"><td><a href="?pseudo='.$selectMembre['pseudo'].'"><img src="skin.php?pseudo='.$selectMembre['pseudo'].'"></a></td><td><a style="font-weight:bold;" href="?pseudo='.$selectMembre['pseudo'].'">'.$selectMembre['pseudo'].'</a><br><a href="messagerie.php?action=ecrire&dest='.$selectMembre['pseudo'].'">Envoyer un message</a></td></tr>';
			}
			$string .= '<table>';
		} else {
			$string = "<a href=\"?pseudo=$pseudo\">Accéder au profil de <strong>$pseudo</strong></a>.";
		}
		echo $string;
	}
	$i++;
	exit;
}

include("include/header.php");

//On récpère le pseudo
$getPseudo = @secure(strip_tags($_GET['pseudo']));

if($etatJSONAPI == true) {
	if(@empty($_GET['aff']) || !is_numeric(@$_GET['aff']) || (@$_GET['aff'] < 1 || @$_GET['aff'] > 3)) {
		$aff = "1";	
	} else {
		$aff = $_GET['aff'];
	}
	$affProfil = "1";
} else {
	$aff = "3";
	$affProfil = "2";
}
?>

<div id="content" class="membre">
	<?php
	
	//Si un pseudo est saisi
	if(!empty($getPseudo)) {
		
		$req_getPseudoInfoSite = $connexion->prepare("SELECT * FROM ".$prefixe."membres WHERE pseudo=:pseudo");
		$req_getPseudoInfoSite->execute(array(
			'pseudo' => $getPseudo,
		));
		
		$nbr_pseudoSite = $req_getPseudoInfoSite->rowCount();
		
		if($nbr_pseudoSite == 1) {
			$getPseudoInfoSite = $req_getPseudoInfoSite->fetch();
	
			$informationsProfil = array('naissance', 'travail', 'localisation', 'web');
			foreach($informationsProfil as $info) {
				if(empty($getPseudoInfoSite[$info])) {
					$getPseudoInfoSite[$info] = "Non-renseigné";
				} else {
					if($info == "web") {
						$getPseudoInfoSite['web'] = '<a href="'.$getPseudoInfoSite['web'].'" target="_blank">'.$getPseudoInfoSite['web'].'</a>';
					}
				}
			}
			
			$rangPseudo = $getPseudoInfoSite['rang'];
			if(empty($getPseudoInfoSite['date'])) {
				$inscriptionSite = false;
			} else {
				$inscriptionSite = $getPseudoInfoSite['date'].' à '.$getPseudoInfoSite['heure'];
			}
				
				
			if($rangPseudo == "2") { $rangPseudo = array('Modérateur', 'green', 'normal'); }
			elseif($rangPseudo == "3") { $rangPseudo = array('Administrateur', '#AA0303', 'bold'); }
			elseif($rangPseudo == "1") { $rangPseudo = array('Membre', 'black', 'normal'); }
			else { $rangPseudo = array('Non-inscrit', 'black', null); }
	
			$tokensPseudo = $getPseudoInfoSite['token'];
			if(empty($tokensPseudo)) {
				$tokensPseudo = '0';
			}
			
			if(!empty($getPseudoInfoSite['ddate']) && !empty($getPseudoInfoSite['dheure'])) {
				$derniereConnexion =  $getPseudoInfoSite['ddate'].' à '.$getPseudoInfoSite['dheure']; 
			} else {
				$derniereConnexion = 'Non renseigné';
			}
			
			if(!empty($getPseudoInfoSite['date']) && !empty($getPseudoInfoSite['heure'])) {
				$dateInscription =  $getPseudoInfoSite['date'].' à '.$getPseudoInfoSite['heure']; 
			} else {
				$dateInscription = 'Non renseigné';
			}
		} 

		$isOnline = false;
		$isOperateur = false;
		
		if($etatJSONAPI != false) {
			$onlinePlayers = $api->call('getPlayerNames');
			$offlinePlayers = $api->call('getOfflinePlayerNames');
			$allPlayers = array_merge($onlinePlayers['success'], $offlinePlayers['success']);
			
			$getPseudoInfoServeur = $api->call('getPlayer', array($getPseudo));
			
			if($getPseudoInfoServeur['result'] != 'error' && !empty($getPseudoInfoServeur['success'])) {
				
				$getPseudoInfoServeur = $getPseudoInfoServeur['success'];
				
				$groupePseudo = $api->call('permissions.getGroups', array($getPseudo));
				$groupePseudo = @$groupePseudo['success']['0'];
				if(empty($groupePseudo)) {
					$groupePseudo = "Indéfini";
				}
				
				//Récupère le solde du joueur
				$moneyPseudo = $api->call('econ.getBalance', array($getPseudo));
				if(!empty($moneyPseudo['success'])) {
					$moneyPseudo = $moneyPseudo['success'];
					if(strstr($moneyPseudo, '.')) {
						$moneyPseudo = str_replace(strstr($moneyPseudo, '.'), null, $moneyPseudo);
					}
				} else {
					$moneyPseudo = 'Indéfini';
				}
				
				$levelPseudo = $getPseudoInfoServeur['level'];
				$experiencePseudo = $getPseudoInfoServeur['experience'];
				$healthPseudo = $getPseudoInfoServeur['health'].'/20';
				$foodPseudo = $getPseudoInfoServeur['foodLevel'].'/20';
				$isOnline = $getPseudoInfoServeur['ip'];
				$isOperateur = $getPseudoInfoServeur['op'];
				
				$isBanned = $getPseudoInfoServeur['banned'];
				if($isBanned == false) {
					$isBanned = "Non";
				} else {
					$isBanned = "Oui";
				}
				
				$armor = 0;
				if(!empty($getPseudoInfoServeur['inventory']['armor']['helmet'])) { $armor = $armor + 1.5; }
				if(!empty($getPseudoInfoServeur['inventory']['armor']['boots'])) { $armor = $armor + 1.5; }
				if(!empty($getPseudoInfoServeur['inventory']['armor']['leggings'])) { $armor = $armor + 6; }
				if(!empty($getPseudoInfoServeur['inventory']['armor']['chestplate'])) { $armor = $armor + 8; }
				$armor = $armor."/20";
	
				if(is_null($getPseudoInfoServeur['gameMode'])) {
					$firstPlayed=$lastPlayed=false;
				} else {
					$firstPlayed = date("d/m/Y à H:i:s", $getPseudoInfoServeur['firstPlayed']);
					$lastPlayed = $getPseudoInfoServeur['lastPlayed'];
					if($lastPlayed == "0") {
						$lastPlayed = $firstPlayed;
					} else {
						$lastPlayed = date("d/m/Y à H:i:s", $lastPlayed);
					}
				}
				if($isOnline != "offline" && $isOnline != "") {
					$isOnline = true;
				} else {
					$isOnline = false;
				}
				
				$world = $getPseudoInfoServeur['worldInfo']['name'];
				$errorJSONAPI = null;
				
			} elseif($getPseudoInfoServeur['result'] == 'success' && empty($getPseudoInfoServeur['success'])) {
				$errorJSONAPI = 'emptyInfos';
			} else {
				$errorJSONAPI = $getPseudoInfoServeur['error'];
			}
		} else {
			$groupePseudo = $moneyPseudo = $levelPseudo = $experiencePseudo = $healthPseudo = $foodPseudo = "Inconnu";
		}

		$skin3d = 'http://www.craftmycms.fr/ressources/skin3d.php?a=0&w=0&wt=0&abg=0&abd=0&ajg=0&ajd=0&ratio=30&format=png&displayHairs=true&headOnly=true&login='.$getPseudo;
		if(!@file_get_contents($skin3d)) {
			$skin3d = 'images/skin3d.png';
		}
		?>
		
		<div id="affichage_profil">
			<img src="<?php echo $skin3d; ?>" alt="skin">
			<h3 class="pseudo">
				<?php
				echo $getPseudo;
				if($isOnline == TRUE) {
					echo '<img src="images/icone/online.png" alt="online" title="'.$getPseudo.' est connecté sur le serveur">';
				} else {
					echo '<img src="images/icone/offline.png" alt="offline" title="'.$getPseudo.' n\'est pas connecté sur le serveur">';
				}
				?>
			</h3>
			
			<div class="infosMembre first">
				<?php
				if($nbr_pseudoSite == "1") {
					echo '<strong>Son âge: </strong>'.age($getPseudoInfoSite['naissance']).'.<br>';
					echo '<strong>Sa localisation: </strong>'.$getPseudoInfoSite['localisation'].'.<br>';
					echo '<strong>Son travail/étude: </strong>'.$getPseudoInfoSite['travail'].'.<br>';
					echo '<a title="Envoyer un message à '.$getPseudo.'" href="messagerie.php?destinataire='.$getPseudo.'">Envoyer un message à '.$getPseudo.'</a>.';
				} else {
					echo "Ce joueur n'est pas encore inscrit sur le site.<br>";
				}
				?>
			</div>

			<br style="clear: both;">

			<div id="div3"></div>
			<a id="tab3"></a>
			
			<ul id="tabmenu">
				<?php
				echo '<li onclick="makeactive(1)"><a id="tab1"';
				
				if($affProfil == "1") {
					echo 'class="active"';
				}
				
				echo '>Informations du joueur (IG)</a></li>';
				echo '<li onclick="makeactive(2)"><a id="tab2"';
				
				if($affProfil == "2") {
					echo 'class="active"';
				}
				
				echo '>Informations du membre (SITE)</a></li>';
				?>
			</ul>
		
			<div class="tab_bar"></div>
			
			<div style="padding:10px;" id="div1" <?php if($affProfil == '1') { echo 'class="active"'; } else { echo 'class="desactive"'; } ?>>
				<?php if($etatJSONAPI == TRUE && empty($errorJSONAPI)) { ?>
					<table class="table table-bordered table-striped infos">
						<tr>
							<td>
								<strong><img src="images/profil/etoile.png">Grade: </strong>
								<?php
								echo $groupePseudo;
								if($isOperateur == TRUE) {
									echo '&nbsp;<span style="color:red; font-weight:bold;">[OP]';
								}
								?>
							</td>
							<td>
								<strong><img src="images/profil/argent.png">Argent IG: </strong><?php echo $moneyPseudo; ?>
							</td>
							<td>
								<strong><img src="images/profil/bannis.png">Bannis: </strong><?php echo $isBanned; ?>
							</td>
		
						</tr>
						<tr>
							<td>
								<strong><img src="images/profil/xp.gif">Niveau(x): </strong><?php echo $levelPseudo; ?>
							</td>
							<td>
								<strong><img src="images/profil/xp.gif">Expérience: </strong><?php echo $experiencePseudo; ?>
							</td>
							<td>
								<strong><img src="images/profil/world.png">Monde: </strong><?php echo $world; ?>
							</td>
						</tr>
						<tr>
							<td>
								<strong><img src="images/profil/coeur.png">Barre de vie: </strong><?php echo $healthPseudo; ?>
							</td>
							<td>
								<strong><img src="images/profil/food.png">Barre de faim: </strong><?php echo $foodPseudo; ?>
							</td>
							<td>
								<strong><img src="images/profil/armor.png">Armure: </strong><?php echo $armor; ?>
							</td>
						</tr>
					</table>
					
					<?php
					if($activeInventaire == true) {
						echo '<div id="getInventory"><img src="images/chargementBig.gif" class="inventaire"></div>';
						$inventory = $getPseudoInfoServeur['inventory'];
						$inventory = serialize($inventory);
						$inventory = base64_encode($inventory);
					}
				
					echo '<div id="more_infos">';
					echo '<ul>';
					
					if($firstPlayed != false) { 
						echo "<li><strong>Première connexion sur le serveur:</strong> $firstPlayed</li>";
						echo "<li><strong>Connexion la plus récente sur le serveur:</strong> $lastPlayed</li>";
					} else {
						echo "<li>Ce joueur ne s'est jamais connecté sur le serveur.</li>";
					}
					
					echo '</li>';
					echo '</ul>';
					echo '</div>';
				} elseif(!empty($errorJSONAPI)) {
					$erreur = 'Il est impossible d\'afficher le profil de ce joueur via le plugin JSONAPI.<br>';
					
					if($errorJSONAPI == "emptyInfos") {
						$erreur .= "Le serveur ne renvoi aucune information concernant le joueur.";
					} else {
						$erreur .= 'Veuillez consulter l\'erreur obtenue: <a href="include/error_log/'.$errorJSONAPI.'.txt">'.$errorJSONAPI.'</a>.';
					}
					
					msg($erreur, 'r', '', '', 0);
				} else {
					msg("Il est impossible de récupérer les données du serveur via le plugin JSONAPI.<br>Veuillez contacter l'administrateur de ce site.", 'r', '', '', '0');
				}
				?>
			</div>

			<div style="padding:10px;" id="div2" <?php if($affProfil == '2') { echo 'class="active"'; } else { echo 'class="desactive"'; } ?>>
				<?php
				if($nbr_pseudoSite == "1") { ?>
					<h3>Informations générales</h3>
					<div class="infosMembre">
						<strong>Rang: </strong><?php echo '<span style="color:'.$rangPseudo[1].'; font-weight:'.$rangPseudo[2].';">'.$rangPseudo[0].'</span>'; ?><br>
						<strong>Dernière connexion: </strong><?php echo $derniereConnexion; ?><br>
						<strong>Inscription: </strong><?php echo $dateInscription; ?><br>
					</div><br>
					
					<h3>Informations personnelles</h3>
					<div class="infosMembre">
						<strong>Sa date de naissance: </strong><?php echo $getPseudoInfoSite['naissance']; ?><br>
						<strong>Son site internet: </strong><?php echo $getPseudoInfoSite['web']; ?>
					</div>
				<?php } else {
					msg("Ce joueur n'est pas encore inscrit sur ce site.", 'b', '', '', '0');
				}
				?>
			</div>
		</div>
	<?php
	} else {
		if($etatJSONAPI == true) {
			$onlinePlayers = $api->call("getPlayerNames");
			$onlinePlayers = $onlinePlayers['success'];
			$nbr_onlinePlayers = count($onlinePlayers);
			$offlinePlayers = $api->call('getOfflinePlayerNames');
			$allPlayers = array_merge($onlinePlayers, $offlinePlayers['success']);
			$allPlayers_nb = count($allPlayers);
		} else {
			$nbr_onlinePlayers = "?";
			$allPlayers_nb = "?";
		}
		
		$count_membresSite = $connexion->query("SELECT * FROM ".$prefixe."membres");
		$count_membre = $count_membresSite->rowCount();
		?>
		<ul id="tabmenu" > 
			<li onclick="makeactive(1)"><a <?php if($aff=="1") { echo 'class="active"'; } ?> id="tab1">Joueurs connectés (<?php echo $nbr_onlinePlayers; ?>)</a></li> 
			<li onclick="makeactive(2)"><a <?php if($aff=="2") { echo 'class="active"'; } ?> id="tab2">Joueurs au total (<?php echo $allPlayers_nb; ?>)</a></li>
			<li onclick="makeactive(3)"><a <?php if($aff=="3") { echo 'class="active"'; } ?> id="tab3">Joueurs inscrits (<?php echo $count_membre; ?>)</a></li>
			<li style="float: right;"><a href="#?w=500" rel="rechercheMembre" class="poplight search"><img src="images/icone/search.png" alt="search" title="Rechercher un membre"></a></li>
		</ul>
		<div class="tab_bar"></div>
		<div id="rechercheMembre" class="popup_block" style="font-size: small;">
			
			<script type='text/javascript'>
			$(document).ready(function(){
				$("#search_results").slideUp();
				$("#button_find").click(function(event){
					event.preventDefault();
					search_ajax_way();
				});
				$("#search_query").keyup(function(event){
					event.preventDefault();
					search_ajax_way();
				});
				return false;
			});
			
			function search_ajax_way(){
				$("#search_results").show();
				var search_this=$("#search_query").val();
				if (search_this == "") {
					$("#display_results").html(null);
				} else {
					$("#display_results").html('Veuillez patienter...');
					$.get("membre.php", {searchPseudo : search_this}, function(data){
						$("#display_results").html(data);
					})
				}
			}
			</script>
			
			<h3>Rechercher un membre inscrit sur le site</h3>
			<form id="searchform" method="post">
				<input type="text" name="search_query" id="search_query" placeholder="Rechercher un membre...">
				<div id="display_results"></div>
			</form>
		</div>
		
		<div id="affichage_membre">
			<?php
			$page = @$_GET['p'];
			$max = 19;
			$i = 0;
			if(empty($page) || $page == 1 || $page == 0 || !is_numeric($page)) {
				$l = '0';
				$page = "1";
			} else {
				$l = $page*"2"-"2"."0";
			}
			?>
			<div id="div1" <?php if($aff == '1') { echo 'class="active"'; } else { echo 'class="desactive"'; } ?>>
				<?php
				if($etatJSONAPI == true) {
					if(empty($onlinePlayers[0])) {
						echo "<h3>Aucun joueur n'est connecté pour le moment</h3>";
					} else {
						echo '<table class="table table-bordered table-striped">';
						foreach($onlinePlayers as $player) {
							
							$pseudo = $player;
							if(getUserInfos('pseudo', $pseudo, 'rang') == "3") {
								$pseudo = '<span style="color:#AA0303;">'.$pseudo.'</span>';
							} elseif(getUserInfos('pseudo', $pseudo, 'rang') == "2") {
								$pseudo = '<span style="color:green;">'.$pseudo.'</span>';
							}
							
							echo '<tr>';
							echo '<td><a href="?pseudo='.$player.'"><img alt="skin" src="skin.php?pseudo='.$player.'"></td>';
							echo '<td><a href="membre.php?pseudo='.$player.'">'.$pseudo.'</a><br><a href="messagerie.php?destinataire='.$player.'">Envoyer un message</a></td>';
							echo '</tr>';
							
						}
						echo '</table>';
						pagination($page, 1, $max, $onlinePlayers);
					}
				} else {
					msg("Il est impossible de récupérer les données du serveur via le plugin JSONAPI.<br>Veuillez contacter l'administrateur de ce site.", 'b', '', '', '10');
				}
				?>
			</div>
			<div id="div2" <?php if($aff == '2') { echo 'class="active"'; } else { echo 'class="desactive"'; } ?>>
				<?php
				$i = 0;
				if($etatJSONAPI == true) {
					if($allPlayers_nb>"0") {
						echo '<table class="table table-bordered table-striped">';
						natcasesort($allPlayers);
						foreach ($allPlayers as $player) {
							$i++;
							if($allPlayers_nb<$l OR $i>$l) {
								
								$pseudo = $player;
								if(getUserInfos('pseudo', $pseudo, 'rang') == "3") {
									$pseudo = '<span style="color:#AA0303;">'.$pseudo.'</span>';
								} elseif(getUserInfos('pseudo', $pseudo, 'rang') == "2") {
									$pseudo = '<span style="color:green;">'.$pseudo.'</span>';
								}
								
								echo '<tr>';
								echo '<td><a href="?pseudo='.$player.'"><img alt="skin" src="skin.php?pseudo='.$player.'"></td>';
								echo '<td><a href="membre.php?pseudo='.$player.'">'.$pseudo.'</a><br><a href="messagerie.php?destinataire='.$player.'">Envoyer un message</a></td>';
								echo '</tr>';
								
								if($i>$l+$max) {
									break;
								}
								
							}
						}
						echo '</table>';
						pagination($page, 2, $max, $allPlayers);
					} else {
						echo "<h3>Aucun joueur n'a été connecté sur le serveur</h3>";
					}
				} else {
					msg("Il est impossible de récupérer les données du serveur via le plugin JSONAPI.<br>Veuillez contacter l'administrateur de ce site.", 'b', '', '', '10');
				}
				?>
			</div>
			<div id="div3" <?php if($aff == '3') { echo 'class="active"'; } else { echo 'class="desactive"'; } ?>>
				<?php
				$i = 0;
				
				echo '<table class="table table-bordered table-striped">';
				$premiereEntree=($page-1)*19;
				$req_membresSite = $connexion->query("SELECT * FROM ".$prefixe."membres ORDER BY rang DESC LIMIT $premiereEntree, 19 ");
				while($membresSite = $req_membresSite->fetch()) {
					
					$pseudo = $membresSite['pseudo'];;
					if(getUserInfos('pseudo', $pseudo, 'rang') == "3") {
						$pseudo = '<span style="color:#AA0303;">'.$pseudo.'</span>';
					} elseif(getUserInfos('pseudo', $pseudo, 'rang') == "2") {
						$pseudo = '<span style="color:green;">'.$pseudo.'</span>';
					}
					
					echo '<tr>';
					echo '<td><a href="?pseudo='.$membresSite['pseudo'].'"><img alt="skin" src="skin.php?pseudo='.$membresSite['pseudo'].'"></td>';
					echo '<td><a href="membre.php?pseudo='.$membresSite['pseudo'].'">'.$pseudo.'</a><br><a href="messagerie.php?destinataire='.$membresSite['pseudo'].'">Envoyer un message</a></td>';
					echo '</tr>';
					
					if($i>$l+$max) {
						break;
					}
				}
				echo '</table>';
				pagination($page, 3, $max, $count_membre);
				?>
			</div>
		</div>
<?php } ?>
</div>

<?php include('include/footer.php');?>

<script language="JavaScript" type="text/javascript">
function makeactive1(tab) { 
    document.getElementById("tab1").className = ""; 
    document.getElementById("tab2").className = ""; 
    document.getElementById("tab"+tab).className = "active";
    document.getElementById("div1").className = "desactive"; 
    document.getElementById("div2").className = "desactive"; 
    document.getElementById("div"+tab).className = "active"; 
}

function makeactive(tab) { 
    document.getElementById("tab1").className = ""; 
    document.getElementById("tab2").className = ""; 
    document.getElementById("tab3").className = "";
    document.getElementById("tab"+tab).className = "active";
    document.getElementById("div1").className = "desactive"; 
    document.getElementById("div2").className = "desactive"; 
    document.getElementById("div3").className = "desactive";
    document.getElementById("div"+tab).className = "active"; 
}

</script>
<script type="text/javascript" src="script/fade.js"></script>

<?php
if(!empty($_GET['pseudo']) && $etatJSONAPI == true && $activeInventaire == true) { ?>
	<script>
	$(window).load(function(){
		imgInventory = "<?php echo sendToCraftMyCMS('getInventory', array('inventory' => $inventory)); ?>";
		imgInventory = "http://system.craftmycms.fr/new/getInventory/save/" + imgInventory;
		$('#getInventory').html('<img src="' + imgInventory + '" class="inventaire">');
	});
	</script>
<?php } ?>