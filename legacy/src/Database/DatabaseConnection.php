<?php

declare(strict_types=1);

namespace Legacy\Database;

use Legacy\Exception\DatabaseException;
use PDO;
use PDOException;

/**
 * Wrapper de conexión PDO con prepared statements.
 *
 * Reemplaza la conexión mysqli hardcodeada del código original
 * por una conexión configurable vía constructor con PDO.
 */
class DatabaseConnection
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string $host,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly int $port = 3306,
        private readonly string $charset = 'utf8mb4',
    ) {}

    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    private function connect(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->host,
            $this->port,
            $this->database,
            $this->charset
        );

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new DatabaseException(
                'No se pudo conectar a la base de datos.',
                (int) $e->getCode(),
                $e
            );
        }
    }
}
