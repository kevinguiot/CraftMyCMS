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

$titre_page = "Gestion des serveurs";
include('../include/init.php');

//Activation de JSONAPI
if(!empty($_POST['modifJSONAPI'])) {
	if($_POST['activeJSONAPI'] == 'on') {
		$activeJSONAPI = "true";
	} else {
		$activeJSONAPI = "false";
	}
	
	modifConfig('activeJSONAPI', $activeJSONAPI, false);
	header('location: serveurs.php?msg=update:jsonapi');
    exit;
}

//On souhaite reloader/stopper le serveur
if(!empty($_GET['reload']) || !empty($_GET['stop'])) {
    if(!empty($_GET['reload'])) {
        $id = $_GET['reload'];
        $type = 'reload';
    } else {
        $type = 'stop';
        $id = $_GET['stop'];
    }
    
    $req_selectServeur = $connexion->prepare('SELECT * FROM '.$prefixe.'serveurs WHERE id=:id');
        $req_selectServeur->execute(array(
        'id' => $id,
    ));
    $nbr_selectServeur = $req_selectServeur->rowCount();
    
    if($nbr_selectServeur == 1) {
        $selectServeur = $req_selectServeur->fetch();
        
        $api = new JSONAPI(
            $selectServeur['external_ip'],
            $selectServeur['port'],
            $selectServeur['user'],
            $selectServeur['password'],
            $selectServeur['salt']
        ); 

        $api->call("runConsoleCommand", array($type));
        die('success');
    }
    exit;
}

if(!empty($_POST) && !empty($_GET['type'])) {
    $type = $_GET['type'];
    
    //Mise à jour d'un serveur
    if($type == 'updateServeur') {
        if(!empty($_POST['etat']) && $_POST['etat'] == '1') {
            $etat = '1';
        } else {
            $etat = '0';
        }
        
        $updateServeur = $connexion->prepare('UPDATE '.$prefixe.'serveurs SET nom=:nom, ip=:ip, external_ip=:external_ip, port=:port, user=:user, password=:password, salt=:salt, etat=:etat WHERE id=:id');
        $updateServeur->execute(array(
            'nom' => $_POST['nom'],
            'ip' => $_POST['ip'],
            'external_ip' => $_POST['external_ip'],
            'port' => $_POST['port'],
            'user' => $_POST['user'],
            'password' => $_POST['password'],
            'salt' => $_POST['salt'],
            'etat' => $etat,
            'id' => $_POST['id'],
        ));
        
        $etat = 'success';
    }
    
    //Suppresion d'un serveur
    if($type == 'deleteServeur') {
        $deleteServeur = $connexion->prepare('DELETE FROM '.$prefixe.'serveurs WHERE id=:id');
        $deleteServeur->execute(array(
            'id' => $_POST['id'],
        ));
        
        $etat = 'success';
    }
    
    if(empty($etat)) {
        $etat = 'error';
    }
    
    die($etat);
}

include('header.php');
?>

<div id="content" class="serveurs">
    
    <?php
    msg("L'activation de JSONAPI a bien été modifiée", 'v', 'get', 'update:jsonapi');
    ?>
    
    <div class="popup_block" id="consoleDiv">
        <textarea id="console" rows="30"></textarea>
        <input id="cmd" type="text" placeholder="Envoyer une commande sur le serveur...">
    </div>
    
    <h3>Activation de JSONAPI</h3>
	<form method="post" style="width: 614px; padding: 5px; margin: 10px auto; background-color: #f9f9f9; border-radius: 3px; border: 1px solid #DDD;">
		<input type="hidden" name="modifJSONAPI" value="true">
		<strong><input id="activeJSONAPI" type="checkbox" name="activeJSONAPI"<?php if($activeJSONAPI == true) echo 'checked'; ?>>
        <label for="activeJSONAPI">Activer l'utilisation de JSONAPI</label> <input type="submit" value="Enregister"></strong><br>
		En désactivant cette option, les serveurs ne se chargeront pas sur votre CMS, cependant, ils resteront chargés dans l'espace d'administration.<br>
	</form><br>
    
    <h3>Liste des serveurs</h3>
    <?php
    $req_selectServeurs = $connexion->query('SELECT * FROM '.$prefixe.'serveurs ORDER BY nom ASC');
    $nbr_selectServeurs = $req_selectServeurs->rowCount();

    $actions = '<img src="../images/admin/serveur/console.png" class="console"><img src="../images/admin/serveur/reload.png" class="reload"><img src="../images/admin/serveur/stop.png" class="stop">';
	
	echo '<div style="display:none;" id="nbrServeurs">'.$nbr_selectServeurs.'</div>';
	echo '<div style="display:none;" id="actions">'.$actions.'<a href="#"><img src="../images/admin/serveur/configuration.png"></a></div>';
	
    if($nbr_selectServeurs > 0) {

        //Gestion des serveurs
        while($selectServeurs = $req_selectServeurs->fetch()) {
            ?>
            <div class="popup_block" id="gererServeur<?php echo $selectServeurs['id']; ?>">
                <h3>Gérer le serveur</h3>
                
                <form method="post" class="updateServeur" id="form<?php echo $selectServeurs['id']; ?>">
                    <div class="infosPost"></div>
                    
                    <table>
                        <tr>
                            <td>
                                Nom du serveur:<br>
                                <input type="text" name="nom" placeholder="Le nom de votre serveur..." value="<?php echo $selectServeurs['nom']; ?>">
                            </td>
                            
                            <td>
                                Adresse IP du serveur:<br>
                                <input type="text" name="ip" placeholder="L'adresse IP de connexion au serveur..." value="<?php echo $selectServeurs['ip']; ?>">
                            </td>
                        </tr>
                        
                        <tr class="tr_jsonapi">
                            <td>
                                JSONAPI: External IP:<br>
                                <input type="text" name="external_ip" placeholder="L'external IP de votre serveur..." value="<?php echo $selectServeurs['external_ip']; ?>">
                            </td>
                            
                            <td>
                                JSONAPI: Port du plugin:<br>
                                <input type="text" name="port" placeholder="Le port du plugin JSONAPI..." value="<?php echo $selectServeurs['port']; ?>">
                            </td>
                        </tr>
                        
                        <tr class="tr_jsonapi">
                            <td>
                                JSONAPI: Utilisateur:<br>
                                <input type="text" name="user" placeholder="L'utilisateur de configuration de JSONAPI..." value="<?php echo $selectServeurs['user']; ?>">
                            </td>
                            
                            <td>
                                JSONAPI: Mot de passe:<br>
                                <input type="text" name="password" placeholder="Le mot de passe de configuration de JSONAPI..." value="<?php echo $selectServeurs['password']; ?>">
                            </td>
                        </tr>
                        
                        <tr class="tr_jsonapi">
                            <td>
                                JSONAPI: Clef salt:<br>
                                <input type="text" name="salt" placeholder="Laisser vide par défaut..." value="<?php echo $selectServeurs['salt']; ?>">
                            </td>
                            
                            <td>
                                Etat du serveur:<br>
                                <input type="radio" name="etat" value="1"<?php if($selectServeurs['etat'] == 1) { echo ' checked'; } ?>>Activé 
                                <input type="radio" name="etat" value="0"<?php if($selectServeurs['etat'] == 0) { echo ' checked'; } ?>>Désactivé
                            </td>
                        </tr>
                        
                        <tr>
                            <td colspan="2">
                                <br><input type="submit" value="Enregistrer" class="submit"> <a href="#" class="deleteServeur">Supprimer le serveur de la liste</a>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
        }
		
        //Afficher la liste des serveurs
        $req_selectServeurs = $connexion->query('SELECT * FROM '.$prefixe.'serveurs ORDER BY nom ASC');
	}

        echo '<table class="table table-bordered table-striped" id="listeServeurs">';
        echo '<tr>';
        echo '<td>Nom du serveur</td>';
        //echo '<td>Informations</td>';
        echo '<td>Actions</td>';
        echo '</tr>';
		
		if($nbr_selectServeurs > 0) {
        
        while($selectServeurs = $req_selectServeurs->fetch(PDO::FETCH_ASSOC)) {
            //Se connecte au serveur via JSONAPI
            $api = new JSONAPI(
                $selectServeurs['external_ip'],
                $selectServeurs['port'],
                $selectServeurs['user'],
                $selectServeurs['password'],
                $selectServeurs['salt']
            );
            $getPlayerCount = $api->call('getPlayerCount');
            
            if($getPlayerCount['result'] == 'success') {
                $serveurInformation = implode('#', $selectServeurs);
            } else {
                $serveurInformation = 'none';
            }
            
            //Affichage des serveurs
            echo '<tr id="'.$selectServeurs['id'].'" jsonapi="'.$serveurInformation.'">';
            echo '<td><strong>'.$selectServeurs['nom'].'</strong><br><small>'.$selectServeurs['ip'].'</small></td>';
            
            if($getPlayerCount['result'] == 'success') {
                echo '<td>';
            } else {
                echo '<td class="disabled">';
            }
            
			echo $actions;
            echo '<a class="poplight" href="#?w=500" rel="gererServeur'.$selectServeurs['id'].'"><img src="../images/admin/serveur/configuration.png"></a>';

            echo '</td>';
            echo '</tr>';
        }
		} else {
			echo "<tr><td colspan=\"2\" style=\"text-align:left;\">Vous n'avez pas encore ajouté de serveur.</td></tr>";
		}
        echo '</table>';
//    } else {
//		msg("Vous n'avez pas encore ajouté de serveur.", 'b');
//	}
    ?>
    
    <br>
	<?php
	$install = 'false';
	include('../include/jsonapi.php');
	?>
    
</div>

<?php
include('footer.php');
?>
<script type="text/javascript" src="../script/fade.js"></script>
<script type="text/javascript" src="../script/jsonapi.class.js"></script>

<script>

var display = $('#console');

//Fonction permettant d'écrire les logs
function cmd_log(e) {
    display.val(display.val() + e);
    display.scrollTop(9999);
    display.scrollTop(display.scrollTop()*12);
}

//Fonction permettant d'envoyer une commande
function send_cmd(e) {
    var url = api.makeURL("runConsoleCommand", [e]);
    url = "/api"+url.substr(url.lastIndexOf("/"));
    url = url.substr(0, url.indexOf("&callback=?"));
    socket.send(url);
    cmd_log("Commande: "+e+"\n");
};

$('#listeServeurs img').live( "click", function() {
    var action = $(this).attr('class');
    var id = $(this).closest('tr').attr('id');
    var jsonapi = $(this).closest('tr').attr('jsonapi');
    
    if (jsonapi != 'none'){
        jsonapi = jsonapi.split("#");
        
        if(typeof(socket) != 'undefined'){ 
            socket.close();
        } 
        
        //Si on demande de stopper le serveur
        if (action == 'stop' || action == 'reload') {
            var img = $(this);
            img.attr('src', '../images/loaders/ajax_loader_h.gif');
            
            $.get("?" + action + "=" + jsonapi[0], function( html ) {
                img.attr('src', '../images/admin/serveur/' + action + '.png');
                
                if (html == 'success') {
                    if (action == 'stop') {
                        alert('Le serveur a bien été stoppé.');
                    } else {
                        alert('Le serveur a bien été rechargé.');
                    }
                } else {
                    alert('Une erreur est survenue, merci de réessayer ultérieurement.');
                }
            });
        }
    
        
        //Si on demande d'afficher la console
        if (action == 'console') {        
            //On recharge l'affichage de la console
            $('#console').val('Veuillez patienter...');
            
            //On affiche la console seule
            fade('consoleDiv');
            
            window.unload = function () {
                socket.close();
            };
            
            $('#cmd').keyup(function (e) {
                if(e.keyCode == 13) {
                    var command = $(this).val();
                    
                    if (command != "") {
                        send_cmd(command);
                        $(this).val('');
                    }
                }
            });
            
            if(!window.WebSocket) {
                alert('WebSocket not detected, console will not work! Get a cooler browser!');
            }
        
            api = new JSONAPI({
                host: jsonapi[3],
                port: parseInt(jsonapi[4]),
                username: jsonapi[5],
                password: jsonapi[6],
                salt: jsonapi[7],
            });
            
            api.call("getPlayerCount", function (data) {
                if(data.result == "success") {
        
                    socket = new WebSocket('ws://'+api.host+':'+(api.port+2)+'/');
                    
                    socket.onopen = function (e) {
                        display.val('');
                        socket.send("/api/subscribe?source=console&key="+api.createKey('console'));
                    };
                    
                    socket.onmessage = function (e) {
                        var data = JSON.parse(e.data);
                        
                        //Ecrit les logs
                        if(data.source == "console") {
                            cmd_log(data.result == "success" ? data.success.line : data.error);
                        }
                    };
                    
                    socket.oncolse = function (e) {
                        cmd_log("Connexion perdue...\n");
                    };
                }
            });
        }   
    }
});

$('form.updateServeur').submit(function() {
    var idForm = $(this).attr('id');
    var id = idForm.replace('form', '');
    
    $('form#form' + id + ' tr:last-child td').html('<br>Modification en cours... <img src="../images/loaders/ajax-loader_1.gif">');
    
    $.ajax({
        url: 'serveurs.php?type=updateServeur',
        type: 'post',
        data: 'id=' + id + '&' + $(this).serialize(),
        success: function(html) {
            if (html == 'success') {
                $('form#form' + id + ' .infosPost').html('<div class="warning_v">Votre serveur a bien été modifié.</div>');
            } else {
                $('form#form' + id).html('<div class="warning_r">Une erreur est survenue, merci de réessayer ultérieurement.</div>');
            }
            $('form#form' + id + ' tr:last-child td').html('<br><input type="submit" value="Enregistrer" class="submit">');
        }
    });
    
    return false;
});

$('a.deleteServeur').click(function() {
    var confirmation = confirm("Êtes-vous sûr de vouloir supprimer le serveur de votre liste ?");
    
    if (confirmation == true) {
        
        var idForm = $(this).closest('form').attr('id');
        var id = idForm.replace('form', '');
        
        $('#form' + id + ' tr:last-child td').html('<br>Suppresion en cours... <img src="../images/loaders/ajax-loader_1.gif">');
        
        $.ajax({
            url: 'serveurs.php?type=deleteServeur',
            type: 'post',
            data: 'id=' + id,
            success: function(html) {
                if (html == 'success') {
                    $('.popup_block').css('display', 'none');
                    $('#fade').css('display', 'none');

                    alert('Ce serveur a bien été supprimé de votre liste.');
                    
                    $('tr#' + id).fadeOut();
                } else {
                    $('#form' + id + ' tr:last-child td').html('<br>Une erreur est survenue, merci de réessayer ultérieurement.');
                }
            }
        });
    }
    
    return false;
});
</script>