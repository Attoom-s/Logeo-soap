<?php
// navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userRole = $_SESSION['rol'] ?? 'CLIENTE';
$userName = $_SESSION['user'] ?? 'Usuario';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="index.php">
            <i class="bi bi-search"></i> Buscador SOAP
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active font-weight-bold' : '' ?>" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'historial.php' ? 'active font-weight-bold' : '' ?>" href="historial.php">Historial de Consultas</a>
                </li>
                <?php if ($userRole === 'ADMIN'): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning <?= $currentPage === 'generar-datos.php' ? 'active font-weight-bold' : '' ?>" href="generar-datos.php">
                            ⚙️ Generar Datos (Admin)
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">
                    Hola, <strong><?= htmlspecialchars($userName) ?></strong>
                    <span class="badge bg-<?= $userRole === 'ADMIN' ? 'danger' : 'primary' ?>"><?= $userRole ?></span>
                </span>
                <a href="index.php?logout=1" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</nav>