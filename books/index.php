<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';
$messageType = '';

/* حذف كتاب */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_book'])
) {
    $bookId = (int) ($_POST['book_id'] ?? 0);

    if ($bookId > 0) {
        $stmt = $pdo->prepare("
            DELETE FROM books
            WHERE id = ?
        ");

        $stmt->execute([$bookId]);

        header('Location: index.php?success=deleted');
        exit;
    }
}

/* رسالة النجاح */
if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {
        $message = 'Le livre a été ajouté avec succès.';
        $messageType = 'success';
    }

    if ($_GET['success'] === 'updated') {
        $message = 'Le livre a été modifié avec succès.';
        $messageType = 'success';
    }

    if ($_GET['success'] === 'deleted') {
        $message = 'Le livre a été supprimé avec succès.';
        $messageType = 'success';
    }
}

/* البحث والتصفية */
$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category'] ?? 0);

$sql = "
    SELECT
        books.id,
        books.titre,
        books.auteur,
        books.annee,
        books.disponible,
        categories.nom AS categorie
    FROM books
    LEFT JOIN categories
        ON categories.id = books.id_category
    WHERE 1 = 1
";

$params = [];

if ($search !== '') {
    $sql .= "
        AND (
            books.titre LIKE ?
            OR books.auteur LIKE ?
        )
    ";

    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryId > 0) {
    $sql .= " AND books.id_category = ?";
    $params[] = $categoryId;
}

$sql .= " ORDER BY books.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* التصنيفات */
$categories = $pdo->query("
    SELECT id, nom
    FROM categories
    ORDER BY nom ASC
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

    <title>Livres | Library Manager</title>

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
                <p class="welcome-text">
                    Gestion de la bibliothèque
                </p>

                <h1>Livres</h1>
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

                    <h2>Liste des livres</h2>

                    <p>
                        Consultez et gérez les livres de la bibliothèque.
                    </p>

                </div>

                <a
                    href="add.php"
                    class="add-book-button"
                >

                    <i class="fa-solid fa-plus"></i>
                    Ajouter un livre

                </a>

            </div>

            <?php if ($message !== ''): ?>

                <div class="alert <?= $messageType ?>">

                    <i class="fa-solid fa-circle-check"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>

            <div class="filter-card">

                <form method="GET" class="books-filter-form">

                    <div class="filter-input">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Rechercher un livre ou un auteur..."
                        >

                    </div>

                    <div class="filter-select">

                        <i class="fa-solid fa-layer-group"></i>

                        <select name="category">

                            <option value="0">
                                Toutes les catégories
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= $category['id'] ?>"
                                    <?= $categoryId === (int) $category['id']
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= htmlspecialchars($category['nom']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="filter-button"
                    >

                        <i class="fa-solid fa-filter"></i>
                        Filtrer

                    </button>

                    <?php if ($search !== '' || $categoryId > 0): ?>

                        <a
                            href="index.php"
                            class="reset-filter-button"
                        >

                            <i class="fa-solid fa-xmark"></i>
                            Réinitialiser

                        </a>

                    <?php endif; ?>

                </form>

            </div>

            <div class="table-card">

                <?php if (count($books) > 0): ?>

                    <div class="table-responsive">

                        <table class="modern-table books-table">

                            <thead>

                                <tr>
                                    <th>Livre</th>
                                    <th>Auteur</th>
                                    <th>Catégorie</th>
                                    <th>Année</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($books as $book): ?>

                                    <tr>

                                        <td>

                                            <div class="book-information">

                                                <div class="book-cover-icon">
                                                    <i class="fa-solid fa-book"></i>
                                                </div>

                                                <div>

                                                    <strong>
                                                        <?= htmlspecialchars(
                                                            $book['titre']
                                                        ) ?>
                                                    </strong>

                                                    <span>
                                                        Livre #<?= $book['id'] ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                $book['auteur'] ?: 'Non précisé'
                                            ) ?>

                                        </td>

                                        <td>

                                            <?php if ($book['categorie']): ?>

                                                <span class="category-badge">

                                                    <?= htmlspecialchars(
                                                        $book['categorie']
                                                    ) ?>

                                                </span>

                                            <?php else: ?>

                                                <span class="empty-value">
                                                    Sans catégorie
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?= $book['annee']
                                                ? (int) $book['annee']
                                                : '—'
                                            ?>

                                        </td>

                                        <td>

                                            <?php if ((int) $book['disponible'] === 1): ?>

                                                <span class="status-badge available">

                                                    <i class="fa-solid fa-circle-check"></i>
                                                    Disponible

                                                </span>

                                            <?php else: ?>

                                                <span class="status-badge borrowed">

                                                    <i class="fa-solid fa-clock"></i>
                                                    Emprunté

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <div class="table-actions">

                                                <a
                                                    href="edit.php?id=<?= $book['id'] ?>"
                                                    class="action-button edit-button"
                                                    title="Modifier"
                                                >

                                                    <i class="fa-solid fa-pen"></i>

                                                </a>

                                                <form
                                                    method="POST"
                                                    class="delete-form"
                                                    onsubmit="return confirm(
                                                        'Voulez-vous vraiment supprimer ce livre ?'
                                                    )"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="book_id"
                                                        value="<?= $book['id'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        name="delete_book"
                                                        class="action-button delete-button"
                                                        title="Supprimer"
                                                    >

                                                        <i class="fa-solid fa-trash"></i>

                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <div class="empty-icon">
                            <i class="fa-solid fa-book-open"></i>
                        </div>

                        <h3>Aucun livre trouvé</h3>

                        <p>
                            Ajoutez un nouveau livre ou modifiez votre recherche.
                        </p>

                        <a
                            href="add.php"
                            class="add-book-button"
                        >

                            <i class="fa-solid fa-plus"></i>
                            Ajouter un livre

                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>