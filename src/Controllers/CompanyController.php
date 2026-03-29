<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Services\ExternalApiService;
use App\Config\Database;

class CompanyController {

    public function consultRuc($ruc) {
        $data = ExternalApiService::consultRuc($ruc);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $model = new CompanyModel();

        if ($model->getByRuc($_POST['ruc'])) {
            header('Location: /register-company?error=ruc_exists');
            return;
        }

        if ($model->getByEmail($_POST['correo_contacto'])) {
            header('Location: /register-company?error=email_exists');
            return;
        }

        // Validate phone: exactly 9 digits
        $telefono = $_POST['telefono'] ?? '';
        if (strlen($telefono) !== 9 || !ctype_digit($telefono)) {
            header('Location: /register-company?error=phone_invalid');
            return;
        }

        if ($_POST['password'] !== $_POST['password_confirm']) {
            header('Location: /register-company?error=pass_mismatch');
            return;
        }

        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
            $dest = __DIR__ . '/../../public/uploads/logos/' . $_POST['ruc'] . '.png';
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
            move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $dest);
            $foto_perfil = '/uploads/logos/' . $_POST['ruc'] . '.png';
        }

        $data = $_POST;
        $data['foto_perfil'] = $foto_perfil;

        if ($model->create($data)) {
            header('Location: /login?reg=success');
        } else {
            header('Location: /register-company?error=db_fail');
        }
    }

    /**
     * Actualizar perfil de empresa (foto, sector, teléfono, dirección, correo)
     * POST /company/profile-update
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $companyId = $_SESSION['user_id'];
        $db = Database::getConnection();

        // Fetch current data to keep RUC for filename
        $stmt = $db->prepare("SELECT ruc, foto_perfil FROM empresas WHERE id = ?");
        $stmt->execute([$companyId]);
        $current = $stmt->fetch();

        $foto_perfil = $current['foto_perfil'];

        // Handle new logo upload
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
            $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $fileName = 'logo_' . $current['ruc'] . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../../public/uploads/logos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $uploadDir . $fileName)) {
                $foto_perfil = '/uploads/logos/' . $fileName;
                // Update session so header/sidebar update immediately
                $_SESSION['user_foto'] = $foto_perfil;
            }
        }

        $nombre_comercial = $_POST['nombre_comercial'] ?? '';
        $sector         = $_POST['sector'] ?? '';
        $correo         = $_POST['correo_contacto'] ?? '';
        $telefono       = $_POST['telefono'] ?? '';
        $direccion      = $_POST['direccion'] ?? '';

        // Validate phone: exactly 9 digits
        if (strlen($telefono) !== 9 || !ctype_digit($telefono)) {
            header('Location: /company/profile?error=phone_invalid');
            exit;
        }

        $upd = $db->prepare("UPDATE empresas 
                             SET nombre_comercial = ?, sector = ?, correo_contacto = ?, telefono = ?, direccion = ?, foto_perfil = ?
                             WHERE id = ?");
        $upd->execute([$nombre_comercial, $sector, $correo, $telefono, $direccion, $foto_perfil, $companyId]);

        $_SESSION['user_nombre'] = $nombre_comercial;

        header('Location: /company/profile?success=1');
        exit;
    }

    /**
     * Admin: Toggle empresa activo/bloqueado
     * POST /admin/empresas/toggle-status
     */
    public function toggleStatus() {
        if ($_SESSION['user_type'] !== 'admin') {
            http_response_code(403);
            return;
        }

        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $empresaId = intval($input['empresa_id'] ?? 0);
        $newStatus = $input['status'] ?? 'activo'; // 'activo' | 'bloqueado'

        if (!$empresaId) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            return;
        }

        $db = Database::getConnection();
        $upd = $db->prepare("UPDATE empresas SET estado = ? WHERE id = ?");
        $upd->execute([$newStatus, $empresaId]);

        echo json_encode(['success' => true, 'nuevo_estado' => $newStatus]);
    }

    /**
     * Admin: Update admin profile (name + password)
     * POST /admin/save-profile
     */
    public function updateAdminProfile() {
        if ($_SESSION['user_type'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $adminId = $_SESSION['user_id'];
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM administradores WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();

        $nombre = $_POST['nombre'] ?? $admin['nombre'];
        $foto   = $admin['foto_perfil'] ?? '';

        // Handle photo upload
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
            $ext      = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $fileName = 'admin_' . $adminId . '_' . time() . '.' . $ext;
            $dir      = __DIR__ . '/../../public/uploads/admins/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $dir . $fileName)) {
                $foto = '/uploads/admins/' . $fileName;
                $_SESSION['user_foto'] = $foto;
            }
        }

        // Handle password update
        $sql = "UPDATE administradores SET nombre = ?, foto_perfil = ? WHERE id = ?";
        $params = [$nombre, $foto, $adminId];

        if (!empty($_POST['new_password'])) {
            $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $sql = "UPDATE administradores SET nombre = ?, foto_perfil = ?, password_hash = ? WHERE id = ?";
            $params = [$nombre, $foto, $hash, $adminId];
        }

        $db->prepare($sql)->execute($params);
        $_SESSION['user_nombre'] = $nombre;

        header('Location: /admin/profile?success=1');
        exit;
    }
}
