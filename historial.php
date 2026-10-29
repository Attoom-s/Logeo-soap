<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['rol'];
$userName = $_SESSION['user'];
$historialJsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'historial.json';

$historial = [];
if (file_exists($historialJsonPath) && filesize($historialJsonPath) > 0) {
    $historial = json_decode(file_get_contents($historialJsonPath), true) ?? [];
}

// Filtrar si es un usuario CLIENTE (solo ve sus propias consultas)
if ($userRole === 'CLIENTE') {
    $historial = array_filter($historial, function ($item) use ($userName) {
        return isset($item['usuario']) && $item['usuario'] === $userName;
    });
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Consultas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Historial de Búsquedas</h3>
        <span class="badge bg-info text-dark">
            <?= $userRole === 'ADMIN' ? 'Modo Auditoría (Todas las consultas)' : 'Mis Consultas' ?>
        </span>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($historial)): ?>
                <div class="alert alert-info mb-0">No hay registros en el historial de búsquedas.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Request</th>
                                <th>Usuario</th>
                                <th>Término Buscado</th>
                                <th>Categoría</th>
                                <th>Fecha Desde</th>
                                <th>Resultados</th>
                                <th>Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $item): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($item['id'] ?? 'N/A') ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['usuario'] ?? 'N/A') ?></strong>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($item['rol'] ?? '') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($item['texto'] ?: '(vacío)') ?></td>
                                    <td><?= htmlspecialchars($item['categoria'] ?: 'Todas') ?></td>
                                    <td><?= htmlspecialchars($item['fechaDesde'] ?: 'Sin filtro') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($item['resultadosEncontrados'] ?? 0) > 0 ? 'success' : 'warning' ?>">
                                            <?= htmlspecialchars($item['resultadosEncontrados'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td><small><?= htmlspecialchars($item['fechaHora'] ?? '') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>