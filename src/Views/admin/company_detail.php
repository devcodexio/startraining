<?php
use App\Config\Database;
$id = $matches[1] ?? 0;
$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    header('Location: /admin/empresas');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($c['nombre_comercial']) ?> | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="animate">
    <?php require_once __DIR__ . '/../../Layouts/Sidebar.php'; ?>
    <?php require_once __DIR__ . '/../../Layouts/Header.php'; ?>

    <main class="main-content">
        <header class="mb-5 d-flex align-items-center gap-4">
            <a href="/admin/empresas" class="btn-futuristic p-3" style="border-radius: 15px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border-glass);">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-gradient mb-0"><?= htmlspecialchars($c['nombre_comercial']) ?></h1>
                <p class="text-muted small fw-bold">ID EMPRESA: #<?= $c['id'] ?></p>
            </div>
        </header>

        <div class="row d-flex gap-4">
            <div class="col-4">
                <div class="glass-card text-center p-5">
                    <div class="mx-auto mb-4" style="width: 150px; height: 150px; border-radius: 30px; border: 3px solid var(--primary); overflow: hidden; background: #000; box-shadow: var(--shadow-neon);">
                        <img src="<?= $c['foto_perfil'] ?: 'https://ui-avatars.com/api/?name='.urlencode($c['nombre_comercial']).'&background=00f2fe&color=000&size=200'; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <h3 class="fw-800 mb-2"><?= htmlspecialchars($c['nombre_comercial']) ?></h3>
                    <p class="text-primary small fw-bold mb-4 ls-2">RUC: <?= $c['ruc'] ?></p>
                    <div class="d-flex justify-content-center gap-2">
                         <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); padding: 8px 15px; border-radius: 10px;">CUENTA ACTIVA</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="glass-card p-5">
                    <h4 class="fw-800 mb-5 border-bottom border-secondary border-opacity-10 pb-3">Información Corporativa</h4>
                    <div class="row mb-5">
                        <div class="col">
                            <label class="text-muted xsmall fw-bold mb-2">SECTOR</label>
                            <p class="fw-600 fs-5"><?= htmlspecialchars($c['sector'] ?: 'No especificado') ?></p>
                        </div>
                        <div class="col">
                            <label class="text-muted xsmall fw-bold mb-2">TELÉFONO</label>
                            <p class="fw-600 fs-5"><?= $c['telefono'] ?: 'Pendiente' ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label class="text-muted xsmall fw-bold mb-2">DIRECCIÓN FISCAL</label>
                            <p class="text-muted fs-5"><?= htmlspecialchars($c['direccion'] ?: 'No registrada') ?></p>
                        </div>
                        <div class="col text-end">
                             <button class="btn-futuristic" onclick="StarAlert.show('Aviso', 'Función de edición administrativa próximamente', 'info')">
                                 <i class="fas fa-edit me-2"></i> Editar Entidad
                             </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
