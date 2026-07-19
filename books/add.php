<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

$categories = $pdo->query("
    SELECT id, nom
    FROM categories
    ORDER BY nom ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = trim($_POST['titre'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');
    $annee = trim($_POST['annee'] ?? '');
    $idCategory = (int) ($_POST['id_category'] ?? 0);
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if ($titre === '') {

        $error = 'Veuillez saisir le titre du livre.';

    } elseif ($idCategory <= 0) {

        $error = 'Veuillez choisir une catégorie.';

    } elseif (
        $annee !== ''
        && (
            !ctype_digit($annee)
            || (int) $annee < 1000
            || (int) $annee > (int) date('Y')
        )
    ) {

        $error = 'Veuillez saisir une année valide.';

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO books (
                titre,
                auteur,
                annee,
                disponible,
                id_category
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $titre,
            $auteur !== '' ? $auteur : null,
            $annee !== '' ? (int) $annee : null,
            $disponible,
            $idCategory
        ]);

        header('Location: index.php?success=added');
        exit;
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

    <title>Ajouter un livre | Library Manager</title>

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

            <a href="index.php" class="active">
                <i class="fa-solid fa-book"></i>
                <span>Livres</span>
            </a>

            <a href="../categories/index.php">
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
                <p class="welcome-text">
                    Gestion des livres
                </p>

                <h1>Nouveau livre</h1>
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
                    Retour aux livres

                </a>

                <h2>Ajouter un livre</h2>

                <p>
                    Renseignez les informations du nouveau livre.
                </p>

            </div>

            <div class="form-card">

                <?php if ($error !== ''): ?>

                    <div class="alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <?= htmlspecialchars($error) ?>

                    </div>

                <?php endif; ?>

                <?php if (count($categories) === 0): ?>

                    <div class="alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        Vous devez d'abord ajouter une catégorie.

                    </div>

                    <a
                        href="../categories/add.php"
                        class="add-book-button"
                    >

                        <i class="fa-solid fa-plus"></i>
                        Ajouter une catégorie

                    </a>

                <?php else: ?>

                    <form method="POST">

                        <div class="form-grid">

                            <div class="modern-form-group full-width">

                                <label for="titre">
                                    Titre du livre
                                    <span>*</span>
                                </label>

                                <div class="modern-input-wrapper">

                                    <i class="fa-solid fa-book"></i>

                                    <input
                                        type="text"
                                        id="titre"
                                        name="titre"
                                        value="<?= htmlspecialchars(
                                            $_POST['titre'] ?? ''
                                        ) ?>"
                                        placeholder="Exemple : Apprendre PHP"
                                        maxlength="255"
                                        required
                                        autofocus
                                    >

                                </div>

                            </div>

                            <div class="modern-form-group">

                                <label for="auteur">
                                    Auteur
                                </label>

                                <div class="modern-input-wrapper">

                                    <i class="fa-solid fa-user-pen"></i>

                                    <input
                                        type="text"
                                        id="auteur"
                                        name="auteur"
                                        value="<?= htmlspecialchars(
                                            $_POST['auteur'] ?? ''
                                        ) ?>"
                                        placeholder="Nom de l'auteur"
                                        maxlength="255"
                                    >

                                </div>

                            </div>

                            <div class="modern-form-group">

                                <label for="annee">
                                    Année de publication
                                </label>

                                <div class="modern-input-wrapper">

                                    <i class="fa-solid fa-calendar"></i>

                                    <input
                                        type="number"
                                        id="annee"
                                        name="annee"
                                        value="<?= htmlspecialchars(
                                            $_POST['annee'] ?? ''
                                        ) ?>"
                                        placeholder="Exemple : 2024"
                                        min="1000"
                                        max="<?= date('Y') ?>"
                                    >

                                </div>

                            </div>

                            <div class="modern-form-group full-width">

                                <label for="id_category">
                                    Catégorie
                                    <span>*</span>
                                </label>

                                <div class="modern-input-wrapper">

                                    <i class="fa-solid fa-layer-group"></i>

                                    <select
                                        id="id_category"
                                        name="id_category"
                                        required
                                    >

                                        <option value="">
                                            Choisir une catégorie
                                        </option>

                                        <?php foreach ($categories as $category): ?>

                                            <option
                                                value="<?= $category['id'] ?>"
                                                <?= (
                                                    (int) ($_POST['id_category'] ?? 0)
                                                    === (int) $category['id']
                                                )
                                                    ? 'selected'
                                                    : ''
                                                ?>
                                            >

                                                <?= htmlspecialchars(
                                                    $category['nom']
                                                ) ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <div class="modern-form-group full-width">

                                <label>Disponibilité</label>

                                <label class="availability-box">

                                    <input
                                        type="checkbox"
                                        name="disponible"
                                        value="1"
                                        <?= !isset($_POST['disponible'])
                                            && $_SERVER['REQUEST_METHOD'] !== 'POST'
                                            ? 'checked'
                                            : (isset($_POST['disponible'])
                                                ? 'checked'
                                                : '')
                                        ?>
                                    >

                                    <span class="custom-checkbox">

                                        <i class="fa-solid fa-check"></i>

                                    </span>

                                    <span>

                                        <strong>Le livre est disponible</strong>

                                        <small>
                                            Décochez cette option si le livre est déjà emprunté.
                                        </small>

                                    </span>

                                </label>

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

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>