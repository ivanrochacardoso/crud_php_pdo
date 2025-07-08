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
    'gr_prg' => [
        // Esconde a coluna 'cod' do formulário e da tabela
        'cod' => ['hidden' => true],
        
        // Muda o rótulo da coluna e a torna obrigatória
        'procedimento' => ['label' => 'Procedimento','type'=>'textarea', 'required' => true],
        
        // Define o tipo do campo 
        'timestamp' => ['label' => 'Data-hora', 'type' => 'text'],
        

    ],
    'estudantes_2008' => [
        // Esconde a coluna 'cod' do formulário e da tabela
        'cod' => ['hidden' => true],
        'nome' => ['label' => 'Nome', 'required' => true],
        'timestamp' => ['hidden' => true],
     
      

    ],
    
    'outra_tabela' => [
        'id' => ['hidden' => true],
        'descricao' => ['label' => 'Descrição do Produto', 'type' => 'textarea'],
        'data_validade' => ['label' => 'Válido até', 'type' => 'date'],
    ]
];