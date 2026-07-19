<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');

    if ($nom === '') {

        $error = 'Veuillez saisir le nom de la catégorie.';

    } else {

        $stmt = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE LOWER(nom) = LOWER(?)
        ");

        $stmt->execute([$nom]);

        if ($stmt->fetch()) {

            $error = 'Cette catégorie existe déjà.';

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO categories (nom)
                VALUES (?)
            ");

            $stmt->execute([$nom]);

            header('Location: index.php?success=added');
            exit;
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

    <title>Ajouter une catégorie</title>

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

            <a href="index.php" class="active">
                <i class="fa-solid fa-layer-group"></i>
                <span>Catégories</span>
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
                <p class="welcome-text">Gestion des catégories</p>
                <h1>Nouvelle catégorie</h1>
            </div>

            <div class="profile-avatar">

                <?= strtoupper(
                    substr($_SESSION['admin_nom'], 0, 1)
                ) ?>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="form-page-header">

                <a href="index.php" class="back-link">

                    <i class="fa-solid fa-arrow-left"></i>
                    Retour aux catégories

                </a>

                <h2>Ajouter une catégorie</h2>

                <p>
                    Saisissez le nom de la nouvelle catégorie.
                </p>

            </div>

            <div class="form-card">

                <?php if ($error !== ''): ?>

                    <div class="alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <?= htmlspecialchars($error) ?>

                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="modern-form-group">

                        <label for="nom">
                            Nom de la catégorie
                            <span>*</span>
                        </label>

                        <div class="modern-input-wrapper">

                            <i class="fa-solid fa-folder"></i>

                            <input
                                type="text"
                                id="nom"
                                name="nom"
                                value="<?= htmlspecialchars(
                                    $_POST['nom'] ?? ''
                                ) ?>"
                                placeholder="Exemple : Informatique"
                                maxlength="100"
                                required
                                autofocus
                            >

                        </div>

                    </div>

                    <div class="form-actions">

                        <a
                            href="index.php"
                            class="cancel-button"
                        >
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="save-button"
                        >

                            <i class="fa-solid fa-check"></i>
                            Enregistrer

                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</body>

</html>