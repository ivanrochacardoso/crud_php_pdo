-- Cria o banco de dados se ele não existir
CREATE DATABASE IF NOT EXISTS siglobal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco de dados
USE siglobal;

-- Cria a tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    COD INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- (Opcional) Insere um usuário de exemplo para teste
-- A senha é "123456"
INSERT INTO usuarios (nome, senha) VALUES
('Usuário de Teste', '$2y$10$gR.gC/C2I5t2a3W8E8b.IuJz.MLmgsoUa2d5T.8g5b.Jg3g4f5h6K');
