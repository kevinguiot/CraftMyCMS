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

$titre_page = "Messagerie";
include('include/init.php');

if(!connect()) {
	header('location: index.php');
	exit;
}

if(!empty($_POST)) {
	if(strstr($_SERVER['REQUEST_URI'], '?selectionForm')) {
		if($_POST['selectionType']!=null) {
			if(!empty($_POST['selection'])) {
				$selectionCase = $_POST['selection'];
				foreach($selectionCase as $selection) {
					$req_selectConv = $connexion->prepare('SELECT * FROM '.$prefixe.'conv_id WHERE (id_sender=:user_id || id_receiver=:user_id) && id=:id_conv');
					$req_selectConv->execute(array('user_id' => USER_ID, 'id_conv' => $selection));
					$selectConv = $req_selectConv->fetch();
					if(!empty($selectConv)) {
						$updateConv = $connexion->prepare('UPDATE '.$prefixe.'conv_id SET etat=:etat WHERE (id_sender=:user_id || id_receiver=:user_id) && id=:id_conv');
						if($selectConv['etat'] != "3" && $selectConv['etat'] != "4") {
							if($selectConv['id_sender'] == USER_ID) {
								$etat = 3;
							} else {
								$etat = 4;
							}
						} else {
							$etat = "0";
						}
						$updateConv->execute(array('user_id' => USER_ID, 'id_conv' => $selection, 'etat' => $etat));
					}
				}
			}
			header('location: messagerie.php');
		} else {
			header('location: messagerie.php');
		}
		exit;
	} else {
		
		if(empty($_GET['id'])) {
			$req_selectLastConv = $connexion->query('SELECT id FROM '.$prefixe.'conv_id ORDER BY id DESC');
			$selectLastConv = $req_selectLastConv->fetch();
			$id_conv = $selectLastConv[0] + 1;
		} else {
			$req_selectConv = $connexion->prepare('SELECT * FROM '.$prefixe.'conv_id WHERE (id_sender=:user_id || id_receiver=:user_id) && id=:id_conv');
			$req_selectConv->execute(array('user_id' => USER_ID, 'id_conv' => $_GET['id']));
			$selectConv = $req_selectConv->fetch();
			if(!empty($selectConv)) {
				$id_conv = $_GET['id'];
			} else {
				$id_conv = false;
			}
		}
	
		if($id_conv == false) {
			header('location: messagerie.php');
			exit;
		} else {
			
			//Si on envoi un nouveau message via une conversation
			if(empty($_POST['destinataire'])) {

				//Si la conversation existe
				if(!empty($selectConv)) {

					$sujet = $selectConv['sujet'];
					$envoiMessage = $connexion->prepare('INSERT INTO '.$prefixe.'conv_text SET
						id_sender=:id_sender,
						content=:content,
						date=:date,
						heure=:heure,
						id_conv=:id_conv'
					);
					$envoiMessage->execute(array(
						'id_sender' => USER_ID,
						'content' => secure($_POST['message']),
						'date' => $date,
						'heure' => $heure,
						'id_conv' => $id_conv
					));
					$reqIdTemp = $connexion->query("SELECT max(id) FROM ".$prefixe."conv_id");
					$idTedgfgmp = $reqIdTemp->fetch();
					
					header('location: messagerie.php?id='.$idTedgfgmp['max(id)']);
				} else {
					header('location: messagerie.php?msg=envoiConv:false_destinataire');
				}
				
				exit;
			} else {
				
				//Si l'utilisateur existe
				if(getUserInfos('pseudo', $_POST['destinataire'], 'pseudo') && $_POST['destinataire'] != USER_PSEUDO) {
					
					//Si le contenu n'est pas vide
					if(!empty($_POST['message'])) {
						$destinataire = getUserInfos('pseudo', $_POST['destinataire'], 'id');
						
						$sujet = secure($_POST['sujet']);
						
						$newConv = $connexion->prepare('INSERT INTO '.$prefixe.'conv_id SET
							sujet=:sujet,
							id_sender=:id_sender,
							id_receiver=:id_receiver'
						);
						$newConv->execute(array(
							'sujet' => $sujet,
							'id_sender' => USER_ID,
							'id_receiver' => $destinataire
						));					
						
						$reqIdTemp = $connexion->query("SELECT max(id) FROM ".$prefixe."conv_id");
						$idTemp = $reqIdTemp->fetch();
						
						$envoiMessage = $connexion->prepare('INSERT INTO '.$prefixe.'conv_text SET
							id_sender=:id_sender,
							content=:content,
							date=:date,
							heure=:heure,
							id_conv=:id_conv'
						);
						$envoiMessage->execute(array(
							'id_sender' => USER_ID,
							'content' => secure($_POST['message']),
							'date' => $date,
							'heure' => $heure,
							'id_conv' => $idTemp['max(id)']
						));
						$reqIdTemp = $connexion->query("SELECT max(id) FROM ".$prefixe."conv_id");
						$idTemp = $reqIdTemp->fetch();
	
						header('location: messagerie.php?id='.$idTemp['max(id)']);
					} else {
						header('location: messagerie.php?msg=envoiConv:false_content');
					}
					exit;
				}
				elseif($_POST['destinataire'] == USER_PSEUDO) {
					header('location: messagerie.php?msg=pseudo:false');
				}
				else {
					header('location: messagerie.php?msg=membre:false');
				}
				exit;
			}
		}
	}
}
include('include/header.php');
?>

<div id="content" class="messagerie">
	<?php
	
	msg("Ce membre n'existe pas.", 'r', 'get', 'membre:false');
	msg("Vous ne pouvez pas envoyer de message à vous-même.", 'r', 'get', 'pseudo:false');
	msg("Veuillez saisir le destinataire de votre message.", 'r', 'get', 'envoiConv:false_destinataire');
	msg("Veuillez saisir le contenu de votre message.", 'r', 'get', 'envoiConv:false_content');
	
	if(empty($_GET['id'])) {
		$req_selectConv = $connexion->prepare('SELECT * FROM '.$prefixe.'conv_id WHERE id_sender=:user_id || id_receiver=:user_id ORDER BY id DESC');
		$req_selectConv->execute(array('user_id' => USER_ID));
		$i = 0;
		while($selectConv = $req_selectConv->fetch()) {
			
			$sujet = $selectConv['sujet'];
			
			if(empty($sujet)) {
				$sujet = '&lt;Message sans sujet&gt;';
			}
			
			$destinataire = $selectConv['id_sender'];
			if($destinataire == USER_ID) {
				$destinataire = $selectConv['id_receiver'];
			}
			
			if($selectConv['id']!=@$selectConvID) {
				if(($selectConv['etat'] == "0") ||($selectConv['id_sender'] == USER_ID && $selectConv['etat'] == "3") || ($selectConv['id_receiver'] == USER_ID && $selectConv['etat'] == "4")) {} else {
					$i++;
					if($i == 1) { ?>
					<h3 style="float: left;">Vos conversations</h3>
					<a href="#?w=500" rel="newMessage" class="poplight newBillet" style="float: right;">Nouveau</a>
					<p style="clear: both;"></p>
					<form method="post" action="?selectionForm" id="selectionForm">
						<table class="table table-bordered table-striped conv">
					<?php
					}
					if($i > 0) {
						$req_selectLastM = $connexion->prepare('SELECT * FROM '.$prefixe.'conv_text WHERE id_conv=:id_conv ORDER BY id DESC');
						$req_selectLastM->execute(array('id_conv' => $selectConv['id']));
						$selectLastM = $req_selectLastM->fetch();
						$pseudoLastM = getUserInfos('id', $selectLastM['id_sender'], 'pseudo');
						
						echo '<tr>';
						echo '<td><input type="checkbox" name="selection[]" value="'.$selectConv['id'].'"></td>';
						echo '<td>';
						
						echo '<strong><a href="?id='.$selectConv['id'].'">'.$sujet.'</a></strong><br>';
						echo 'Dernier message par <a href="membre.php?pseudo='.$pseudoLastM.'" title="Aller sur le profil de '.$pseudoLastM.'">'.$pseudoLastM.'</a>, le '.$selectLastM['date'].' à '.$selectLastM['heure'].'.<br>';
						echo 'Conversation avec <strong>'.getUserInfos('id', $destinataire, 'pseudo').'</strong>.';
						
						echo '</td>';
						echo '</tr>';
					}
				}
			}
			$selectConvID = $selectConv['id'];
		}
		if($i > 0) {
			echo '<tr><td><input type="checkbox" id="selectionAll"></td>';
			echo '<td>';
			echo '<select name="selectionType" onchange="document.getElementById(\'selectionForm\').submit()">';
			echo '<option value="" selected>Pour la séléction...</option><option value="delete">Supprimer</option>';
			echo '</select>';
			echo '</td></tr></table></form>';
		} else {
			msg("Vous n'avez aucune conversation en cours, <a class=\"poplight\" href=\"#?w=500\" rel=\"newMessage\">cliquer ici pour envoyer un message</a>.", 'b');
		}
		?>

		<div id="newMessage" class="popup_block">
			<form method="post">
				<table>
					<tr>
						<td>
							Destinataire*:<br>
							<input type="text" placeholder="Pseudo du destinataire..." name="destinataire"
							<?php
							if(!empty($_GET['destinataire'])) {
								echo 'value="'.secure($_GET['destinataire']).'"';
							}
							echo '>';
							?>
						</td>
						<td>
							Sujet:<br>
							<input type="text" placeholder="Sujet de votre message..." name="sujet">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							Contenu de votre message*:<br>
							<textarea placeholder="Contenu de votre message..."name="message" rows="5"></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="submit" value="Envoyer votre message">
						</td>
					</tr>
				</table>
			</form>
		</div>
	<?php
	} else {
	$req_selectConv = $connexion->prepare('SELECT * FROM '.$prefixe.'conv_id WHERE (id_sender=:user_id || id_receiver=:user_id) && id=:id_conv');
	$req_selectConv->execute(array('user_id' => USER_ID, 'id_conv' => $_GET['id']));
	$selectConv = $req_selectConv->fetch();
	
	if(!empty($selectConv)) {
		$sujet = $selectConv['sujet'];
		if(empty($sujet)) {
			$sujet = '&lt;Message sans sujet&gt;';
		}
		
		$i = 0;
		$req_selectMessage = $connexion->prepare('SELECT * FROM '.$prefixe.'conv_text WHERE id_conv=:id_conv');
		$req_selectMessage->execute(array('id_conv' => $_GET['id']));
		while($selectMessage = $req_selectMessage->fetch()) {
			if(($selectConv['etat'] == "0") ||($selectConv['id_sender'] == USER_ID && $selectConv['etat'] == "3") || ($selectConv['id_receiver'] == USER_ID && $selectConv['etat'] == "4")) {
				msg('Cette conversation n\'est plus disponible.', 'r');
				break;
			} else {
				$pseudoSender = getUserInfos('id', $selectMessage['id_sender'], 'pseudo');
				
				if($i == 0) {
					echo '<form method="post">';
					echo '<h3>'.$sujet.'</h3>';
					echo '<table class="table table-bordered table-striped affConv" style="clear:both;">';
				}
				
				echo '<tr>';
				echo '<td><a href="membre.php?pseudo='.$pseudoSender.'" title="Aller sur le profil de '.$pseudoSender.'"><img src="skin.php?pseudo='.$pseudoSender.'"></a></td>';
				echo '<td><a href="membre.php?pseudo='.$pseudoSender.'" title="Aller sur le profil de '.$pseudoSender.'">'.$pseudoSender.'</a>, envoyé le '.$selectMessage['date'].' à '.$selectMessage['heure'].'<br>';
				echo str_replace("\r", "<br>", $selectMessage['content']);
				echo '</td>';
				echo '</tr>';
				$i++;
			}
		}

		if($i > 0) {
			if($selectConv['etat'] == "3" OR $selectConv['etat'] == "4") {
				echo '</table></form>';
				msg('La discussion a été fermé.', 'b');
			} else {
				echo '<tr>';
				echo '<td><img src="skin.php?pseudo='.USER_PSEUDO.'" alt="skin"></td>';
				echo '<td><textarea name="message"></textarea><input type="submit" value="Envoyer votre message"> <a href="messagerie.php" style="font-size:small;">Retourner à la liste des messages</a></td>';
				echo '</tr>';
				echo '</table>';
				echo '</form>';
			}
		}
	}
} ?>
</div>
<?php
include('include/footer.php');
?>
<script>
$(document).ready(function() {
    $('#selectionAll').click(function() { 
        var cases = $("td").find(':checkbox');
        if(this.checked) {
            cases.attr('checked', true);
        } else {
            cases.attr('checked', false);
        }
    });
});
</script>
<script type="text/javascript" src="script/fade.js"></script>

<?php if(!empty($_GET['destinataire'])) { ?>
	<script>
		var popID = 'newMessage';
		fade(popID);
	</script>
<?php } ?>