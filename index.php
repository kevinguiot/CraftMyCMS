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
include("include/init.php");
include("include/header.php");
?>
<div id="content" class="index">
    <?php
    if(!empty($_GET['msg'])) {
        $msg = $_GET['msg'];
        if($msg == "admin:false") { echo '<div class="warning_r">Vous n\'avez pas les droits nécessaires pour accéder à cette page</div>'; }
        if($msg == "install") { echo '<div class="warning_r">Vous devez supprimer le fichier /install/LOCK pour pouvoir réinstaller votre CMS.</div>'; }
        if($msg == "update") { echo '<div class="warning_v nomargin">Des fichiers ont été modifiés automatiquement sur votre site.<br>Merci de vous rendre sur <a href="admin/update.php">cette page</a> afin de faire la mise à jour complète.</div>'; }
    }
    if(!empty($_GET['id'])) {
        $req_selectNews = $connexion->prepare('SELECT * FROM '.$prefixe.'news WHERE id=:id');
        $req_selectNews->execute(array('id' => $_GET['id']));
    } else {
        $req_selectNews = $connexion->query('SELECT * FROM '.$prefixe.'news ORDER BY id DESC');
        
        if($newsParPage > 0) {
            $nbr_selectNews = $req_selectNews->rowCount();
            $nbrPageNews = ceil($nbr_selectNews/$newsParPage);
            
            if(!empty($_GET['p'])) {
                $page = (int) $_GET['p'];
                if($page > $nbrPageNews) {
                    $page = $nbrPageNews;
                }
            } else {
                $page = "1";
            }
            
            $firstNews = ($page-1)*$newsParPage;
            $req_selectNews = $connexion->query('SELECT * FROM '.$prefixe.'news ORDER BY id DESC LIMIT '.$firstNews.', '.$newsParPage);
        } 
    }
    
    while($selectNews = $req_selectNews->fetch()) {
        $content = str_replace("[retour]", "<br>", $selectNews['content']);
        $content = str_replace('[a href=&quot;', '<a href="', $content);
        $content = str_replace('[a href="', '<a href="', $content);
        $content = str_replace('[/a]', '</a>', $content);
        $content = str_replace('&quot;]', '">', $content);
        $content = str_replace('"]', '">', $content);
        $content = htmlspecialchars_decode($content);
        
        $content = nl2br($content);

        if(!empty($_GET['id'])) {
            echo '&#8617; <a href="index.php" style="font-weight:bold;">Revenir sur la liste des news</a><br>';
        }

        if(empty($_GET['id']) && $selectNews['small'] == true) {
            $content = newsSmall($content, 270, $selectNews['id']);
        }      
        
        if(empty($selectNews['user'])) {
            $user = false;
        } else {
            $user = " par <a style=\"font-weight:bold;\" href=\"membre.php?pseudo=".$selectNews['user']."\">".$selectNews['user']."</a>";
        }
        
        echo '<div class="selectNews">';
        if(!empty($selectNews['titre'])) {
            echo '<a class="titre" href="?id='.$selectNews['id'].'">'.$selectNews['titre'].'</a>';
            echo '<hr class="hrdashed">';
        }
        if(!empty($selectNews['image'])) {
            echo '<img src="'.$selectNews['image'].'" class="imageNew" alt="new">';
        }
        echo $content;
        echo '<br style="clear:both;">';
        echo '<hr class="hrdashed">';
        echo '<a style="font-weight:bold;" href="?id='.$selectNews['id'].'">#</a> Ajouté le '.$selectNews['date'].' à '.$selectNews['heure'].$user;
        echo '</div>';
    }
    
    if($newsParPage > 0) {
        echo '<div class="pagination">';
        for($i = 1; $i <= $nbrPageNews; $i++) {
            
            if($i == $page) {
                echo '<span class="page active">'.$i.'</span>';
            } else {
                echo '<a href="?p='.$i.'" class="page">'.$i.'</a>';
            }
            
        }
        echo '</div>';
    }
    ?>

</div>
<?php include ("include/footer.php"); ?>