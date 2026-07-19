<?php

session_start();
require_once 'config/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = 'Veuillez remplir tous les champs.';

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM admins
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $admin &&
            password_verify($password, $admin['mot_de_passe'])
        ) {

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nom'] = $admin['nom'];

            header('Location: dashboard.php');
            exit;

        } else {

            $error = 'Adresse e-mail ou mot de passe incorrect.';

        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Connexion | Library Manager</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body class="login-page">

    <div class="login-wrapper">

        <section class="login-visual">

            <div class="login-brand">

                <div class="login-brand-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>

                <div>
                    <h2>Library</h2>
                    <span>Manager</span>
                </div>

            </div>

            <div class="login-presentation">

                <h1>
                    Gérez votre bibliothèque simplement.
                </h1>

                <p>
                    Centralisez vos livres, vos catégories et leur disponibilité
                    depuis un espace moderne et facile à utiliser.
                </p>

            </div>

            <div class="login-decoration">
                <span></span>
                <span></span>
                <span></span>
            </div>

        </section>

        <section class="login-form-section">

            <div class="login-form-content">

                <div class="mobile-logo">

                    <div class="mobile-logo-icon">
                        <i class="fa-solid fa-book-open"></i>
                    </div>

                    <strong>Library Manager</strong>

                </div>

                <h2>Bon retour 👋</h2>

                <p class="login-subtitle">
                    Connectez-vous pour accéder à votre espace d'administration.
                </p>

                <?php if ($error !== ''): ?>

                    <div class="login-error-message">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>

                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="login-form-group">

                        <label for="email">
                            Adresse e-mail
                        </label>

                        <div class="login-input-wrapper">

                            <i class="fa-solid fa-envelope"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                placeholder="exemple@email.com"
                                required
                            >

                        </div>

                    </div>

                    <div class="login-form-group">

                        <label for="password">
                            Mot de passe
                        </label>

                        <div class="login-input-wrapper">

                            <i class="fa-solid fa-lock"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Entrez votre mot de passe"
                                required
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="login-submit-button"
                    >
                        Se connecter
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>

                </form>

                <p class="login-footer-text">
                    © <?= date('Y') ?> Library Manager
                </p>

            </div>

        </section>

    </div>

</body>

</html>