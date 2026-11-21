<?php
// Keep actual database settings outside Apache's document root.
$configDirectory = getenv('DISCOVER_SAUDI_CONFIG_DIR') ?: 'C:/xampp/apache/conf/setup';
$configFile = $configDirectory . '/discover-saudi-db.php';
try {
    if (!is_readable($configFile)) {
        throw new RuntimeException('Database configuration unavailable.');
    }

    $settings = require $configFile;
    if (!is_array($settings) || !isset(
        $settings['host'], $settings['port'], $settings['database'], $settings['username'], $settings['password']
    ) || $settings['password'] === 'REPLACE_WITH_YOUR_LOCAL_PASSWORD') {
        throw new RuntimeException('Database configuration incomplete.');
    }

    $dsn = 'mysql:host=' . $settings['host'] . ';port=' . (int) $settings['port']
         . ';dbname=' . $settings['database'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $settings['username'], $settings['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (RuntimeException $e) {
    http_response_code(503);
    exit('Database connection failed.');
} finally {
    unset($settings);
}



