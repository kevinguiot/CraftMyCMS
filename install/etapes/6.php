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

if(!empty($_POST)) {
	$monnaie_serveur = secure($_POST['monnaie_serveur']);
	$monnaie_site = secure($_POST['monnaie_site']);
	$valeur = $_POST['valeur'];
	$idp = $_POST['idp'];
	$idd = $_POST['idd'];
	if(!empty($_POST['connect_serveur'])) { $connect_serveur = "true"; } else { $connect_serveur = "0"; }
	if(!empty($_POST['banlist'])) { $banlist = "1"; } else { $banlist = "0"; }
	if(!empty($_POST['reglement'])) { $reglement = "1"; } else { $reglement = "0"; }
	if(!empty($_POST['captcha'])) { $captcha = "1"; } else { $captcha = "0"; }
	if(empty($valeur)) { $erreurv = 'Vous n\'avez pas saisi la valeur d\'un code Starpass'; }
	elseif(!is_numeric($valeur)) { $erreurv = "Seuls les chiffres sont autorisés dans le champ valeur"; } 
	elseif (empty($monnaie_serveur)) { $erreurv = "Vous n'avez pas spécifié le nom de la monnaie du serveur"; } 
	elseif (empty($monnaie_site)) { $erreurv = "Vous n'avez pas spécifié le nom de la monnaie du site"; } 
	elseif (empty($idd) || (empty($idp))) { $erreurv = 'Vous n\'avez pas remplis les codes idp & idd'; }
	elseif (ctype_digit($idp)!="1" && ctype_digit($idd)!="1") { $erreurv = 'Les codes idp & idd ne doivent pas contenir de lettres'; }
	else {
$data ='<?php
$monnaie_site = "'.$monnaie_site.'";
$monnaie_serveur = "'.$monnaie_serveur.'";
$valeur = "'.trim($valeur).'";
$idp = "'.trim($idp).'"; 
$idd = "'.trim($idd).'";
$connect_serveur = "'.$connect_serveur.'";
$banlist = "'.$banlist.'";
$reglement = "'.$reglement.'";
$captcha = "'.$captcha.'";
?>';
		$fp = fopen("temp/page.php","w+");
		fputs($fp, $data);
		fclose($fp);
		$connexion->query("UPDATE ".$prefixe."etape_temp SET etape6='1'");
		header("location: index.php");
	}
}

include("header.php");
?>

<div id="content" class="parametre_page">
	
    <?php
	if(!empty($erreurv)) {
		echo '<div class="warning_r">'.$erreurv.'</div>';
	}

	$host_st = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$host_st = str_replace('www.', null, $host_st);
	$host_st = str_replace('/install/index.php', null, $host_st);	
	?>
	
	<form method="post">
		<h3>Configuration de la boutique</h3>
        <table class="table table-bordered table-striped">
			<tr>
				<td>Le nom de la monnaie du site<sup>*</sup></td>
				<td><input type="text" name="monnaie_site" value="<?php if(!empty($monnaie_site)) { echo $monnaie_site; } ?>"></td>
			</tr>
		
			<tr>
				<td>Le nom de la monnaie du serveur<sup>*</sup></td>
				<td><input type="text" name="monnaie_serveur" value="<?php if(!empty($monnaie_serveur)) { echo $monnaie_serveur; } ?>"></td>
			</tr>
			<tr>
				<td>Valeur d'un code acheté via Starpass<sup>*</sup><br>
				<small>Exemple: 1 code acheté = <strong>10</strong> tokens</small></td>
				<td><input size="1" type="text" name="valeur" value="<?php if(!empty($valeur)) { echo $valeur; }?>"></td>
			</tr>
		</table>
		
		<h3>Configuration Starpass</h3> 
		<p>Afin de reçevoir les revenus grâce aux achats de crédits pour la boutique automatique, vous devez configurer votre document starpass.</p>
		<ul>
			<li>Rendez vous sur le site de <a href="http://www.starpass.fr/">Starpass</a> et connectez-vous.</li>
			<li>Allez dans <a href="http://membres.starpass.fr/document_creer.php">Créer un document</a>, puis choisissez <a href="?http://membres.starpass.fr/document_paiement.php?iPaymentType=0">StarPass ClASSIC</a>.</li>
			<li>
				Insérer ceci dans les champs correspondant:<br><br>
				<b>URL de la page d'accès</b>:<br><?php echo "http://".$host_st."/commande.php"; ?><br>
				<b>URL du document</b>:<br><?php echo "http://".$host_st."/commande.php?verif"; ?><br>
				<b>URL d'erreur</b>:<br><?php echo "http://".$host_st."/commande.php?verif"; ?><br><br>
				<img src="../images/starpass.png" alt="starpass">
			</li>
			<li>Remplissez les autres champs comme vous le désirez et cliquez sur <i>Enregistrer</i>.</li>
			<li>
				Vous allez être rédigez sur une page qui contiendra deux scripts.<br>
				Allez en bas de la page, et cliquez sur <strong>Installation Script PHP</strong><br>
				<img src="../images/installationphp.png">					
			</li>
			<li>Veuillez reporter les codes de votre page dans les champs respectifs:<br><br>
				<img src="../images/idpidd.png"><br>
				<span style="color:red; font-weight:bold;">Reportez pas les codes de l'image, mais ceux de votre page!</span><br>
				Ne mettez pas les points virgules.<br>
				$idp<sup>*</sup> = <input size="3" type="text" value="<?php if(isset($idp)) { echo $idp; } ?>" name="idp"><br>
				$idd<sup>*</sup> = <input size="3" type="text" value="<?php if(isset($idd)) { echo $idd; } ?>" name="idd"><br>
			</li>
		</ul>

		<h3>Autres paramètres</h3>
        <table class="table table-bordered table-striped autres">
			<tr>
				<td><label for="banlist">Activer la page Ban-list</label></td>
				<td><input id="banlist" name="banlist" type="checkbox"<?php if(@$banlist=="1" OR empty($banlist)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="reglement">Activer la page Réglement</label></td>
				<td><input id="reglement" name="reglement" type="checkbox"<?php if(@$reglement=="1" OR empty($reglement)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="captcha">Activer le captcha (conseillé)</label></td>
				<td><input id="captcha" name="captcha" type="checkbox"<?php if(@$captcha=="1" OR empty($captcha)) { echo" checked"; } ?>></td>
			</tr>
			<tr>
				<td><label for="connect_serveur">Obliger le joueur à être connecté sur le serveur pour pouvoir s'inscrire</label></td>
				<td><input id="connect_serveur" name="connect_serveur" type="checkbox"<?php if(@$connect_serveur=="1") { echo" checked"; } ?>></td>
			</tr>
		</table>
		<div style="text-align: center;"><input type="submit" value="Enregistrer ces nouveaux paramètres"></div>
	</form>
<?php include("footer.php"); ?>