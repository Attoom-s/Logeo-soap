<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('soap.wsdl_cache_enabled', 0);

$wsdlPath = __DIR__ . DIRECTORY_SEPARATOR . 'bus-busqueda.wsdl';
$historialJsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'historial.json';

$dashboardData = null;
$searchResults = null;
$errorMsg = null;
$userRole = $_SESSION['rol'];
$userName = $_SESSION['user'];

// CONEXIÓN Y CONSUMO SOAP
try {
    if (!file_exists($wsdlPath)) {
        throw new Exception("No se encuentra el archivo WSDL.");
    }

    $client = new SoapClient($wsdlPath, [
        'location' => 'http://127.0.0.1:8088/bus-busqueda',
        'trace' => 1,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'connection_timeout' => 5
    ]);

    // BÚSQUEDA Y REGISTRO EN HISTORIAL
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buscar') {
        try {
            $texto = trim($_POST['texto'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $fechaDesde = $_POST['fechaDesde'] ?? '';

            $params = [
                'texto' => $texto,
                'categoria' => $categoria,
                'fechaDesde' => $fechaDesde,
                'usuario' => $userName
            ];

            // Petición SOAP
            $searchResults = $client->buscar($params);

            // Guardar automáticamente la consulta en historial.json
            $historial = [];
            if (file_exists($historialJsonPath) && filesize($historialJsonPath) > 0) {
                $historial = json_decode(file_get_contents($historialJsonPath), true) ?? [];
            }

            $nuevaConsulta = [
                'id' => $searchResults->consultaId ?? ('REQ-' . time()),
                'usuario' => $userName,
                'rol' => $userRole,
                'texto' => $texto,
                'categoria' => $categoria,
                'fechaDesde' => $fechaDesde,
                'resultadosEncontrados' => $searchResults->cantidad ?? 0,
                'fechaHora' => date('Y-m-d H:i:s')
            ];

            array_unshift($historial, $nuevaConsulta);
            file_put_contents($historialJsonPath, json_encode($historial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        } catch (Exception $e) {
            $errorMsg = "Error en la consulta SOAP: " . $e->getMessage();
        }
    }

    // DASHBOARD (Métricas solo para ADMIN)
    if ($userRole === 'ADMIN') {
        try {
            $dashboardData = $client->obtenerDashboard(['rol' => $userRole]);
        } catch (Exception $e) {
            // Ignorar si el servicio Mock no responde
        }
    }

} catch (Exception $e) {
    $errorMsg = "Error con el cliente SOAP: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Buscador SOAP</title>
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

    <!-- TARJETAS DE AUDITORÍA/MÉTRICAS (ADMIN) -->
    <?php if ($userRole === 'ADMIN' && $dashboardData): ?>
        <h4 class="mb-3 text-secondary">Métricas Generales</h4>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Consultas Activas</h6>
                        <p class="card-text fs-2"><?= htmlspecialchars($dashboardData->totalConsultasActivas ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Término Más Buscado</h6>
                        <p class="card-text fs-2"><?= htmlspecialchars($dashboardData->terminoMasBuscado ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Categoría Más Buscada</h6>
                        <p class="card-text fs-2"><?= htmlspecialchars($dashboardData->categoriaMasBuscada ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- MÓDULO DE BÚSQUEDA -->
    <div class="card mb-4 shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Consulta de Información</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="buscar">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="texto" class="form-label">Texto o Término</label>
                        <input type="text" class="form-control" id="texto" name="texto" required placeholder="Ej: software">
                    </div>
                    <div class="col-md-4">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" placeholder="Ej: Ingeniería">
                    </div>
                    <div class="col-md-4">
                        <label for="fechaDesde" class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="fechaDesde" name="fechaDesde">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Buscar en Servicio SOAP</button>
            </form>
        </div>
    </div>

    <!-- RESULTADOS -->
    <?php if ($searchResults): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span>Resultados de la Búsqueda</span>
                <span class="badge bg-secondary">ID Consulta: <?= htmlspecialchars($searchResults->consultaId ?? '') ?></span>
            </div>
            <div class="card-body">
                <p><strong>Total encontrados:</strong> <?= htmlspecialchars($searchResults->cantidad ?? 0) ?></p>
                
                <?php 
                $lista = $searchResults->resultados->resultado ?? [];
                if (!is_array($lista) && $lista) $lista = [$lista];

                if (empty($lista)): ?>
                    <div class="alert alert-warning mb-0">No se encontraron coincidencias.</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($lista as $doc): ?>
                            <div class="list-group-item">
                                <h5 class="mb-1 text-primary"><?= htmlspecialchars($doc->titulo ?? '') ?></h5>
                                <p class="mb-1"><?= htmlspecialchars($doc->descripcion ?? '') ?></p>
                                <small class="text-muted">Categoría: <?= htmlspecialchars($doc->categoria ?? '') ?> | Fecha: <?= htmlspecialchars($doc->fechaPublicacion ?? '') ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>