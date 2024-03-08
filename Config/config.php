<?php
class Config {
    private const SERVER_NAME = 'localhost';
    private const DB_NAME = 'typepick';
    private const DB_USER = 'root';
    private const DB_PASSWORD = '';
    private const CHARSET = 'utf8mb4';
    private static $tp_databaseConnection;
    
    public static function initialize() {
        self::initDatabaseConnection();
    }

    private static function initDatabaseConnection() {
        $DB_INFO = "mysql:host=" . self::SERVER_NAME . ";dbname=" . self::DB_NAME . ";charset=" . self::CHARSET;
        $options = [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        self::$tp_databaseConnection = new PDO($DB_INFO, self::DB_USER, self::DB_PASSWORD, $options);
        self::$tp_databaseConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public static function getDatabaseConnection() {
        return self::$tp_databaseConnection;
    }
}

//set_exception_handler(function ($e) {
//    error_log($e->getMessage());
//    exit("Error occurred. Please contact administration."); 
//});
?>
