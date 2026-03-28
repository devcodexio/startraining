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
                $url = getenv("DATABASE_URL");

                if (!$url) {
                    die("DATABASE_URL no está configurada");
                }

                $db = parse_url($url);

                self::$conn = new PDO(
                    "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db['path'], '/'),
                    $db['user'],
                    $db['pass']
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
