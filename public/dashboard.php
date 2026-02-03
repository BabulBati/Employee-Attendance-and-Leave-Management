<?php
include "../includes/header.php";
require "../config/db.php";
require "../includes/auth.php";
auth();

$id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

/* TOTAL EMPLOYEES (ADMIN ONLY) */
if ($role === 'admin') {
    $e = $pdo->query("SELECT COUNT(*) FROM users WHERE role='employee'");
}

/* ATTENDANCE COUNT */
if ($role === 'admin') {
    $a = $pdo->query("SELECT COUNT(*) FROM attendance");
} else {
    $a = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id=?");
    $a->execute([$id]);
}

/* LEAVE COUNT */
if ($role === 'admin') {
    $l = $pdo->query("SELECT COUNT(*) FROM leaves");
} else {
    $l = $pdo->prepare("SELECT COUNT(*) FROM leaves WHERE user_id=?");
    $l->execute([$id]);
}


$n = $pdo->prepare("SELECT name FROM users WHERE id=?");
$n->execute([$id]);
$name = $n->fetchColumn();

?>

<section class="dashboard-section">
    <h2 class="page-title">Hello <?= htmlspecialchars($name) ?></h2>

    <div class="dashboard-cards">

        <?php if ($role === 'admin'): ?>
            <div class="card">
                <h3>Total Employees</h3>
                <p class="stat-number"><?= $e->fetchColumn() ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Attendance Records</h3>
            <p class="stat-number"><?= $a->fetchColumn() ?></p>
        </div>

        <div class="card">
            <h3>Leave Requests</h3>
            <p class="stat-number"><?= $l->fetchColumn() ?></p>
        </div>

    </div>
</section>


<?php include "../includes/footer.php"; ?>