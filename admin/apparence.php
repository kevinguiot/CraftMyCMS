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

$titre_page = "Apparence du CMS";
include("../include/init.php");

if(strstr($_SERVER['REQUEST_URI'], 'onglet')) {
	$req_selectOnglet = $connexion->prepare('SELECT * FROM '.$prefixe.'onglets WHERE id=:id');
	$req_selectOnglet->execute(array('id' => $_GET['id']));
	$nbr_selectOnglet = $req_selectOnglet->rowCount();
	
	if($nbr_selectOnglet == 1) {
		if($_GET['onglet'] == 'update') {
			if(empty($_POST['new'])) {
				$new = '0';
			} else {
				$new = '1';
			}
			
			$updateOnglet = $connexion->prepare('UPDATE '.$prefixe.'onglets SET nom=:nom, href=:href, new=:new WHERE id=:id');
			$updateOnglet->execute(array(
				'nom' => $_POST['nom'],
				'href' => $_POST['href'],
				'new' => $new,
				'id' => $_GET['id']
			));
		}
		
		if($_GET['onglet'] == 'delete' && !empty($_GET['token'])) {
			$deleteOnglet = $connexion->prepare('DELETE FROM '.$prefixe.'onglets WHERE id=:id');
			$deleteOnglet->execute(array(
				'id' => $_GET['id']
			));
		}
		header('location: apparence.php');
	}
	exit;
}

if(strstr($_SERVER['REQUEST_URI'], 'newOnglet')) {
	$addOnglet = $connexion->prepare('INSERT INTO '.$prefixe.'onglets SET nom=:nom, href=:href, new=:new, pos=:pos, niv=:niv');
	$addOnglet->execute(array(
		'nom' => 'Nouvel onglet',
		'href' => '',
		'new' => false,
		'pos' => 'pos + 1',
		'niv' => 'main'
	));
	
	$req_selectLastOnglet = $connexion->query('SELECT id FROM '.$prefixe.'onglets ORDER BY id DESC');
	$selectLastOnglet = $req_selectLastOnglet->fetch();
	
	header('location: apparence.php');
	exit;
}

if(!empty($_GET['json'])) {
    $i = '0';
    $l = '0';
    $array = json_decode($_GET['json'], true);
    
    foreach($array as $onglet) {
        if(!empty($onglet['children'])) {
            $niv = "nav";
            foreach($onglet['children'] as $children) {
                $req = 'UPDATE '.$prefixe.'onglets SET pos = "'.$l.'", niv = "'.$onglet['id'].'" WHERE id = "'.$children['id'].'"';
                $updateOnglet = $connexion->query($req);
                
                echo '<br>'.$req;
                $l++;
            }
            
        } else {
            $niv = "main";
        }
        $req = 'UPDATE '.$prefixe.'onglets SET pos = "'.$i.'", niv = "'.$niv.'" WHERE id = "'.$onglet['id'].'"';
        $updateOnglet = $connexion->query($req);
        
        echo '<br>'.$req;
        $i++;
    }
    exit;
}

include('header.php');
$req_selectOnglets = $connexion->query('SELECT * FROM '.$prefixe.'onglets WHERE niv = "main" OR niv = "nav" ORDER BY pos ASC');
?>

<link rel="stylesheet" href="../style/onglets.css" media="screen">

<div id="content">
	<h3>Structure de votre menu</h3>
	<p>Glissez les élements pour changer l'ordre. Vous pouvez créer des sous-élements.</p><br>
	
	<div class="dd" id="nestable" style="margin: 0px auto;">
		<ol class="dd-list">
			<?php
			
			function moveOnglets($onglets) {
				if($onglets['new'] == '1') {
					$new = ' checked';
				} else {
					$new = '';
				}
				
				echo '<li class="dd-item dd3-item" data-id="'.$onglets['id'].'">';
				echo '<div class="dd-handle dd3-handle">Drag</div>';
				echo '<div class="dd3-content"><strong>'.$onglets['nom'].'</strong>';
				
				if($onglets['niv'] != 'nav') {
					echo ' ('.$onglets['href'].')';
				}
				
				//Options
				echo '<span class="optionsOnglets">';
				echo '<img rel="page_white_edit'.$onglets['id'].'_edit" id="page_white_edit'.$onglets['id'].'" class="poplight" /*onclick="edit('.$onglets['id'].');"*/ src="../images/page_white_edit.png">';
				echo '<img id="delete'.$onglets['id'].'" onclick="deleteOnglet('.$onglets['id'].');" src="../images/false.png">';
				echo '</span>';
	
				echo '</div>';
				
				echo '<div id="page_white_edit'.$onglets['id'].'_edit" class="popup_block">
	
				<form method="post" action="?id='.$onglets['id'].'&onglet=update">
					<table>
						<tr>
							<td>Nom de l\'onglet:<br><input type="text" name="nom" value="'.$onglets['nom'].'"></td>
							<td>Lien de l\'onglet:<br><input type="text" name="href" value="'.$onglets['href'].'"></td>
						</tr>
						<tr>
							<td>Lien externe: <input type="checkbox" name="new" '.$new.'><br>
							<small>Le lien s\'ouvrira dans un nouvel onglet.</small></td>
							<td><input type="submit"></td>
						</tr>
					</table>
				</form>
				
				</div>';
			}
		
			while($selectOnglets = $req_selectOnglets->fetch()) {
				moveOnglets($selectOnglets);
				if($selectOnglets['niv'] == "nav") {
					echo '<ol class="dd-list">';
					$req_selectOngletsNav = $connexion->query('SELECT * FROM '.$prefixe.'onglets WHERE niv = "'.$selectOnglets['id'].'"');
					while($selectOngletsNav = $req_selectOngletsNav->fetch()) {
						moveOnglets($selectOngletsNav);
						echo '</li>';
					}
					echo '</ol>';
				}
				echo '</li>';
			}
			?>
		</ol>
	</div><br>
	<p style="text-align: center;"><span id="loading"></span><a href="?newOnglet">Ajouter un nouvel onglet</a></p>
</div>

<?php include('footer.php'); ?>

<script src="../script/jquery.nestable.js"></script>
<script src="../script/fade.js"></script>
<script>
function deleteOnglet(id) {
	var confirmation = confirm("Êtes-vous bien sûr de vouloir supprimer cet onglet ?");
	
	if (confirmation == true) {
		$('#delete' + id).attr('src', '../images/loading.gif');
		$.get('?id=' + id + '&onglet=delete&token=<?php echo $_SESSION['token']; ?>', function(data) {
			$('li[data-id=' + id + ']').remove();
		});
	}
}

$(document).ready(function() {
    var updateOutput = function(e) {
        var list   = e.length ? e : $(e.target);
		var data = window.JSON.stringify(list.nestable('serialize'));

		$('#loading').html('<strong>Enregistrement de la position des onglets...</strong><br>');
		
		$.ajax({
			url: 'apparence.php',
			type: 'get',
			data: 'json=' + data,
			success: function(html) {
				$('#loading').html('');
			}
		})
    };
	
    $('#nestable').nestable({
        group: 1
    })
    .on('change', updateOutput);
});
</script>