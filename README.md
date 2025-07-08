# CRUD Genérico com PHP e Vanilla JS

Este projeto é um sistema de CRUD (Create, Read, Update, Delete) dinâmico e genérico - MariaDB/MySQL. Ele foi projetado para se adaptar a qualquer tabela do banco de dados, gerando a interface de gerenciamento automaticamente a partir da estrutura da tabela.

## Recursos

- **CRUD Genérico**: Funciona com qualquer tabela sem necessidade de escrever código novo.
- **UI Dinâmica**: A tabela de dados e os formulários são gerados em tempo real.
- **Descoberta de Schema**: Detecta automaticamente as colunas da tabela para montar a interface.
- **Schema Opcional**: Permite customizar a exibição, os rótulos e os tipos de campo para tabelas específicas.
- **Paginação e Ordenação**: Suporte para navegar por grandes volumes de dados e ordenar por colunas.
- **Roteamento Simples**: Utiliza URLs amigáveis (ex: `/usuarios`) para definir a tabela alvo.

---

## Como Usar

### 1. Configuração do Banco de Dados

Certifique-se de que as credenciais no arquivo `src/config.php` estão corretas.

```php
// src/config.php
define('DB_HOST', 'seu_host');
define('DB_NAME', 'seu_banco_de_dados');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 2. Iniciar o Servidor

Para usar o sistema de roteamento, inicie o servidor embutido do PHP a partir da raiz do projeto (`crud_php_pdo`) com o seguinte comando:

```bash
php -S localhost:8000 
```

### 3. Acessar o CRUD

Abra o navegador e acesse a URL correspondente à tabela que deseja gerenciar. O nome da tabela é simplesmente adicionado ao final da URL.

- Para a tabela `usuarios`: **http://localhost:8000/usuarios**
- Para a tabela `produtos`: **http://localhost:8000/produtos**
- Para a tabela `cadastros`: **http://localhost:8000/cadastros**

O sistema cuidará do resto.

---

## Customizando a Exibição com `schemas.php` (Opcional)

Por padrão, o sistema gera a interface de forma 100% automática. No entanto, para ter mais controle sobre a aparência e o comportamento, você pode criar um arquivo `src/schemas.php`.

Este arquivo permite definir regras específicas para cada tabela. Se uma tabela não estiver definida neste arquivo, ela usará o modo automático.

**Para usar, crie o arquivo `src/schemas.php` com a seguinte estrutura:**

```php
<?php
// src/schemas.php
return [
    'usuarios' => [
        // Esconde a coluna 'cod' do formulário e da tabela
        'cod' => ['hidden' => true],
        
        // Muda o rótulo da coluna 'nome' e a torna obrigatória
        'nome' => ['label' => 'Nome do Usuário', 'required' => true],
        
        // Define o tipo do campo 'email' como 'email' para validação do navegador
        'email' => ['label' => 'Endereço de E-mail', 'type' => 'email'],
        
        // A coluna 'senha' é automaticamente escondida da tabela de listagem,
        // mas aqui podemos definir um rótulo para o formulário.
        'senha' => ['label' => 'Senha', 'type' => 'password'],
    ],
    
    'outra_tabela' => [
        'id' => ['hidden' => true],
        'descricao' => ['label' => 'Descrição do Produto', 'type' => 'textarea'],
        'data_validade' => ['label' => 'Válido até', 'type' => 'date'],
    ]
];
```

**Opções disponíveis:**

- `label` (string): Define um rótulo personalizado para a coluna no formulário e no cabeçalho da tabela.
- `type` (string): Força um tipo específico para o campo `<input>` no formulário (ex: `text`, `email`, `number`, `date`, `password`, `textarea`).
- `hidden` (boolean): Se `true`, oculta a coluna da tabela de listagem e do formulário.
- `required` (boolean): Se `true`, adiciona o atributo `required` ao campo no formulário.

---

## Estrutura de Arquivos

```
.
├── api/
│   └── index.php         # Endpoint da API genérica
├── assets/
│   ├── css/style.css     # Estilos
│   └── js/main.js        # Lógica do frontend (Vanilla JS)
├── src/
│   ├── config.php        # Configuração do DB
│   ├── Database.php      # Classe de conexão PDO
│   ├── GenericModel.php  # Modelo genérico para operações no DB
│   └── schemas.php       # (Opcional) Arquivo de customização
├── index.php             # Template HTML principal
├── router.php            # Roteador para o servidor PHP
└── README.md             # Esta documentação
```
