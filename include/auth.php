<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function is_logged_in() { return !empty($_SESSION['usuario']); }
function current_user() { return $_SESSION['usuario'] ?? null; }
function require_login() {
    if (!is_logged_in()) { header("Location: index.php"); exit(); }
}
function require_role($role) {
    require_login();
    $u = current_user();
    if ($u['Rol'] !== $role && $u['Rol'] !== 'admin') {
        http_response_code(403);
        echo "Acceso denegado"; exit();
    }
}
?>
