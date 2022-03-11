<?php 

/**
 * Contrôleur de la page d'accueil
 */
function genHome()
{
    // Sélection des articles
    $articleModel = new ArticleModel();
    $articles = $articleModel->getAllArticles(5);

    // On récupère le message flash le cas échéant
    $flashMessage = addFlashMessage();

    // Affichage : inclusion du fichier de template
    $template = 'home';
    include TEMPLATE_DIR . '/base.phtml'; 
}

/**
 * Contrôleur de la page Article
 */
function genArticle()
{
    // Valider le paramètre idArticle
    if (!array_key_exists('idArticle', $_GET) || !$_GET['idArticle'] || !ctype_digit($_GET['idArticle'])) {
        echo '<p>ERREUR : Id Article manquant ou incorrect</p>';
        exit;
    }

    // Récupérer le paramètre idArticle
    $idArticle = (int) $_GET['idArticle']; 

    // Sélection de l'article
    $articleModel = new ArticleModel();
    $article = $articleModel->getOneArticle($idArticle);

    // Test pour savoir si l'article existe
    if (!$article) {
        echo 'ERREUR : aucun article ne possède l\'ID ' . $idArticle;
        exit;
    }

    // Création d'un objet CommentModel
    $commentModel = new CommentModel();

    // Traitement des données du formulaire d'ajout de commentaires
    if (!empty($_POST)) {

        // Récupération des données 
        $content = trim($_POST['content']);
        $rate = (int) $_POST['rate'];

        // Validation
        $errors = [];

        // Si le champ "content" est vide => message d'erreur
        if (!$content) {
            $errors['content'] = 'Le champ "Commentaire" est obligatoire';
        }

        // Si pas d'erreurs
        if (empty($errors)) {

            // On récupère l'id de l'utilsiateur connecté
            $userId = getUserId();

            // @TODO vérifier qu'on récupère bien un userId !

            // Insertion du commentaire en base de données
            $commentModel->insertComment($content, $idArticle, $rate, $userId);

            // Redirection
            header('Location: index.php?action=article&idArticle=' . $idArticle);
            exit;
        }

    }

    // Sélection des commentaires associésà l'article
    $comments = $commentModel->getCommentsByArticleId($idArticle);

    // Affichage : inclusion du fichier de template
    $template = 'article';
    include TEMPLATE_DIR . '/base.phtml';
}

/
 * Contrôleur de la déconnexion
 */
function genLogout()
{
    // On se déconnecte
    logout();

    // Message flash
    addFlashMessage('Vous êtes bien déconnecté');

    // Redirection vers l'accueil
    header('Location: index.php');
    exit;
}