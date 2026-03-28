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
        <div class="notif-btn" title="Notificaciones">
            <i class="fas fa-bell"></i>
            <span class="dot"></span>
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
