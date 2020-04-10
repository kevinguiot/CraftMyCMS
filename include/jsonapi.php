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
	if(
		!empty($_POST['nom']) &&
		!empty($_POST['ip']) &&
		!empty($_POST['external_ip']) &&
		!empty($_POST['port']) &&
		!empty($_POST['user']) &&
		!empty($_POST['password'])
	) {
		
		$install = $_POST['install'];
		if($install == 'true') {
			$install = true;
		} else {
			$install = false;
		}
		
		if($install == true) {
			include('class/jsonapi.class.php');
			include ('../install/temp/bdd.php');
			
			try {
				@$connexion = new PDO('mysql:host='.$serveur.';dbname='.$base.';charset=utf8', $user, $mdp, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
			} catch (PDOException $e) {
				die('bdd');
			}
		} else {
			include('../include/init.php');
		}
		
		if(!empty($_POST['salt'])) {
			$salt = $_POST['salt'];
		} else {
			$salt = "";
		}
		
		//On teste si la connexion est bonne.
		$api = new JSONAPI($_POST['external_ip'], $_POST['port'], $_POST['user'], $_POST['password'], $salt);
		$getPlayerCount = $api->call('getPlayerCount');
		
		//La connexion est correcte
		if($getPlayerCount['result'] == 'success') {
			$getPlayerLimit = $api->call('getPlayerLimit');
			$getPlayerLimit = $getPlayerLimit['success'];
			
			$broadcast = $api->call('broadcast', array('CraftMyCMS: Félicitation, la liaison JSONAPI a bien été effectuée.'));
			
			//Ajout du serveur
			$insertServeur = $connexion->prepare('INSERT INTO '.$prefixe.'serveurs SET nom=:nom, ip=:ip, external_ip=:external_ip, port=:port, user=:user, password=:password, salt=:salt');
			$insertServeur->execute(array(
				'nom' => $_POST['nom'],
				'ip' => $_POST['ip'],
				'external_ip' => $_POST['external_ip'],
				'port' => $_POST['port'],
				'user' => $_POST['user'],
				'password' => $_POST['password'],
				'salt' => $salt
			));
			
			$req_selectServeurs = $connexion->query('SELECT * FROM '.$prefixe.'serveurs');
			$nbr_selectServeurs = $req_selectServeurs->rowCount();
			
			if($install == true) {
				$updateEtape = $connexion->query("UPDATE ".$prefixe."etape_temp SET etape4='1'");
			} else {
				if($nbr_selectServeurs > 0) {
					$req_selectServeur = $connexion->query('SELECT * FROM '.$prefixe.'serveurs ORDER BY id DESC LIMIT 1');
					$selectServeur = $req_selectServeur->fetch(PDO::FETCH_ASSOC);
					
					$serveur = implode('#', $selectServeur);
					$serveur = $selectServeur['nom'].'[{NEW}]'.$selectServeur['ip'].'[{NEW}]'.$selectServeur['id'].'[{NEW}]'.$serveur;
				} else {
					$serveur = "";
				}
			}

			$etat = 'success[{NEW}]'.$serveur;
		} else {
			$etat = 'jsonapi';
		}
	} else {
		$etat = 'infos';
	}
	
	die($etat);
} else {
	
	if(empty($install)) {
		$install = 'true';
	}
	
	?>
	
	<h3>Ajouter un serveur</h3>
	<div id="protocoleJSONAPI">
		<ul>
			<li>Avant toute manipulation, veuillez supprimer le dossier JSONAPI et le fichier JSONAPI.jar s'ils sont déjà existants.</li>
			
			<li>
				Téléchargez le plugin JSONAPI en fonction de la version de CraftBukkit que vous utilisez.
				Rendez-vous sur <a target="_blank" href="http://www.craftmycms.fr/jsonapi.php">cette page</a> pour accéder aux plugins recommandés par CraftMyCMS, puis téléchargez le plugin.
				<br><small>Exemple: Téléchargez JSONAPI 5.2.5 pour CraftBukkit 1.6.4</small>
			</li>
			
			<li>Télécharges le plugin Vault: <a target="_blank" href="http://dev.bukkit.org/bukkit-plugins/vault/">cliquez-ici</a> (version correspondante à votre Craftbukkit).</li>
			<li>Téléchargez le plugin ProtocolLib: <a target="_blank" href="http://dev.bukkit.org/bukkit-plugins/protocollib/">cliquez-ici</a> (version correspondante à votre Craftbukkit).</li>
			
			<li>Allez sur le FTP de votre serveur, ouvrez le dossier <strong>plugins</strong> et glissez les trois plugins téléchargés (.jar) précédemment  à l'intérieur du dossier.</li>
			<li>Redémarrez votre serveur Minecraft ou démarrrez-le.</li>
			
			<li>
				Retournez dans le dossier <strong>plugins</strong> et actualisez le dossier en cliquant sur "Actualiser" via le menu (clique droit) ou en appuyant la touche F5 de votre clavier. Après la rechargement, vous verrez un nouveau dossier <strong>JSONAPI</strong> créé.<br>
				<img src="../images/jsonapi/fichiers.png">
			</li>
	
			<li>
				A partir de là, veuillez sélectionner la version de votre Craftbukkit.<br>
				<select>
					<option data-div-id="null">Version de CraftBukkit</option>
					<option data-div-id="152">CraftBukkit 1.5.2</option>
					<option data-div-id="16">Autre...</option>
				</select>
			</li>
			
			<div id="16" class="jsonapiCB" style="display: none;">
				<li>
					Nous allons d'abord nous intéresser aux utilisateurs pour se connecter au plugin JSONAPI. Ouvrez le dossier <strong>JSONAPI</strong> puis le fichier le fichier <strong>users.yml</strong> (avec <a href="http://notepad-plus-plus.org/fr/">Notepad++</a> de préférence).<br>
					<img src="../images/jsonapi/users.png" style="margin-bottom:5px;"><br>
					<img src="../images/jsonapi/open_users.png"><br>
					<img src="../images/jsonapi/users_file.png"><br>
					&bull; Remplacez <span style="background: #FFFFB2;">admin</span> par votre pseudo.<br>
					&bull; Remplacez <span style="background: #B2FFB2;">changeme</span> par votre mot de passe.<br>
					Vous pouvez choisir n'importe quel pseudo/mot de passe.<br>
					<strong>Enregistrez le fichier et rechargez (reload) / redémarrez votre serveur</strong>
				</li>
				<li>
					Refaite l'étape n°9 avec le fichier <strong>config.yml</strong><br>
					<img src="../images/jsonapi/config_file.png"><br>
					&bull; Remplacez <span style="background: #CCF4FF;">20059</span> par 20060 ou 20061 ou 20062 etc, ne laissez pas le port 20059 par défaut. Si vous avez des problèmes pour vous connecter à votre serveur, nous changerons de nouveau le port. Attention, <b>la limite du port est de 65535</b>, veuillez pas choisir un port au dessus de cette valeur.<br>
					&bull; Remplacez <span style="background: #FFF4CC;">true</span> par <u>false</u>. Ceci est à faire <strong>impérativement</strong>.<br>
					&bull; Remplacez <span style="background: #FFE0CC;">'false'</span> par <u>log_jsonapi</u>. Ça vous permettra d'avoir les logs de JSONAPI dans le fichier log_jsonapi présent à la racine du serveur:
					<img src="../images/jsonapi/log_jsonapi.png"><br>
					<strong>Enregistrez le fichier et rechargez (reload) / redémarrez votre serveur.</strong><br><br>
					
					<b>Retrouver l'external IP (JSONAPI) de votre serveur:</b> Veuillez ouvrir le fichier <u>log_jsonapi</u> (à la racine), vous retrouvez la ligne suivante <u>External IP:</u> puis l'external IP demandée.
				</li>			
			</div>
			
			<div id="152" class="jsonapiCB" style="display: none;">
				<li>
					Nous allons d'abord nous intéresser à la configuration de JSONAPI afin de relier votre site à votre serveur. Ouvrez le dossier <strong>JSONAPI</strong> puis le fichier <strong>config.yml</strong> (avec <a href="http://notepad-plus-plus.org/fr/">Notepad++</a> de préférence).<br>
					<img src="../images/jsonapi/config.png" style="margin-bottom:5px;"><br>
					<img src="../images/jsonapi/open_config.png"><br>
					<img src="../images/jsonapi/config152.png"><br>
					&bull; Remplacez <span style="background: #FF99CC;">usernameGoesHere</span> par votre pseudo.<br>
					&bull; Remplacez <span style="background: #B2FFB2;">passwordGoesHere</span> par votre mot de passe.<br>
					Vous pouvez choisir n'importe quel pseudo/mot de passe.<br><br>
					&bull; Veillez à ce que la valeur de use-new-api soit bien <span style="background: #CCFFCC">false</span>. Si ce n'est pas le cas, mettez bien <u>false</u>.<br>
					&bull; Replacez <span style="background:#FFFF00;">salt goes here</span> par CraftMyCMS.<br>
					&bull; Remplacez <span style="background: #CCF4FF;">20059</span> par 20060 ou 20061 ou 20062 etc, ne laissez pas le port 20059 par défaut. Si vous avez des problèmes pour vous connecter à votre serveur, nous changerons de nouveau le port.<br>
					&bull; Remplacez <span style="background: #FFE0CC;">'false'</span> par log_jsonapi. Ça vous permettra d'avoir les logs de JSONAPI dans le fichier log_jsonapi présent à la racine du serveur:
					<img src="../images/jsonapi/log_jsonapi.png"><br>
					<strong>Enregistrez le fichier et rechargez (reload) / redémarrez votre serveur.</strong><br><br>
					
					<b>Retrouver l'external IP* (JSONAPI) de votre serveur:</b> Veuillez ouvrir le fichier <u>log_jsonapi</u> (à la racine), vous retrouvez la ligne suivante <u>External IP:</u> puis l'external IP demandée.
				</li>
			</div>
			
		
			<li style="display: none;" id="testServeurLi">
				<form method="post" id="testServeur">
					<input type="hidden" name="install" value="<?php echo $install; ?>">
					
					Après avoir effectuer les étapes pour lier votre site à votre serveur, vous pouvez reporter les informations demandées ci-dessous.<br>
					<table>
						<tr>
							<td>
								Affichage: Nom du serveur:<br>
								<input type="text" name="nom" placeholder="Le nom de votre serveur...">
							</td>
							
							<td>
								Affichage: Adresse IP du serveur:<br>
								<input type="text" name="ip" placeholder="L'adresse IP de connexion au serveur...">
							</td>
						</tr>
						
						<tr class="tr_jsonapi">
							<td>
								JSONAPI: External IP*:<br>
								<input type="text" name="external_ip" placeholder="L'external IP de votre serveur...">
							</td>
							
							<td>
								JSONAPI: Port du plugin:<br>
								<input type="text" name="port" placeholder="Le port du plugin JSONAPI...">
							</td>
						</tr>
						
						<tr class="tr_jsonapi">
							<td>
								JSONAPI: Utilisateur:<br>
								<input type="text" name="user" placeholder="L'utilisateur de configuration de JSONAPI...">
							</td>
							
							<td>
								JSONAPI: Mot de passe:<br>
								<input type="text" name="password" placeholder="Le mot de passe de configuration de JSONAPI...">
							</td>
						</tr>
						
						<tr class="tr_jsonapi">
							<td>
								JSONAPI: Clef salt:<br>
								<input type="text" name="salt" placeholder="Laisser vide par défaut...">
							</td>
						</tr>
						
						<tr>
							<td colspan="2">
								<br><input type="submit" value="Ajouter votre serveur" class="submit"><img src="../images/loaders/ajax-loader_1.gif" id="chargement">
							</td>
						</tr>
					</table>
				</form>
			</li>
		</ul>
	</div>
	
	<?php
	echo '<script>';
	if($install == 'true') {
		echo 'var install = "true";';
	} else {
		echo 'var install = "false";';
	}
	echo '</script>';
	
	if($install == 'true') {
		echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>';
	}
	?>
	
	<script type="text/javascript" src="../script/fade.js"></script>
	<script type="text/javascript" src="../script/jquery.form.js"></script>
	<script type="text/javascript" src="../script/jsonapi.js"></script>
<?php } ?>