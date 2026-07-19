<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$borrowerId = (int) ($_GET['id'] ?? 0);

if ($borrowerId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM borrowers
    WHERE id = ?
");

$stmt->execute([$borrowerId]);

$borrower = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$borrower) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($nom === '') {

        $error = 'Veuillez saisir le nom de l’emprunteur.';

    } elseif (
        $email !== ''
        && !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error = 'Veuillez saisir une adresse e-mail valide.';

    } else {

        if ($email !== '') {

            $stmt = $pdo->prepare("
                SELECT id
                FROM borrowers
                WHERE email = ?
                AND id != ?
            ");

            $stmt->execute([
                $email,
                $borrowerId
            ]);

            if ($stmt->fetch()) {
                $error = 'Cette adresse e-mail est déjà utilisée.';
            }
        }

        if ($error === '') {

            $stmt = $pdo->prepare("
                UPDATE borrowers
                SET
                    nom = ?,
                    email = ?,
                    telephone = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $nom,
                $email !== '' ? $email : null,
                $telephone !== '' ? $telephone : null,
                $borrowerId
            ]);

            header('Location: index.php?success=updated');
            exit;
        }
    }
}

$formNom = $_POST['nom'] ?? $borrower['nom'];
$formEmail = $_POST['email'] ?? $borrower['email'];
$formTelephone = $_POST['telephone'] ?? $borrower['telephone'];

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Modifier un emprunteur</title>

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

            <a href="index.php" class="active">
                <i class="fa-solid fa-users"></i>
                <span>Emprunteurs</span>
            </a>

            <a href="../loans/index.php">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                <span>Emprunts</span>
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
                    Gestion des emprunteurs
                </p>

                <h1>Modifier l’emprunteur</h1>

            </div>

            <div class="profile-avatar">

                <?= strtoupper(
                    substr($_SESSION['admin_nom'], 0, 1)
                ) ?>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="form-page-header">

                <a
                    href="index.php"
                    class="back-link"
                >

                    <i class="fa-solid fa-arrow-left"></i>
                    Retour aux emprunteurs

                </a>

                <h2>Modifier les informations</h2>

                <p>
                    Mettez à jour les données de l’emprunteur.
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
                            Nom complet
                            <span>*</span>
                        </label>

                        <div class="modern-input-wrapper">

                            <i class="fa-solid fa-user"></i>

                            <input
                                type="text"
                                id="nom"
                                name="nom"
                                value="<?= htmlspecialchars($formNom) ?>"
                                maxlength="150"
                                required
                                autofocus
                            >

                        </div>

                    </div>

                    <div class="modern-form-group">

                        <label for="email">
                            Adresse e-mail
                        </label>

                        <div class="modern-input-wrapper">

                            <i class="fa-solid fa-envelope"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars(
                                    $formEmail ?? ''
                                ) ?>"
                                maxlength="150"
                            >

                        </div>

                    </div>

                    <div class="modern-form-group">

                        <label for="telephone">
                            Numéro de téléphone
                        </label>

                        <div class="modern-input-wrapper">

                            <i class="fa-solid fa-phone"></i>

                            <input
                                type="text"
                                id="telephone"
                                name="telephone"
                                value="<?= htmlspecialchars(
                                    $formTelephone ?? ''
                                ) ?>"
                                maxlength="30"
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