<?php
//****************************************************
// Auteur: CraftMyCMS (Kévin GUIOT)
// CraftMyCMS PERSO 4.0.2.2
// Copyright © 2012 - 2015
// Sortie: 26 avril 2015 à 19h30
// Contact: contact@craftmycms.fr
//
// http://www.craftmycms.fr/
// http://developpeur.craftmycms.fr/changelog/cms.php#k0345han1l
//****************************************************

$titre_page = "Paramètre des pages";
include("../include/init.php");

$host_st = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$host_st = str_replace('www.', null, $host_st);
$host_st = str_replace('/admin/pages.php', null, $host_st);

$arrayPermissions = array(
	'Afficher les statistiques du site (tableau de bord)',
	'Ajouter une news',
	'Modifier une news',
	'Supprimer une news',
	'Répondre au billet du support',
	'Mettre des billets en résolu',
	'Ajouter un article',
	'Modifier un article',
	'Supprimer un article',
	'Mettre à jour le CMS'
);

if(!empty($_POST)) {
	$form = array(
		'newsParPage',
		'adressePaypal',
		'monnaie_site',
		'monnaie_serveur',
		'valeur',
		'idp',
		'idd',
		'provenanceSkin2D',
		'provenanceSkin3D',
		'connect_serveur',
		'banlist',
		'reglement',
		'captcha',
		'activeBlocInfos',
		'connect_serveur',
		'activeBlocStats',
		'activeMoneyIG',
		'activeInventaire',
		'activeStarpass',
		'activePaypal',
		'activeSkin',
	);
	$i = 1;
	foreach($form as $value) {
		if($i < 10) {
			if(!is_numeric($_POST['newsParPage'])) {
				header('location: ?msg=false'); exit;
			}
			
			if($value == 'idp' || $value == 'idd' || $value == 'valeur') {
				$_POST[$value] = trim($_POST[$value]);
			}
			
			modifConfig($value, secure($_POST[$value]));
		} else {
			modifConfig($value, false);
		}
		$i++;
	}
	if(!empty($_POST['offrePaypal'])) {
		$array = 'array(';
		$i = 0;
		foreach($_POST['offrePaypal'] as $offrePaypal) {
			if($i != "5") {
				$array .= '"'.$offrePaypal.'",';
			} else {
				$array .= '"'.$offrePaypal.'")';
			}
			$i++;
		}
		modifConfig('offrePaypal', $array, false);
	}
	
	if(!empty($_POST)) {
		$array = 'array(';
		for($i = 0; $i<10; $i++) {
			if(empty($_POST['permissionsPost'][$i])) {
				$permissionsPost = 'false';
			} else {
				$permissionsPost = 'true';
			}
			
			if($i != 9) {
				$array .= $permissionsPost.',';
			} else {
				$array .= $permissionsPost.')';
			}
		}
		modifConfig('permissionsModo', $array, false);
	}	
	header("location: ?msg=true");
	exit;
}
include("header.php");
?>
<div id="content" class="parametre_page">
	
	<?php
	msg("La configuration de vos a bien été modifiée.", 'v', 'get', 'true');
	msg("Impossible de modifier la configuration de vos pages.", 'r', 'get', 'false');
	?>
	
	<form method="post">
		<h3>Boutique</h3>
		<table class="table table-bordered table-striped">
			<tr>
				<td>Le nom de la monnaie du site</td>
				<td><input type="text" name="monnaie_site" value="<?php if(!empty($monnaie_site)) { echo $monnaie_site; } ?>"></td>
			</tr>
			<tr>
				<td>Le nom de la monnaie du serveur</td>
				<td><input type="text" name="monnaie_serveur" value="<?php if(empty($monnaie_serveur)) { echo $monnaie_serveur; } else { echo $monnaie_serveur ; } ?>"></td>
			</tr>
			<tr>
				<td>Valeur d'un code acheté via Starpass</td>
				<td><input size="1" type="text" name="valeur" value="<?php if(isset($valeur)) { echo $valeur; }?>"></td>
			</tr>
		</table>
		
		<h3>
			Configuration paiement par Starpass
			<input type="checkbox" name="activeStarpass"<?php if($activeStarpass == true) { echo " checked"; } ?>>
		</h3>
		
		<div class="starpass">
			Afin de recevoir les revenus grâce aux achats de crédits pour la boutique automatique, vous devez configurer votre document starpass.<br><br>
			<ul>
				<li>Rendez-vous sur <a href="http://www.starpass.fr" target="_blankl">Starpass.fr</a> et connectez-vous.</li>
				<li>Allez dans <a href="http://membres.starpass.fr/document_creer.php">Créer un document</a> et choisissez l'option "Starpass CLASSIC".</li>
				<li>
					Insérer ceci dans les champs correspondant:<br><br>
					<b>URL de la page d'accès</b>:<br> <?php echo "http://".$host_st."/commande.php"; ?><br>
					<b>URL du document</b>:<br> <?php echo "http://".$host_st."/commande.php?verif"; ?><br>
					<b>URL d'erreur</b>:<br> <?php echo "http://".$host_st."/commande.php?verif"; ?><br><br>
					<img src="../images/starpass.png" alt="starpass">
				</li>
				<li>Remplissez les autres champs comme vous le désirez et cliquez sur <i>Enregistrer</i>.</li>
				<li>
					Vous allez être redirigé sur une page qui contiendra deux scripts.<br>
					Allez en bas de cette page et cliquez sur <strong>Installation Script PHP</strong>.
					<img src="../images/installationphp.png" alt="installationphp">					
				</li>
				<li>Veuillez reporter les codes de votre page dans les champs respectifs:<br>
					<img src="../images/idpidd.png" alt="idpidd"><br>
					<p>Ne reportez pas les codes de l'image, mais ceux de votre page !</p>
					$idp = <input type="text" size="3" value="<?php if(isset($idp)) { echo $idp; } ?>" name="idp"><br>
					$idd = <input type="text" size="3" value="<?php if(isset($idd)) { echo $idd; } ?>" name="idd"><br>
				</li>
			</ul>
		</div>
		
		<h3>
			Configuration paiement par PayPal
			<input type="checkbox" name="activePaypal"<?php if($activePaypal == true) { echo " checked"; } ?>>
		</h3>
		
		<table class="table table-bordered table-striped paypal">
			<tr>
				<td>Adresse e-mail de votre compte PayPal</td>
				<td><input type="text" name="adressePaypal" value="<?php if(!empty($adressePaypal)) { echo $adressePaypal; } ?>"></td>
			</tr>
			<tr>
				<td>
					Vos offres<br>
					<small>
					<strong>Exemple:</strong> 1€ pour 5 tokens<br>
					Votre client achetera 5 tokens pour le prix de 1€.<br>
					<strong>Attention:</strong> Utilisez le point pour faire une virgule dans le prix de votre offre.
					</small>
				</td>
				<td>
					<?php
					for($i = 0; $i<6; $i++) {
						if($i%2) {
							echo '<input name="offrePaypal[]" type="text" value="'.$offrePaypal[$i].'" size="1"> tokens <br>';
						} else {
							echo '<input name="offrePaypal[]" type="text" value="'.$offrePaypal[$i].'" size="1">€ pour';
						}
					}
					?>
				</td>
			</tr>
		</table>

		<h3>Configuration des skins <input type="checkbox" name="activeSkin"<?php if($activeSkin == true) { echo " checked"; } ?>></h3>
		<table class="table table-bordered table-striped">
			<tr>
				<td><label for="provenanceSkin2D">Provenance des skins 2D<br><small>URL de la page qui affiche le skin 2D.</small></label></td>
				<td><input id="provenanceSkin2D" type="text" name="provenanceSkin2D" value="<?php echo $provenanceSkin2D; ?>"></td>
			</tr>
			
			<tr>
				<td><label for="provenanceSkin3D">Provenance des skins 3D<br><small>URL de la page qui affiche le skin 3D.</small></label></td>
				<td><input id="provenanceSkin3D" type="text" name="provenanceSkin3D" value="<?php echo $provenanceSkin3D; ?>"></td>
			</tr>
			
			<tr>
				<td colspan="2">
					URL pour skin 2D par défaut:<br><strong>https://minotar.net/helm/{PSEUDO}/{SIZE}</strong><br><br>
					URL pour skin 3D par défaut:<br><strong>http://www.craftmycms.fr/ressources/new_skin3d.php?&login={PSEUDO}</strong>
				</td>
			</tr>
		</table>
		
		<h3>Autres</h3>
		<table class="table table-bordered table-striped autres">
			<tr>
				<td><label for="banlist">Activer la page Ban-list</label></td>
				<td><input id="banlist" name="banlist" type="checkbox"<?php if(!empty($banlist)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="reglement">Activer la page Réglement</label></td>
				<td><input id="reglement" name="reglement" type="checkbox"<?php if(!empty($reglement)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="captcha">Activer le captcha (conseillé)</label></td>
				<td><input id="captcha" name="captcha" type="checkbox"<?php if(!empty($captcha)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="activeBlocInfos">Activer le bloc d'informations</label></td>
				<td><input id="activeBlocInfos" name="activeBlocInfos" type="checkbox"<?php if(!empty($activeBlocInfos)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="connect_serveur">Obliger le joueur à être connecté sur le serveur pour pouvoir s'inscrire</label></td>
				<td><input id="connect_serveur" name="connect_serveur" type="checkbox"<?php if(!empty($connect_serveur)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="activeBlocStats">Activer le bloc des statistiques</label></td>
				<td><input id="activeBlocStats" name="activeBlocStats" type="checkbox"<?php if(!empty($activeBlocStats)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="activeMoneyIG">Activer la boutique In-Game</label><br><small>Donnez la possibilité à vos membres d'acheter avec leur argent sur votre serveur.</small></td>
				<td><input id="activeMoneyIG" name="activeMoneyIG" type="checkbox"<?php if(!empty($activeMoneyIG)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="activeInventaire">Activer l'affichage de l'inventaire</label><br><small>Donnez la possibilité à vos membres de voir l'inventaire des joueurs de votre serveur.</small></td>
				<td><input id="activeInventaire" name="activeInventaire" type="checkbox"<?php if(!empty($activeInventaire)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="newsParPage">News par page</label><br><small>Permet de créer une pagination sur la page d'accueil de votre site.<br>0 permet de désactiver le système de pagination.</small></td>
				<td><input id="newsParPage" type="text" name="newsParPage" value="<?php echo $newsParPage; ?>"></td>
			</tr>
<!--			<tr>
				<td><label for="skinHelm">Afficher le casque (helm) du skin d'un joueur</label></td>
				<td><input id="skinHelm" name="skinHelm" type="checkbox"<?php if(!empty($skinHelm)) { echo" checked"; } ?>></td>
			</tr>
-->		</table>
		
		<h3>Permissions rang Modérateur</h3>
		<table class="table table-bordered table-striped autres">
			<?php
			$i = 0;

			foreach($arrayPermissions as $permissionsPost) {
				if($permissionsModo[$i] == true) {
					$checked = " checked";
				} else {
					$checked = null;
				}
				echo '<tr>';
				echo '<td><label for="permissionsPost['.$i.']">'.$permissionsPost.'</label></td>';
				echo '<td><input id="permissionsPost['.$i.']" name="permissionsPost['.$i.']" type="checkbox"'.$checked.'></td>';
				echo '</tr>'."\n";
				$i++;
			}
			?>
		</table>
		<div class="center"><input type="submit" value="Enregister les paramètres"></div>
	</form>
</div>
<?php include("footer.php"); ?>