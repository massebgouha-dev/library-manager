<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {
        $message = 'L’emprunt a été enregistré avec succès.';
    }

    if ($_GET['success'] === 'returned') {
        $message = 'Le livre a été retourné avec succès.';
    }
}

$status = $_GET['status'] ?? '';

$sql = "
    SELECT
        loans.id,
        loans.date_emprunt,
        loans.date_retour_prevue,
        loans.date_retour_reelle,
        loans.statut,
        books.titre,
        books.auteur,
        borrowers.nom AS emprunteur,
        borrowers.telephone
    FROM loans
    INNER JOIN books
        ON books.id = loans.id_book
    INNER JOIN borrowers
        ON borrowers.id = loans.id_borrower
    WHERE 1 = 1
";

$params = [];

if ($status === 'en_cours' || $status === 'retourne') {
    $sql .= " AND loans.statut = ?";
    $params[] = $status;
}

$sql .= "
    ORDER BY
        CASE
            WHEN loans.statut = 'en_cours'
            THEN 0
            ELSE 1
        END,
        loans.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Emprunts | Library Manager</title>

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

            <a href="../admins/index.php">
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
                <p class="welcome-text">Gestion de la bibliothèque</p>
                <h1>Emprunts</h1>
            </div>

            <div class="profile-avatar">

                <?= strtoupper(
                    substr($_SESSION['admin_nom'], 0, 1)
                ) ?>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="page-title">

                <div>

                    <h2>Historique des emprunts</h2>

                    <p>
                        Consultez les emprunts en cours et les livres retournés.
                    </p>

                </div>

                <a href="add.php" class="add-book-button">

                    <i class="fa-solid fa-plus"></i>
                    Nouvel emprunt

                </a>

            </div>

            <?php if ($message !== ''): ?>

                <div class="alert success">

                    <i class="fa-solid fa-circle-check"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>

            <div class="loan-filters">

                <a
                    href="index.php"
                    class="<?= $status === '' ? 'active' : '' ?>"
                >
                    Tous
                </a>

                <a
                    href="index.php?status=en_cours"
                    class="<?= $status === 'en_cours' ? 'active' : '' ?>"
                >
                    En cours
                </a>

                <a
                    href="index.php?status=retourne"
                    class="<?= $status === 'retourne' ? 'active' : '' ?>"
                >
                    Retournés
                </a>

            </div>

            <div class="table-card">

                <?php if (count($loans) > 0): ?>

                    <div class="table-responsive">

                        <table class="modern-table">

                            <thead>

                                <tr>
                                    <th>Livre</th>
                                    <th>Emprunteur</th>
                                    <th>Date d’emprunt</th>
                                    <th>Retour prévu</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($loans as $loan): ?>

                                    <?php
                                    $isLate =
                                        $loan['statut'] === 'en_cours'
                                        && $loan['date_retour_prevue'] < date('Y-m-d');
                                    ?>

                                    <tr>

                                        <td>

                                            <div class="book-information">

                                                <div class="book-cover-icon">
                                                    <i class="fa-solid fa-book"></i>
                                                </div>

                                                <div>

                                                    <strong>
                                                        <?= htmlspecialchars($loan['titre']) ?>
                                                    </strong>

                                                    <span>
                                                        <?= htmlspecialchars(
                                                            $loan['auteur']
                                                            ?: 'Auteur non précisé'
                                                        ) ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td>

                                            <div class="contact-information">

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $loan['emprunteur']
                                                    ) ?>
                                                </strong>

                                                <span>

                                                    <i class="fa-solid fa-phone"></i>

                                                    <?= htmlspecialchars(
                                                        $loan['telephone']
                                                        ?: 'Non précisé'
                                                    ) ?>

                                                </span>

                                            </div>

                                        </td>

                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime($loan['date_emprunt'])
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $loan['date_retour_prevue']
                                                )
                                            ) ?>

                                            <?php if ($isLate): ?>

                                                <span class="late-label">
                                                    En retard
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?php if (
                                                $loan['statut'] === 'en_cours'
                                            ): ?>

                                                <span class="status-badge borrowed">

                                                    <i class="fa-solid fa-clock"></i>
                                                    En cours

                                                </span>

                                            <?php else: ?>

                                                <span class="status-badge available">

                                                    <i class="fa-solid fa-circle-check"></i>
                                                    Retourné

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?php if (
                                                $loan['statut'] === 'en_cours'
                                            ): ?>

                                                <form
                                                    method="POST"
                                                    action="return.php"
                                                    onsubmit="return confirm(
                                                        'Confirmer le retour de ce livre ?'
                                                    )"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="loan_id"
                                                        value="<?= $loan['id'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="return-button"
                                                    >

                                                        <i class="fa-solid fa-rotate-left"></i>
                                                        Retourner

                                                    </button>

                                                </form>

                                            <?php else: ?>

                                                <span class="return-date">

                                                    Retourné le

                                                    <?= date(
                                                        'd/m/Y',
                                                        strtotime(
                                                            $loan['date_retour_reelle']
                                                        )
                                                    ) ?>

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <div class="empty-icon">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        </div>

                        <h3>Aucun emprunt trouvé</h3>

                        <p>
                            Enregistrez le premier emprunt de la bibliothèque.
                        </p>

                        <a href="add.php" class="add-book-button">

                            <i class="fa-solid fa-plus"></i>
                            Nouvel emprunt

                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>