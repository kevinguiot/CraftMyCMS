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

$titre_page = "accueil";
include('../include/init.php');
include('header.php');

?>

<div id="content" class="finalisation">
    <h3>Félicitation, votre CMS est désormais installé !</h3>
    <p class="thanks">"Je vous remercie encore d'avoir acheté CraftMyCMS version Perso. J'espère vraiment que ce CMS vous sera très utile avec votre serveur. En cas de soucis, merci de rejoindre le support du site."</p>

    <p style="color:#AA0303; font-weight:bold;">N'oubliez pas de mettre à jour votre CMS en <a style="color:#AA0303;" href="update.php">cliquant ici</a></p>
    
    <hr>
    <a href="https://twitter.com/CraftMyCMS" class="twitter-follow-button" data-show-count="true" data-lang="fr" data-size="large">Suivre @CraftMyCMS</a>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    <br><br><div class="fb-like" data-href="https://www.facebook.com/craftmycms" data-send="true" data-width="500" data-show-faces="true" data-font="tahoma" data-colorscheme="light"></div>
</div>

<?php
include('footer.php');
?>
<script>
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>