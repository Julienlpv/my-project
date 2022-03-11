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

/**
 * Contrôleur de la page Contact
 */
function genContact()
{
    // Affichage : inclusion du fichier de template
    $template = 'contact';
    include TEMPLATE_DIR . '/base.phtml';
}

/**
 * Contrôleur de la page Mentions légales
 */
function genMentions()
{
    // Affichage : inclusion du fichier de template
    $template = 'mentions';
    include TEMPLATE_DIR . '/base.phtml';
}

/**
 * Contrôleur de la page de création de compte
 */
function genSignup()
{
    $firstname = '';
    $lastname = '';
    $email = '';
    $password = '';
    $password_confirm = '';

    // Si le formulaire est soumis... 
    if (!empty($_POST)) {

        // On récupère les données du formulaire
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password-confirm'];

        // Validation du formulaire (à faire en dernier quand ça fonctionne sans erreur)
        $errors = validateSignupForm($firstname, $lastname, $email, $password, $password_confirm);

        // S'il n'y a pas d'erreur, si tout est OK
        if (empty($errors)) {

            // Hashage du mot de passe
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // On fait appel au modèle ( la fonction insertUser() ) pour insérer les données dans la table user
            $userModel = new UserModel();
            $userModel->insertUser($firstname, $lastname, $email, $hash);
            
            // Ajout d'un message flash en session
            addFlashMessage('Votre compte a bien été créé');

            // On redirige l'internaute pour l'instant vers la page d'accueil
            header('Location: index.php');
            exit;
            
        }
    }

    // Affichage : inclusion du fichier de template
    $template = 'signup';
    include TEMPLATE_DIR . '/base.phtml';
}

/**
 * Contrôleur de la page de connexion
 */
function genLogin()
{
    // Si le formulaire est soumis
    if (!empty($_POST)) {

        // On récupère les données du formulaire (email et mot de passe)
        $email = $_POST['email'];
        $password = $_POST['password'];

        // On vérifie les identifiants ( fonction checkCredentials() )
        $userModel = new UserModel();
        $user = $userModel->checkCredentials($email, $password);

        // Si les identifiants sont corrects
        if ($user) {

            // On enregistre les données de l'utilisateur en session ( fonction userRegister() )
            userRegister($user['idUser'], $user['firstname'], $user['lastname'], $user['email']);

            // On ajoute un message flash ( fonction addFlashMessage() )
            addFlashMessage('Bonjour ' . $user['firstname']);

            // On redirige l'internaute vers la page d'accueil
            header('Location: index.php');
            exit;
        }

        // Si les identifiants sont incorrects, on stocke un message d'erreur dans une variable
        $error = 'Identifiants incorrects';
    }

    // Affichage : inclusion du fichier de template
    $template = 'login';
    include TEMPLATE_DIR . '/base.phtml';
}

/**
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