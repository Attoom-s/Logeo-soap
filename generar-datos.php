<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit;
}

// Control estricto: solo ROL ADMIN puede acceder
if ($_SESSION['rol'] !== 'ADMIN') {
    header('Location: index.php');
    exit;
}

$userName = $_SESSION['user'];
$datosJsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'datos.json';
$errorMsg = null;
$successMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear_dato') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $fechaPublicacion = $_POST['fechaPublicacion'] ?? date('Y-m-d');

    if (!empty($titulo) && !empty($descripcion) && !empty($categoria)) {
        try {
            $baseDatos = [];
            if (file_exists($datosJsonPath) && filesize($datosJsonPath) > 0) {
                $baseDatos = json_decode(file_get_contents($datosJsonPath), true) ?? [];
            }

            $nuevoDato = [
                'id' => 'DOC-' . (count($baseDatos) + 1),
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'categoria' => $categoria,
                'fechaPublicacion' => $fechaPublicacion,
                'creadoPor' => $userName
            ];

            array_unshift($baseDatos, $nuevoDato);

            if (file_put_contents($datosJsonPath, json_encode($baseDatos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $successMsg = "Registro '" . htmlspecialchars($titulo) . "' agregado correctamente a datos.json.";
            } else {
                throw new Exception("Error al escribir permisos en datos.json.");
            }
        } catch (Exception $e) {
            $errorMsg = "Error: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Datos - Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container py-2">

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Módulo Administrativo: Registrar Datos de Prueba</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Los datos creados aquí se guardan en <code>datos.json</code> y serán leídos por el Mock Service de SoapUI.</p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="crear_dato">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="titulo" class="form-label font-weight-bold">Título del Recurso</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ej: Especificación REST vs SOAP">
                    </div>
                    <div class="col-md-4">
                        <label for="categoria" class="form-label font-weight-bold">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" required placeholder="Ej: Arquitectura">
                    </div>
                    <div class="col-md-4">
                        <label for="fechaPublicacion" class="form-label font-weight-bold">Fecha de Publicación</label>
                        <input type="date" class="form-control" id="fechaPublicacion" name="fechaPublicacion" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label for="descripcion" class="form-label font-weight-bold">Descripción / Resumen</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required placeholder="Descripción que será buscada por el servicio SOAP..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger mt-3">Guardar e Indexar Registro</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>