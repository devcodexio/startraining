<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $conn = null;

    public static function getConnection()
    {
        if (self::$conn === null) {
            try {
                // 🔹 Variables de entorno (Render)
                $host = getenv("DB_HOST");
                $db   = getenv("DB_NAME");
                $user = getenv("DB_USER");
                $pass = getenv("DB_PASS");
                $port = getenv("DB_PORT") ?: 5432;

                self::$conn = new PDO(
                    "pgsql:host=$host;port=$port;dbname=$db",
                    $user,
                    $pass
                );

                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                die("Connection error: " . $e->getMessage());
            }
        }

        return self::$conn;
    }
}
