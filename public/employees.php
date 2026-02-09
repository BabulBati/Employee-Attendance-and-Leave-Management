<?php
include "../includes/header.php";
require "../config/db.php";
require "../includes/auth.php";
require "../includes/csrf.php";

auth();
adminOnly();

$error = "";
$success = "";

/* ---------------- DELETE USER ---------------- */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_id']) &&
    check_csrf($_POST['csrf'])
) {
    $deleteId = (int) $_POST['delete_id'];

    if ($deleteId === $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$deleteId]);
        $success = "User deleted successfully.";
    }
}

/* ---------------- ADD USER ---------------- */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_user']) &&
    check_csrf($_POST['csrf'])
) {
    $allowedRoles = ['admin', 'employee'];
    $role = in_array($_POST['role'], $allowedRoles) ? $_POST['role'] : 'employee';

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['email']),
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $role
        ]);

        $success = "User added successfully.";

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Email already exists.";
        } else {
            $error = "Something went wrong.";
        }
    }
}

/* ---------------- EDIT USER (POPUP) ---------------- */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['edit_user']) &&
    check_csrf($_POST['csrf'])
) {
    $allowedRoles = ['admin', 'employee'];
    $role = in_array($_POST['role'], $allowedRoles) ? $_POST['role'] : 'employee';

    // Prevent duplicate email
    $check = $pdo->prepare(
        "SELECT id FROM users WHERE email=? AND id!=?"
    );
    $check->execute([
        trim($_POST['email']),
        (int) $_POST['user_id']
    ]);

    if ($check->rowCount()) {
        $error = "Email already exists.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE users SET name=?, email=?, role=? WHERE id=?"
        );

        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['email']),
            $role,
            (int) $_POST['user_id']
        ]);

        $success = "User updated successfully.";
    }
}

/* ---------------- FETCH USERS ---------------- */
$users = $pdo->query(
    "SELECT id, name, email, role FROM users ORDER BY id DESC"
)->fetchAll();
?>

<!-- ================= ADD USER ================= -->
<section class="employee-section">
    <h2 class="page-title">Add User</h2>

    <?php if ($error): ?>
        <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="alert alert-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="card">
        <form method="post" class="employee-form">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="add_user" value="1">

            <label>Name</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Role</label>
            <select name="role">
                <option value="employee">Employee</option>
                <option value="admin">Admin</option>
            </select>

            <button class="btn">Add User</button>
        </form>
    </div>
</section>

<!-- ================= USERS TABLE ================= -->
<section class="employee-section">
    <h2 class="page-title">All Users</h2>

    <div class="table-card">
        <table class="employee-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="openEditModal(
                                <?= $u['id'] ?>,
                                '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>',
                                '<?= $u['role'] ?>'
                            )">
                                Edit
                            </button>

                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this user?')">
                                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                                    <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-danger btn-sm">
                                        Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- ================= EDIT MODAL ================= -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit User</h3>

        <form method="post" class="employee-form">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="edit_user" value="1">
            <input type="hidden" name="user_id" id="edit_id">

            <label>Name</label>
            <input type="text" name="name" id="edit_name" required>

            <label>Email</label>
            <input type="email" name="email" id="edit_email" required>

            <label>Role</label>
            <select name="role" id="edit_role">
                <option value="employee">Employee</option>
                <option value="admin">Admin</option>
            </select>

            <button class="btn">Update</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- ================= MODAL JS ================= -->
<script>
    function openEditModal(id, name, email, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role').value = role;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function (e) {
        const modal = document.getElementById('editModal');
        if (e.target === modal) closeModal();
    }
</script>

<!-- ================= MODAL CSS ================= -->
<style>
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 999;
    }

    .modal-content {
        background: #fff;
        width: 420px;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
    }
</style>

<?php include "../includes/footer.php"; ?>