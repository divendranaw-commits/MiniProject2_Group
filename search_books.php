<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM books WHERE book_title LIKE :t OR author LIKE :t ORDER BY book_title");
$stmt->execute(['t' => '%' . $term . '%']);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));