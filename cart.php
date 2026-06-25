<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php';

$usuarioId = $_SESSION['usuario_id'];

$stmt = $pdo->prepare(
    'SELECT c.id AS carrito_id, c.cantidad, p.id AS producto_id, p.nombre, p.precio, p.stock
     FROM carrito c JOIN productos p ON c.producto_id = p.id
     WHERE c.usuario_id = ? ORDER BY c.fecha_agregado DESC'
);
$stmt->execute([$usuarioId]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

$tituloPagina = 'Mi carrito';
include 'includes/header.php';
?>

<h1>Mi carrito</h1>

<?php if (empty($items)): ?>
    <div class="carrito-vacio">
        <p>Tu carrito está vacío.</p>
        <a href="index.php" class="boton">Ir al catálogo</a>
    </div>
<?php else: ?>
    <table class="tabla-carrito">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                <td>$<?php echo number_format($item['precio'], 2); ?></td>
                <td>
                    <form method="POST" action="cart_action.php" style="display:flex; gap:6px;">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="carrito_id" value="<?php echo $item['carrito_id']; ?>">
                        <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                        <button type="submit" class="boton boton-secundario">Actualizar</button>
                    </form>
                </td>
                <td>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                <td>
                    <form method="POST" action="cart_action.php">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="carrito_id" value="<?php echo $item['carrito_id']; ?>">
                        <button type="submit" class="boton boton-peligro">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p class="resumen-total">Total: $<?php echo number_format($total, 2); ?></p>

    <div style="text-align:right; margin-top:15px;">
        <a href="index.php" class="boton boton-secundario">Seguir comprando</a>
        <a href="checkout.php" class="boton">Proceder a comprar</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
