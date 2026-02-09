<?php
require "../config/db.php";
require "../includes/auth.php";
require "../includes/csrf.php";

auth();
header('Content-Type: application/json');

$userId = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

/* =======================
   EMPLOYEE MARK PRESENT
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_present']) && $role === 'employee') {
    $today = date('Y-m-d');

    // Check if already marked
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id=? AND attendance_date=?");
    $stmt->execute([$userId, $today]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Attendance already marked']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO attendance(user_id, attendance_date, status) VALUES(?,?, 'Present')");
    $stmt->execute([$userId, $today]);
    echo json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
    exit;
}

/* =======================
   ADMIN UPDATE ATTENDANCE
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance']) && $role === 'admin' && check_csrf($_POST['csrf'])) {
    if ((int) $_POST['user_id'] === (int) $userId) {
        echo json_encode(['success' => false, 'message' => 'Admin cannot edit own attendance']);
        exit;
    }
    $stmt = $pdo->prepare(
        "UPDATE attendance SET status=?, attendance_date=? WHERE user_id=? AND attendance_date=?"
    );
    $stmt->execute([
        $_POST['status'],
        $_POST['date'],
        $_POST['user_id'],
        $_POST['original_date']
    ]);
    echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
    exit;
}

/* =======================
   ADMIN FETCH ATTENDANCE (PAGINATED)
======================= */
if ($role === 'admin') {
    $limit = 10;
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    $date = $_GET['date'] ?? date('Y-m-d');

    // Total records
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM attendance a
         JOIN users u ON a.user_id = u.id
         WHERE u.role='employee' AND DATE(a.attendance_date)=?"
    );
    $stmt->execute([$date]);
    $total = $stmt->fetchColumn();

    // Fetch paginated records
    $stmt = $pdo->prepare(
        "SELECT a.*, u.name FROM attendance a
         JOIN users u ON a.user_id = u.id
         WHERE u.role='employee' AND DATE(a.attendance_date)=?
         ORDER BY u.name ASC
         LIMIT ? OFFSET ?"
    );
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
    exit;
}

/* =======================
   EMPLOYEE FETCH ATTENDANCE (PAGINATED)
======================= */
if ($role === 'employee') {
    $limit = 10; // max 10 records per page
    $page = max(1, (int) ($_GET['page'] ?? 1));
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

    echo json_encode([
        'data' => $records,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
    exit;
}
