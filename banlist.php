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

$titre_page = "Banlist";
include("include/init.php");
include("include/header.php");
include('include/class/spyc.class.php');
?>

<div id="content" class="banlist">
<?php
if($etatJSONAPI == TRUE) {
    if($banlist == TRUE) {
        $banList = $api->call('getBannedPlayers');
        $banList = $banList['success'];
        $nbr_banList = count($banList);
        if($nbr_banList > 0) {
            $banParPage = 10;
            if(!empty($_GET['page'])) {
                $page = $_GET['page'];
            } else {
                $page = "1";
            }
            $page = $page."0";
            $nbrPage = ceil($nbr_banList/$banParPage);
    
            if(isset($_GET['page'])) {
                $pageActuelle = intval($_GET['page']);
                if($pageActuelle > $nbrPage) {
                    $pageActuelle = $nbrPage;
                }
            } else {
                $pageActuelle = 1;
            }
            
            $pPage = ($pageActuelle-1)*$banParPage;
            $l = $pageActuelle-'1'.'0';
            $i = 0;
            natcasesort($banList);
    
            echo '<table class="table table-bordered table-striped">';
            foreach ($banList as &$value) {
                $i++;
                if(($i%2)==0) { $color="background-color : #E6E6E6;"; } else { $color=""; }
                if($i>$l) {
					if(!empty($value)) {
						$value = secure($value);
						$motif = $api->call("getFileContents", array('plugins/Essentials/userdata/'.mb_strtolower($value).'.yml'));
						
						if(!empty($motif['success'])) {				
							$motif = str_replace(chr(10).chr(10), null, $motif["success"]);
							$motif = Spyc::YAMLLoad($motif);
							$motif1 = @secure($motif["ban"]["reason"]);
							if($motif1 == NULL) {
								$motif1 = @secure($motif["ban"]["reason: '§4Banned"]["0"]);
								$motif1 = str_replace('§r', null, $motif1);
								$motif1 = str_replace("&#039;", null, $motif1);
							}
						} else {
							$motif1 = "Aucun motif enregistré";
						}
						
						$heure = $api->call("getFileContents", array("banned-players.txt"));
						$heure = $heure['success'];
						$test = "$value|";
						$heure = strstr($heure, $test);
						$heure = str_replace("$value|", "", $heure);
						$heure = substr($heure, 0, 20);
						$heure = strtotime($heure);
						$heure = date("d/m/Y à H:i:s", $heure);
						
						echo '<tr title="'.$value.', bannis le '.$heure.'">';
						echo '<td><a href="membre.php?pseudo='.$value.'" title="Accéder au profil de '.$value.'"><img alt="'.$value.'" src="skin.php?pseudo='.$value.'"></a></td>';
						echo "<td><a href=\"membre.php?pseudo=$value\">$value</a>: $motif1</td>";
						echo '</tr>';
		
						if($i>$l+"10") {
							break;
						}
					}
                }
            }
            echo '</table>';
            echo '<div id="pagination">';
            unset($value);
            for($i=1; $i<=$nbrPage; $i++) {
                if($i==$pageActuelle) {
                    echo ' <strong>['.$i.']</strong> '; 
                } else {
                    echo ' <a style="font-weight:none;" href="?page='.$i.'">'.$i.'</a> ';
                }
            }
            echo '</div>';
        } else {
            echo '<h3>Aucun joueur n\'a été banni de ce serveur</h3>';
        }
    } else {
		msg("La banlist est désactivée.", 'r');
    }
} else {
	msg("JSONAPI rencontre des problèmes, merci de contacter l'administrateur de ce site.", 'r');
}
echo '</div>';
include ("include/footer.php"); ?>