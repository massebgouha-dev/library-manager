<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';
$messageType = '';

if (isset($_GET['delete'])) {

    $categoryId = (int) $_GET['delete'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM books
        WHERE id_category = ?
    ");

    $stmt->execute([$categoryId]);
    $booksCount = $stmt->fetchColumn();

    if ($booksCount > 0) {

        $message = 'Impossible de supprimer cette catégorie car elle contient des livres.';
        $messageType = 'error';

    } else {

        $stmt = $pdo->prepare("
            DELETE FROM categories
            WHERE id = ?
        ");

        $stmt->execute([$categoryId]);

        header('Location: index.php?success=deleted');
        exit;
    }
}

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {
        $message = 'La catégorie a été ajoutée avec succès.';
        $messageType = 'success';
    }

    if ($_GET['success'] === 'deleted') {
        $message = 'La catégorie a été supprimée avec succès.';
        $messageType = 'success';
    }
}

$categories = $pdo->query("
    SELECT
        categories.id,
        categories.nom,
        COUNT(books.id) AS nombre_livres
    FROM categories
    LEFT JOIN books
        ON books.id_category = categories.id
    GROUP BY categories.id, categories.nom
    ORDER BY categories.id DESC
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

    <title>Catégories | Library Manager</title>

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


              <a href="../borrowers/index.php">
    <i class="fa-solid fa-users"></i>
    <span>Emprunteurs</span>
</a>

<a href="../loans/index.php">
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
                <h1>Catégories</h1>
            </div>

            <div class="profile">

                <div class="profile-text">

                    <strong>
                        <?= htmlspecialchars($_SESSION['admin_nom']) ?>
                    </strong>

                    <span>Administrateur</span>

                </div>

                <div class="profile-avatar">

                    <?= strtoupper(
                        substr($_SESSION['admin_nom'], 0, 1)
                    ) ?>

                </div>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="page-title">

                <div>

                    <h2>Liste des catégories</h2>

                    <p>
                        Organisez les livres selon leurs catégories.
                    </p>

                </div>

                <a
                    href="add.php"
                    class="add-book-button"
                >

                    <i class="fa-solid fa-plus"></i>
                    Ajouter une catégorie

                </a>

            </div>

            <?php if ($message !== ''): ?>

                <div class="alert <?= $messageType ?>">

                    <i class="fa-solid
                        <?= $messageType === 'success'
                            ? 'fa-circle-check'
                            : 'fa-circle-exclamation'
                        ?>">
                    </i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>

            <div class="table-card">

                <?php if (count($categories) > 0): ?>

                    <div class="table-responsive">

                        <table class="modern-table">

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Nom de la catégorie</th>
                                    <th>Nombre de livres</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($categories as $category): ?>

                                    <tr>

                                        <td>
                                            #<?= $category['id'] ?>
                                        </td>

                                        <td>

                                            <div class="category-name">

                                                <div class="category-small-icon">
                                                    <i class="fa-solid fa-folder"></i>
                                                </div>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $category['nom']
                                                    ) ?>
                                                </strong>

                                            </div>

                                        </td>

                                        <td>

                                            <span class="number-badge">

                                                <?= $category['nombre_livres'] ?>

                                                livre<?= $category['nombre_livres'] > 1
                                                    ? 's'
                                                    : ''
                                                ?>

                                            </span>

                                        </td>

                                        <td>

                                            <a
                                                href="index.php?delete=<?= $category['id'] ?>"
                                                class="action-button delete-button"
                                                onclick="return confirm(
                                                    'Voulez-vous vraiment supprimer cette catégorie ?'
                                                )"
                                                title="Supprimer"
                                            >

                                                <i class="fa-solid fa-trash"></i>

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <div class="empty-icon">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>

                        <h3>Aucune catégorie</h3>

                        <p>
                            Commencez par ajouter votre première catégorie.
                        </p>

                        <a
                            href="add.php"
                            class="add-book-button"
                        >

                            <i class="fa-solid fa-plus"></i>
                            Ajouter une catégorie

                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>