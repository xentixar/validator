<?php

namespace Xentixar\Validator;

use PDO;
use PDOException;

class Database
{
    private static Database|null $instance = null;
    private PDO $connection;

    private function __construct($configurations)
    {
        try {
            $this->connection = new PDO("{$configurations['driver']}:host={$configurations['host']}:{$configurations['port']};dbname={$configurations['database']}", $configurations['username'], $configurations['password'], ['charset' => $configurations['charset'], 'collation' => $configurations['collation'], 'prefix' => $configurations['prefix']]);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    private static function getConfigurations(): array
    {
        if (file_exists(__DIR__ . "/../../../../config/vendor/validator/database.php")) {
            return require_once __DIR__ . "/../../../../config/vendor/validator/database.php";
        } else {
            return require_once __DIR__ . "/../config/database.php";
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new Database(Database::getConfigurations());
        }
        return self::$instance->connection;
    }
}

Database::getInstance();

