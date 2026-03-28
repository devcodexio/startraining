<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $host = 'localhost';
    private static $db_name = 'startraining';
    private static $username = 'postgres';
    private static $password = 'root'; // Use local password
    private static $conn = null;

    public static function getConnection()
    {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "pgsql:host=" . self::$host . ";dbname=" . self::$db_name,
                    self::$username,
                    self::$password
                    );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }
            catch (PDOException $e) {
                die("Connection error: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
