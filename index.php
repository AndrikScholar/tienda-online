<?php
require_once 'config/db.php';

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();

// Parámetros de consulta (diferentes formas de ver/seleccionar productos)
$categoriaId = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
$busqueda    = trim($_GET['q'] ?? '');
$orden       = $_GET['orden'] ?? 'recientes';

$sql = 'SELECT p.*, c.nombre AS categoria_nombre FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1';
$parametros = [];

if ($categoriaId > 0) {
    $sql .= ' AND p.categoria_id = ?';
    $parametros[] = $categoriaId;
}
if ($busqueda !== '') {
    $sql .= ' AND p.nombre LIKE ?';
    $parametros[] = '%' . $busqueda . '%';
}

switch ($orden) {
    case 'precio_asc':  $sql .= ' ORDER BY p.precio ASC';  break;
    case 'precio_desc': $sql .= ' ORDER BY p.precio DESC'; break;
    case 'nombre':      $sql .= ' ORDER BY p.nombre ASC';  break;
    default:            $sql .= ' ORDER BY p.fecha_creacion DESC';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$productos = $stmt->fetchAll();

$tituloPagina = 'Catálogo';
include 'includes/header.php';

if (isset($_GET['agregado'])) {
    echo '<div class="alerta alerta-exito">Producto agregado al carrito.</div>';
}
?>

<h1>Catálogo de productos</h1>

<form method="GET" action="index.php" class="filtros">
    <input type="text" name="q" placeholder="Buscar producto..." value="<?php echo htmlspecialchars($busqueda); ?>">

    <select name="categoria">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categorias as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo $categoriaId == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['nombre']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="orden">
        <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
        <option value="nombre" <?php echo $orden === 'nombre' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
        <option value="precio_asc" <?php echo $orden === 'precio_asc' ? 'selected' : ''; ?>>Precio: menor a mayor</option>
        <option value="precio_desc" <?php echo $orden === 'precio_desc' ? 'selected' : ''; ?>>Precio: mayor a menor</option>
    </select>

    <button type="submit" class="boton">Filtrar</button>
</form>

<?php if (empty($productos)): ?>
    <p>No se encontraron productos con los filtros seleccionados.</p>
<?php else: ?>
    <div class="rejilla-productos">
        <?php foreach ($productos as $producto): ?>
            <div class="tarjeta-producto">
                <div class="img-placeholder">🛍️</div>
                <div class="info">
                    <span class="categoria"><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></span>
                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                    <p class="precio">$<?php echo number_format($producto['precio'], 2); ?></p>
                    <?php if ($producto['stock'] > 0): ?>
                        <p class="stock">Disponible: <?php echo $producto['stock']; ?> piezas</p>
                    <?php else: ?>
                        <p class="stock agotado">Agotado</p>
                    <?php endif; ?>
                    <div class="acciones">
                        <a href="product.php?id=<?php echo $producto['id']; ?>" class="boton boton-ancho">Ver detalle</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
