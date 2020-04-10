<?php
/* ================
 *    INITIALISATION
 * ================ */
 
// Démarrage d'une session qui va stocker la valeur cryptée du code à recopier.
// session_start() se place toujours avant toute sortie vers la page web
session_start();
 
// Chemin absolu vers le dossier
if ( !defined('ABSPATH') ) define('ABSPATH', dirname(__FILE__) . '/');
 
/* =======================
 *    FONCTIONS UTILITAIRES
 * ======================= */
 
/**
 * Fonction qui génère une chaîne de caractères aléatoires.
 * - strlen() retourne la taille de la chaine en paramètre
 * - mt_rand(a, b) génère un nombre aléatoire entre a et b compris : cette fonction est plus rapide que rand() de la bibliothèque standard
 * - $chars{0} retourne le premier caractère de la chaîne $chars, $chars{1} le deuxième ...
 *
 * @param $length La taille souhaitée pour le code
 * @return Le code à recopier par l'utilisateur
 */
function getCode($length) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Certains caractères ont été enlevés car ils prêtent à confusion
    $code = '';
    // On prend $length fois un caractère de $chars au hasard
    for ($i=0; $i<$length; $i++) {
        $code .= $chars{ mt_rand( 0, strlen($chars) - 1 ) };
    }
    return $code; // Le code, par exemple : R5AQJ
}
 
/**
 * Fonction qui retourne une valeur aléatoire du tableau reçu en paramètre.
 * On l'utilise pour générer aléatoirement la couleur et la police de caratères
 *
 * @param Le tableau dont on extrait une valeur au hasard
 */
function random($tab) {
    return $tab[array_rand($tab)];
}
 
/* ==========================
 *    STOCKAGE DU HASH DU CODE
 * ========================== */
 
// Stockage de la chaîne aléatoire de 5 caractères obtenue
$code = getCode(5);
 
// Hashage de la chaine avec md5() et stockage dans la variable de session $_SESSION['captcha'].
$_SESSION['captcha'] = md5($code);
 
/* =======================
 *    TRAITEMENT DE L'IMAGE
 * ======================= */
 
// Afin de traiter les caractères séparément, on les stocke un par un dans des variables.
$char1 = substr($code,0,1);
$char2 = substr($code,1,1);
$char3 = substr($code,2,1);
$char4 = substr($code,3,1);
$char5 = substr($code,4,1);
 
// glob() retourne un tableau répertoriant les fichiers du dossier 'fonts', ayant l'extension .ttf ( pas .TTF ! ).
// Vous pouvez donc ajouter autant de polices TrueType que vous désirez, en veillant à les renommer.
$fonts = glob('../../style/default/police/blue.ttf');

// imagecreatefrompng() crée une image dynamique à partir d'un fichier PNG statique.
// Cela permet d'écrire sur l'image via PHP
$image = imagecreatefrompng('../../images/captcha.png');
 
// imagecolorallocate() retourne un identifiant de couleur.
// On définit les couleurs RVB qu'on va utiliser pour nos polices et on les stocke dans le tableau $colors[].
// Vous pouvez ajouter autant de couleurs que vous voulez.
$colors = array ( imagecolorallocate($image, 131, 154, 255),
                  imagecolorallocate($image,  89, 186, 255),
                  imagecolorallocate($image, 155, 190, 214),
                  imagecolorallocate($image, 255, 128, 234),
                  imagecolorallocate($image, 255, 123, 123) );
 
// imagettftext(image, taille police, inclinaison, coordonnée X, coordonnée Y, couleur, police, texte) écrit le texte sur l'image.
// Mise en forme de chacun des caractères et placement sur l'image.
imagettftext($image, 28, -10, 0, 37, random($colors), ABSPATH .'/'. random($fonts), $char1);
imagettftext($image, 28, 20, 37, 37, random($colors), ABSPATH .'/'. random($fonts), $char2);
imagettftext($image, 28, -35, 55, 37, random($colors), ABSPATH .'/'. random($fonts), $char3);
imagettftext($image, 28, 25, 100, 37, random($colors), ABSPATH .'/'. random($fonts), $char4);
imagettftext($image, 28, -15, 120, 37, random($colors), ABSPATH .'/'. random($fonts), $char5);
 
/* =========================
 *    FIN -> ENVOI DE L'IMAGE
 * ========================= */
 
// C'est le fichier dynamique captcha.php et non captcha.png qui est appelé pour afficher l'image,
// on envoie donc un en-tête HTTP au navigateur via header() pour qu'il considère
// que captcha.php est une image de type PNG.
header('Content-Type: image/png');
 
// .. et on envoie notre image PNG au navigateur.
imagepng($image);
 
// L'image ayant été envoyée, on libère toute la mémoire qui lui est associée via imagedestroy().
imagedestroy($image);
?>