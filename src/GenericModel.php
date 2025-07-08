<?php

require_once __DIR__ . '/Database.php';

class GenericModel
{
    private $pdo;
    private $table;
    private $schema;

    public function __construct($tableName)
    {
        $this->pdo = Database::getInstance();
        $this->table = $this->sanitizeTableName($tableName);
        $this->schema = $this->getSchema(); // Carrega o schema ao instanciar
    }

    private function sanitizeTableName($tableName)
    {
        // Prevenção básica contra SQL injection no nome da tabela
        return preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    }

    /**
     * Busca o schema (colunas) da tabela no banco de dados.
     *
     * @return array
     */
    public function getSchema()
    {
        if ($this->schema) {
            return $this->schema;
        }
        try {
            $stmt = $this->pdo->query("DESCRIBE `{$this->table}`");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Tabela não encontrada ou outro erro
            return [];
        }
    }

    /**
     * Busca todos os registros com opções de ordenação e paginação.
     */
    public function getAll($sortBy = '1', $sortOrder = 'ASC', $limit = 10, $offset = 0)
    {
        $columns = array_map(function($col) {
            return '`' . $col['Field'] . '`';
        }, $this->schema);
        
        $columnList = implode(', ', $columns);

        // Validação da ordenação
        $allowedColumns = array_map(fn($col) => $col['Field'], $this->schema);
        $sortBy = in_array($sortBy, $allowedColumns) ? $sortBy : $allowedColumns[0] ?? '1';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT {$columnList} FROM `{$this->table}` ORDER BY `{$sortBy}` {$sortOrder} LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conta o número total de registros.
     */
    public function countAll()
    {
        $primaryKey = $this->getPrimaryKey() ?? '*';
        $stmt = $this->pdo->query("SELECT COUNT({$primaryKey}) FROM `{$this->table}`");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca um registro pelo seu ID.
     */
    public function getById($id)
    {
        $primaryKey = $this->getPrimaryKey();
        if (!$primaryKey) return null;

        $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE `{$primaryKey}` = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo registro.
     */
    public function create($data)
    {
        unset($data[$this->getPrimaryKey()]); // Remove a chave primária dos dados

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        // Criptografa a senha se o campo 'senha' existir
        if (isset($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        }

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $this->table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Atualiza um registro existente.
     */
    public function update($id, $data)
    {
        $primaryKey = $this->getPrimaryKey();
        if (!$primaryKey) return false;

        unset($data[$primaryKey]);

        $setClauses = array_map(fn($col) => "`{$col}` = :{$col}", array_keys($data));

        // Criptografa a senha se estiver sendo atualizada
        if (isset($data['senha']) && !empty($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        } else {
            unset($data['senha']); // Não atualiza a senha se estiver vazia
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = :primary_key_id',
            $this->table,
            implode(', ', $setClauses),
            $primaryKey
        );

        $data['primary_key_id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Deleta um registro.
     */
    public function delete($id)
    {
        $primaryKey = $this->getPrimaryKey();
        if (!$primaryKey) return false;

        $sql = "DELETE FROM `{$this->table}` WHERE `{$primaryKey}` = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Helper para obter o nome da chave primária.
     */
    private function getPrimaryKey()
    {
        foreach ($this->schema as $column) {
            if ($column['Key'] === 'PRI') {
                return $column['Field'];
            }
        }
        return null; // Ou o primeiro campo como fallback
    }
}