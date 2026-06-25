<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php';

$usuarioId = $_SESSION['usuario_id'];

// Obtener items del carrito
$stmt = $pdo->prepare(
    'SELECT c.id AS carrito_id, c.cantidad, p.id AS producto_id, p.nombre, p.precio, p.stock
     FROM carrito c JOIN productos p ON c.producto_id = p.id
     WHERE c.usuario_id = ?'
);
$stmt->execute([$usuarioId]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Direcciones guardadas previamente por el usuario
$stmt = $pdo->prepare('SELECT * FROM direcciones_envio WHERE usuario_id = ?');
$stmt->execute([$usuarioId]);
$direcciones = $stmt->fetchAll();

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calle    = trim($_POST['calle'] ?? '');
    $numero   = trim($_POST['numero'] ?? '');
    $colonia  = trim($_POST['colonia'] ?? '');
    $ciudad   = trim($_POST['ciudad'] ?? '');
    $estado   = trim($_POST['estado'] ?? '');
    $cp       = trim($_POST['codigo_postal'] ?? '');
    $telefono = trim($_POST['telefono_contacto'] ?? '');

    if ($calle === '' || $ciudad === '' || $estado === '' || $cp === '') {
        $errores[] = 'Completa los datos obligatorios de la dirección de envío.';
    }

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            // 1. Revalidar stock disponible al momento de confirmar
            foreach ($items as $item) {
                $stmt = $pdo->prepare('SELECT stock FROM productos WHERE id = ? FOR UPDATE');
                $stmt->execute([$item['producto_id']]);
                $stockActual = $stmt->fetch()['stock'];
                if ($stockActual < $item['cantidad']) {
                    throw new Exception('El producto "' . $item['nombre'] . '" ya no tiene existencias suficientes.');
                }
            }

            // 2. Insertar dirección de envío
            $stmt = $pdo->prepare(
                'INSERT INTO direcciones_envio (usuario_id, calle, numero, colonia, ciudad, estado, codigo_postal, telefono_contacto)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$usuarioId, $calle, $numero, $colonia, $ciudad, $estado, $cp, $telefono]);
            $direccionId = $pdo->lastInsertId();

            // 3. Crear el pedido
            $stmt = $pdo->prepare(
                'INSERT INTO pedidos (usuario_id, direccion_envio_id, total, estado) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$usuarioId, $direccionId, $total, 'confirmado']);
            $pedidoId = $pdo->lastInsertId();

            // 4. Insertar detalle del pedido y actualizar inventario
            foreach ($items as $item) {
                $stmt = $pdo->prepare(
                    'INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$pedidoId, $item['producto_id'], $item['cantidad'], $item['precio']]);

                // Actualización de existencias del producto
                $stmt = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
                $stmt->execute([$item['cantidad'], $item['producto_id']]);
            }

            // 5. Vaciar el carrito del usuario
            $stmt = $pdo->prepare('DELETE FROM carrito WHERE usuario_id = ?');
            $stmt->execute([$usuarioId]);

            $pdo->commit();
            header('Location: order_confirmation.php?id=' . $pedidoId);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errores[] = 'No se pudo procesar la compra: ' . $e->getMessage();
        }
    }
}

$tituloPagina = 'Finalizar compra';
include 'includes/header.php';
?>

<h1>Finalizar compra</h1>

<?php foreach ($errores as $error): ?>
    <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
<?php endforeach; ?>

<div class="resumen-pedido">
    <h3>Resumen del pedido</h3>
    <table>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nombre']); ?> x <?php echo $item['cantidad']; ?></td>
                <td style="text-align:right;">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>Total</strong></td>
            <td style="text-align:right;"><strong>$<?php echo number_format($total, 2); ?></strong></td>
        </tr>
    </table>
</div>

<div class="tarjeta-formulario">
    <h2>Dirección de envío</h2>
    <form method="POST" action="checkout.php">
        <div class="campo">
            <label for="calle">Calle *</label>
            <input type="text" id="calle" name="calle" value="<?php echo htmlspecialchars($_POST['calle'] ?? ($direcciones[0]['calle'] ?? '')); ?>" required>
        </div>
        <div class="campo">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($_POST['numero'] ?? ($direcciones[0]['numero'] ?? '')); ?>">
        </div>
        <div class="campo">
            <label for="colonia">Colonia</label>
            <input type="text" id="colonia" name="colonia" value="<?php echo htmlspecialchars($_POST['colonia'] ?? ($direcciones[0]['colonia'] ?? '')); ?>">
        </div>
        <div class="campo">
            <label for="ciudad">Ciudad *</label>
            <input type="text" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($_POST['ciudad'] ?? ($direcciones[0]['ciudad'] ?? '')); ?>" required>
        </div>
        <div class="campo">
            <label for="estado">Estado *</label>
            <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($_POST['estado'] ?? ($direcciones[0]['estado'] ?? '')); ?>" required>
        </div>
        <div class="campo">
            <label for="codigo_postal">Código Postal *</label>
            <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($_POST['codigo_postal'] ?? ($direcciones[0]['codigo_postal'] ?? '')); ?>" required>
        </div>
        <div class="campo">
            <label for="telefono_contacto">Teléfono de contacto</label>
            <input type="text" id="telefono_contacto" name="telefono_contacto" value="<?php echo htmlspecialchars($_POST['telefono_contacto'] ?? ($direcciones[0]['telefono_contacto'] ?? '')); ?>">
        </div>
        <button type="submit" class="boton boton-ancho">Confirmar compra</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
