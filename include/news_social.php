<?php
//****************************************************
// Auteur: CraftMyCMS (Kévin GUIOT)
// Système de "J'aime" et de commentaires sur les news.
// Indépendant du thème : rendu HTML neutre + style/news_social.css
//****************************************************

if(!defined('AUTH_ID') OR AUTH_ID != true) {
	exit;
}

DEFINE('NEWS_COMMENT_MAX', 1000);
DEFINE('NEWS_COMMENT_DELAI', 30);

function newsSocialInstall()
{
	global $connexion, $prefixe;

	$connexion->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."news_likes` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`id_news` int(11) NOT NULL,
		`id_membre` int(11) NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `news_membre` (`id_news`, `id_membre`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

	$connexion->exec("CREATE TABLE IF NOT EXISTS `".$prefixe."news_commentaires` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`id_news` int(11) NOT NULL,
		`id_membre` int(11) NOT NULL,
		`content` text NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY (`id`),
		KEY `id_news` (`id_news`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

function newsSocialMembreId()
{
	return (connect() && defined('USER_ID')) ? (int) USER_ID : 0;
}

function newsSocialPeutModerer()
{
	global $rang, $permissions;
	return $rang == 3 || ($rang == 2 && !empty($permissions[3]));
}

function newsSocialExiste($idNews)
{
	global $connexion, $prefixe;
	$req = $connexion->prepare('SELECT id FROM '.$prefixe.'news WHERE id=:id');
	$req->execute(array('id' => $idNews));
	return $req->rowCount() == 1;
}

function newsSocialNbrLikes($idNews)
{
	global $connexion, $prefixe;
	$req = $connexion->prepare('SELECT COUNT(*) FROM '.$prefixe.'news_likes WHERE id_news=:id');
	$req->execute(array('id' => $idNews));
	return (int) $req->fetchColumn();
}

function newsSocialNbrCommentaires($idNews)
{
	global $connexion, $prefixe;
	$req = $connexion->prepare('SELECT COUNT(*) FROM '.$prefixe.'news_commentaires WHERE id_news=:id');
	$req->execute(array('id' => $idNews));
	return (int) $req->fetchColumn();
}

function newsSocialALike($idNews, $idMembre)
{
	global $connexion, $prefixe;
	if($idMembre <= 0) {
		return false;
	}
	$req = $connexion->prepare('SELECT id FROM '.$prefixe.'news_likes WHERE id_news=:news AND id_membre=:membre');
	$req->execute(array('news' => $idNews, 'membre' => $idMembre));
	return $req->rowCount() > 0;
}

function newsSocialToggleLike($idNews, $idMembre)
{
	global $connexion, $prefixe;
	if(newsSocialALike($idNews, $idMembre)) {
		$req = $connexion->prepare('DELETE FROM '.$prefixe.'news_likes WHERE id_news=:news AND id_membre=:membre');
		$req->execute(array('news' => $idNews, 'membre' => $idMembre));
		return false;
	}
	$req = $connexion->prepare('INSERT IGNORE INTO '.$prefixe.'news_likes (id_news, id_membre, date) VALUES (:news, :membre, NOW())');
	$req->execute(array('news' => $idNews, 'membre' => $idMembre));
	return true;
}

function newsSocialAjouterCommentaire($idNews, $idMembre, $content)
{
	global $connexion, $prefixe;

	$content = trim(str_replace("\r\n", "\n", $content));
	$longueur = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);
	if($longueur == 0) {
		return 'commentaire:vide';
	}
	if($longueur > NEWS_COMMENT_MAX) {
		return 'commentaire:long';
	}

	$req = $connexion->prepare('SELECT COUNT(*) FROM '.$prefixe.'news_commentaires WHERE id_membre=:membre AND date > DATE_SUB(NOW(), INTERVAL '.NEWS_COMMENT_DELAI.' SECOND)');
	$req->execute(array('membre' => $idMembre));
	if($req->fetchColumn() > 0) {
		return 'commentaire:flood';
	}

	$req = $connexion->prepare('INSERT INTO '.$prefixe.'news_commentaires (id_news, id_membre, content, date) VALUES (:news, :membre, :content, NOW())');
	$req->execute(array('news' => $idNews, 'membre' => $idMembre, 'content' => $content));
	return 'commentaire:true';
}

function newsSocialSupprimerCommentaire($idCommentaire, $idMembre)
{
	global $connexion, $prefixe;
	$req = $connexion->prepare('SELECT id_membre FROM '.$prefixe.'news_commentaires WHERE id=:id');
	$req->execute(array('id' => $idCommentaire));
	$commentaire = $req->fetch();

	if(!$commentaire || ((int) $commentaire['id_membre'] !== $idMembre && !newsSocialPeutModerer())) {
		return 'supprcommentaire:false';
	}

	$req = $connexion->prepare('DELETE FROM '.$prefixe.'news_commentaires WHERE id=:id');
	$req->execute(array('id' => $idCommentaire));
	return 'supprcommentaire:true';
}

function newsSocialSupprimerNews($idNews)
{
	global $connexion, $prefixe;
	foreach(array('news_likes', 'news_commentaires') as $table) {
		$req = $connexion->prepare('DELETE FROM '.$prefixe.$table.' WHERE id_news=:id');
		$req->execute(array('id' => $idNews));
	}
}

function newsSocialTraiter()
{
	if(empty($_POST['news_social']) || empty($_POST['id_news'])) {
		return;
	}

	$idNews = (int) $_POST['id_news'];
	$idMembre = newsSocialMembreId();
	$ajax = !empty($_POST['ajax']);
	$retour = 'index.php?id='.$idNews;

	if($idMembre <= 0) {
		$msg = 'social:connexion';
	} elseif(empty($_POST['token']) || empty($_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
		$msg = 'social:token';
	} elseif(!newsSocialExiste($idNews)) {
		$msg = 'social:news';
	} else {
		switch($_POST['news_social']) {
			case 'like':
				$like = newsSocialToggleLike($idNews, $idMembre);
				if($ajax) {
					header('Content-Type: application/json; charset=utf-8');
					echo json_encode(array('like' => $like, 'nbr' => newsSocialNbrLikes($idNews)));
					exit;
				}
				$msg = null;
				if(!empty($_POST['retour']) && $_POST['retour'] == 'liste') {
					$retour = 'index.php'.(!empty($_POST['p']) ? '?p='.(int) $_POST['p'] : '');
				}
				$retour .= '#news-'.$idNews;
				break;
			case 'commenter':
				$msg = newsSocialAjouterCommentaire($idNews, $idMembre, isset($_POST['commentaire']) ? (string) $_POST['commentaire'] : '');
				break;
			case 'supprimer':
				$msg = newsSocialSupprimerCommentaire(isset($_POST['id_commentaire']) ? (int) $_POST['id_commentaire'] : 0, $idMembre);
				break;
			default:
				$msg = null;
		}
	}

	if($ajax) {
		header('Content-Type: application/json; charset=utf-8', true, 403);
		echo json_encode(array('erreur' => $msg));
		exit;
	}

	if(!empty($msg)) {
		$retour .= '&msg='.$msg.'#commentaires';
	}
	header('location: '.$retour);
	exit;
}

function newsSocialMessages()
{
	msg("Votre commentaire a bien été publié.", 'v', 'get', 'commentaire:true', 0);
	msg("Votre commentaire est vide.", 'r', 'get', 'commentaire:vide', 0);
	msg("Votre commentaire ne doit pas dépasser ".NEWS_COMMENT_MAX." caractères.", 'r', 'get', 'commentaire:long', 0);
	msg("Merci de patienter ".NEWS_COMMENT_DELAI." secondes entre deux commentaires.", 'r', 'get', 'commentaire:flood', 0);
	msg("Le commentaire a bien été supprimé.", 'v', 'get', 'supprcommentaire:true', 0);
	msg("Vous ne pouvez pas supprimer ce commentaire.", 'r', 'get', 'supprcommentaire:false', 0);
	msg("Vous devez être connecté pour aimer ou commenter une news.", 'r', 'get', 'social:connexion', 0);
	msg("Le jeton de sécurité est périmé, merci de réessayer.", 'r', 'get', 'social:token', 0);
}

function newsSocialIcone($nom)
{
	$chemins = array(
		'coeur' => 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z',
		'bulle' => 'M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z',
	);
	return '<svg class="cmc-social-icon" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path fill="currentColor" d="'.$chemins[$nom].'"/></svg>';
}

function newsSocialToken()
{
	return !empty($_SESSION['token']) ? secure($_SESSION['token']) : '';
}

function newsSocialBarre($idNews, $detail = false)
{
	$idMembre = newsSocialMembreId();
	$nbrLikes = newsSocialNbrLikes($idNews);
	$nbrCommentaires = newsSocialNbrCommentaires($idNews);
	$aLike = newsSocialALike($idNews, $idMembre);

	$html = '<div class="cmc-social" data-news="'.$idNews.'">';

	if($idMembre > 0) {
		$html .= '<form method="post" action="index.php" class="cmc-social-like-form">';
		$html .= '<input type="hidden" name="news_social" value="like">';
		$html .= '<input type="hidden" name="id_news" value="'.$idNews.'">';
		$html .= '<input type="hidden" name="token" value="'.newsSocialToken().'">';
		if(!$detail) {
			$html .= '<input type="hidden" name="retour" value="liste">';
			if(!empty($_GET['p'])) {
				$html .= '<input type="hidden" name="p" value="'.(int) $_GET['p'].'">';
			}
		}
		$html .= '<button type="submit" class="cmc-social-btn cmc-social-like'.($aLike ? ' cmc-social-active' : '').'" aria-pressed="'.($aLike ? 'true' : 'false').'" title="'.($aLike ? 'Je n\'aime plus' : 'J\'aime').'">';
		$html .= newsSocialIcone('coeur').' <span class="cmc-social-count">'.$nbrLikes.'</span>';
		$html .= '</button></form>';
	} else {
		$html .= '<a href="login.php" class="cmc-social-btn cmc-social-like" title="Connectez-vous pour aimer cette news">';
		$html .= newsSocialIcone('coeur').' <span class="cmc-social-count">'.$nbrLikes.'</span></a>';
	}

	$html .= '<a href="index.php?id='.$idNews.'#commentaires" class="cmc-social-btn cmc-social-comments" title="Commentaires">';
	$html .= newsSocialIcone('bulle').' '.$nbrCommentaires.' commentaire'.($nbrCommentaires > 1 ? 's' : '').'</a>';
	$html .= '</div>';

	return $html;
}

function newsSocialCommentaires($idNews)
{
	global $connexion, $prefixe;

	$idMembre = newsSocialMembreId();
	$peutModerer = newsSocialPeutModerer();

	$req = $connexion->prepare('SELECT c.id, c.id_membre, c.content, c.date, m.pseudo, m.rang FROM '.$prefixe.'news_commentaires c LEFT JOIN '.$prefixe.'membres m ON m.id = c.id_membre WHERE c.id_news=:id ORDER BY c.id ASC');
	$req->execute(array('id' => $idNews));
	$commentaires = $req->fetchAll();

	ob_start();
	?>
	<div class="cmc-comments" id="commentaires">
		<h3 class="cmc-comments-title">Commentaires (<?php echo count($commentaires); ?>)</h3>
		<?php newsSocialMessages(); ?>

		<?php if(count($commentaires) == 0) { ?>
			<p class="cmc-comments-empty">Aucun commentaire pour le moment. Soyez le premier à réagir !</p>
		<?php } ?>

		<?php foreach($commentaires as $commentaire) {
			$pseudo = !empty($commentaire['pseudo']) ? secure($commentaire['pseudo']) : 'Membre supprimé';
			$timestamp = strtotime($commentaire['date']);
			?>
			<div class="cmc-comment" id="commentaire-<?php echo (int) $commentaire['id']; ?>">
				<img class="cmc-comment-avatar" src="skin.php?pseudo=<?php echo urlencode((string) $commentaire['pseudo']); ?>" alt="" width="32" height="32" loading="lazy">
				<div class="cmc-comment-body">
					<div class="cmc-comment-meta">
						<?php if(!empty($commentaire['pseudo'])) { ?>
							<a class="cmc-comment-author<?php if($commentaire['rang'] == 3) { echo ' cmc-rang-admin'; } elseif($commentaire['rang'] == 2) { echo ' cmc-rang-modo'; } ?>" href="membre.php?pseudo=<?php echo urlencode($commentaire['pseudo']); ?>"><?php echo $pseudo; ?></a>
						<?php } else { ?>
							<span class="cmc-comment-author"><?php echo $pseudo; ?></span>
						<?php } ?>
						<span class="cmc-comment-date">le <?php echo date('d/m/Y', $timestamp); ?> à <?php echo date('H:i', $timestamp); ?></span>
						<?php if($idMembre > 0 && ((int) $commentaire['id_membre'] === $idMembre || $peutModerer)) { ?>
							<form method="post" action="index.php" class="cmc-comment-delete" onsubmit="return confirm('Supprimer ce commentaire ?');">
								<input type="hidden" name="news_social" value="supprimer">
								<input type="hidden" name="id_news" value="<?php echo $idNews; ?>">
								<input type="hidden" name="id_commentaire" value="<?php echo (int) $commentaire['id']; ?>">
								<input type="hidden" name="token" value="<?php echo newsSocialToken(); ?>">
								<button type="submit" class="cmc-social-link">Supprimer</button>
							</form>
						<?php } ?>
					</div>
					<div class="cmc-comment-content"><?php echo nl2br(secure($commentaire['content'])); ?></div>
				</div>
			</div>
		<?php } ?>

		<?php if($idMembre > 0) { ?>
			<form method="post" action="index.php" class="cmc-comment-form">
				<input type="hidden" name="news_social" value="commenter">
				<input type="hidden" name="id_news" value="<?php echo $idNews; ?>">
				<input type="hidden" name="token" value="<?php echo newsSocialToken(); ?>">
				<textarea name="commentaire" rows="3" maxlength="<?php echo NEWS_COMMENT_MAX; ?>" placeholder="Votre commentaire..." required></textarea>
				<button type="submit" class="cmc-social-btn cmc-social-submit">Commenter</button>
			</form>
		<?php } else { ?>
			<p class="cmc-comments-login"><a href="login.php">Connectez-vous</a> ou <a href="inscription.php">inscrivez-vous</a> pour commenter.</p>
		<?php } ?>
	</div>
	<?php
	return ob_get_clean();
}

function newsSocialAssets()
{
	?>
	<link rel="stylesheet" href="style/news_social.css" media="screen">
	<script>
	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form.classList || !form.classList.contains('cmc-social-like-form') || !window.fetch || !window.FormData) {
			return;
		}
		e.preventDefault();
		var data = new FormData(form);
		data.append('ajax', '1');
		fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) { throw r; } return r.json(); })
			.then(function (json) {
				var btn = form.querySelector('.cmc-social-like');
				btn.classList.toggle('cmc-social-active', json.like);
				btn.setAttribute('aria-pressed', json.like ? 'true' : 'false');
				btn.title = json.like ? "Je n'aime plus" : "J'aime";
				form.querySelector('.cmc-social-count').textContent = json.nbr;
			})
			.catch(function () { form.submit(); });
	});
	</script>
	<?php
}

newsSocialInstall();
