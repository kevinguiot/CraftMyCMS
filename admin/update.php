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

$titre_page = "Mise à jours";
include("../include/init.php");
$getUpdates = sendToCraftMyCMS('getUpdates');
$getUpdates = base64_decode($getUpdates);
$getUpdates = explode('#', $getUpdates);
$erreur = "Impossible de récupérer le contenu de la MàJ de CraftMyCMS, veuillez réessayer ultérieurement.<br>Vous pouvez aussi contacter le support de CraftMyCMS: <a target=\"_blank\" href=\"http://www.craftmycms.fr/support/\">http://www.craftmycms.fr/support/</a>.";
include("header.php");
?>
<div id="content" class="update">
  <?php
  if(!empty($_GET['success']) || !empty($_GET['error'])) {
    if(!empty($_GET['success']) && $_GET['success'] == true && !empty($_GET['file'])) {
      msg('La mise à jour s\'est bien déroulée. Vous pouvez retrouver les logs ici:<br><a target="_blank" href="http://www.craftmycms.fr/log/'.$_GET['file'].'.txt">http://www.craftmycms.fr/log/'.$_GET['file'].'.txt</a>', 'v', '', '', 0).'<br>';
    } else {
      if(!empty($_GET['error'])) {
        switch ($_GET['error']) {
          case 'zip':
          msg("Attention, vérifiez que ces fonctions: zip_open, zip_read, zip_entry_name et zip_close soient bien activées pour poursuivre le téléchargement et l'installation de la mise à jour.", 'r', '', '', 0);
          break;
          case 'get_content':
          msg('Impossible de récupérer l\'archive contenant la mise à jour du système.<br>Veuillez contacter le support de CraftMyCMS.fr, en <a href="http://www.craftmycms.fr/support/">cliquant ici</a>.', 'r', '', '', 0);
          break;
          case 'untraceable':
          msg("La mise à jour que vous souhaitez installer n'existe pas.", 'r', '', '', 0);
          break;
          case 'files':
          if(!empty($_GET['maj']) && !empty($_GET['file'])) {
            msg("La mise à jour ".$_GET['maj']." a rencontré une erreur. Veuillez regarder les logs:<br>
            <a target='_blank' href='http://www.craftmycms.fr/log/".$_GET['file'].".txt'>http://www.craftmycms.fr/log/".$_GET['file'].".txt</a>", 'r', '', '', 0);
            break;
          }
        }
      }
    }
    echo '<br>';
  }

  if(!empty($erreur)) {
    msg($erreur, 'r', null, null, 0);
  }
  if(empty($erreur)) { ?>
    <h3 style="margin-top:0px;">Informations de la mise à jour</h3>
    <div class="maj">
      <img src="../images/admin/info.png">
      <p style="float: left;">
        <b><?php echo $getUpdates[2]; ?></b><br>
        Sortie le <?php echo date('d/m/Y à H:i:s', $getUpdates[3]); ?><br>
        <a target="_blank" href="http://developpeur.craftmycms.fr/changelog/cms/#<?php echo $getUpdates[4]; ?>">Afficher le changelog de la mise à jour</a>
      </p>
      <p style="clear: both;"></p>
    </div>

    <h3>Mettre à jour votre CMS</h3>
    <div class="maj">
      <img id="image-neon" src="../images/admin/download.png">
      <p style="float: left;">
        <?php
        if($maj_etat == false) {
          echo '<b>Attention votre CMS n\'est pas à jour !</b>';
        } else {
          echo '<b>Bravo, votre CMS est à jour !</b>';
        }
        ?><br>
        <button onclick="confirmMaj()">Télécharger et Installer</button> <a id="affParametres">Afficher les paramètres avancés</a>
      </p>
      <br style="clear: both;">
      <div id="parametres"><br>

        <input type="checkbox" name="reinit[]" value="module" id="module"> <label for="module">Réinitialiser le fichier de customisation des modules</label><br>
        <input type="checkbox" name="reinit[]" value="reglement" id="reglement"> <label for="reglement">Réinitialiser le règlement</label><br>
        <input type="checkbox" name="reinit[]" value="style" id="style"> <label for="style">Réinitialiser le fichier de customisation du style</label><br>
        <input type="checkbox" name="reinit[]" value="images" id="images"> <label for="images">Réinitialiser le background, le logo, et le favicon</label><br>
        <input type="checkbox" name="reinit[]" value="sliders" id="sliders"> <label for="sliders">Réinitialiser les image des sliders</label><br>
        <br>
        <table>
          <tr>
            <td><b>Dossier où télécharger et installer le CMS</b><br><small>Laissez vide pour remplacer les fichiers actuels de votre CMS.<br>Ne dois pas comporter de slash à la fin ni au début.</small></td>
            <td><input type="text" name="dir"></td>
          </tr>
        </table>
      </div>
    </div>
  <?php } ?>
  <h3>Les mises à jour sont-elles obligatoires ?</h3>
  Oui, votre CMS doit être à jour en permanence afin d'éviter tout problème de sécurité.
  <h3>Les données vont-elles être perdues ?</h3>
  Les éléments sauvegardés sont: La configuration de votre CMS ainsi que celle de JSONAPI, vos onglets, vos modules, votre style (S'il est bien placé dans la page /style/custom_style.css), votre slider et votre règlement. Il est inutile de modifier les pages de votre CMS.
  <h3>Que dois-je faire après la mise à jour ?</h3>
  Premièrement, vous devez vider le cache de votre navigateur (<a href="http://www.libellules.ch/support/cache.php" target="_blank">cliquez-ici</a>). Vous pouvez ensuite regarder le changelog de cette mise à jour afin d'être au courant des modifications apportées.
</div>
<?php include("footer.php"); ?>

<script>


$('a#affParametres').click(function() {
  var parametres = $('#parametres');

  if (parametres.css('display') == 'none') {
    parametres.css('display', 'block');
  } else {
    parametres.css('display', 'none');
  }
});


<?php

if(empty($erreur)) {
  echo 'var token = "'.$token.'";';  ?>

  $( "input[name=type]" ).on( "click", function() {
    var reinit = $("#parametres").find(':checkbox');

    if ($( "input:checked" ).val() == 'new') {
      reinit.attr('checked', true)
      reinit.attr('disabled', true)
    } else {
      reinit.attr('checked', false);
      reinit.attr('disabled', false)
    }
  });

  function confirmMaj() {
    var checked = []
    $("input[name='reinit[]']:checked").each(function () {
      checked.push($(this).val());
    });
    checked = encodeURIComponent(checked);

    dir = $('input[name=dir]').val();
    type = $('input[type=radio][name=type]:checked').attr('value');

    r = confirm("Confirmez-vous la mise à jour de votre CMS ?");
    if (r==true) {
      document.location.href = "../update.php?reinit=" + checked + "&dir=" + dir;
    }
  }
  </script>
<?php } ?>
