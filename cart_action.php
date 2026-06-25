<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php'; // Requiere sesión activa

$usuarioId = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? '';

if ($accion === 'agregar') {
    $productoId = (int) ($_POST['producto_id'] ?? 0);
    $cantidad   = max(1, (int) ($_POST['cantidad'] ?? 1));

    // Validar stock disponible
    $stmt = $pdo->prepare('SELECT stock FROM productos WHERE id = ?');
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch();

    if ($producto && $cantidad <= $producto['stock']) {
        // Si ya existe en el carrito, se suma la cantidad; si no, se inserta
        $stmt = $pdo->prepare('SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?');
        $stmt->execute([$usuarioId, $productoId]);
        $existente = $stmt->fetch();

        if ($existente) {
            $nuevaCantidad = min($producto['stock'], $existente['cantidad'] + $cantidad);
            $stmt = $pdo->prepare('UPDATE carrito SET cantidad = ? WHERE id = ?');
            $stmt->execute([$nuevaCantidad, $existente['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)');
            $stmt->execute([$usuarioId, $productoId, $cantidad]);
        }
        header('Location: index.php?agregado=1');
        exit;
    }
    header('Location: product.php?id=' . $productoId);
    exit;
}

if ($accion === 'actualizar') {
    $carritoId = (int) ($_POST['carrito_id'] ?? 0);
    $cantidad  = (int) ($_POST['cantidad'] ?? 1);

    if ($cantidad <= 0) {
        // Si la cantidad es 0 o negativa, se elimina el producto del carrito
        $stmt = $pdo->prepare('DELETE FROM carrito WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$carritoId, $usuarioId]);
    } else {
        // No permitir actualizar a una cantidad mayor al stock disponible
        $stmt = $pdo->prepare(
            'SELECT p.stock FROM carrito c JOIN productos p ON c.producto_id = p.id
             WHERE c.id = ? AND c.usuario_id = ?'
        );
        $stmt->execute([$carritoId, $usuarioId]);
        $fila = $stmt->fetch();

        if ($fila) {
            $cantidad = min($cantidad, $fila['stock']);
            $stmt = $pdo->prepare('UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$cantidad, $carritoId, $usuarioId]);
        }
    }
    header('Location: cart.php');
    exit;
}

if ($accion === 'eliminar') {
    $carritoId = (int) ($_POST['carrito_id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM carrito WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$carritoId, $usuarioId]);
    header('Location: cart.php');
    exit;
}

header('Location: cart.php');
exit;
