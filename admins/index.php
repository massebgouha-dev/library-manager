<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($nom === '' || $email === '' || $password === '') {

        $error = 'Veuillez remplir tous les champs obligatoires.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Veuillez saisir une adresse e-mail valide.';

    } elseif (strlen($password) < 6) {

        $error = 'Le mot de passe doit contenir au moins 6 caractères.';

    } elseif ($password !== $confirmation) {

        $error = 'Les deux mots de passe ne correspondent pas.';

    } else {

        $stmt = $pdo->prepare("
            SELECT id
            FROM admins
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $error = 'Cette adresse e-mail est déjà utilisée.';

        } else {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare("
                INSERT INTO admins (
                    nom,
                    email,
                    mot_de_passe
                )
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $nom,
                $email,
                $passwordHash
            ]);

            $success = 'Le compte administrateur a été créé avec succès.';

            $_POST = [];
        }
    }
}

$admins = $pdo->query("
    SELECT id, nom, email
    FROM admins
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Administrateurs | Library Manager</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body class="dashboard-page">

    <aside class="sidebar">

        <div class="logo">

            <div class="logo-icon">
                <i class="fa-solid fa-book-open"></i>
            </div>

            <div>
                <h2>Library</h2>
                <span>Manager</span>
            </div>

        </div>

        <nav class="sidebar-menu">

            <a href="../dashboard.php">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Tableau de bord</span>
            </a>

            <a href="../books/index.php">
                <i class="fa-solid fa-book"></i>
                <span>Livres</span>
            </a>

            <a href="../categories/index.php">
                <i class="fa-solid fa-layer-group"></i>
                <span>Catégories</span>
            </a>

            <a href="../borrowers/index.php">
                <i class="fa-solid fa-users"></i>
                <span>Emprunteurs</span>
            </a>

            <a href="../loans/index.php">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                <span>Emprunts</span>
            </a>

            <a href="index.php" class="active">
                <i class="fa-solid fa-user-shield"></i>
                <span>Administrateurs</span>
            </a>

        </nav>

        <div class="sidebar-footer">

            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>

        </div>

    </aside>

    <main class="dashboard-main">

        <header class="dashboard-header">

            <div>
                <p class="welcome-text">Gestion des comptes</p>
                <h1>Administrateurs</h1>
            </div>

            <div class="profile-avatar">

                <?= strtoupper(
                    substr($_SESSION['admin_nom'], 0, 1)
                ) ?>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="admin-management-grid">

                <div class="form-card admin-form-card">

                    <h2>Ajouter un administrateur</h2>

                    <p class="admin-description">
                        Créez un compte pour un autre responsable.
                    </p>

                    <?php if ($error !== ''): ?>

                        <div class="alert error">

                            <i class="fa-solid fa-circle-exclamation"></i>

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>

                    <?php if ($success !== ''): ?>

                        <div class="alert success">

                            <i class="fa-solid fa-circle-check"></i>

                            <?= htmlspecialchars($success) ?>

                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="modern-form-group">

                            <label for="nom">
                                Nom complet
                                <span>*</span>
                            </label>

                            <div class="modern-input-wrapper">

                                <i class="fa-solid fa-user"></i>

                                <input
                                    type="text"
                                    id="nom"
                                    name="nom"
                                    value="<?= htmlspecialchars(
                                        $_POST['nom'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>

                        </div>

                        <div class="modern-form-group">

                            <label for="email">
                                Adresse e-mail
                                <span>*</span>
                            </label>

                            <div class="modern-input-wrapper">

                                <i class="fa-solid fa-envelope"></i>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars(
                                        $_POST['email'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>

                        </div>

                        <div class="modern-form-group">

                            <label for="password">
                                Mot de passe
                                <span>*</span>
                            </label>

                            <div class="modern-input-wrapper">

                                <i class="fa-solid fa-lock"></i>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    minlength="6"
                                    required
                                >

                            </div>

                        </div>

                        <div class="modern-form-group">

                            <label for="confirmation">
                                Confirmer le mot de passe
                                <span>*</span>
                            </label>

                            <div class="modern-input-wrapper">

                                <i class="fa-solid fa-lock"></i>

                                <input
                                    type="password"
                                    id="confirmation"
                                    name="confirmation"
                                    minlength="6"
                                    required
                                >

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="save-button admin-save-button"
                        >

                            <i class="fa-solid fa-user-plus"></i>
                            Créer le compte

                        </button>

                    </form>

                </div>

                <div class="table-card">

                    <div class="admin-list-header">

                        <h2>Comptes existants</h2>

                        <span>
                            <?= count($admins) ?>
                            administrateur<?= count($admins) > 1 ? 's' : '' ?>
                        </span>

                    </div>

                    <div class="table-responsive">

                        <table class="modern-table">

                            <thead>
                                <tr>
                                    <th>Administrateur</th>
                                    <th>Email</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($admins as $admin): ?>

                                    <tr>

                                        <td>

                                            <div class="borrower-information">

                                                <div class="borrower-avatar">

                                                    <?= strtoupper(
                                                        substr(
                                                            $admin['nom'],
                                                            0,
                                                            1
                                                        )
                                                    ) ?>

                                                </div>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $admin['nom']
                                                    ) ?>
                                                </strong>

                                            </div>

                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $admin['email']
                                            ) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </section>

    </main>

</body>

</html>