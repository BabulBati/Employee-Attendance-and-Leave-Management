<?php
require "../config/db.php";
require "../includes/auth.php";

auth();

$userId = $_SESSION['user']['id'];

$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Total records
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id=?");
$stmt->execute([$userId]);
$total = $stmt->fetchColumn();

// Fetch paginated records
$stmt = $pdo->prepare(
    "SELECT attendance_date, status 
     FROM attendance 
     WHERE user_id=? 
     ORDER BY attendance_date DESC
     LIMIT ? OFFSET ?"
);
$stmt->bindValue(1, $userId, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON
echo json_encode([
    'data' => $records,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);
