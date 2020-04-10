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

DEFINE('titre_page', 'Gestion des plugins');

include('../include/init.php');

if(!empty($_POST['modifPlugin'])) {

  if($_POST['activePlugins'] == 'on') {
    $activePlugins = "true";
  } else {
    $activePlugins = "false";
  }

  modifConfig('activePlugins', $activePlugins, false);
  header('location: plugins.php?msg=modif:ok');
  exit;
}

//Ajout d'un addon au CMS
if(!empty($_GET['add'])) {
  if(addAddon($_GET['add']) == true) {
    header('location: plugins.php?msg=ajout:true');
  } else {
    header('location: plugins.php?msg=ajout:false');
  }
  exit;
}

//Téléchargement d'un addon
if(!empty($_GET['download'])) {
  if(downloadAddon('plugin', $_GET['download']) == true) {
    header('location: plugins.php?msg=telechargement:true');
  } else {
    header('location: plugins.php?msg=telechargement:false');
  }
  exit;
}

//Achat d'un addon
if(!empty($_GET['buy'])) {
  $buyAddon = buyAddon($_GET['buy']);

  if($buyAddon != true) {
    header('location: plugins.php?msg=buy:false');
  } else {
    header('location: http://www.craftmycms.fr/membre/'.$buyAddon);
  }
  exit;
}

//Gestion du plugin
if(!empty($_GET['p'])) {
  $plugin = $_GET['p'];
  if(empty($_GET['action'])) {
    if(file_exists('../include/plugins/'.$plugin.'/INSTALLED')) {
      include('../include/plugins/'.$plugin.'/config/admin.php');
      exit;
    }
  } elseif($_GET['action']=="uninstall") {
    if(file_exists('../include/plugins/'.$plugin.'/INSTALLED')) {
      if(!unlink('../include/plugins/'.$plugin.'/INSTALLED')) {
        echo 'Impossible de supprimer le fichier <strong>"../include/plugins/'.$plugin.'/INSTALLED"</strong>';
        exit;
      } else {
        header('location: plugins.php?msg=uninstall:true');
      }
    }
  } elseif($_GET['action']=="install") {
    $dir = '../include/plugins/'.$_GET['p'].'/file/';
    if(is_dir($dir)) {
      $folder = opendir ($dir);
      while ($file = readdir ($folder)) {
        $test = "";
        if($file != "." && $file  != "..") {
          if(is_dir('../include/plugins/'.$_GET['p'].'/file/'.$file.'/')) {
            if(!is_dir('../'.$file)) {
              mkdir('../'.$file);
            }
            $folder2 = opendir('../include/plugins/'.$_GET['p'].'/file/'.$file.'/');
            while ($file2 = readdir ($folder2)) {
              $contenu = "<?php include('../include/init.php'); ?>";
              if($file2 != "." && $file2  != "..") {
                $fileCreate = '../'.$file.'/'.$file2;
                if(!file_exists($fileCreate)) {
                  $createFichier = fopen($fileCreate, 'w');
                  fwrite($createFichier, $contenu);
                  fclose($createFichier);
                }
              }
            }
          } else {
            $contenu = "<?php include('include/init.php'); ?>";
            $fileCreate = '../'.$file;
            if(!file_exists($fileCreate)) {
              $createFichier = fopen($fileCreate, 'w');
              fwrite($createFichier, $contenu);
              fclose($createFichier);
            }
          }
        }
      }
      closedir ($folder);
      include('../include/plugins/'.$_GET['p'].'/config/install.php');
      $createINSTALLED = fopen('../include/plugins/'.$_GET['p'].'/INSTALLED', 'w+');
      fwrite($createINSTALLED, null);
      fclose($createINSTALLED);

      header('location: plugins.php?msg=install:true');
      exit;
    } else {
      $erreurP = "Ce plugin est introuvable";
    }
  }
}

include("header.php");
?>

<div id="content" class="plugins">

  <?php
  msg('Cet addon a bien été ajouté à votre CMS.', 'v', 'get', 'ajout:true');
  msg("Il est impossible d'ajouter cet addon à votre CMS.", 'r', 'get', 'ajout:false');
  msg("Le téléchargement de cet addon a bien été effectué.", 'v', 'get', 'telechargement:true');
  msg("Le téléchargement de cet addon a echoué.", 'r', 'get', 'telechargement:false');
  msg("La désinstallation de cet addon a bien été effectuée.", 'v', 'get', 'uninstall:true');
  msg("L'installation de cet addon a bien été effectué.", 'v', 'get', 'install:true');
  msg("Il est impossible d'acheter cet addon.", 'r', 'get', 'buy:false');
  msg("L'état de l'utilisation des plugins a bien été modifié.", 'v', 'get', 'modif:ok');
  ?>

  <h3>Activation des plugins</h3>
  <form method="post" style="width: 614px; padding: 5px; margin: 10px auto; background-color: #f9f9f9; border-radius: 3px; border: 1px solid #DDD;">
    <input type="hidden" name="modifPlugin" value="true">
    <strong><input id="activePlugins" type="checkbox" name="activePlugins"<?php if($activePlugins == true) echo 'checked'; ?>> <label for="activePlugins">Activer l'utilisation des plugins</label> <input type="submit" value="Enregister"></strong><br>
    En désactivant cette option, les plugins ne seront pas activés sur votre CMS.<br>
    Cette option se désactive automatiquement après chaque mise à jours du système.
  </form><br>

  <h3>Magasin des plugins</h3>


  <?php
  //Récupération de l'ordre voulue
  if(!empty($_GET['ordre'])) {
    $ordre = $_GET['ordre'];
  } else {
    $ordre = null;
  }

  //Récupération des plugins.
  $listePlugins = getAddons('getPluginsShop', $ordre);

  if(!strstr($listePlugins[0], 'error=>') ) {
    ?>
    <table class="table table-bordered table-striped shop">
      <tr>
        <td>
          <span style="float: left; line-height: 38px">Nom du plugin</span>
          <span style="float: right; line-height: 38px">
            <select id="ordre" onchange="ordre();">
              <?php
              if(!empty($_GET['ordre'])) {
                $ordreGet = secure($_GET['ordre']);
              } else {
                $ordreGet = null;
              }

              $ordreList = array(
                array('', 'Affichage par défaut'),
                array('alphabetique', 'Alphabétique'),
                array('prix', 'Prix'),
                array('date', 'Date'),
              );

              $i = 0;
              foreach($ordreList as $ordre) {
                if($ordreList[$i][0] == $ordreGet) {
                  echo '<option value="'.$ordre[0].'" selected>'.$ordre[1].'</option>';
                } else {
                  echo '<option value="'.$ordre[0].'">'.$ordre[1].'</option>';
                }
                $i++;
              }
              ?>
            </select>
          </span>
        </td>
        <td>Développeur</td>
        <td>Version</td>
        <td></td>
      </tr>

      <?php
      foreach($listePlugins as $plugin) {
        list(
          $infosPlugin['id_addon'],
          $infosPlugin['user_id'],
          $infosPlugin['nom'],
          $infosPlugin['description'],
          $infosPlugin['image'],
          $infosPlugin['payant'],
          $infosPlugin['date'],
          $infosPlugin['heure'],
          $infosPlugin['udate'],
          $infosPlugin['uheure'],
          $infosPlugin['isDownload'],
          $infosPlugin['isMaj'],
          $infosPlugin['nomConfig'],
          $infosPlugin['version'],
          $infosPlugin['maj'],
          $infosPlugin['isDev'],

          ) = explode('%', $plugin);

          $prix = $infosPlugin['payant'];
          if($prix == '0') {
            $prix = 'Ce plugin est proposé gratuitement.';
          } else {
            $prix = 'Ce plugin est proposé pour la somme de <strong>'.$prix.'&#8364;</strong>.';
          }

          if($infosPlugin['isDev'] == 'true') {
            $infosPlugin['nom'] = '<span style="color: #AA0303;">[DEV]</span> '.$infosPlugin['nom'];
          }

          $page = 'http://www.craftmycms.fr/addons/'.$infosPlugin['nomConfig'].'/';

          echo '<tr>';
          echo '<td>

          <span style="float:left;">
          <a href="'.$page.'">'.$infosPlugin['nom'].'</a><br><small><a href="'.$page.'">Cliquez-ici pour accéder à la page du plugin.</a><br>'.$prix;

          if($infosPlugin['isDownload'] == 1) echo '<br><strong>Ce plugin est ajouté sur votre CMS.</strong>';
          echo '</small></span>';

          if($infosPlugin['isDownload'] == '1') {
            echo '<span style="float:right;">';
            if(file_exists('../include/plugins/'.$infosPlugin['nomConfig'].'/config/admin.php')) {
              if(file_exists('../include/plugins/'.$infosPlugin['nomConfig'].'/INSTALLED')) {
                echo '<a href="?p='.$infosPlugin['nomConfig'].'&action=uninstall"><img src="../images/admin/minus.png"></a>';
                echo ' <a href="?p='.$infosPlugin['nomConfig'].'"><img src="../images/admin/cog-1.png"></a>';
              } else {
                echo '<a href="?p='.$infosPlugin['nomConfig'].'&action=install"><img src="../images/admin/plus.png"></a> ';
                echo '<img class="notInstalled" src="../images/admin/cog-1.png">';
              }



            } else {
              echo '<img class="notInstalled" src="../images/admin/plus.png"> <img class="notInstalled" src="../images/admin/cog-1.png">';
            }
            echo '</span>';
          }

          echo '</td>';
          echo '<td><a title="Accéder au profil du développeur" target="_blank" href="http://www.craftmycms.fr/profil/'.$infosPlugin['user_id'].'">'.$infosPlugin['user_id'].'</a></td>';
          echo '<td>'.$infosPlugin['version'].'</td>';

          if($infosPlugin['isDownload'] == 1) {
            if($infosPlugin['isMaj'] != 'true' || !file_exists('../include/plugins/'.$infosPlugin['nomConfig'].'/config/admin.php')) {
              echo '<td style="background: #AA0303"><a href="?download='.$infosPlugin['id_addon'].'"><img src="../images/admin/arrow-upload_white.png"></a></td>';
            } else {
              echo '<td><a href="?download='.$infosPlugin['id_addon'].'"><img src="../images/admin/arrow-upload_black.png"></a></td>';
            }
          } elseif($infosPlugin['payant'] != '0') {
            echo '<td><a href="?buy='.$infosPlugin['id_addon'].'"><img src="../images/admin/cart-arrow-down.png"></a></td>';
          } else {
            echo '<td><a href="?add='.$infosPlugin['id_addon'].'"><img src="../images/admin/circle-plus.png"></td>';
          }

          echo '</tr>';
          echo '<div id="infos_'.$infosPlugin['id_addon'].'" class="popup_block">
          <h3>'.$infosPlugin['nom'].'</h3>
          </div>';
        }
        echo '</table>';

      } else {
        $message = getMessage($listePlugins[0]);
        msg($message, 'r', null, null, 10);
      }
      ?>
    </div>

    <?php include('footer.php'); ?>

    <script type="text/javascript" src="../script/fade.js"></script>
    <script>
    function ordre() {
      var val = $('#ordre').val();
      $(location).attr('href',"plugins.php?ordre=" + val);
    }
  </script>
