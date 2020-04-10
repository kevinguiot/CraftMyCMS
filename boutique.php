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

$titre_page = "Boutique";
include("include/init.php");

if(connect()) {
	$req_selectMembre = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE pseudo=:pseudo');
	$req_selectMembre->execute(array(
		'pseudo' => $pseudo
	));
	$selectMembre = $req_selectMembre->fetch();
	$nbrToken = $selectMembre['token'];
	
	//Envoi de token
	if(strstr($_SERVER['REQUEST_URI'], 'envoiToken') && $_POST) {
		
		//Si la valeur est bien un chiffre
		if(ctype_digit($_POST['valeur'])) {
			$valeur = (int) $_POST['valeur'];
			
			//Si la valeur est positive
			if($valeur >= 0) {
				
				//Si l'utilisateur a assez de tokens
				if($valeur <= $money_nbr) {
					
					//Envoi des tokens.
					$req_selectMembreEnvoi = $connexion->prepare('SELECT * FROM '.$prefixe.'membres WHERE pseudo=:pseudo');
					$req_selectMembreEnvoi->execute(array(
						'pseudo' => $_POST['pseudo']
					));
					$selectMembreEnvoi = $req_selectMembreEnvoi->fetch();
					
					if(!empty($selectMembreEnvoi)) {
						$enlevCredit = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token - :token WHERE pseudo = :pseudo');
						$enlevCredit->execute(array(
							'pseudo' => $pseudo,
							'token' => $valeur,
						));
						
						$ajoutCommande = $connexion->prepare('INSERT INTO '.$prefixe.'commandes SET nb=:nb, pseudo=:pseudo, date=:date, heure=:heure');
						$ajoutCommande->execute(array(
							'nb' => rand(1000000000, 9999999999).' (Envoi crédit: suppression)',
							'pseudo' => $pseudo,
							'date' => $date,
							'heure' => $heure
						));
			
						$ajoutCommande = $connexion->prepare('INSERT INTO '.$prefixe.'commandes SET nb=:nb, pseudo=:pseudo, date=:date, heure=:heure');
						$ajoutCommande->execute(array(
							'nb' => rand(1000000000, 9999999999).' (Envoi crédit: ajout)',
							'pseudo' => $_POST['pseudo'],
							'date' => $date,
							'heure' => $heure
						));
						
						$ajoutCredit = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token + :token WHERE pseudo = :pseudo');
						$ajoutCredit->execute(array(
							'pseudo' => $_POST['pseudo'],
							'token' => $_POST['valeur'],
						));

						header('location: ?envoiToken&msg=true');
					} else {
						header('location: ?envoiToken&msg=membre:false');
					}
				} else {
					header('location: ?envoiToken&msg=credit:insuffisant');
				}
			} else {
				header('location: ?envoiToken&msg=credit:erreur');
			}
		} else {
			header('location: ?envoiToken&msg=credit:erreur');
		}
		exit;
	}
	
	if($activeJSONAPI == true) {
		if(!empty($_GET['receive'])) {
			$req_selectBoutiqueListe = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_liste WHERE id=:id');
			$req_selectBoutiqueListe->execute(array(
				'id' => $_GET['receive']
			));
			$selectBoutiqueListe = $req_selectBoutiqueListe->fetch();
			
			if(!empty($selectBoutiqueListe)) {
				if($selectBoutiqueListe['pseudo'] == USER_PSEUDO && $selectBoutiqueListe['etat'] == 0) {
					$req_selectArticle = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id_boutique');
					$req_selectArticle->execute(array(
						'id_boutique' => $selectBoutiqueListe['id_boutique']
					));
					$selectArticle = $req_selectArticle->fetch();
					if(!empty($selectArticle)) {

						//On charge le serveur qu'on va utilisé
						include('include/class/getServeur.php');
						
						if($etatJSONAPI == true) {
							$playerconnect = $api->call("getPlayer", array($pseudo));
	
							if($playerconnect['result'] == 'success' && $playerconnect["success"]["ip"] != "offline") {
								$updateBoutiqueListe = $connexion->prepare('UPDATE '.$prefixe.'boutique_liste SET etat=1 WHERE id=:id');
								$updateBoutiqueListe->execute(array('id' => $_GET['receive']));
								
								$commande = $selectArticle['commande'];
								
								if(strstr($commande, '[{NEW}]')) {
									$commandeArray = explode('[{NEW}]', $commande);
									foreach($commandeArray as $commande) {
										$commande = str_replace('$player', $pseudo, $commande);
										$envoiCommande = $api->call("runConsoleCommand", array($commande));
									}
								} else {
									$commande = str_replace('$player', $pseudo, $commande);
									$envoiCommande = $api->call("runConsoleCommand", array($commande));
								}
								header('location: compte.php?msg=receive:true'); 
							} else {
								header('location: compte.php?msg=receive:false'); 
							}
						} else {
							header('location: compte.php?msg=receive:false'); 
						}
						exit;
					}
				}
			}
			header('location: compte.php?msg=receive:false');
			exit;
		}
	
		if(!empty($_GET['id'])) {
			$req_selectArticle = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id');
			$req_selectArticle->execute(array(
				'id' => $_GET['id']
			));
			$selectArticle = $req_selectArticle->fetch();
			if(!empty($selectArticle)) {
				
				if(@$_GET['moyenPaiement'] == 'site') {
					$moyenPaiement = 'site';
				} elseif (@$_GET['moyenPaiement'] == 'serveur') {
					$moyenPaiement = 'serveur';
				} else {
					die('credit:false');
				}

				if($moyenPaiement == "site" || $moyenPaiement == "serveur") {
					if($moyenPaiement == "serveur" && $activeMoneyIG == false) {
						$moyenPaiement = "site";
					}
				} else {
					$moyenPaiement = "site";
				}
				
				if($selectArticle['limite'] == true) {
					$req_selectAchat = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_liste WHERE id_boutique=:id AND pseudo=:pseudo');
					$req_selectAchat->execute(array(
						'id' => $selectArticle['id'],
						'pseudo' => USER_PSEUDO
					));
					$nbr_selectAchat = $req_selectAchat->rowCount();
					if($nbr_selectAchat > 0) {
						die('limite:false');
					}
				}
				
				if(!empty($selectArticle['requis'])) {
					$req_selectAchat = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_liste WHERE id_boutique=:id AND pseudo=:pseudo');
					$req_selectAchat->execute(array(
						'id' => $selectArticle['requis'],
						'pseudo' => USER_PSEUDO
					));
					$selectAchat = $req_selectAchat->fetch();
					
					if(empty($selectAchat)) {
						$req_selectArticleRequis  = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id');
						$req_selectArticleRequis->execute(array('id' => $selectArticle['requis']));
						$selectArticleRequis  = $req_selectArticleRequis->fetch();
						
						$requis = $selectArticleRequis['article'];
						die('requis:'.$requis);
					}
				}
				
				//On charge le serveur utilisé
				include('include/class/getServeur.php');
				if($etatJSONAPI == true) {
					$playerconnect = $api->call("getPlayer", array($pseudo));
					
					if($moyenPaiement == 'serveur') {
						$moneyPseudo = $api->call('econ.getBalance', array($pseudo));
						$moneyPseudo = $moneyPseudo['success'];
						if(strstr($moneyPseudo, '.')) {
							$moneyPseudo = str_replace(strstr($moneyPseudo, '.'), null, $moneyPseudo);
						}
		
						if($moneyPseudo >= $selectArticle['valeur_ig']) {
							$updatePseudo = $api->call("econ.withdrawPlayer", array($pseudo, $selectArticle['valeur_ig']));
						} else {
							die('credit_serveur:false');
						}
					}
					
					if($moyenPaiement == "site") {
						if($nbrToken >= $selectArticle['valeur']) {
							$updateMembre = $connexion->prepare('UPDATE '.$prefixe.'membres SET token = token - :token WHERE pseudo=:pseudo');
							$updateMembre->execute(array(
								'token' => $selectArticle['valeur'],
								'pseudo' => $pseudo
							));
						} else {
							die('credit_site:false');
						}
					}
					
					if(empty($_GET['autoReceive'])) {
						$autoReceive = 'saveByUser';
						$etat = false;
					} elseif ($playerconnect['result'] == 'error' || $playerconnect["success"]["ip"] == "offline") {
						$autoReceive = 'saveBySystem';
						$etat = false;
					} else {
						$autoReceive = 'receive';
						$etat = true;
						$commande = $selectArticle['commande'];
						
						if(strstr($commande, '[{NEW}]')) {
							$commandeArray = explode('[{NEW}]', $commande);
							foreach($commandeArray as $commande) {
								$commande = str_replace('$player', $pseudo, $commande);
								$envoiCommande = $api->call("runConsoleCommand", array($commande));
							}
						} else {
							$commande = str_replace('$player', $pseudo, $commande);
							$envoiCommande = $api->call("runConsoleCommand", array($commande));
						}
					}
		
					$updateBoutique = $connexion->prepare('UPDATE '.$prefixe.'boutique SET achat = achat + 1 WHERE id=:id');
					$updateBoutique->execute(array(
						'id' => $selectArticle['id']
					));
					$ajoutListe = $connexion->prepare("INSERT INTO ".$prefixe."boutique_liste (pseudo, id_boutique, date, heure, etat) VALUES (:pseudo, :id, :date, :heure, :etat)");
					$ajoutListe->execute(
						array(
							'pseudo' => $pseudo,
							'id' => $selectArticle['id'],
							'date' => $date,
							'heure' => $heure,
							'etat' => $etat,
						)
					);
					
					die('commande:'.$autoReceive);
				} else {
					die('error');
				}
			} else {
				die("article:false");
			}
		}
	}
}

include("include/header.php");
?>

<div id="content" class="boutique">
	<?php
	if(strstr($_SERVER['REQUEST_URI'], 'envoiToken')) {
		msg("Vous n'avez pas assez de $monnaie_site.", 'r', 'get', 'credit:insuffisant');
		msg("Une erreur est survenue, merci de réessayer ultérieurement.", 'r', 'get', 'credit:erreur');
		msg("Ce membre n'existe pas.", 'r', 'get', 'membre:false');
		msg("L'envoi de $monnaie_site a bien été effectué.", 'v', 'get', 'true');
		?>
		
		<h3>Envoi de <?php echo $monnaie_site; ?> à un ami</h3>
		<?php
		if($nbrToken > 0) { ?>
		<form method="post">
			<strong>Pseudo du membre:</strong> <input type="text" name="pseudo"><br>
			<strong>Valeur a envoyer:</strong>
			<input id="slider1" value="1" type="range" min="1" max="<?php echo $nbrToken; ?>" step="1" onchange="printValue('slider1','rangeValue1')">
			<input name="valeur" id="rangeValue1" type="text" size="2"><br>
			<input type="submit" value="Envoyer">
		</form>
		<?php } else {
			msg("Vous n'avez pas de $monnaie_site.", 'b');
		}
		?>
		</div>
		<?php include('include/footer.php'); ?>
		<script type="text/javascript">
		function printValue(sliderID, textbox) {
			var x = document.getElementById(textbox);
			var y = document.getElementById(sliderID);
			x.value = y.value;
		}
		window.onload = function() { printValue('slider1', 'rangeValue1'); printValue('slider2', 'rangeValue2'); printValue('slider3', 'rangeValue3'); printValue('slider4', 'rangeValue4'); }
		</script>
		<?php
		exit;
	}
	
	msg('Votre code est correct, vous venez de recevoir '.$valeur.' '.$monnaie_site.'.', 'v', 'get', 'code:true');
	msg('Votre code est incorrect, veuillez réessayer.', 'r', 'get', 'code:false');
	msg('Votre transaction PayPal a bien été envoyée et sera traitée dans quelques instants.', 'v', 'get', 'paypal:true');
	
	if($activeMoneyIG == true && $etatJSONAPI == true) {
		if(connect()) {
			$moneyPseudo = $api->call('econ.getBalance', array($pseudo));
			
			if($moneyPseudo['result'] == 'success') {
				$moneyPseudo = $moneyPseudo['success'];
				if(strstr($moneyPseudo, '.')) { $moneyPseudo = str_replace(strstr($moneyPseudo, '.'), null, $moneyPseudo); }
			} else {
				$moneyPseudo = "0";
			}
		}
		msg("Vous pouvez désormais payer vos articles avec l'argent du serveur.", 'b');
	}

	echo '<div class="nbrcredit">';
	if(connect()) {
		if($activeMoneyIG == true && $etatJSONAPI == true) {
			echo 'Vous avez <strong>'.$nbrToken.'</strong> '.$monnaie_site.' et <strong>'.$moneyPseudo.'</strong> '.$monnaie_serveur;
		} else {
			echo 'Vous avez <strong>'.$nbrToken.'</strong> '.$monnaie_site;
		}
		echo '.<br>';
		echo '<a href="commande.php">Commander des '.$monnaie_site.'</a> &bull; ';
		echo '<a href="boutique.php?envoiToken">Envoyer des '.$monnaie_site.'</a>';
		echo '</p>';
	} else {
		echo '<p>Vous devez être connecté sur le site pour acheter des crédits.</p>';
	}
	echo '</div>';
		
	if($etatJSONAPI == false) {
		msg("JSONAPI rencontre des problèmes, merci de contacter l'administrateur du site.", 'r');
		echo '</div>';
		include('include/footer.php');
		exit;
	} 
	
	echo '<div id="articles">';
	$req_categorie = $connexion->query("SELECT * FROM ".$prefixe."boutique_cat");
	$i = 0;
	$l = 0;
	while($categorie = $req_categorie->fetch()) {
		$req_article = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE categorie=:categorie AND serveur=:serveur');
		$req_article->execute(array(
			'categorie' => $categorie['id'],
			'serveur' => SERVEUR_ID,
		));
		$nbrArticle = $req_article->rowCount();
		if($nbrArticle>0) {
			$l = 1;
			echo '<h3>'.$categorie['categorie'].'</h3>';
			echo '<table><tr>';
			while($article = $req_article->fetch()) {
				$limit = false;
				$requis = false;
				
				$selectArticle = $article;
				$valeur_ig = $article['valeur_ig'];
				$valeurArticle = $article['valeur'];

				$description = str_replace("\n", null, $article['description']);
				$description = str_replace("\r", null, $description);
				$description = str_replace('"', "'", $description);

				if($article['limite'] == true && connect()) {
					$req_selectAchat = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_liste WHERE id_boutique=:id AND pseudo=:pseudo');
					$req_selectAchat->execute(array(
						'id' => $selectArticle['id'],
						'pseudo' => USER_PSEUDO
					));
					$nbr_selectAchat = $req_selectAchat->rowCount();
					if($nbr_selectAchat > 0) {
						$limit = true;
					}
				}
				
				if(!empty($article['requis']) && connect()) {
					$req_selectAchat = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique_liste WHERE id_boutique=:id AND pseudo=:pseudo');
					$req_selectAchat->execute(array(
						'id' => $article['requis'],
						'pseudo' => USER_PSEUDO
					));
					$selectAchat = $req_selectAchat->fetch();
					
					$req_selectArticleRequis  = $connexion->prepare('SELECT * FROM '.$prefixe.'boutique WHERE id=:id');
					$req_selectArticleRequis->execute(array('id' => $selectArticle['requis']));
					$selectArticleRequis  = $req_selectArticleRequis->fetch();
					
					$requis = $selectArticleRequis['article'];
			
					if(!empty($selectAchat)) {
						$requis = false;
					}
				}

				$donnee = array(
					$article['id'],
					$article['article'],
					$description,
					$article['valeur'],
					$article['valeur_ig'],
					$limit,
					$requis,
					$article['image'],
				);

				$divAchat = '<div id="divAchat'.$donnee[0].'" class="divAchat">';
				if($limit == false && $requis == false) {
					$divAchat .= '<img style="float:left;" src="'.$donnee[7].'">';
					$divAchat .= '<p style="float:left;">Vous êtes sur le point d\'acheter: <strong>'.$donnee[1].'</strong>.<br>';
					
					if($donnee[3] == "" && $donnee[4] == "") {
						$divAchat .= "Aucun moyen de paiement disponible";
						$moneyPaiement = "Aucun moyen de paiement disponible.";
					}
					
					if($donnee[3] != "" && $donnee[4] == "") {
						$divAchat .= 'Le prix est de <strong>'.$donnee[3].'</strong> '.$monnaie_site;
						$moneyPaiement = '<input type="radio" name="moyenPaiement" value="site" checked> '.$monnaie_site;
					}
					
					if($donnee[3] == "" && $donnee[4] != "") {
						$divAchat .= 'Le prix est de <strong>'.$donnee[4].'</strong> '.$monnaie_serveur;
						$moneyPaiement = '<input type="radio" name="moyenPaiement" value="serveur" checked> '.$monnaie_serveur;
					}
					
					if($donnee[3] != "" && $donnee[4] != "") {
						$divAchat .= 'Le prix est de <strong>'.$donnee[3].'</strong> '.$monnaie_site.' ou de <strong>'.$donnee[4].'</strong> '.$monnaie_serveur;
						$moneyPaiement = '<input type="radio" name="moyenPaiement" value="site" checked> '.$monnaie_site.' <input type="radio" name="moyenPaiement" value="serveur"> '.$monnaie_serveur;
					}
					
					$divAchat .= '.</p><br style="clear:both;"><br>';
					$divAchat .= '<strong>Description de l\'article:</strong>';
					$divAchat .= '<p class="description">'.$donnee[2].'</p><br>';
					$divAchat .= '<div class="divAchatOptions">';
					
					
					
					$divAchat .= "<strong>Moyen de paiement:</strong> ".$moneyPaiement."<br>";
					
					
					
					$divAchat .= '<input type="checkbox" name="autoReceive" value="1" checked>Recevez votre article directement après l\'achat.';
					$divAchat .= '</div>';
				} elseif($limit == true) {
					$divAchat .= '<div class="warning_b">Vous ne pouvez acheter cet article qu\'une seule fois.</div>';
				} else {
					$divAchat .= '<div class="warning_b">Vous devez acheter l\'article <u>'.$requis.'</u> pour pouvoir continuer.</div>';
				}
				
				$divAchat .= '</div>';
				
				echo $divAchat;
				unset($divAchat);

				echo '<td>';
				$i = 0;
				$divArticle = '<div class="article" title="'.$description.'" onclick="buy(';
				foreach($donnee as $donnee) {
					$donnee = addslashes($donnee);
					
					$divArticle .= "'".$donnee."'";
					if($i < 7) {
						$divArticle .= ",";
					}
					$i++;
				}
				$divArticle .= ')">';
				echo $divArticle;
				
				if($valeur_ig != "" && $activeMoneyIG == true) {
					echo '<div class="nbrCreditIG">'.$valeur_ig.'</div>';
				}
				
				if($valeurArticle != "") {
					echo '<div class="nbrCredit">'.$valeurArticle.'</div>';
				}
				
				echo '&nbsp;<p style="clear:both;">';
				
				if(empty($article['image'])) {
					$article['image'] = "images/empty_article.png";
				}
				
				echo '<img src="'.$article['image'].'" alt="'.$article['article'].'"><br>';
				echo stripcslashes($article['article']);
				echo '</div>';
				echo '</td>';
				$division = $l/3;
				if (is_int($division)) {
					echo '</tr><tr>';
				}
				$l++;
			}
			echo '</tr></table><br>';
		}
	}
	if($l == 0) { echo "<h3 style=\"text-align:center; margin:15px;\">Aucun article n'est encore en vente</h3>"; }

	echo '</div>';
	?>
	
	<div id="achatBoutique" style="display: none;" class="popup_block">
		<div class="achatBoutique header">
			<h3>Confirmation d'achat</h3>
		</div>
		<div class="achatBoutique content">
			<?php if(!connect()) {
				msg("Vous devez être connecté sur le site pour poursuivre cet achat.", 'b');
			}
			?>
		</div>
	</div>		
</div>



<?php include("include/footer.php"); ?>

<script type="text/javascript">
function buy(id, article, description, tarif_site, tarif_game, limit, requis, image) {
	$('.achatBoutique.footer').remove();
	var connect = "<?php echo connect(); ?>";
	var monnaie_site = "<?php echo $monnaie_site; ?>";
	var monnaie_serveur = "<?php echo $monnaie_serveur; ?>";
	var popID = 'achatBoutique';
	var popWidth = '500';
	
	$('#' + popID).fadeIn().css({ 'width': Number( popWidth ) })
	
	var popMargLeft = ($('#' + popID).width() + 80) / 2;
	
	$('#' + popID).css({ 'margin-left' : -popMargLeft });
	
	$('body').append('<div id="fade"></div>');
	$('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeIn();
	$('#' + popID).animate({ top: '75px' }, 150);
	
	if (connect == 1) {
		$('.content').html('<form method="post" id="form' + id + '">' + $('#divAchat' + id).html() + '</form>');
		if (limit == false && requis == false) {
			$('.content').after('<div class="achatBoutique footer"><a class="button vert">Confirmer l\'achat</a></div>');
		
			$('.achatBoutique.footer a').click(function() {
				$('.achatBoutique.footer').html('<span class="button vert">Veuillez patienter...</span>');
				$.ajax({
					url: 'boutique.php',
					type: 'get',
					data: 'id=' + id + '&' + $('#form' + id).serialize(),
					success: function(html) {
						if (html.indexOf("requis:") != "-1") {
							html = html.replace('requis:', "");
							html = '<div class="warning_b">Vous devez acheter l\'article <u>' + html + '</u> pour pouvoir continuer.</div>';
						} else {
							switch (html) {
								case 'credit:false':
									html = '<div class="warning_r">Vous ne pouvez pas acheter cet article, car les moyens de paiement pour effectuer l\'achat ne sont pas configurés.</div>';
									break;
								case 'credit_serveur:false':
									html = '<div class="warning_r">Vous n\'avez pas assez de ' + monnaie_serveur + ' pour poursuivre cet achat.</div>';
									break;
								case 'credit_site:false':
									html = '<div class="warning_r">Vous n\'avez pas assez de ' + monnaie_site + ' pour poursuivre cet achat.</div>';
									break;
								case 'commande:receive':
									html = '<div class="warning_v">Votre commande a bien été effectuée.<br>Vous venez de recevoir automatiquement votre article en jeu.</div>';
									break;
								case 'commande:saveByUser':
									html = '<div class="warning_v">Votre commande a bien été effectuée.<br>Pour recevoir votre article en jeu, merci de consulter votre compte, puis aller dans la rubrique concernant la liste d\'achat.</div>';
									break;
								case 'commande:saveBySystem':
									html = '<div class="warning_v">Votre commande a bien été effectuée.<br>Vous êtes actuellement non-connecté sur le serveur. Votre article est donc en suspens. Pour le recevoir, merci de consulter votre compte, puis aller dans la rubrique concernant la liste d\'achat.</div>';
									break;
								case 'limite:false':
									html = '<div class="warning_b">Vous ne pouvez acheter cet article qu\'une seule fois.</div>';
									break;
								default:
									html = '<div class="warning_r">Il est impossible de traiter votre demande pour le moment.<br>Erreur: ' + html;
									break;
							}
						}
						
						$('.achatBoutique.content').html(html);
						$('.achatBoutique.footer').html('<a class="button gris">Fermer</a>');
					}
				});
				
			});	
		} else {
			
		}
	}
}
	
$('button, #fade, a.gris').live('click', function() {
	$('.popup_block').animate({ top: '-500px'}, 150);
	$('.popup_block').fadeOut();
	$('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeOut();
	return false;
});
</script>