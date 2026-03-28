<?php

namespace App\Middleware;

class AuthMiddleware {
    
    public static function check() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }

    public static function isAdmin() {
        self::check();
        if ($_SESSION['user_type'] !== 'admin') {
            header('Location: /403');
            exit();
        }
    }

    public static function isEmpresa() {
        self::check();
        if ($_SESSION['user_type'] !== 'empresa') {
            header('Location: /403');
            exit();
        }
    }

    public static function guestOnly() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit();
        }
    }
}
