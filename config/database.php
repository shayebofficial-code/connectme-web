<?php
/**
 * ConnectMe - Database Connection (PDO Singleton)
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            // Cloud DB Compatibility (Aiven, Render, PlanetScale)
            if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                die("<div style='font-family:sans-serif; padding:40px 20px; background:#0f172a; color:#f8fafc; text-align:center;'>
                    <h2 style='color:#ef4444; margin-bottom:12px;'>⚠️ Database Connection Error</h2>
                    <p style='color:#94a3b8; margin-bottom:16px;'>Unable to connect to MySQL database at <strong>" . htmlspecialchars(DB_HOST) . "</strong> (Port: " . htmlspecialchars(DB_PORT) . ").</p>
                    <div style='background:#1e293b; border:1px solid #334155; padding:12px 16px; border-radius:8px; display:inline-block; text-align:left; font-family:monospace; font-size:0.85rem; color:#fde68a; margin-bottom:20px;'>
                        " . htmlspecialchars($e->getMessage()) . "
                    </div>
                    <p style='font-size:0.85rem; color:#64748b;'>Please verify your Render Environment Variables (DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT).</p>
                </div>");
            }
        }

        return self::$instance;
    }
}

function getDB(): PDO {
    return Database::getInstance();
}
