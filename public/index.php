<?php
require "../config/db.php";
include "../includes/header.php";

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Trim inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {

        // SQL injection safe (prepared statement)
        $stmt = $pdo->prepare(
            "SELECT id, email, password, role FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password'])) {
            $_SESSION['user'] = $u;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<div class="login-form__container">
    <div class="login-form__wrapper">
        <h2>Login</h2>

        <?php if ($error): ?>
            <p class="error" style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-input__field">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>

            <div class="form-input__field">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="********" required>
            </div>

            <button class="btn" id="login-btn">Login</button>
        </form>
    </div>
</div>