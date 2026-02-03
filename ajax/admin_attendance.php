<?php
require "../config/db.php";
require "../includes/auth.php";

auth();

if ($_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['data'=>[], 'total'=>0, 'page'=>1, 'limit'=>10]);
    exit;
}

$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$filterDate = $_GET['date'] ?? date('Y-m-d');

// Total records
$stmt = $pdo->prepare(
    "SELECT COUNT(*) 
     FROM attendance a 
     JOIN users u ON a.user_id = u.id
     WHERE u.role='employee' AND a.attendance_date=?"
);
$stmt->execute([$filterDate]);
$total = $stmt->fetchColumn();

// Fetch paginated records
$stmt = $pdo->prepare(
    "SELECT a.*, u.name 
     FROM attendance a 
     JOIN users u ON a.user_id = u.id
     WHERE u.role='employee' AND a.attendance_date=?
     ORDER BY u.name
     LIMIT ? OFFSET ?"
);
$stmt->bindValue(1, $filterDate);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'data' => $records,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);
