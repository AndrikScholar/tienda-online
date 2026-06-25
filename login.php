<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya hay sesión activa, redirigir al catálogo
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errores[] = 'Debes ingresar correo y contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nombre, apellido, password_hash FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
            header('Location: index.php');
            exit;
        } else {
            $errores[] = 'Correo o contraseña incorrectos.';
        }
    }
}

$tituloPagina = 'Iniciar sesión';
include 'includes/header.php';
?>

<div class="tarjeta-formulario">
    <h2>Iniciar sesión</h2>

    <?php if (isset($_GET['redirigido'])): ?>
        <div class="alerta alerta-error">Debes iniciar sesión para continuar.</div>
    <?php endif; ?>

    <?php foreach ($errores as $error): ?>
        <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; ?>

    <form method="POST" action="login.php">
        <div class="campo">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="campo">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="boton boton-ancho">Entrar</button>
    </form>

    <p class="enlace-formulario">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
</div>

<?php include 'includes/footer.php'; ?>
