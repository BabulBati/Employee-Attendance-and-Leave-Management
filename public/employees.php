<?php
include "../includes/header.php";
require "../config/db.php";
require "../includes/auth.php";
require "../includes/csrf.php";

auth();
adminOnly();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_csrf($_POST['csrf'])) {

    /* ALLOW ONLY VALID ROLES (SECURITY) */
    $allowedRoles = ['admin', 'employee'];
    $role = in_array($_POST['role'], $allowedRoles) ? $_POST['role'] : 'employee';

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $role
        ]);

        $success = "User added successfully";

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Email already exists. Please use a different email.";
        } else {
            $error = "Something went wrong.";
        }
    }
}

$users = $pdo->query("SELECT id, name, email, role FROM users");
?>

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

            <label>Name</label>
            <input type="text" name="name" required placeholder="Full Name">

            <label>Email</label>
            <input type="email" name="email" required placeholder="Email">

            <label>Password</label>
            <input type="password" name="password" required placeholder="Password">

            <label>Role</label>
            <select name="role" required>
                <option value="employee">Employee</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit" class="btn">Add User</button>
        </form>
    </div>
</section>

<section class="employee-section">
    <h2 class="page-title">All Users</h2>

    <div class="table-card">
        <table class="employee-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>


<?php include '../includes/footer.php'; ?>