<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        require_once __DIR__ . '/config.php';

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Em um ambiente de produção, você não deve exibir o erro diretamente.
            // Logue o erro e mostre uma mensagem genérica ao usuário.
            error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
            // http_response_code(500); // Internal Server Error
            // die('Erro interno do servidor. Por favor, tente novamente mais tarde.');
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Retorna a instância única da conexão PDO (Singleton Pattern).
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    // Previne a clonagem da instância
    private function __clone() {}

    // Previne a desserialização da instância
    public function __wakeup() {}
}
