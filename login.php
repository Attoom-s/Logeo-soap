<?php
session_start();

$error = '';
$success = '';
$usuariosJsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'usuarios.json';

// Cargar usuarios existentes de JSON
$usuarios = [];
if (file_exists($usuariosJsonPath) && filesize($usuariosJsonPath) > 0) {
    $usuarios = json_decode(file_get_contents($usuariosJsonPath), true) ?? [];
} else {
    // Usuarios predeterminados del sistema
    $usuarios = [
        'admin' => ['password' => '1234', 'nombre' => 'Brian Cona (Admin)', 'rol' => 'ADMIN'],
        'cliente' => ['password' => '1234', 'nombre' => 'Cliente Demo', 'rol' => 'CLIENTE']
    ];
    file_put_contents($usuariosJsonPath, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// PROCESAR FORMULARIOS (LOGIN Y REGISTRO)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $user = strtolower(trim($_POST['usuario'] ?? ''));
        $pass = trim($_POST['password'] ?? '');

        if (isset($usuarios[$user]) && $usuarios[$user]['password'] === $pass) {
            $_SESSION['user'] = $usuarios[$user]['nombre'];
            $_SESSION['rol'] = $usuarios[$user]['rol'];
            $_SESSION['username'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } elseif ($action === 'registro') {
        $user = strtolower(trim($_POST['reg_usuario'] ?? ''));
        $nombre = trim($_POST['reg_nombre'] ?? '');
        $pass = trim($_POST['reg_password'] ?? '');

        if (empty($user) || empty($nombre) || empty($pass)) {
            $error = 'Todos los campos del registro son obligatorios.';
        } elseif (isset($usuarios[$user])) {
            $error = 'El nombre de usuario ya se encuentra registrado.';
        } else {
            // Guardar nuevo cliente con rol CLIENTE
            $usuarios[$user] = [
                'password' => $pass,
                'nombre' => $nombre,
                'rol' => 'CLIENTE',
                'fechaRegistro' => date('Y-m-d H:i:s')
            ];
            file_put_contents($usuariosJsonPath, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = '¡Cuenta creada con éxito! Ahora puedes iniciar sesión.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al Sistema - Buscador SOAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4 text-primary">Plataforma de Búsqueda SOAP</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2" role="alert"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success py-2" role="alert"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <!-- PESTAÑAS LOGIN / REGISTRO -->
                    <ul class="nav nav-pills nav-justified mb-3" id="authTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab">Iniciar Sesión</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab">Crear Cuenta</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <!-- FORMULARIO DE LOGIN -->
                        <div class="tab-pane fade show active" id="pills-login" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="action" value="login">
                                <div class="mb-3">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" name="usuario" class="form-control" required placeholder="Ingrese su usuario">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" name="password" class="form-control" required placeholder="Ingrese su contraseña">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Ingresar al Sistema</button>
                            </form>
                        </div>

                        <!-- FORMULARIO DE REGISTRO PARA NUEVOS CLIENTES -->
                        <div class="tab-pane fade" id="pills-register" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="action" value="registro">
                                <div class="mb-3">
                                    <label class="form-label">Nombre Completo</label>
                                    <input type="text" name="reg_nombre" class="form-control" required placeholder="Ej. Carlos Mendoza">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nombre de Usuario</label>
                                    <input type="text" name="reg_usuario" class="form-control" required placeholder="Ej. cmendoza">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" name="reg_password" class="form-control" required placeholder="Cree su contraseña">
                                </div>
                                <button type="submit" class="btn btn-success w-100">Registrar Cuenta</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>