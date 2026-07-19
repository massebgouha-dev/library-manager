<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$loanId = (int) ($_POST['loan_id'] ?? 0);

if ($loanId <= 0) {
    header('Location: index.php');
    exit;
}

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT
            id_book,
            statut
        FROM loans
        WHERE id = ?
        FOR UPDATE
    ");

    $stmt->execute([$loanId]);

    $loan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loan) {
        throw new Exception('Emprunt introuvable.');
    }

    if ($loan['statut'] !== 'en_cours') {
        throw new Exception('Ce livre a déjà été retourné.');
    }

    $stmt = $pdo->prepare("
        UPDATE loans
        SET
            statut = 'retourne',
            date_retour_reelle = CURDATE()
        WHERE id = ?
    ");

    $stmt->execute([$loanId]);

    $stmt = $pdo->prepare("
        UPDATE books
        SET disponible = 1
        WHERE id = ?
    ");

    $stmt->execute([$loan['id_book']]);

    $pdo->commit();

    header('Location: index.php?success=returned');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Erreur : ' . htmlspecialchars($e->getMessage()));
}