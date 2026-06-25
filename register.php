<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($nombre === '' || $apellido === '' || $email === '' || $password === '') {
        $errores[] = 'Todos los campos obligatorios deben llenarse.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }
    if (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($password !== $password2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        // Verificar si el correo ya existe
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe una cuenta registrada con ese correo.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, apellido, email, password_hash, telefono) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$nombre, $apellido, $email, $hash, $telefono]);
            $exito = true;
        }
    }
}

$tituloPagina = 'Registro';
include 'includes/header.php';
?>

<div class="tarjeta-formulario">
    <h2>Crear cuenta</h2>

    <?php if ($exito): ?>
        <div class="alerta alerta-exito">
            ¡Cuenta creada exitosamente! Ya puedes <a href="login.php">iniciar sesión</a>.
        </div>
    <?php else: ?>

        <?php foreach ($errores as $error): ?>
            <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>

        <form method="POST" action="register.php">
            <div class="campo">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
            </div>
            <div class="campo">
                <label for="apellido">Apellido *</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
            </div>
            <div class="campo">
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="campo">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
            </div>
            <div class="campo">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="campo">
                <label for="password2">Confirmar contraseña *</label>
                <input type="password" id="password2" name="password2" required>
            </div>
            <button type="submit" class="boton boton-ancho">Registrarme</button>
        </form>

        <p class="enlace-formulario">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
