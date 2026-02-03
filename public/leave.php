<?php
include "../includes/header.php";
require "../config/db.php";
require "../includes/auth.php";
require "../includes/csrf.php";

auth(); // Ensure user is logged in

// Check if user is admin
$isAdmin = $_SESSION['user']['role'] === 'admin';

// Handle admin leave approval/rejection
if ($isAdmin && isset($_GET['id'], $_GET['status'])) {
    $stmt = $pdo->prepare("UPDATE leaves SET status=? WHERE id=?");
    $stmt->execute([$_GET['status'], $_GET['id']]);
}

// Handle employee leave application
if (!$isAdmin && $_POST && check_csrf($_POST['csrf'])) {
    $stmt = $pdo->prepare(
        "INSERT INTO leaves (user_id,start_date,end_date,reason,status)
         VALUES (?,?,?,?, 'Pending')"
    );
    $stmt->execute([
        $_SESSION['user']['id'],
        $_POST['start'],
        $_POST['end'],
        $_POST['reason']
    ]);
}

// Fetch leaves
if ($isAdmin) {
    // Admin sees all leaves with user names
    $leaves = $pdo->query(
        "SELECT l.*, u.name FROM leaves l JOIN users u ON l.user_id=u.id"
    );
} else {
    // Employee sees only their own leaves
    $stmt = $pdo->prepare("SELECT * FROM leaves WHERE user_id=?");
    $stmt->execute([$_SESSION['user']['id']]);
    $leaves = $stmt;
}
?>

<section class="leave-section">
    <h2 class="page-title"><?= $isAdmin ? 'Leave Requests' : 'Apply Leave' ?></h2>

    <?php if (!$isAdmin): ?>
        <!-- Employee Leave Form -->
        <div class="card leave-form-card">
            <form method="post" class="leave-form">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">

                <div class="form-input__field" style="grid-area:leave-start-date;">
                    <label>Start Date</label>
                    <input type="date" name="start" required>
                </div>

                <div class="form-input__field" style="grid-area:leave-end-date;">
                    <label>End Date</label>
                    <input type="date" name="end" required>
                </div>

                <div class="form-textarea__field" style="grid-area:leave-reason;">
                    <label>Reason</label>
                    <textarea name="reason" placeholder="Enter reason for leave"></textarea>
                </div>

                <button type="submit" class="btn" style="grid-area:leave-apply-btn;">Apply Leave</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Leaves Table -->
    <h2 class="page-title"><?= $isAdmin ? 'All Leaves' : 'My Leaves' ?></h2>
    <div class="table-card">
        <table class="leave-table">
            <thead>
                <tr>
                    <?php if ($isAdmin): ?>
                        <th>Employee</th>
                    <?php endif; ?>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <?php if ($isAdmin): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaves as $l): ?>
                    <tr>
                        <?php if ($isAdmin): ?>
                            <td><?= htmlspecialchars($l['name']) ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($l['start_date']) ?></td>
                        <td><?= htmlspecialchars($l['end_date']) ?></td>
                        <td><?= htmlspecialchars($l['reason']) ?></td>
                        <td>
                            <span class="status-badge
                                <?= $l['status'] === 'Approved' ? 'status-present' : '' ?>
                                <?= $l['status'] === 'Rejected' ? 'status-absent' : '' ?>
                                <?= $l['status'] === 'Pending' ? 'status-pending' : '' ?>
                            ">
                                <?= htmlspecialchars($l['status']) ?>
                            </span>
                        </td>
                        <?php if ($isAdmin): ?>
                            <td>
                                <a href="?id=<?= $l['id'] ?>&status=Approved" class="table-action-btn">Approve</a>
                                <a href="?id=<?= $l['id'] ?>&status=Rejected" class="table-action-btn">Reject</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>


<?php include "../includes/footer.php" ?>