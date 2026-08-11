<?php
/**
 * Conexão PDO única, criada sob demanda.
 * Use a função db() em qualquer lugar do backend.
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            config('db.host'),
            config('db.name'),
            config('db.charset', 'utf8mb4')
        );

        try {
            self::$pdo = new PDO($dsn, config('db.user'), config('db.pass'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            if (config('debug')) {
                die('Erro de conexão: ' . $e->getMessage());
            }
            die('Erro de conexão com o banco de dados.');
        }

        return self::$pdo;
    }
}

function db(): PDO
{
    return Database::pdo();
}
