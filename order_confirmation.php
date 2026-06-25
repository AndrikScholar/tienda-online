<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php';

$usuarioId = $_SESSION['usuario_id'];
$pedidoId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');
$stmt->execute([$pedidoId, $usuarioId]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Pedido confirmado';
include 'includes/header.php';
?>

<div class="confirmacion">
    <div class="icono-exito">✔</div>
    <h2>¡Gracias por tu compra!</h2>
    <p>Tu pedido <strong>#<?php echo $pedido['id']; ?></strong> fue confirmado.</p>
    <p>Total pagado: <strong>$<?php echo number_format($pedido['total'], 2); ?></strong></p>
    <p>Estado: <?php echo htmlspecialchars($pedido['estado']); ?></p>
    <br>
    <a href="index.php" class="boton">Volver al catálogo</a>
</div>

<?php include 'includes/footer.php'; ?>
