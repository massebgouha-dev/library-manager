<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$bookId = (int) ($_GET['id'] ?? 0);

if ($bookId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM books
    WHERE id = ?
");

$stmt->execute([$bookId]);

$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: index.php');
    exit;
}

$categories = $pdo->query("
    SELECT id, nom
    FROM categories
    ORDER BY nom ASC
")->fetchAll(PDO::FETCH_ASSOC);

$error = '';

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
            UPDATE books
            SET
                titre = ?,
                auteur = ?,
                annee = ?,
                disponible = ?,
                id_category = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $titre,
            $auteur !== '' ? $auteur : null,
            $annee !== '' ? (int) $annee : null,
            $disponible,
            $idCategory,
            $bookId
        ]);

        header('Location: index.php?success=updated');
        exit;
    }
}

$formTitre = $_POST['titre'] ?? $book['titre'];
$formAuteur = $_POST['auteur'] ?? $book['auteur'];
$formAnnee = $_POST['annee'] ?? $book['annee'];
$formCategory = (int) ($_POST['id_category'] ?? $book['id_category']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formDisponible = isset($_POST['disponible']);
} else {
    $formDisponible = (int) $book['disponible'] === 1;
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

    <title>Modifier un livre | Library Manager</title>

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

                <h1>Modifier le livre</h1>
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

                <h2>Modifier un livre</h2>

                <p>
                    Modifiez les informations du livre sélectionné.
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
                                    value="<?= htmlspecialchars($formTitre) ?>"
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
                                        $formAuteur ?? ''
                                    ) ?>"
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
                                        $formAnnee ?? ''
                                    ) ?>"
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

                                    <?php foreach ($categories as $category): ?>

                                        <option
                                            value="<?= $category['id'] ?>"
                                            <?= $formCategory === (int) $category['id']
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
                                    <?= $formDisponible ? 'checked' : '' ?>
                                >

                                <span class="custom-checkbox">

                                    <i class="fa-solid fa-check"></i>

                                </span>

                                <span>

                                    <strong>Le livre est disponible</strong>

                                    <small>
                                        Décochez cette option si le livre est emprunté.
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

                            <i class="fa-solid fa-floppy-disk"></i>
                            Enregistrer les modifications

                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</body>

</html>