<?php
$userType   = $_SESSION['user_type'] ?? 'empresa';
$userName   = $_SESSION['user_nombre'] ?? 'Invitado';
$profileImg = $_SESSION['user_foto'] ?? '';
if ($profileImg && strpos($profileImg, 'http') === false && strpos($profileImg, '/') !== 0) {
    $profileImg = '/' . $profileImg;
}
$pageTitle = 'Panel de Control';
// Get current page title from context
$uriParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$pageTitles = [
    'dashboard'   => 'Dashboard',
    'vacancies'   => 'Convocatorias',
    'postulations'=> 'Candidatos',
    'profile'     => 'Mi Perfil',
    'config'      => 'Configuración',
    'empresas'    => 'Empresas',
    'admin'       => 'Administración',
];
if (!empty($uriParts[0]) && isset($pageTitles[$uriParts[0]])) {
    $pageTitle = $pageTitles[$uriParts[0]];
} elseif (count($uriParts) > 1 && isset($pageTitles[end($uriParts)])) {
    $pageTitle = $pageTitles[end($uriParts)];
}

$avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=3b82f6&color=fff&size=96';
$imgSrc = $profileImg ?: $avatarFallback;

// Fetch Notifications
use App\Config\Database;
$db = Database::getConnection();
$notifs = [];
if ($userType === 'empresa') {
    $stmt = $db->prepare("SELECT p.nombre_completo as titulo, v.titulo_puesto as sub, p.fecha_postulacion as fecha 
                           FROM postulaciones p 
                           JOIN vacantes v ON p.vacante_id = v.id 
                           WHERE v.empresa_id = ? 
                           ORDER BY p.fecha_postulacion DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $notifs = $stmt->fetchAll();
} else {
    $stmt = $db->prepare("SELECT nombre_comercial as titulo, ruc as sub, creado_en as fecha 
                           FROM empresas 
                           ORDER BY creado_en DESC LIMIT 5");
    $stmt->execute();
    $notifs = $stmt->fetchAll();
}
?>
<header class="top-header" id="topHeader">
    <!-- Left: Hamburger + Page title + greeting -->
    <div class="d-flex align-items-center gap-3">
        <div class="menu-trigger" id="sidebarToggle" title="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </div>
        <div style="line-height: 1.15;">
            <div style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.3px;">
                Hola, <?= htmlspecialchars(explode(' ', $userName)[0]) ?>! 👋
            </div>
            <div style="font-size: 0.72rem; font-weight: 500; color: var(--text-secondary);">
                Bienvenido a tu espacio de trabajo
            </div>
        </div>
    </div>

    <!-- Right: Actions -->
    <div class="d-flex align-items-center gap-2">
        <!-- Theme Toggle -->
        <button id="theme-toggle" class="theme-toggle-btn" title="Cambiar tema">
            <i class="fas fa-sun"></i>
        </button>

        <!-- Notification Bell -->
        <div class="notif-btn" title="Notificaciones" id="notifBell">
            <i class="fas fa-bell"></i>
            <?php if (!empty($notifs)): ?><span class="dot"></span><?php endif; ?>
            
            <!-- Notif Dropdown -->
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notificaciones Recientes</span>
                    <span class="badge badge-primary"><?= count($notifs) ?></span>
                </div>
                <div class="notif-body">
                    <?php if (empty($notifs)): ?>
                        <div class="p-4 text-center text-muted xsmall">No hay notificaciones nuevas</div>
                    <?php else: ?>
                        <?php foreach ($notifs as $n): ?>
                            <div class="notif-item">
                                <div class="notif-icon"><i class="fas <?= $userType==='admin' ? 'fa-building' : 'fa-user-plus' ?>"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= htmlspecialchars($n['titulo']) ?></div>
                                    <div class="notif-sub"><?= htmlspecialchars($n['sub']) ?></div>
                                    <div class="notif-time"><?= date('d/m H:i', strtotime($n['fecha'])) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($userType === 'empresa'): ?>
                    <a href="/postulations" class="notif-footer">Ver todas las postulaciones</a>
                <?php else: ?>
                    <a href="/admin/empresas" class="notif-footer">Ver todas las empresas</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Chip -->
        <a href="<?= $userType === 'admin' ? '/admin/profile' : '/company/profile' ?>"
           class="profile-chip" style="color: inherit;">
            <img src="<?= $imgSrc ?>"
                 onerror="this.src='<?= $avatarFallback ?>'"
                 alt="<?= htmlspecialchars($userName) ?>"
                 class="profile-chip-img">
            <div>
                <div class="profile-chip-name"><?= htmlspecialchars($userName) ?></div>
                <div class="profile-chip-role"><?= ucfirst($userType) ?> <i class="fas fa-chevron-down" style="font-size:0.5rem; opacity:0.6;"></i></div>
            </div>
        </a>
    </div>
</header>

<script>
// Toggle Notifications
document.getElementById('notifBell').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('notifDropdown').classList.toggle('show');
});
document.addEventListener('click', () => {
    document.getElementById('notifDropdown').classList.remove('show');
});
document.getElementById('notifDropdown').addEventListener('click', e => e.stopPropagation());
</script>
