<?php
include "../includes/header.php";
require "../config/db.php";
require "../includes/auth.php";
require "../includes/csrf.php";

auth();

$userId = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];
$message = "";

/* =========================
   EMPLOYEE: MARK PRESENT
========================= */
if (
    $role === 'employee' && $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['mark_present']) && check_csrf($_POST['csrf'])
) {

    $check = $pdo->prepare(
        "SELECT COUNT(*) FROM attendance 
         WHERE user_id=? AND attendance_date=?"
    );
    $check->execute([$userId, date('Y-m-d')]);

    if ($check->fetchColumn() > 0) {
        $message = "Attendance already marked for today.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO attendance (user_id, attendance_date, status)
             VALUES (?, ?, 'Present')"
        );
        $stmt->execute([$userId, date('Y-m-d')]);

        $message = "Attendance marked successfully.";
    }
}

/* =========================
   ADMIN: UPDATE ATTENDANCE (AJAX POST)
========================= */
if (
    $role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_attendance']) && check_csrf($_POST['csrf'])
) {

    if ($_POST['user_id'] != $userId) {
        $stmt = $pdo->prepare(
            "UPDATE attendance
             SET status = ?, attendance_date = ?
             WHERE user_id = ? AND attendance_date = ?"
        );
        $stmt->execute([
            $_POST['status'],
            $_POST['date'],
            $_POST['user_id'],
            $_POST['original_date']
        ]);
        echo json_encode(['success' => true, 'message' => "Attendance updated"]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => "Admin cannot update own attendance"]);
        exit;
    }
}

?>

<section class="attendance-section">
    <h2 class="page-title">Attendance</h2>

    <?php if ($message): ?>
        <p class="alert alert-success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- =========================
     EMPLOYEE VIEW
========================= -->

    <?php if ($role === 'employee'): ?>

        <form method="post" class="attendance-actions" onsubmit="setTimeout(loadAttendance, 500)">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="mark_present" value="1">
            <button type="submit" class="btn">Mark Present</button>
        </form>

        <h3>Your Attendance</h3>

        <table class="attendance-table" id="attendanceTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div id="pagination" style="margin-top:10px;"></div>



    <?php endif; ?>
    <!-- =========================
     ADMIN VIEW
========================= -->
    <?php if ($role === 'admin'): ?>
        <h3>Manage Employee Attendance</h3>

        <div class="admin-filter">
            <label>
                Select Date
                <input type="date" id="adminFilterDate" value="<?= date('Y-m-d') ?>">
            </label>
            <button type="button" class="btn" onclick="loadAdminAttendance(1)">Filter</button>
        </div>


        <div class="table-card">
            <table class="attendance-table" id="adminAttendanceTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="adminPagination" style="margin-top:10px;"></div>


    <?php endif; ?>
</section>

<?php include "../includes/footer.php"; ?>