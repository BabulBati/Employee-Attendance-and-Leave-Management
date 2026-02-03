<?php
function auth() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}

function adminOnly() {
    if ($_SESSION['user']['role'] !== 'admin') {
        die("Access Denied");
    }
}
