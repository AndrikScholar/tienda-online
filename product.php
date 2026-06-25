<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, c.nombre AS categoria_nombre FROM productos p
                        LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php');
    exit;
}

$tituloPagina = $producto['nombre'];
include 'includes/header.php';
?>

<a href="index.php">&larr; Volver al catálogo</a>

<div class="tarjeta-formulario" style="max-width:600px;">
    <div class="img-placeholder" style="height:200px; border-radius:8px; margin-bottom:15px;">🛍️</div>
    <span class="categoria"><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></span>
    <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
    <p style="margin:10px 0; color:#4b5563;"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
    <p class="precio">$<?php echo number_format($producto['precio'], 2); ?></p>

    <?php if ($producto['stock'] > 0): ?>
        <p class="stock">Disponible: <?php echo $producto['stock']; ?> piezas</p>

        <?php if (isset($_SESSION['usuario_id'])): ?>
            <form method="POST" action="cart_action.php">
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                <div class="campo">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>">
                </div>
                <button type="submit" class="boton boton-ancho">Agregar al carrito</button>
            </form>
        <?php else: ?>
            <a href="login.php" class="boton boton-ancho">Inicia sesión para comprar</a>
        <?php endif; ?>
    <?php else: ?>
        <p class="stock agotado">Producto agotado</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
