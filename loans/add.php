<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

/* جلب الكتب المتوفرة فقط */
$books = $pdo->query("
    SELECT id, titre, auteur
    FROM books
    WHERE disponible = 1
    ORDER BY titre ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* جلب المستعيرين */
$borrowers = $pdo->query("
    SELECT id, nom, email
    FROM borrowers
    ORDER BY nom ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bookId = (int) ($_POST['id_book'] ?? 0);
    $borrowerId = (int) ($_POST['id_borrower'] ?? 0);

    $dateEmprunt = $_POST['date_emprunt'] ?? '';
    $dateRetourPrevue = $_POST['date_retour_prevue'] ?? '';

    if ($bookId <= 0) {

        $error = 'Veuillez sélectionner un livre.';

    } elseif ($borrowerId <= 0) {

        $error = 'Veuillez sélectionner un emprunteur.';

    } elseif ($dateEmprunt === '') {

        $error = 'Veuillez sélectionner la date d’emprunt.';

    } elseif ($dateRetourPrevue === '') {

        $error = 'Veuillez sélectionner la date de retour prévue.';

    } elseif ($dateRetourPrevue < $dateEmprunt) {

        $error = 'La date de retour doit être postérieure à la date d’emprunt.';

    } else {

        try {

            $pdo->beginTransaction();

            /* Vérifier que le livre est toujours disponible */
            $stmt = $pdo->prepare("
                SELECT disponible
                FROM books
                WHERE id = ?
                FOR UPDATE
            ");

            $stmt->execute([$bookId]);

            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                throw new Exception('Livre introuvable.');
            }

            if ((int) $book['disponible'] !== 1) {
                throw new Exception('Ce livre n’est plus disponible.');
            }

            /* Enregistrer l’emprunt */
            $stmt = $pdo->prepare("
                INSERT INTO loans (
                    id_book,
                    id_borrower,
                    date_emprunt,
                    date_retour_prevue,
                    statut
                )
                VALUES (?, ?, ?, ?, 'en_cours')
            ");

            $stmt->execute([
                $bookId,
                $borrowerId,
                $dateEmprunt,
                $dateRetourPrevue
            ]);

            /* Mettre le livre en statut emprunté */
            $stmt = $pdo->prepare("
                UPDATE books
                SET disponible = 0
                WHERE id = ?
            ");

            $stmt->execute([$bookId]);

            $pdo->commit();

            header('Location: index.php?success=added');
            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e->getMessage();
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

    <title>Nouvel emprunt | Library Manager</title>

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

            <a href="index.php" class="active">
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
                <p class="welcome-text">Gestion des emprunts</p>
                <h1>Nouvel emprunt</h1>
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
                    Retour aux emprunts

                </a>

                <h2>Enregistrer un emprunt</h2>

                <p>
                    Sélectionnez le livre, l’emprunteur et les dates.
                </p>

            </div>

            <div class="form-card">

                <?php if ($error !== ''): ?>

                    <div class="alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <?= htmlspecialchars($error) ?>

                    </div>

                <?php endif; ?>

                <?php if (count($books) === 0): ?>

                    <div class="alert error">

                        <i class="fa-solid fa-book"></i>

                        Aucun livre disponible actuellement.

                    </div>

                <?php elseif (count($borrowers) === 0): ?>

                    <div class="alert error">

                        <i class="fa-solid fa-users"></i>

                        Vous devez d’abord ajouter un emprunteur.

                    </div>

                    <a
                        href="../borrowers/add.php"
                        class="add-book-button"
                    >

                        <i class="fa-solid fa-user-plus"></i>
                        Ajouter un emprunteur

                    </a>

                <?php else: ?>

                    <form method="POST">

                        <div class="modern-form-group">

                            <label for="id_book">
                                Livre
                                <span>*</span>
                            </label>

                            <div class="modern-input-wrapper">

                                <i class="fa-solid fa-book"></i>

                                <select
                                    id="id_book"
                                    name="id_book"
                                    required
                                >

                                    <option value="">
                                        Choisir un livre disponible
                                    </option>

                                    <?php foreach ($books as $book): ?>

                                        <option
                                            value="<?= $book['id'] ?>"
                                            <?= (
                                                (int) ($_POST['id_book'] ?? 0)
                                                === (int) $book['id']
                                            ) ? 'selected' : '' ?>
                                        >

                                            <?= htmlspecialchars($book['titre']) ?>

                                            <?php if ($book['auteur']): ?>
                                                — <?= htmlspecialchars($book['auteur']) ?>
                                            <?php endif; ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>

                        <div class="modern-form-group">

                            <label for="id_borrower">
                                Emprunteur
                                <span>*</span>
                            </label>

                            <div class="modern-input-wrapper">

                                <i class="fa-solid fa-user"></i>

                                <select
                                    id="id_borrower"
                                    name="id_borrower"
                                    required
                                >

                                    <option value="">
                                        Choisir un emprunteur
                                    </option>

                                    <?php foreach ($borrowers as $borrower): ?>

                                        <option
                                            value="<?= $borrower['id'] ?>"
                                            <?= (
                                                (int) ($_POST['id_borrower'] ?? 0)
                                                === (int) $borrower['id']
                                            ) ? 'selected' : '' ?>
                                        >

                                            <?= htmlspecialchars($borrower['nom']) ?>

                                            <?php if ($borrower['email']): ?>
                                                — <?= htmlspecialchars($borrower['email']) ?>
                                            <?php endif; ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="modern-form-group">

                                <label for="date_emprunt">
                                    Date d’emprunt
                                    <span>*</span>
                                </label>

                                <div class="modern-input-wrapper">

                                    <i class="fa-solid fa-calendar-day"></i>

                                    <input
                                        type="date"
                                        id="date_emprunt"
                                        name="date_emprunt"
                                        value="<?= htmlspecialchars(
                                            $_POST['date_emprunt']
                                            ?? date('Y-m-d')
                                        ) ?>"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="modern-form-group">

                                <label for="date_retour_prevue">
                                    Retour prévu
                                    <span>*</span>
                                </label>

                                <div class="modern-input-wrapper">

                                    <i class="fa-solid fa-calendar-check"></i>

                                    <input
                                        type="date"
                                        id="date_retour_prevue"
                                        name="date_retour_prevue"
                                        value="<?= htmlspecialchars(
                                            $_POST['date_retour_prevue']
                                            ?? date(
                                                'Y-m-d',
                                                strtotime('+14 days')
                                            )
                                        ) ?>"
                                        required
                                    >

                                </div>

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
                                Enregistrer l’emprunt

                            </button>

                        </div>

                    </form>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>