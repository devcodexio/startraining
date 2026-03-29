<?php
use App\Models\VacancyModel;
$vacancyModel = new VacancyModel();
$search = $_GET['search'] ?? '';
$carrera = $_GET['carrera'] ?? '';
$modalidad = $_GET['modalidad'] ?? '';
$vacancies = $vacancyModel->getAll(['search' => $search, 'carrera' => $carrera, 'modalidad' => $modalidad]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StarTraining | Encuentra tus Prácticas</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .hero { padding: 8rem 0 4rem; text-align: center; }
        .hero h1 { font-size: 5rem; margin-bottom: 1.5rem; }
        .hero p { font-size: 1.5rem; color: var(--text-muted); max-width: 800px; margin: 0 auto 3rem; }
        .search-hero { max-width: 800px; margin: 0 auto; padding: 0.5rem; border-radius: 50px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); display: flex; gap: 0.5rem; }
        .search-hero input { background: transparent; border: none; padding: 1rem 2rem; color: #fff; flex: 1; outline: none; font-size: 1.1rem; }
        .vacancy-card { display: flex; gap: 2.5rem; padding: 2.5rem; border-radius: 30px; margin-bottom: 2rem; }
        .vacancy-logo { width: 90px; height: 90px; border-radius: 24px; background: rgba(255,255,255,0.02); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--glass-border); }
        .vacancy-logo img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body class="animate">
    <nav class="glass animate" style="position: fixed; top: 1.5rem; left: 1.5rem; right: 1.5rem; height: 85px; display: flex; align-items: center; justify-content: space-between; padding: 0 4rem; z-index: 1000; border-radius: 30px; border: 1px solid var(--border-glass);">
        <h2 class="logo-text m-0" style="font-size: 2rem;">StarTraining</h2>
        <div class="d-flex align-items-center gap-5">
            <a href="/login" class="nav-item m-0 fw-800" style="background: transparent; color: var(--text-primary); text-decoration: none; font-size: 1.1rem; letter-spacing: 1px;">LOGIN</a>
            <a href="/register-company" class="btn-futuristic" style="padding: 0.8rem 2rem; font-size: 0.9rem;">REGISTRARSE &rarr;</a>
        </div>
    </nav>


    <header class="hero animate">
        <h1 class="fw-800"><span class="text-gradient">El impulso que tu carrera</span><br>necesita está aquí.</h1>
        <p>Conectamos a estudiantes estrella con las empresas más innovadoras del Perú.</p>
        
        <form action="/" method="GET" class="search-hero">
            <input type="text" name="search" placeholder="Buscar por puesto o empresa..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-premium px-5" style="border-radius: 40px;">Buscar Ahora</button>
        </form>
    </header>

    <div class="container" style="max-width: 1100px; margin: 0 auto 10rem; padding: 0 2rem;">
        <div class="mb-5 d-flex justify-content-between align-items-end">
            <div>
                <h2 class="fs-1">Vacantes Recientes</h2>
                <p class="text-muted">Explora las últimas oportunidades de prácticas pre-profesionales.</p>
            </div>
            <span class="text-muted small fw-600"><?= count($vacancies) ?> Convocatorias encontradas</span>
        </div>

        <?php foreach ($vacancies as $v): ?>
            <div class="glass-card vacancy-card animate">
                <div class="vacancy-logo">
                    <img src="<?= $v['foto_perfil'] ?: 'https://placehold.co/100x100/06070a/00f2fe?text=LOGO' ?>" alt="Logo">
                </div>
                <div class="flex-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h3 class="fs-2 mb-2"><?= htmlspecialchars($v['titulo_puesto']) ?></h3>
                        <span class="badge" style="background: rgba(0, 242, 254, 0.1); color: var(--primary); font-size: 0.8rem; padding: 6px 15px; border-radius: 20px;"><?= $v['modalidad'] ?></span>
                    </div>
                    <p class="text-muted fs-5 mb-4"><?= htmlspecialchars($v['nombre_comercial']) ?> • <span class="text-primary"><?= htmlspecialchars($v['carrera']) ?></span></p>
                    <p class="text-muted line-clamp-2"><?= htmlspecialchars($v['descripcion_puesto']) ?></p>
                    
                    <div class="mt-5 pt-4 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i> <?= htmlspecialchars($v['ubicacion'] ?: 'Remoto / Perú') ?>
                        </div>
                        <a href="/vacante/<?= $v['id'] ?>" class="btn-premium px-5">Ver Detalles &rarr;</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php require_once __DIR__ . '/../Layouts/Footer.php'; ?>
</body>
</html>
