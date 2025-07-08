<?php
// router.php

// Pega o caminho da requisição
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Se for um arquivo existente na pasta 'assets', serve-o diretamente.
// Isso garante que o CSS, JS e imagens carreguem normalmente.
if (preg_match('/\.(?:css|js|png|jpg|jpeg|gif|ico)$/', $path)) {
    return false; // Deixa o servidor embutido tratar a requisição.
}

// Para qualquer outra URL (ex: /usuarios, /cadastros, /),
// carrega o arquivo principal da aplicação.
require __DIR__ . '/index.php';
