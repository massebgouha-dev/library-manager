<?php

session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();

$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$availableBooks = $pdo->query("
    SELECT COUNT(*)
    FROM books
    WHERE disponible = 1
")->fetchColumn();

$borrowedBooks = $pdo->query("
    SELECT COUNT(*)
    FROM books
    WHERE disponible = 0
")->fetchColumn();

$totalBooks = (int) $totalBooks;
$totalCategories = (int) $totalCategories;
$availableBooks = (int) $availableBooks;
$borrowedBooks = (int) $borrowedBooks;

$availablePercentage = 0;
$borrowedPercentage = 0;

if ($totalBooks > 0) {
    $availablePercentage = round(
        ($availableBooks / $totalBooks) * 100
    );

    $borrowedPercentage = round(
        ($borrowedBooks / $totalBooks) * 100
    );
}

$recentBooks = $pdo->query("
    SELECT
        books.id,
        books.titre,
        books.auteur,
        books.disponible,
        categories.nom AS categorie
    FROM books
    LEFT JOIN categories
        ON categories.id = books.id_category
    ORDER BY books.id DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$lateLoansDetails = $pdo->query("
    SELECT
        loans.id,
        loans.date_retour_prevue,
        books.titre,
        borrowers.nom AS emprunteur,
        borrowers.telephone,
        DATEDIFF(CURDATE(), loans.date_retour_prevue) AS jours_retard
    FROM loans
    INNER JOIN books
        ON books.id = loans.id_book
    INNER JOIN borrowers
        ON borrowers.id = loans.id_borrower
    WHERE loans.statut = 'en_cours'
      AND loans.date_retour_prevue < CURDATE()
    ORDER BY loans.date_retour_prevue ASC
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

    <title>Library Manager</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
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


            <a href="dashboard.php" class="active">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Tableau de bord</span>
            </a>

            <a href="books/index.php">
                <i class="fa-solid fa-book"></i>
                <span>Livres</span>
            </a>

            <a href="categories/index.php">
                <i class="fa-solid fa-layer-group"></i>
                <span>Catégories</span>
            </a>

            <a href="borrowers/index.php">
    <i class="fa-solid fa-users"></i>
    <span>Emprunteurs</span>
</a>

<a href="loans/index.php">
    <i class="fa-solid fa-arrow-right-arrow-left"></i>
    <span>Emprunts</span>
</a>
<a href="admins/index.php">
    <i class="fa-solid fa-user-shield"></i>
    <span>Administrateurs</span>
</a>
        </nav>

        <div class="sidebar-footer">

            <a href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>

        </div>

    </aside>

    <main class="dashboard-main">

        <header class="dashboard-header">

            <div>

                <p class="welcome-text">
                    Bienvenue,
                </p>

                <h1>
                    <?= htmlspecialchars($_SESSION['admin_nom']) ?>
                </h1>

            </div>

            <div class="profile">

                <div class="profile-text">
                    <strong>
                        <?= htmlspecialchars($_SESSION['admin_nom']) ?>
                    </strong>

                    <span>Administrateur</span>
                </div>

                <div class="profile-avatar">
                    <?= strtoupper(substr($_SESSION['admin_nom'], 0, 1)) ?>
                </div>

            </div>

        </header>

        <section class="dashboard-content">

        <?php if (count($lateLoansDetails) > 0): ?>

    <div class="overdue-alert">

        <div class="overdue-alert-header">

            <div class="overdue-alert-title">

                <div class="overdue-alert-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div>
                    <h3>Livres en retard</h3>

                    <p>
                        <?= count($lateLoansDetails) ?>
                        emprunt<?= count($lateLoansDetails) > 1 ? 's' : '' ?>
                        nécessite<?= count($lateLoansDetails) > 1 ? 'nt' : '' ?>
                        votre attention.
                    </p>
                </div>

            </div>

            <a href="loans/index.php?status=en_cours">
                Voir les emprunts
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

        <div class="overdue-list">

            <?php foreach ($lateLoansDetails as $lateLoan): ?>

                <div class="overdue-item">

                    <div class="overdue-book">

                        <div class="overdue-book-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>

                        <div>
                            <strong>
                                <?= htmlspecialchars($lateLoan['titre']) ?>
                            </strong>

                            <span>
                                Emprunté par
                                <?= htmlspecialchars($lateLoan['emprunteur']) ?>
                            </span>
                        </div>

                    </div>

                    <div class="overdue-contact">

                        <?php if ($lateLoan['telephone']): ?>

                            <span>
                                <i class="fa-solid fa-phone"></i>

                                <?= htmlspecialchars(
                                    $lateLoan['telephone']
                                ) ?>
                            </span>

                        <?php endif; ?>

                        <strong>
                            <?= (int) $lateLoan['jours_retard'] ?>
                            jour<?= (int) $lateLoan['jours_retard'] > 1
                                ? 's'
                                : ''
                            ?>
                            de retard
                        </strong>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

<?php endif; ?>

            <div class="page-title">

                <div>
                    <h2>Vue d'ensemble</h2>
                    <p>
                        Consultez les statistiques principales de la bibliothèque.
                    </p>
                </div>

                <a href="books/add.php" class="add-book-button">
                    <i class="fa-solid fa-plus"></i>
                    Ajouter un livre
                </a>

            </div>

            <div class="stats-grid">

                <div class="modern-stat-card">

                    <div class="stat-icon books-icon">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div class="stat-information">
                        <span>Total des livres</span>
                        <strong><?= $totalBooks ?></strong>
                    </div>

                </div>

                <div class="modern-stat-card">

                    <div class="stat-icon categories-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>

                    <div class="stat-information">
                        <span>Catégories</span>
                        <strong><?= $totalCategories ?></strong>
                    </div>

                </div>

                <div class="modern-stat-card">

                    <div class="stat-icon available-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div class="stat-information">
                        <span>Livres disponibles</span>
                        <strong><?= $availableBooks ?></strong>
                    </div>

                </div>

                <div class="modern-stat-card">

                    <div class="stat-icon borrowed-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>

                    <div class="stat-information">
                        <span>Livres empruntés</span>
                        <strong><?= $borrowedBooks ?></strong>
                    </div>

                </div>

            </div>

            <div class="dashboard-panels">

    <div class="dashboard-panel">

        <div class="panel-header">

            <div>
                <h3>Derniers livres ajoutés</h3>

                <p>
                    Les cinq livres les plus récents.
                </p>
            </div>

            <a
                href="books/index.php"
                class="panel-link"
            >
                Voir tout
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

        <?php if (count($recentBooks) > 0): ?>

            <div class="recent-books-list">

                <?php foreach ($recentBooks as $book): ?>

                    <div class="recent-book-item">

                        <div class="recent-book-left">

                            <div class="recent-book-icon">
                                <i class="fa-solid fa-book"></i>
                            </div>

                            <div class="recent-book-information">

                                <strong>
                                    <?= htmlspecialchars($book['titre']) ?>
                                </strong>

                                <span>

                                    <?= htmlspecialchars(
                                        $book['auteur'] ?: 'Auteur non précisé'
                                    ) ?>

                                    <?php if ($book['categorie']): ?>

                                        • <?= htmlspecialchars(
                                            $book['categorie']
                                        ) ?>

                                    <?php endif; ?>

                                </span>

                            </div>

                        </div>

                        <?php if ((int) $book['disponible'] === 1): ?>

                            <span class="status-badge available">
                                Disponible
                            </span>

                        <?php else: ?>

                            <span class="status-badge borrowed">
                                Emprunté
                            </span>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="small-empty-state">

                <i class="fa-solid fa-book-open"></i>

                <p>Aucun livre n'a encore été ajouté.</p>

            </div>

        <?php endif; ?>

    </div>

    <div class="dashboard-panel library-status">

        <div class="panel-header">

            <div>
                <h3>État de la bibliothèque</h3>

                <p>
                    Répartition actuelle des livres.
                </p>
            </div>

        </div>

        <div class="status-summary">

            <div class="status-percentage">

                <strong>
                    <?= $availablePercentage ?>%
                </strong>

                <span>Disponibles</span>

            </div>

            <div class="status-percentage">

                <strong>
                    <?= $borrowedPercentage ?>%
                </strong>

                <span>Empruntés</span>

            </div>

        </div>

        <div class="status-row">

            <div class="status-label">

                <span>
                    <i class="fa-solid fa-circle available-dot"></i>
                    Disponibles
                </span>

                <strong>
                    <?= $availableBooks ?>
                </strong>

            </div>

            <div class="progress">

                <div
                    class="progress-bar available-progress"
                    style="width: <?= $availablePercentage ?>%;"
                ></div>

            </div>

        </div>

        <div class="status-row">

            <div class="status-label">

                <span>
                    <i class="fa-solid fa-circle borrowed-dot"></i>
                    Empruntés
                </span>

                <strong>
                    <?= $borrowedBooks ?>
                </strong>

            </div>

            <div class="progress">

                <div
                    class="progress-bar borrowed-progress"
                    style="width: <?= $borrowedPercentage ?>%;"
                ></div>

            </div>

        </div>

        <?php if ($totalBooks === 0): ?>

            <div class="library-empty-message">

                <i class="fa-solid fa-circle-info"></i>

                Ajoutez des livres pour afficher les statistiques.

            </div>

        <?php endif; ?>

    </div>

</div>

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>
                            <h3>Actions rapides</h3>
                            <p>
                                Accédez rapidement aux fonctions principales.
                            </p>
                        </div>

                    </div>

                    <div class="quick-actions">

                        <a href="books/add.php" class="quick-action">

                            <div>
                                <i class="fa-solid fa-book-medical"></i>
                            </div>

                            <span>Ajouter un livre</span>

                        </a>

                        <a href="categories/add.php" class="quick-action">

                            <div>
                                <i class="fa-solid fa-folder-plus"></i>
                            </div>

                            <span>Ajouter une catégorie</span>

                        </a>

                        <a href="books/index.php" class="quick-action">

                            <div>
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>

                            <span>Consulter les livres</span>

                        </a>

                    </div>

                </div>

                <div class="dashboard-panel library-status">

                    <div class="panel-header">

                        <div>
                            <h3>État de la bibliothèque</h3>
                            <p>
                                Répartition actuelle des livres.
                            </p>
                        </div>

                    </div>

                    <div class="status-row">

                        <div class="status-label">
                            <span>Disponibles</span>
                            <strong><?= $availableBooks ?></strong>
                        </div>

                        <div class="progress">
                            <div class="progress-bar available-progress"></div>
                        </div>

                    </div>

                    <div class="status-row">

                        <div class="status-label">
                            <span>Empruntés</span>
                            <strong><?= $borrowedBooks ?></strong>
                        </div>

                        <div class="progress">
                            <div class="progress-bar borrowed-progress"></div>
                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>

</body>

</html>