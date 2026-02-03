<?php
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function csrf() {
    return $_SESSION['csrf'];
}

function check_csrf($t) {
    return hash_equals($_SESSION['csrf'], $t);
}
