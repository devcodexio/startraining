<?php
use App\Models\ConfigModel;
$configModel = new ConfigModel();
$siteName = $configModel->getSiteName();
?>
<footer class="footer mt-5 p-4 glass glass-border text-center">
    <div class="footer-content">
        <p class="text-secondary opacity-50 mb-2">© <?= date('Y') ?> <?= $siteName ?>. Todos los derechos reservados.</p>
        <div class="social-links mt-3 d-flex justify-content-center gap-4">
            <a href="#" class="text-secondary hover-primary transition"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="text-secondary hover-primary transition"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-secondary hover-primary transition"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="text-secondary hover-primary transition"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>
<style>
.hover-primary:hover {
    color: var(--primary) !important;
    transform: translateY(-3px);
}

.transition {
    transition: var(--transition);
}

.footer {
    border-radius: 20px 20px 0 0;
    margin-left: 280px;
    padding: 3rem 1.5rem;
}
</style>
