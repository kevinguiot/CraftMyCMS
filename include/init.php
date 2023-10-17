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

ob_start();
session_start();

header("Content-Type: text/html; charset=utf-8");

ini_set('display_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

DEFINE('AUTH_ID', true);

if (!empty($_GET['token']) && $_GET['token'] != $_SESSION['token']) {
    die('Le jeton de sécurité est périmé.');
}

if (file_exists(__DIR__ . '/config/CMS_INFO')) {
    $CMS_INFO = file_get_contents(__DIR__ . '/config/CMS_INFO');
} else {
    $erreurActivation = 2;
}

include('class/send.CraftMyCMS.php');

date_default_timezone_set('Europe/Paris');
$date = date("d/m/Y");
$heure = date("H:i:s");

$copyright = 'Site internet Minecraft proposé par <a target=_blank href="http://www.craftmycms.fr?ref=cms">CraftMyCMS</a>';
$urlFooter = $_SERVER['REQUEST_URI'];
$include = "TRUE";
$compatible = "&#10004;";
$non_compatible = "&#10006;";

if ($urlFooter == "/include/footer.php") {
    $copyright = encrypt($copyright);
    echo $copyright;
    exit;
}

$repertoire = dirname($_SERVER["PHP_SELF"]);
if (empty($theme)) {
    $theme = "default";
}

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}


//Liste des fonctions utilisées
function addslashes_recursive($input)
{
    if (is_array($input)) {
        $var = array_mal(__FUNCTION__, $input);
    } else {
        $var = addslashes($input);
        $var = str_replace('"', '&quot;', $var);
    }
    $var = str_replace("\r\n", '<br>', $var);
    $var = str_replace('[retour]', '<br>', $var);
    return $var;
}


function age($naissance)
{
    $secPerYear = 31556926;

    if ($naissance == "Non-renseigné") {
        return $naissance;
    }

    $segments = explode('/', $naissance);
    $timestampDoB = strtotime($segments[2] . "-" . $segments[1] . "-" . $segments[0]);
    $deltaSec = time() - $timestampDoB;
    $age = floor($deltaSec / $secPerYear);

    return $age . ' ans';
}


function getNavigateur($HTTP_USER_AGENT)
{
    $_SERVER["HTTP_USER_AGENT"] = $HTTP_USER_AGENT;

    if (preg_match_all("#Opera (.*)(\[[a-z]{2}\];)?$#isU", $_SERVER["HTTP_USER_AGENT"], $version)) {
        $navigateur = 'Opéra ' . $version[1][0];
    } elseif (preg_match_all("#MSIE (.*);#isU", $_SERVER["HTTP_USER_AGENT"], $version)) {
        $navigateur = 'Internet Explorer ' . $version[1][0];
    } elseif (preg_match_all("#Firefox(.*)$#isU", $_SERVER["HTTP_USER_AGENT"], $version)) {
        $version = str_replace('/', '', $version[1][0]);
        $navigateur = 'Firefox ' . $version;
    } elseif (preg_match_all("#Chrome(.*) Safari#isU", $_SERVER["HTTP_USER_AGENT"], $version)) {
        $version = str_replace('/', '', $version[1][0]);
        $navigateur = 'Chrome ' . $version;
    } elseif (preg_match_all("#Opera(.*) \(#isU", $_SERVER["HTTP_USER_AGENT"], $version)) {
        $version = str_replace('/', '', $version[1][0]);
        $navigateur = 'Opéra ' . $version;
    } elseif (preg_match("#Nokia#", $_SERVER["HTTP_USER_AGENT"])) {
        $navigateur = 'Nokia';
    } elseif (preg_match("#Safari#", $_SERVER["HTTP_USER_AGENT"])) {
        $navigateur = 'Safari';
    } elseif (preg_match("#SeaMonkey#", $_SERVER["HTTP_USER_AGENT"])) {
        $navigateur = 'SeaMonkey';
    } elseif (preg_match("#PSP#", $_SERVER["HTTP_USER_AGENT"])) {
        $navigateur = 'PSP';
    } elseif (preg_match("#Netscape#", $_SERVER["HTTP_USER_AGENT"])) {
        $navigateur = 'Netscape';
    } else {
        $navigateur = 'Inconnu';
    }

    return $navigateur;
}


function getUserInfos($infos, $id, $get)
{
    global $connexion;
    global $prefixe;
    $req_selectMembre = $connexion->prepare("SELECT * FROM " . $prefixe . "membres WHERE $infos=:$infos");
    $req_selectMembre->execute(array($infos => $id));
    $selectMembre = $req_selectMembre->fetch();
    if (!empty($selectMembre)) {
        if ($get != 'color') {
            return $selectMembre[$get];
        } else {
            $rang = $selectMembre['rang'];
            if ($rang == "3") {
                $color = '#AA0303';
            } elseif ($rang == "2") {
                $color = 'green';
            } else {
                $color = 'black';
            }

            return '<span style="color:' . $color . '">' . $selectMembre['pseudo'] . '</span>';
        }
    } else {
        return false;
    }
}


function iscurlinstalled()
{
    ((in_array("curl", get_loaded_extensions()))) ? $r = true : $r = false;
    return $r;
}

function isioncubeinstalled()
{
    (extension_loaded('ionCube Loader')) ? $r = true : $r = false;
    return $r;
}


function modifConfig($parametre, $content, $quote = true, $file = null)
{
    if ($file == null) {
        $file = __DIR__ . "/config/config.inc.php";
    } else {
        $file = __DIR__ . $file;
    }

    $fd = @fopen($file, "r");
    $i = 1;
    while (!feof($fd)) {
        $ligne = fgets($fd, 1024);
        if (!feof($fd)) {
            if (strstr($ligne, '$' . $parametre)) {
                $id = $i;
            }
        }
        $i++;
    }
    fclose($fd);

    $id = $id - 1;
    if ($content != null) {
        if ($quote == true) {
            $new = '$' . $parametre . ' = "' . $content . '";' . "\n";
        } else {
            $new = '$' . $parametre . ' = ' . $content . ';' . "\n";
        }
    } else {
        if (empty($_POST[$parametre]) or $_POST[$parametre] == 'false' or $_POST[$parametre] == false) {
            $arg = "false";
        } else {
            $arg = "true";
        }
        $new = '$' . $parametre . ' = ' . $arg . ';' . "\n";
    }

    if (is_file($file)) $content = file($file);
    else $content = array();
    $content[(int)$id] = $new;
    $content = implode("", $content);
    if (($fp = fopen($file, 'w')) !== FALSE) {
        fwrite($fp, $content);
        fclose($fp);
    }
}

function msg($messageText, $niveau = 'v', $type = null, $url = null, $margin = '10', $close = null)
{
    if ($type == 'get') {
        if (!empty($_GET['msg']) && $_GET['msg'] == $url) {
            $message = $messageText;
        }
    } elseif ($type == 'url') {
        if (strstr($_SERVER['REQUEST_URI'], $url)) {
            $message = $messageText;
        }
    } else {
        $message = $messageText;
    }

    if ($margin != null) {
        $margin = ' style="margin: ' . $margin . 'px"';
    }

    if ($close != null) {
        $close = '<img alt="closeMsg" title="Fermer ce message" src="../images/circle-ex.png">';
    }

    if (!empty($message)) {
        if ($niveau == 'v' || $niveau == null) {
            $messageNiveau = '<div class="warning_v"' . $margin . '>';
        } elseif ($niveau == 'r') {
            $messageNiveau = '<div class="warning_r"' . $margin . '>';
        } else {
            $messageNiveau = '<div class="warning_b"' . $margin . '>';
        }

        echo $messageNiveau . $close . $message . '</div>';
    }
}


function newsSmall($texte, $nombreDeCaractere, $id)
{
    if (strlen($texte) > $nombreDeCaractere) {
        $texte = substr($texte, 0, $nombreDeCaractere);

        if (strstr($texte, ' ')) {
            $texte = substr($texte, 0, strrpos($texte, ' '));
        }

        $texte = strip_tags($texte);

        return $texte . ', <a style="font-weight:bold;" href="?id"=' . $id . '">afficher la suite...</a>';
    } else {
        return $texte;
    }
}


function pagination($page, $aff, $max, $entites)
{
    if ($aff != "3") {
        $count = count($entites);
    } else {
        $count = $entites;
    }
    $max20 = $max + 1;
    $cur_page = $page;
    $no_of_paginations = ceil($count / $max20);
    $page -= 1;
    $per_page = 15;
    $previous_btn = true;
    $next_btn = true;
    $first_btn = true;
    $last_btn = true;
    $start = $page * $per_page;

    if ($cur_page >= 7) {
        $start_loop = $cur_page - 3;
        if ($no_of_paginations > $cur_page + 3)
            $end_loop = $cur_page + 3;
        else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
            $start_loop = $no_of_paginations - 6;
            $end_loop = $no_of_paginations;
        } else {
            $end_loop = $no_of_paginations;
        }
    } else {
        $start_loop = 1;
        if ($no_of_paginations > 7)
            $end_loop = 7;
        else
            $end_loop = $no_of_paginations;
    }
    $msg = "<div class='pagination'><ul>";
    if ($previous_btn && $cur_page > 1) {
        $pre = $cur_page - 1;
        $msg .= "<a href='membre.php?aff=$aff&p=$pre'>Précedent</a>";
    } else if ($previous_btn) {
        $msg .= "<li class='inactive'>Précédent</li>";
    }
    for ($i = $start_loop; $i <= $end_loop; $i++) {
        if ($cur_page == $i)
            $msg .= "<a style='color:black;background-color:#bababa; margin:0px 2px;' href='membre.php?aff=$aff&p=$i'>$i</a>";
        else
            $msg .= "<a style='margin:0px 2px;' href='membre.php?aff=$aff&p=$i'>$i</a>";
    }
    if ($next_btn && $cur_page < $no_of_paginations) {
        $nex = $cur_page + 1;
        $msg .= "<a href='membre.php?aff=$aff&p=$nex'>Suivant</a>";
    } else if ($next_btn) {
        $msg .= "<li class='inactive'>Suivant</li>";
    }
    if ($cur_page > $no_of_paginations) {
        $cur_page = $no_of_paginations;
    }
    $total_string = "<span class='total' a='$no_of_paginations'>Page <b>" . $cur_page . "</b> sur <b>$no_of_paginations</b></span>";
    $msg = $msg . "</ul>" . $total_string . "<span style='text-align:right;' class='loading total'></span></div>";
    echo $msg;
}

function r_mkdir($path, $recursive = true)
{
    if (empty($path))
        return false;
    if ($recursive) {
        $toDo = substr($path, 0, strrpos($path, '/'));
        if ($toDo !== '.' && $toDo !== '..')
            r_mkdir($toDo);
    }

    if (!is_dir($path))
        mkdir($path);
    return true;
}

function secure($var)
{
    $foo = htmlspecialchars($var, ENT_QUOTES);
    return $foo;
}

function verifmail($adresse)
{
    $Syntaxe = "#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#";
    if (preg_match($Syntaxe, $adresse)) {
        return true;
    } else {
        return false;
    }
}

if (iscurlinstalled()) {
    if (file_exists(__DIR__ . "/config/CMS_INFO")) {

        //Installation de CraftMyCMS
        if (strstr($repertoire, '/install')) {
            if (!file_exists('LOCK')) {

                include('ip.php');
                $adresseIp = array($_SERVER['REMOTE_ADDR'], '127.0.0.1');
                if (in_array($ip_install, $adresseIp) or $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
                    $getActivation = 1;

                    if ($getActivation == 1) {
                        if (file_exists('temp/bdd.php')) {
                            include('temp/bdd.php');
                            try {
                                @$connexion = new PDO('mysql:host=' . $serveur . ';dbname=' . $base . ';charset=utf8', $user, $mdp, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
                                $req_selectEtape = $connexion->query('SHOW TABLES FROM `' . $base . '` LIKE "' . $prefixe . 'etape_temp"') or die(print_r($connexion->errorInfo(), true));
                                $nbr_selectEtape = $req_selectEtape->rowCount();

                                if ($nbr_selectEtape == 1) {
                                    $req_selectEtape = $connexion->query('SELECT * FROM ' . $prefixe . 'etape_temp');
                                    $nbr_selectEtape = $req_selectEtape->rowCount();

                                    if ($nbr_selectEtape > 0) {
                                        while ($selectEtape = $req_selectEtape->fetch()) {
                                            for ($i = 1; $i < 8; $i++) {
                                                if ($selectEtape['etape' . $i] == 0) {
                                                    $etape = $i;
                                                    break;
                                                }
                                            }
                                        }
                                    } else {
                                        $etape = 2;
                                    }

                                } else {
                                    $etape = 2;
                                }
                            } catch (PDOException $e) {
                                $etape = 2;
                            }
                        } else {
                            $etape = 2;
                        }
                    } else {
                        $etape = 1;
                    }

                    //Passer une étape ou pas
                    $listeEtape = array();
                    $listeEtape[1] = array(false, false);
                    $listeEtape[2] = array(false, false);
                    $listeEtape[3] = array(true, false);
                    $listeEtape[4] = array(true, true);
                    $listeEtape[5] = array(true, true);
                    $listeEtape[6] = array(true, true);
                    $listeEtape[7] = array(true, true);

                    if ($listeEtape[$etape][0] == true) {
                        $etapePrevious = true;

                        if (!empty($_GET['force']) && $_GET['force'] == 'previous') {
                            $etape = $etape - 1;

                            $connexion->query('UPDATE ' . $prefixe . 'etape_temp SET etape' . $etape . '="0"');

                            //Suppression des données de la SQL
                            if ($etape == 2) {
                                unlink('temp/bdd.php');
                            }

                            if ($etape == 4) {
                                unlink('temp/jsonapi.php');
                            }

                            if ($etape == 5) {
                                unlink('temp/site.php');
                            }

                            if ($etape == 6) {
                                unlink('temp/pages.php');
                            }

                            header('location: index.php');
                        }
                    } else {
                        $etapePrevious = false;
                    }

                    if ($listeEtape[$etape][1] == true) {
                        $etapeNext = true;

                        if (!empty($_GET['force']) && $_GET['force'] == 'next') {
                            $connexion->query('UPDATE ' . $prefixe . 'etape_temp SET etape' . $etape . '="1"');

                            //Création du fichier temporaire des informations du site
                            if ($etape == 5) {
                                $content = '<?php' . "\n" . '/* Ce fichier ne doit pas être édité manuellement. */' . "\n" . "\n";
                                $content .= '$titresite = null;' . "\n";
                                $content .= '$slogan = null;' . "\n";
                                $content .= '$description = null;' . "\n";
                                $content .= '$keywords = null;' . "\n";
                                $content .= '$facebook = null;' . "\n";
                                $content .= '$twitter = null;' . "\n";
                                $content .= '$youtube = null;' . "\n";
                                $content .= '$background = null;' . "\n";
                                $content .= '$logo = null;' . "\n";
                                $content .= '$favicon = null;' . "\n";
                                $content .= '?>';

                                $configFile = fopen('temp/site.php', "w+");
                                fwrite($configFile, $content);
                                fclose($configFile);
                            }

                            //Création du fichier temporaire des informations de la page
                            if ($etape == 6) {
                                $content = '<?php' . "\n" . '/* Ce fichier ne doit pas être édité manuellement. */' . "\n" . "\n";
                                $content .= '$monnaie_site = null;' . "\n";
                                $content .= '$monnaie_serveur = null;' . "\n";
                                $content .= '$valeur = null;' . "\n";
                                $content .= '$idp = null;' . "\n";
                                $content .= '$idd = null;' . "\n";
                                $content .= '$connect_serveur = null;' . "\n";
                                $content .= '$banlist = null;' . "\n";
                                $content .= '$reglement = null;' . "\n";
                                $content .= '$captcha = null;' . "\n";
                                $content .= '$activeStarpass = false;' . "\n";
                                $content .= '?>';

                                $configFile = fopen('temp/page.php', "w+");
                                fwrite($configFile, $content);
                                fclose($configFile);
                            }

                            header('location: index.php');
                        }
                    } else {
                        $etapeNext = false;
                    }

                    //Liste des étapes
                    $listeEtapeArray = array(
                        '1' => 'Activation du CMS',
                        '2' => 'Base de donnée',
                        '3' => 'Administration',
                        '4' => 'Liaison site/serveur',
                        '5' => 'Configuration du site',
                        '6' => 'Configuration des pages',
                        '7' => 'Finalisation de l\'installation'
                    );

                    if (!is_dir('temp/')) {
                        mkdir('temp/');
                    }

                    //Inclure le fichier de l'étape
                    include('etapes/' . $etape . '.php');
                } else {
                    $erreurActivation = 10;
                }
            } else {
                $erreurActivation = 9;
            }

        } else {

            // On supprime la validation sur CraftMyCMS
            $activationCMS = true;

            if (!empty($activationCMS) && $activationCMS == true) {
                function connect()
                {
                    global $connexion;
                    global $prefixe;

                    if (!empty($_SESSION['session'])) {
                        $req_selectUser = $connexion->prepare('SELECT * FROM ' . $prefixe . 'membres WHERE session=:session');
                        $req_selectUser->execute(array('session' => $_SESSION['session']));
                        $nbr_selectUser = $req_selectUser->rowCount();

                        if ($nbr_selectUser == 1) {
                            return true;
                        } else {
                            session_unset();
                            session_destroy();
                        }
                    } else {
                        return false;
                    }
                }

                $true = "<img src=\"/images/true.png\" alt=\"true\">";
                $false = "<img src=\"/images/false.png\" alt=\"false\">";
                if (file_exists(__DIR__ . "/config/config.inc.php")) {
                    include(__DIR__ . "/config/config.inc.php");
                    include(__DIR__ . "/class/jsonapi.class.php");

                    $mail_admin = $email_contact;
                    $rang = false;

                    try {
                        @$connexion = new PDO('mysql:host=' . $serveur . ';dbname=' . $base . ';charset=utf8', $user, $mdp, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
                        if (!empty($_SESSION['session'])) {
                            if (connect() == true) {
                                $req_selectUser = $connexion->prepare('SELECT * FROM ' . $prefixe . 'membres WHERE session=:session LIMIT 1');
                                $req_selectUser->execute(array('session' => $_SESSION['session']));
                                $selectUser = $req_selectUser->fetch();

                                $email = $selectUser['email'];
                                $pseudo = $selectUser['pseudo'];
                                $id = $selectUser['id'];
                                $rang = $selectUser['rang'];
                                $prenom = $selectUser['prenom'];
                                $nom = $selectUser['nom'];
                                $money_nbr = (int)$selectUser['token'];

                                DEFINE('USER_ID', $selectUser['id']);
                                DEFINE('USER_PSEUDO', $selectUser['pseudo']);
                                DEFINE('USER_RANG', $selectUser['rang']);
                            }
                        }
                    } catch (PDOException $e) {
                        $erreurActivation = "5";
                    }

                    if ($maintenance != false && $repertoire != "/admin" && strstr($_SERVER['REQUEST_URI'], 'login.php') != true && $rang != "3") {
                        $erreurActivation = "7";
                    }

                    //Gestion des permissions
                    $i = 0;
                    $nbrPermissions = 9;

                    if ($rang == 3) {
                        for ($i = 0; $i < $nbrPermissions + 1; $i++) {
                            $permissions[$i] = true;
                        }

                        $pageModo = array('index.php', 'news.php', 'boutique.php');
                    } elseif ($rang == 2) {
                        for ($i = 0; $i < $nbrPermissions + 1; $i++) {
                            $permissions[$i] = $permissionsModo[$i];
                        }

                        //Gestions des modérateurs
                        $pageModo = array('index.php');

                        //Autorisation d'accéder aux news
                        if ($permissionsModo[1] || $permissionsModo[2] || $permissionsModo[3]) {
                            array_push($pageModo, 'news.php');
                        }

                        //Autorisation d'accéder aux articles
                        if ($permissionsModo[6] || $permissionsModo[7] || $permissionsModo[8]) {
                            array_push($pageModo, 'boutique.php');
                        }

                        //Autorisation d'accéder à la MàJ
                        if ($permissionsModo[9]) {
                            array_push($pageModo, 'update.php');
                        }

                        foreach ($pageModo as $item) {
                            if (strstr($_SERVER['PHP_SELF'], $item)) {
                                $accesModo = true;
                            }
                        }
                    } else {
                        for ($i = 0; $i < $nbrPermissions + 1; $i++) {
                            $permissions[$i] = false;
                        }
                    }

                    //Si on accéde à l'espace d'administration
                    if (strstr($repertoire, '/admin')) {
                        if (!strstr($_SERVER['REQUEST_URI'], '.php')) {
                            header('location: index.php');
                            exit;
                        }

                        if (!empty($rang) and ($rang == "3" or $rang == "2")) {
                            if ($rang == "2" && (empty($accesModo) or $accesModo != true)) {
                                $erreurActivation = "11";
                            }
                        } else {
                            $erreurActivation = "11";
                        }
                    }

                    if (empty($erreurActivation)) {

                        //Affichage de la liste des addons.
                        function getAddons($type, $ordre)
                        {
                            global $maintenanceCMC;
                            global $site;

                            //Initiation de l'array
                            $addonsArray = array();

                            //Récupération des addons
                            $getAddons = sendToCraftMyCMS('getAddons', array('type' => $type, 'ordre' => $ordre));

                            //Traitement des addons
                            if (!strstr($getAddons, 'error')) {
                                $getAddons = explode('@', $getAddons);

                                foreach ($getAddons as $addons) {
                                    array_push($addonsArray, $addons);
                                }

                            } else {
                                array_push($addonsArray, $getAddons);
                            }
                            return $addonsArray;
                        }


                        //Ajout d'un addon à votre CMS.
                        function addAddon($id)
                        {
                            $dlAddon = sendToCraftMyCMS('addAddon', array('id' => $id));
                            if (!strstr($dlAddon, 'error') && $dlAddon != 'false') {
                                return true;
                            }
                        }


                        //Achat d'un plugin
                        function buyAddon($addon)
                        {
                            $buyAddon = sendToCraftMyCMS('buyAddon', array('id_addon' => $addon));
                            return $buyAddon;
                        }


                        //Téléchargement d'un addon.
                        function downloadAddon($type, $id_addon)
                        {
                            $downloadAddon = sendToCraftMyCMS('downloadAddon', array('type' => $type, 'id_addon' => $id_addon));

                            if (!strstr($downloadAddon, 'error') && $downloadAddon != 'false') {

                                $downloadAddon = explode('#', $downloadAddon);

                                if ($type == 'plugin') {
                                    $dirAddon = '../include/plugins/';
                                }

                                if ($type == 'theme') {
                                    $dirAddon = '../style/';
                                }

                                $addonDownload = 'http://system.craftmycms.fr/new/getAddons/' . $downloadAddon[0] . '.zip';

                                if (file_get_contents($addonDownload)) {

                                    $addonDownload = file_get_contents($addonDownload);

                                    if (!is_dir('../telechargement/')) {
                                        mkdir('../telechargement/');
                                    }

                                    $dlHandler = fopen('../telechargement/' . $downloadAddon[0] . '.zip', 'w+');
                                    fwrite($dlHandler, $addonDownload);
                                    fclose($dlHandler);

                                    $zipHandle = zip_open('../telechargement/' . $downloadAddon[0] . '.zip');
                                    $rand = md5(rand());
                                    while ($aF = zip_read($zipHandle)) {
                                        $thisFileName = zip_entry_name($aF);
                                        $thisFileDir = dirname($thisFileName);
                                        if (substr($thisFileName, -1, 1) == "/") continue;
                                        if (!is_dir($thisFileDir)) {
                                            r_mkdir($dirAddon . $thisFileDir);
                                        }
                                        if (!is_dir($thisFileName)) {
                                            $contents = zip_entry_read($aF, zip_entry_filesize($aF));
                                            $contents = str_replace("\\r\\n", "\\n", $contents);
                                            $updateThis = "";
                                            if (file_exists($thisFileName)) {
                                                unlink($thisFileName);
                                            }
                                            $updateThis = fopen($dirAddon . $thisFileName, "w+");
                                            fwrite($updateThis, $contents);
                                            fclose($updateThis);
                                        }
                                    }
                                    zip_close($zipHandle);
                                    unlink('../telechargement/' . $downloadAddon[0] . '.zip');

                                    if ($type == 'plugin') {
                                        $PLUGINS = fopen('../include/config/PLUGINS', 'w+');
                                        fwrite($PLUGINS, $downloadAddon[2]);
                                        fclose($PLUGINS);

                                        $INFOS = fopen('../include/plugins/' . $downloadAddon[1] . '/INFOS', 'w+');
                                        fwrite($INFOS, $downloadAddon[3]);
                                        fclose($INFOS);
                                    } else {
                                        modifConfig('theme', $downloadAddon[1]);
                                    }

                                    return true;
                                }
                            }
                        }

                        function listePlugins($param, $param2)
                        {
                            global $connexion;
                            global $prefixe;
                            global $activePlugins;

                            //Si l'utilisation des plugins est activée
                            if ($activePlugins) {
                                $dirPlugin = opendir(__DIR__ . '/plugins/');
                                while ($filePlugin = readdir($dirPlugin)) {

                                    if ($filePlugin != "." && $filePlugin != "..") {
                                        if (is_dir(__DIR__ . '/plugins/' . $filePlugin)) {
                                            $dirPluginNew = opendir(__DIR__ . '/plugins/' . $filePlugin . '/file/');
                                            while ($filePluginNew = readdir($dirPluginNew)) {
                                                if ($filePluginNew != "." && $filePluginNew != "..") {
                                                    if ($filePluginNew == str_replace('/', null, $_SERVER['PHP_SELF']) or $param != null) {
                                                        $isInstall = __DIR__ . '/plugins/' . $filePlugin . '/INSTALLED';
                                                        if (file_exists($isInstall)) {
                                                            if (is_validate($filePlugin, $filePluginNew) == true) {
                                                                if ($param == "modules") {
                                                                    if (@$module_content != file_get_contents(__DIR__ . '/plugins/' . $filePlugin . '/config/module.php')) {
                                                                        include(__DIR__ . '/plugins/' . $filePlugin . '/config/config.php');

                                                                        if ($module[0] != false) {


                                                                            //$module_content = file_get_contents(__DIR__.'/plugins/'.$filePlugin.'/config/module.php');
                                                                            if (($param2 == "first" && $module[1] == true) || ($param2 == null && $module[1] == false)) {
                                                                                //echo $module_content;
                                                                                include(__DIR__ . '/plugins/' . $filePlugin . '/config/module.php');
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                closedir($dirPlugin);
                            }
                        }


                        //Validation d'un plugin.
                        function is_validate($filePlugin, $filePluginNew)
                        {
                            return true;

                            if (file_exists(__DIR__ . '/config/PLUGINS') and file_exists(__DIR__ . '/plugins/' . $filePlugin . '/INFOS')) {
                                $PLUGINS = file_get_contents(__DIR__ . '/config/PLUGINS');
                                $INSTALLED = file_get_contents(__DIR__ . '/plugins/' . $filePlugin . '/INFOS');
                                $plugins = explode('#', base64_decode($PLUGINS));
                                if (!empty($plugins[1]) and !empty($plugins[0])) {
                                    $plugins = explode('%', $plugins[1]);
                                    $installed = base64_decode($INSTALLED);
                                    $installed = explode('#', $installed);
                                    $installed = $installed[0];
                                    if (in_array($installed, $plugins)) {
                                        $installed = explode('@', $installed);
                                        $dirPlugin = $installed['1'];

                                        if ($filePlugin == $dirPlugin) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    } else {
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
                            }
                        }

                        if (empty($erreur)) {

                            //Si l'utilisation des plugins est activée
                            if ($activePlugins == true) {
                                if (!strstr($_SERVER['REQUEST_URI'], 'admin')) {
                                    $dirPlugin = opendir(__DIR__ . '/plugins/');
                                    while ($filePlugin = readdir($dirPlugin)) {

                                        if ($filePlugin != "." && $filePlugin != "..") {
                                            if (is_dir(__DIR__ . '/plugins/' . $filePlugin)) {
                                                $dirPluginNew = opendir(__DIR__ . '/plugins/' . $filePlugin . '/file/');
                                                while ($filePluginNew = readdir($dirPluginNew)) {
                                                    if ($filePluginNew != "." && $filePluginNew != "..") {
                                                        if (strstr($_SERVER['SCRIPT_NAME'], $filePluginNew)) {
                                                            if (file_exists(__DIR__ . '/plugins/' . $filePlugin . '/INSTALLED')) {
                                                                if (is_validate($filePlugin, $filePluginNew) == true) {
                                                                    include(__DIR__ . '/plugins/' . $filePlugin . '/config/config.php');
                                                                    include(__DIR__ . '/plugins/' . $filePlugin . '/file/' . $filePluginNew);
                                                                    exit;
                                                                } else {
                                                                    $titre_page = "Erreur plugin";
                                                                    include(__DIR__ . '/header.php');
                                                                    echo '<div id="content">';
                                                                    msg("Un problème est survenu lors d'affichage de ce plugin.<br>Veuillez consulter l'administrateur de ce site.", 'r');
                                                                    echo '</div>';
                                                                    include(__DIR__ . '/footer.php');
                                                                    exit;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    closedir($dirPlugin);
                                }
                            }
                        }

                    }
                } else {
                    $erreurActivation = "4";
                }
            } else {
                $erreurActivation = "3";
            }
        }
    } else {
        $erreurActivation = "2";
    }
} else {
    $erreurActivation = "1";
}

if (!empty($erreurActivation)) {
    if (strstr($_SERVER['REQUEST_URI'], '/admin/')) {
        header('location: ../');
        exit;
    }

    if (empty($titresite) and empty($titre)) {
        $titre = "CraftMyCMS";
    } else {
        $titre = $titresite;
    }

    switch ($erreurActivation) {
        case '1':
            $erreur_text = "L'extension cURL n'est pas installé sur ce site.";
            break;
        case '2':
            $erreur_text = "Le fichier d'activation du CMS est inexistant.";
            break;
        case '3':
            $erreur_text = "Votre CMS n'est pas encore activé.";
            break;
        case '4':
            $erreur_text = "Vous n'avez pas encore configuré votre CMS.";
            break;
        case '5':
            $erreur_text = "Impossible de se connecter à la base de donnée.";
            break;
        case '6':
            $erreur_text = "Impossible de se connecter au service d'activation des CMS de CraftMyCMS.fr.<br>Vérifiez que votre connexion internet soit bien établie.";
            break;
        case '7':
            $erreur_text = $maintenance;
            break;
        case '8':
            $erreur_text = "Vous devez activer les cookies pour acceder à ce site.";
            break;
        case '9':
            $erreur_text = "Veuillez supprimer le fichier \"install/LOCK\" pour installer CraftMyCMS.";
            break;
        case '10':
            $erreur_text = "Pour pouvoir installer ce CMS, vous devez ajouter votre adresse IP dans le fichier \"install/ip.php\" afin d'être le seul à installer votre CMS.";
            break;
        case '11':
            $erreur_text = "Vous devez être connecté en tant qu'administrateur pour accéder à cette page.<br><a href='../'>Cliquez ici pour retourner à l'accueil du site</a>.";
            break;
    }

    if (file_exists('./images/etablie.png')) {
        $dir = '.';
    } else {
        $dir = '..';
    }
    ?>
    <!doctype html>
    <html lang="fr">
<head>
    <title><?php echo $titre; ?></title>
    <meta name="author" content="CraftMyCMS, Kévin Guiot">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?php echo $dir; ?>/style/<?php echo $theme; ?>/erreur.css" media="screen">
    <link type="image/png" rel="shortcut icon" href="./images/favicon.ico">
</head>
<body>
<div id="erreur">
    <?php
    if ($erreurActivation == "1" or $erreurActivation == "2" or $erreurActivation == "3" or $erreurActivation == "4" or $erreurActivation == "6") { ?>
        <h1 style="margin:10px 0 0px;"><img src="<?php echo $dir; ?>/images/etablie.png"
                                            style="width:32px; vertical-align: middle; padding-bottom:9px; padding-right:15px;">CraftMyCMS<img
                    src="<?php echo $dir; ?>/images/etablie.png"
                    style="width:32px; vertical-align: middle; padding-bottom:9px; padding-left:15px;"></h1>
        <h4 style="margin:0px"><?php echo $erreur_text; ?></h4>
        <hr style="margin-bottom:20px; margin-top:10px;">
        <a class="bouton" href="install/" class="readmore">Panel d'installation</a>
        <a class="bouton" target="_blank" href="http://www.craftmycms.fr/?ref=cms">Découvrez CraftMyCMS</a>
        <a class="bouton" target="_blank" href="http://www.craftmycms.fr/achat.php?ref=cms">Acheter</a>
        <hr style="margin-top:20px; margin-bottom: 10px;">
        <strong>Site internet Minecraft proposé par <a target="_blank" href="http://www.craftmycms.fr/?ref=cms">CraftMyCMS</a></strong>
    <?php } else { ?><h4 style="margin:0px"><?php echo $erreur_text; ?></h4><?php } ?>
</div>
</body>
    </html><?php
    exit;
}
?>