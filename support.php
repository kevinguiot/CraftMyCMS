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

$titre_page = "Support";
include("include/init.php");

if(connect()) {
    //Mettre un billet en résolu
    if(!empty($_GET['resolu']) && !empty($_GET['token'])) {
        $req_selectBillet = $connexion->prepare('SELECT * FROM '.$prefixe.'support WHERE id=:id');
        $req_selectBillet->execute(array(
            'id' => $_GET['resolu']
        ));
        $selectBillet = $req_selectBillet->fetch();
        if(!empty($selectBillet)) {
            if($permissions[5] == true OR $selectBillet['pseudo'] == $pseudo) {
                $supprBillet = $connexion->prepare("UPDATE ".$prefixe."support SET etat=1 WHERE id=:id");
                $supprBillet->execute(array(
                    'id' => $_GET['resolu']
                ));
                header("location: ?msg=resolu:true");
            }
        }
    }
    
    //Supprimer un ticket
    if(!empty($_GET['suppr']) && !empty($_GET['token'])) {
        $req_selectBillet = $connexion->prepare('SELECT * FROM '.$prefixe.'support WHERE id=:id');
        $req_selectBillet->execute(array(
            'id' => $_GET['suppr']
        ));
        $selectBillet = $req_selectBillet->fetch();
        if(!empty($selectBillet)) {
            if($permissions[5] == true OR $selectBillet['pseudo'] == $pseudo) {
                $supprBillet = $connexion->prepare('DELETE FROM '.$prefixe.'support WHERE id=:id');
                $supprBillet->execute(array(
                    'id' => $_GET['suppr']
                ));
                header("location: ?msg=suppr:true");
                exit;
            }
        }
    }

    //Répondre à un ticket
    if(!empty($_POST) && strstr($_SERVER['REQUEST_URI'], '?newReponse')) {
        $req_selectBillet = $connexion->prepare('SELECT * FROM '.$prefixe.'support WHERE id=:id');
        $req_selectBillet->execute(array(
            'id' => $_GET['newReponse']
        ));
        $selectBillet = $req_selectBillet->fetch();
        if(!empty($selectBillet)) {
            if($permissions[4] == true OR $selectBillet['pseudo']==$pseudo) {
                
                if(!empty($_POST['content'])) {
                    $addReponse = $connexion->prepare('INSERT INTO '.$prefixe.'support_rep SET id_post=:id_post, pseudo=:pseudo, content=:content, date=:date, heure=:heure');
                    $addReponse->execute(array(
                        'id_post' => $_GET['newReponse'],
                        'pseudo' => $pseudo,
                        'content' => secure($_POST['content']),
                        'date' => $date,
                        'heure' => $heure
                    ));
                    header('location: ?id='.$_GET['newReponse'].'&msg=rep:true');
                } else {
                    header('location: ?id='.$_GET['newReponse'].'&msg=rep:vide');
                }
                exit;
            }
        }
    }
    
    //Ajouter un nouveau ticket
    if(!empty($_POST) && strstr($_SERVER['REQUEST_URI'], '?newBillet')) {
        if(!empty($_POST['content']) && !empty($_POST['sujet'])) {
            if(!empty($_POST['prive'])) {
                $prive = true;
            } else {
                $prive = false;
            }
            
            $addBillet = $connexion->prepare('INSERT INTO '.$prefixe.'support SET sujet=:sujet, pseudo=:pseudo, email=:email, content=:content, type=2, prive=:prive, date=:date, heure=:heure');
            $addBillet->execute(array(
                'sujet' => secure($_POST['sujet']),
                'pseudo' => $pseudo,
                'email' => $email,
                'content' => secure($_POST['content']),
                'prive' => $prive,
                'date' => $date,
                'heure' => $heure
            ));
            
            $sql = $connexion->query("SELECT * FROM ".$prefixe."support ORDER BY id DESC");
            $req = $sql->fetch();
            header('location: ?id='.$req['id'].'&msg=envoi:true');
        } else {
            header('location: ?msg=envoiBillet:false');
        }
        exit;
    }
}

include("include/header.php");
?>

<div id="content" class="support">
    <div id="newBillet" class="popup_block">
        <?php
        if(connect()) { ?>
            <h3>Publier un nouveau billet</h3>
            <form method="post" action="?newBillet">
                <input type="text" name="sujet" placeholder="Sujet de votre billet...">
                <textarea rows="4" name="content" placeholder="Contenu de votre billet..."></textarea>
                <input type="submit" value="Envoyer"> <label for="prive">Billet privé<input id="prive" name="prive" type="checkbox"></label>
                <button style="float: right;">Annuler</button>
            </form>
        <?php } else {
            echo '<div class="warning_r">Veuillez vous connecter pour poster un billet</div>';
        }
        ?>
    </div>

    <?php
    if(!empty($_GET['msg'])) {
        if($_GET['msg']=="resolu:inexistant") { echo '<div class="warning_r">Ce ticket n\'existe pas</div>'; }
        if($_GET['msg']=="resolu:true") { echo '<div class="warning_v">Ce ticket est désormais résolu</div>'; }
        if($_GET['msg']=="resolu:false") { echo '<div class="warning_r">Vous n\'avais pas les droits suffisants pour résoudre ce ticket</div>'; }
        if($_GET['msg']=="suppr:inexistant") { echo '<div class="warning_r">Ce ticket n\'existe pas</div>'; }
        if($_GET['msg']=="suppr:true") { echo '<div class="warning_v">Ce ticket est désormais supprimé</div>'; }
        if($_GET['msg']=="suppr:false") { echo '<div class="warning_r">Vous n\'avez pas les droits suffisants pour supprimer ce ticket</div>'; }
        if($_GET['msg']=="ajout:true" OR $_GET['msg'] == "envoi:true") { echo '<div class="warning_v">Votre ticket a bien été posté</div>'; }
        if($_GET['msg']=="ajout:false") { echo '<div class="warning_r">Votre ticket est invalide</div>'; }
        if($_GET['msg']=="ajout:content") { echo '<div class="warning_r">Vous n\'avez pas saisi le contenu de votre ticket</div>'; }
        if($_GET['msg']=="rep:true") { echo '<div class="warning_v">Votre réponse a bien été publiée</div>'; }
        if($_GET['msg']=="rep:false") { echo '<div class="warning_r">Votre ticket est invalide</div>'; }
        if($_GET['msg']=="rep:droit") { echo '<div class="warning_r">Vous n\'avez pas les droits suffisants pour répondre à ce ticket</div>'; }
        if($_GET['msg']=="rep:pseudo") { echo '<div class="warning_r">Vous n\'avez pas saisi votre pseudo</div>'; }
        if($_GET['msg']=="connect:false") { echo '<div class="warning_r">Vous n\'avez pas les droits suffisants</div>'; }
    }
    msg("Veuillez saisir le sujet et le contenu de votre ticket.", 'r', 'get', 'envoiBillet:false');
    msg("Veuillez saisir le contenu de votre réponse.", 'r', 'get', 'rep:vide');
    
    //Affichage de la liste des billets
    if(empty($_GET['id'])) {
        if($rang > 1) {
            $req_selectBillet = $connexion->query('SELECT * FROM '.$prefixe.'support ORDER BY id DESC');
        }
        if(!connect()) {
            $req_selectBillet = $connexion->query('SELECT * FROM '.$prefixe.'support WHERE prive=0 ORDER BY id DESC');
        } else {
            $req_selectBillet = $connexion->query('SELECT * FROM '.$prefixe.'support ORDER BY id DESC');
        }
        $nbrBillet = $req_selectBillet->rowCount();
        if($nbrBillet > 0) { ?>
            <div id="listeBillets">
                <h3>Affichage de la liste des billets</h3>
                <a href="#?w=500" rel="newBillet" class="poplight newBillet" style="float: right;">Nouveau</a>
                <p style="clear: both;"></p>
                <table class="table table-bordered table-striped">
                    <?php
                    while($selectBillet = $req_selectBillet->fetch()) {
                        if(connect() && $rang < "2") {
                            if($selectBillet['prive']=="0") {
                                $etat = true;
                            } else {
                                if($selectBillet['prive']=="1" && $pseudo==$selectBillet['pseudo']) {
                                    $etat = true;
                                } else {
                                    $etat = false;
                                }
                            }
                        } else {
                            $etat = true;
                        }
                        
                        if($etat == true) {
                            if($selectBillet['etat']=="0") {
                                $etat = '<img src="images/false.png" alt="false">';
                            } else {
                                $etat = '<img src="images/true.png" alt="true">';
                            }
                            echo '<tr>';
                            echo '<td>'.$etat.'</td>';
 
                            echo '</td><td><a href="?id='.$selectBillet['id'].'">'.$selectBillet['sujet'].'</a>';
                            if($selectBillet['prive']=="1") {
                                echo ' (Privé)';
                            }                                  
                            echo '<br>Publié par <a href="membre.php?pseudo='.$selectBillet['pseudo'].'">'.$selectBillet['pseudo'].'</a>, le '.$selectBillet['date'].' à '.$selectBillet['heure'].'</a></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </table>
            </div>
        <?php
        } else {
            msg('Aucun ticket n\'a été publié pour le moment, <a href="#?w=500" rel="newBillet" class="poplight">ajoutez le votre</a>.', 'b');
        }
    } else {
        
        //Affichage d'un ID
        $req_selectBillet = $connexion->prepare('SELECT * FROM '.$prefixe.'support WHERE id=:id');
        $req_selectBillet->execute(array(
            'id' => $_GET['id'],
        ));
        $selectBillet = $req_selectBillet->fetch();
        if($selectBillet) {
            if($selectBillet['prive'] == "1") {
                if(connect()) {
                    if($pseudo == $selectBillet['pseudo'] || $rang == "3" || $rang == "2") {
                        $etat = true;
                    
                    } else {
                        $etat = false;
                    }
                    
                } else {
                    $etat = false;
                }
            } else {
                $etat = true;
            }
        }
        
        if(!empty($selectBillet) && $etat == true) {
            if($selectBillet['etat']=="1") {
                $resolu = ' <span style="color:green; font-weight:bold">[RESOLU]</span>';
            } else {
                $resolu = false;
            }
            
            if($selectBillet['prive']=="1") {
                $prive = ' (Privé)';
            } else {
                $prive = false;
            }
            ?>
            <div id="newReponse" class="popup_block">
                <h3>Ajouter une réponse à ce billet</h3>
                <form method="post" action="?newReponse=<?php echo $_GET['id']; ?>">
                    <textarea rows="4" name="content" placeholder="Contenu de votre billet..."></textarea>
                    <input type="submit" value="Envoyer"> <button>Annuler</button>
                </form>
            </div>
            
            <div class="support_titre">
                <?php
                echo '<strong>'.$selectBillet['sujet'].'</strong>'.$prive,$resolu.'<br>';
                echo "Publié par <a href='membre.php?pseudo=".$selectBillet['pseudo']."'>".$selectBillet['pseudo']."</a> le ".$selectBillet['date'].' à '.$selectBillet['heure'];
                ?>
            </div>
            <div class="support_texte">
                <?php echo str_replace("\r", '<br>', $selectBillet['content']); ?>
            </div>
            
            <?php
            if(connect() && ($rang == "3" OR $selectBillet == $pseudo)) {
                echo '<div style="padding: 5px 15px 0px; font-weight:bold;">';
                echo '<a href="?resolu='.$selectBillet['id'].'&token='.$_SESSION['token'].'">Mettre ce billet en résolu</a> • <a href="?suppr='.$selectBillet['id'].'&token='.$_SESSION['token'].'">Supprimer ce billet</a>';
                echo '</div>';
            }
            
            $req_selectReponses = $connexion->prepare('SELECT * FROM '.$prefixe.'support_rep WHERE id_post=:id_s');
            $req_selectReponses->execute(array(
                'id_s' => $selectBillet['id']
            ));
            $nbr_selectReponses = $req_selectReponses->rowCount();
            if($nbr_selectReponses>0) {
                echo '<br><h3>Réponses</h3>';
                $i = 0;
                while($selectReponses = $req_selectReponses->fetch()) {
                    
                    $content = $selectReponses['content'];
                    
                    if(getUserInfos('pseudo', $selectReponses['pseudo'], 'rang') == '3') {
                        $content = htmlspecialchars_decode($content);
                    }
                    
                    $content = nl2br($content);
                    
                    if($i > 0) {
                        echo '<br>';
                    }
                    echo '<div class="billet_titre">';
                    echo '<a href="membre.php?pseudo='.$selectReponses['pseudo'].'">'.$selectReponses['pseudo']."</a>, le ".$selectReponses['date'].' à '.$selectReponses['heure'];
                    echo '</div>';
                    echo '<div class="billet_texte">';
                    echo $content;
                    echo '</div>';
                    $i++;
                }
    
                if(connect() && ($permissions[4] OR $pseudo == $selectBillet['pseudo'])) {
                    echo '<div style="text-align:center; font-weight:bold;"><a href="#?w=500" rel="newReponse" class="poplight">Ajouter une réponse</a> • <a href="support.php">Revenir à la liste des billets</div>';
                }
            } else {
                if($permissions[4] OR (!empty($pseudo) && $pseudo == $selectBillet['pseudo'])) {
                    msg('Aucune réponse n\'a été apportée à ce billet, <a href="#?w=500" rel="newReponse" class="poplight">ajoutez une réponse</a>.', 'b');
                } else {
                    msg('Aucune réponse n\'a été apportée à ce billet', 'b');
                }
            }
        } else {
            msg('Ce billet est indisponible.', 'r');
        }
    }
echo '</div>';
include("include/footer.php");?>
<script type="text/javascript" src="script/fade.js"></script>