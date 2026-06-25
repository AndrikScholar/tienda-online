<?php
/**
 * Incluir este archivo al inicio de cualquier página que requiera
 * que el usuario haya iniciado sesión (carrito, checkout, etc.)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?redirigido=1');
    exit;
}
