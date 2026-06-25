<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioConectado = isset($_SESSION['usuario_id']);
$nombreUsuario = $usuarioConectado ? $_SESSION['usuario_nombre'] : null;

// Contar items en el carrito (solo si hay sesión)
$totalCarrito = 0;
if ($usuarioConectado && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(cantidad), 0) AS total FROM carrito WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $totalCarrito = (int) $stmt->fetch()['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' - Tienda en Línea' : 'Tienda en Línea'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="barra-nav">
    <div class="contenedor barra-nav-interior">
        <a href="index.php" class="logo">🛒 Tienda en Línea</a>
        <nav>
            <a href="index.php">Catálogo</a>
            <?php if ($usuarioConectado): ?>
                <a href="cart.php">Carrito (<?php echo $totalCarrito; ?>)</a>
                <span class="usuario-saludo">Hola, <?php echo htmlspecialchars($nombreUsuario); ?></span>
                <a href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
                <a href="register.php">Registrarse</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="contenedor">
