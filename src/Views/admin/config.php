<?php
use App\Models\ConfigModel;
$configModel = new ConfigModel();
$config = $configModel->getSettings();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Seguridad | Admin StarTraining</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="animate">
    <?php require_once __DIR__ . '/../../Layouts/Sidebar.php'; ?>
    <?php require_once __DIR__ . '/../../Layouts/Header.php'; ?>

    <main class="main-content">
        <header class="mb-5">
            <h1 class="text-gradient">Configuración Global</h1>
            <p class="text-muted">Parámetros de seguridad y comportamiento de la plataforma.</p>
        </header>

        <div class="row d-flex gap-4">
            <div class="col-8">
                <div class="glass-card mb-4 animate" style="padding: 3rem;">
                    <h3 class="fw-800 mb-4 d-flex align-items-center gap-3">
                        <i class="fas fa-cog text-primary"></i> Ajustes Generales
                    </h3>
                    <form action="/admin/save-config" method="POST">
                        <div class="form-group mb-4">
                            <label class="mb-2 text-muted small fw-bold">NOMBRE DE LA PLATAFORMA</label>
                            <input type="text" name="nombre_sitio" class="form-input" value="<?= htmlspecialchars($config['nombre_sitio'] ?? 'StarTraining') ?>">
                            <p class="text-muted small mt-2">Este nombre aparecerá en el Sidebar y títulos de página.</p>
                        </div>
                        <button type="submit" class="btn-futuristic px-4 py-2">Guardar Cambios</button>
                    </form>
                </div>

                <div class="glass-card animate" style="padding: 4rem;">
                    <h3 class="fw-800 mb-5 d-flex align-items-center gap-3">
                        <i class="fas fa-shield-alt text-primary"></i> Seguridad & Mantenimiento
                    </h3>


                    <form action="/admin/save-config" method="POST">
                        <input type="hidden" name="modo_mantenimiento" id="maintInput" value="<?= $config['modo_mantenimiento'] ?? 'off' ?>">
                        <div class="form-group mb-5">
                            <label class="mb-2 text-muted small fw-bold">MODO MANTENIMIENTO</label>
                            <div class="d-flex align-items-center gap-5 p-4" style="background: rgba(255,255,255,0.02); border-radius: 20px; border: 1px solid var(--border-glass); cursor: pointer;" onclick="toggleMaint()">
                                <div class="flex-1">
                                    <p class="fw-800 mb-0">Desactivar temporalmente el acceso público</p>
                                    <p class="text-muted small mb-0">Solo los administradores podrán iniciar sesión.</p>
                                </div>
                                <div id="maintVisualBtn" class="theme-switch m-0 <?= ($config['modo_mantenimiento'] ?? 'off') === 'on' ? 'active' : '' ?>" style="width: 60px; height: 30px; position: relative; border-radius: 20px; background: rgba(255,255,255,0.1);">
                                     <div id="maintCircle" style="width: 24px; height: 24px; background: var(--primary); border-radius: 50%; position: absolute; top: 3px; left: <?= ($config['modo_mantenimiento'] ?? 'off') === 'on' ? '33px' : '3px' ?>; transition: 0.3s; box-shadow: 0 0 10px var(--primary);"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-5">
                            <label class="mb-2 text-muted small fw-bold">MENSAJE DE BLOQUEO</label>
                            <input type="text" name="mantenimiento_msg" class="form-input" value="<?= htmlspecialchars($config['mantenimiento_msg'] ?? 'Estamos en mantenimiento. Volveremos pronto.') ?>">
                        </div>

                        <div class="d-flex justify-content-end p-4 pt-5">
                            <button type="submit" class="btn-futuristic px-5 py-3 fs-5">
                                <i class="fas fa-save me-2"></i> Guardar Seguridad
                            </button>
                        </div>
                    </form>

                    <script>
                        function toggleMaint() {
                            const input = document.getElementById('maintInput');
                            const circle = document.getElementById('maintCircle');
                            const btn = document.getElementById('maintVisualBtn');
                            if (input.value === 'on') {
                                input.value = 'off';
                                circle.style.left = '3px';
                                btn.style.background = 'rgba(255,255,255,0.1)';
                            } else {
                                input.value = 'on';
                                circle.style.left = '33px';
                                btn.style.background = 'rgba(var(--primary-rgb), 0.2)';
                            }
                        }
                    </script>

                </div>
            </div>

            <div class="col">
                <div class="glass-card animate" style="padding: 3rem;">
                    <h4 class="fw-800 mb-4">Estado del Sistema</h4>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Versión DB</span>
                            <span class="fw-bold">v2.1.0-CYBER</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">PHP Engine</span>
                            <span class="fw-bold">v8.2.1-MVC</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary border-opacity-10">
                            <span class="text-muted small">SSL Cert</span>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 10px; padding: 4px 10px;">VALID</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
