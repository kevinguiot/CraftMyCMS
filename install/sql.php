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

if(defined('AUTH_ID') && AUTH_ID == true) {

    //URL du site (pour images)
    $urlSite = "http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
    $urlSite = str_replace('install/index.php', null, $urlSite);
    $urlSite = str_replace(" ", '%20', $urlSite);

    //Créations des tables
    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."onglets` (
       `id` int(1) NOT NULL AUTO_INCREMENT,
       `nom` varchar(255) NOT NULL,
       `href` text NOT NULL,
       `new` int(1) NOT NULL DEFAULT '0',
       `pos` varchar(1) NOT NULL,
       `niv` varchar(4) NOT NULL,
       PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."boutique` (
       `id` int(100) NOT NULL AUTO_INCREMENT,
       `article` varchar(255) NOT NULL,
       `description` text NOT NULL,
       `categorie` varchar(255) NOT NULL,
       `image` varchar(255) NOT NULL,
       `commande` text NOT NULL,
       `valeur` varchar(255) NOT NULL,
       `valeur_ig` varchar(255) NOT NULL,
       `achat` int(100) NOT NULL,
       `date` varchar(255) NOT NULL,
       `heure` varchar(255) NOT NULL,
       `requis` varchar(255) NOT NULL,
       `limite` varchar(255) NOT NULL,
       `serveur` varchar(255) NOT NULL,
       PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."boutique_liste` (
       `id` int(3) NOT NULL AUTO_INCREMENT,
       `pseudo` varchar(255) NOT NULL,
       `id_boutique` varchar(255) NOT NULL,
       `date` varchar(255) NOT NULL,
       `heure` varchar(255) NOT NULL,
       `etat` int(1)  DEFAULT '1',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."boutique_cat` (
       `id` int(100) NOT NULL AUTO_INCREMENT,
       `categorie` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."commandes` (
      `id` int(3) NOT NULL AUTO_INCREMENT,
      `nb` varchar(255) NOT NULL,
      `pseudo` varchar(255) NOT NULL,
      `date` varchar(255) NOT NULL,
      `heure` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."conv_id` (
      `id` int(3) NOT NULL AUTO_INCREMENT,
      `sujet` varchar(255) NOT NULL,
      `id_sender` int(3) NOT NULL,
      `id_receiver` int(3) NOT NULL,
      `etat` int(1)  DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."conv_text` (
      `id` int(3) NOT NULL AUTO_INCREMENT,
      `id_conv` int(3) NOT NULL,
      `id_sender` int(3) NOT NULL,
      `content` text NOT NULL,
      `date` varchar(255) NOT NULL,
      `heure` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."etape_temp` (
      `etape1` varchar(255) NOT NULL DEFAULT '0',
      `etape2` varchar(255) NOT NULL DEFAULT '0',
      `etape3` varchar(255) NOT NULL DEFAULT '0',
      `etape4` varchar(255) NOT NULL DEFAULT '0',
      `etape5` varchar(255) NOT NULL DEFAULT '0',
      `etape6` varchar(255) NOT NULL DEFAULT '0',
      `etape7` varchar(255) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");


    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."log_connexion` (
      `id` int(1) NOT NULL AUTO_INCREMENT,
      `pseudo` varchar(255) NOT NULL,
      `ip` varchar(255) NOT NULL,
      `navigateur` varchar(255) NOT NULL,
      `date` varchar(255) NOT NULL,
      `tentative` int(1) NOT NULL,
      `etat` int(1) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");


    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."membres` (
      `id` int(3) NOT NULL AUTO_INCREMENT,
      `session` text NOT NULL,
      `rang` int(1) NOT NULL DEFAULT '1',
      `pseudo` varchar(255) NOT NULL,
      `prenom` varchar(255) NOT NULL,
      `nom` varchar(255) NOT NULL,
      `passe` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `date` varchar(255) NOT NULL,
      `heure` varchar(255) NOT NULL,
      `oublie` varchar(255) NOT NULL,
      `ip` varchar(255) NOT NULL,
      `token` int(10)  NOT NULL DEFAULT '0',
      `ddate` varchar(255) NOT NULL,
      `dheure` varchar(255) NOT NULL,
      `travail` varchar(255) NOT NULL,
      `naissance` varchar(10) NOT NULL,
      `localisation` varchar(255) NOT NULL,
      `web` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."news` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `titre` varchar(255) NOT NULL,
      `content` text NOT NULL,
      `image` varchar(255) NOT NULL,
      `date` varchar(255) NOT NULL,
      `heure` varchar(255) NOT NULL,
      `user` varchar(255) NOT NULL,
      `small` int(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."slider` (
      `id` int(3) NOT NULL AUTO_INCREMENT,
      `slider` text NOT NULL,
      `titre` varchar(255) NOT NULL,
      `content` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."support` (
      `id` int(3) NOT NULL AUTO_INCREMENT,
        `sujet` varchar(255) NOT NULL,
        `pseudo` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `type` int(1) NOT NULL,
        `prive` int(1) NOT NULL DEFAULT '0',
        `etat` int(1) NOT NULL,
        `date` varchar(255) NOT NULL,
        `heure` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."support_rep` (
      `id` int(3) NOT NULL AUTO_INCREMENT,
        `id_post` int(3) NOT NULL,
        `pseudo` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `date` varchar(255) NOT NULL,
        `heure` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."visites` (
      `date` varchar(255) NOT NULL,
      `ip` varchar(255) NOT NULL
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    $dbh->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."serveurs` (
        `id` int(1) NOT NULL AUTO_INCREMENT,
        `nom` varchar(255) NOT NULL,
        `ip` varchar(255) NOT NULL,
        `external_ip` varchar(255) NOT NULL,
        `port` int(1) NOT NULL,
        `user` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `salt` varchar(255) NOT NULL,
        `etat` int(1) NOT NULL DEFAULT '1',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");

    //Ajout des données
    $dbh->exec("INSERT INTO `".$prefixe."news` (`titre`, `content`, `image`, `date`, `heure`)
     VALUES ('Nouveau chez CraftMyCMS', 'Merci à vous d\'avoir acheté et installé CraftMyCMS version Perso ![retour]Plus d\'informations sur [a href=\&quot;http://www.craftmycms.fr\&quot;]www.craftmycms.fr[/a][retour]Si vous trouvez un bug, n\'hésitez pas à le reporter sur le support du site.', '".$urlSite."images/orlingot.png', '".$date."', '".$heure."')")

or die(print_r($dbh->errorInfo(), true)); // incorrect
    $dbh->exec("INSERT INTO `".$prefixe."boutique_cat` (`categorie`) VALUES ('defaut')");

    $dbh->exec("INSERT INTO `".$prefixe."slider` (`id`, `slider`, `titre`, `content`) VALUES
    (1, '".$urlSite."images/slider/bienvenue.jpg', 'Nouveau chez CraftMyCMS', 'Merci à vous d\'avoir acheté CraftMyCMS version Perso !'),
    (2, '".$urlSite."images/slider/boutique.jpg', 'Boutique automatique', 'Vendez tout et n\'importe quoi sur votre site.'),
    (3, '".$urlSite."images/slider/news.jpg', 'News, simple et rapide', 'Informez votre communauté simplement, et rapidement.'),
    (4, '".$urlSite."images/slider/support.jpg', 'Support, aide en ligne', 'Aidez vos membres directement via votre site.');
    ");

    $dbh->exec('INSERT INTO '.$prefixe.'onglets (nom, href, new, pos, niv) VALUES
        ("Accueil", "index.php", "0", "0", "main"),
        ("Membres", "membre.php", "0", "1", "main"),
        ("Boutique", "boutique.php", "0", "2", "main"),
        ("Banlist", "banlist.php", "0", "3", "main"),
        ("Support", "support.php", "0", "4", "main"),
        ("Autre", "", "0", "5", "nav"),
        ("Règlement", "reglement.php", "0", "0", "6");
    ');
}
?>
