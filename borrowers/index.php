<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';
$messageType = '';

/* حذف مستعير */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_borrower'])
) {

    $borrowerId = (int) ($_POST['borrower_id'] ?? 0);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM loans
        WHERE id_borrower = ?
    ");

    $stmt->execute([$borrowerId]);

    $loansCount = (int) $stmt->fetchColumn();

    if ($loansCount > 0) {

        $message = 'Impossible de supprimer cet emprunteur car il possède un historique d’emprunts.';
        $messageType = 'error';

    } else {

        $stmt = $pdo->prepare("
            DELETE FROM borrowers
            WHERE id = ?
        ");

        $stmt->execute([$borrowerId]);

        header('Location: index.php?success=deleted');
        exit;
    }
}

/* رسائل النجاح */
if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {
        $message = 'L’emprunteur a été ajouté avec succès.';
        $messageType = 'success';
    }

    if ($_GET['success'] === 'updated') {
        $message = 'Les informations ont été modifiées avec succès.';
        $messageType = 'success';
    }

    if ($_GET['success'] === 'deleted') {
        $message = 'L’emprunteur a été supprimé avec succès.';
        $messageType = 'success';
    }
}

/* البحث */
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT
        borrowers.id,
        borrowers.nom,
        borrowers.email,
        borrowers.telephone,
        borrowers.date_creation,
        COUNT(loans.id) AS nombre_emprunts,
        SUM(
            CASE
                WHEN loans.statut = 'en_cours'
                THEN 1
                ELSE 0
            END
        ) AS emprunts_en_cours
    FROM borrowers
    LEFT JOIN loans
        ON loans.id_borrower = borrowers.id
";

$params = [];

if ($search !== '') {

    $sql .= "
        WHERE
            borrowers.nom LIKE ?
            OR borrowers.email LIKE ?
            OR borrowers.telephone LIKE ?
    ";

    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= "
    GROUP BY
        borrowers.id,
        borrowers.nom,
        borrowers.email,
        borrowers.telephone,
        borrowers.date_creation

    ORDER BY borrowers.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$borrowers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Emprunteurs | Library Manager</title>

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

                <h1>Emprunteurs</h1>

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

                    <h2>Liste des emprunteurs</h2>

                    <p>
                        Consultez et gérez les personnes inscrites.
                    </p>

                </div>

                <a
                    href="add.php"
                    class="add-book-button"
                >

                    <i class="fa-solid fa-user-plus"></i>
                    Ajouter un emprunteur

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

            <div class="filter-card">

                <form method="GET" class="borrower-search-form">

                    <div class="filter-input">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Rechercher par nom, email ou téléphone..."
                        >

                    </div>

                    <button
                        type="submit"
                        class="filter-button"
                    >

                        <i class="fa-solid fa-search"></i>
                        Rechercher

                    </button>

                    <?php if ($search !== ''): ?>

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

                <?php if (count($borrowers) > 0): ?>

                    <div class="table-responsive">

                        <table class="modern-table">

                            <thead>

                                <tr>
                                    <th>Emprunteur</th>
                                    <th>Coordonnées</th>
                                    <th>Total emprunts</th>
                                    <th>En cours</th>
                                    <th>Date d’inscription</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($borrowers as $borrower): ?>

                                    <tr>

                                        <td>

                                            <div class="borrower-information">

                                                <div class="borrower-avatar">

                                                    <?= strtoupper(
                                                        substr(
                                                            $borrower['nom'],
                                                            0,
                                                            1
                                                        )
                                                    ) ?>

                                                </div>

                                                <div>

                                                    <strong>
                                                        <?= htmlspecialchars(
                                                            $borrower['nom']
                                                        ) ?>
                                                    </strong>

                                                    <span>
                                                        Emprunteur #<?= $borrower['id'] ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td>

                                            <div class="contact-information">

                                                <span>

                                                    <i class="fa-solid fa-envelope"></i>

                                                    <?= htmlspecialchars(
                                                        $borrower['email']
                                                        ?: 'Email non précisé'
                                                    ) ?>

                                                </span>

                                                <span>

                                                    <i class="fa-solid fa-phone"></i>

                                                    <?= htmlspecialchars(
                                                        $borrower['telephone']
                                                        ?: 'Téléphone non précisé'
                                                    ) ?>

                                                </span>

                                            </div>

                                        </td>

                                        <td>

                                            <span class="number-badge">

                                                <?= (int) $borrower['nombre_emprunts'] ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?php if (
                                                (int) $borrower['emprunts_en_cours'] > 0
                                            ): ?>

                                                <span class="status-badge borrowed">

                                                    <?= (int) $borrower['emprunts_en_cours'] ?>
                                                    en cours

                                                </span>

                                            <?php else: ?>

                                                <span class="status-badge available">
                                                    Aucun
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $borrower['date_creation']
                                                )
                                            ) ?>

                                        </td>

                                        <td>

                                            <div class="table-actions">

                                                <a
                                                    href="edit.php?id=<?= $borrower['id'] ?>"
                                                    class="action-button edit-button"
                                                    title="Modifier"
                                                >

                                                    <i class="fa-solid fa-pen"></i>

                                                </a>

                                                <form
                                                    method="POST"
                                                    class="delete-form"
                                                    onsubmit="return confirm(
                                                        'Voulez-vous vraiment supprimer cet emprunteur ?'
                                                    )"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="borrower_id"
                                                        value="<?= $borrower['id'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        name="delete_borrower"
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
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <h3>Aucun emprunteur trouvé</h3>

                        <p>
                            Ajoutez une personne pour commencer à gérer les emprunts.
                        </p>

                        <a
                            href="add.php"
                            class="add-book-button"
                        >

                            <i class="fa-solid fa-user-plus"></i>
                            Ajouter un emprunteur

                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>