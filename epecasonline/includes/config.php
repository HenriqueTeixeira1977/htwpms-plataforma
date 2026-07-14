<?php
/**
 * Configuração central da plataforma de acompanhamento do projeto.
 * Ajuste os dados abaixo conforme a identidade do seu cliente.
 */

// --- Dados do projeto / cliente ---
define('PROJETO_NOME', 'Setup, Estruturação e Integração – ePeças Online');
define('CLIENTE_NOME', 'ePeças Online');
define('AGENCIA_NOME', 'HT WebSites Solutions');

// --- Acesso administrativo (painel de atualização de status) ---
// IMPORTANTE: troque o usuário e a senha abaixo antes de publicar o site.
// A senha é comparada com password_hash() — gere um novo hash com:
// php -r "echo password_hash('nova_senha', PASSWORD_DEFAULT);"
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$10$ADts2e7VHwas5/Q.eZDwr.BMwcwt4VI0UTGhmH4Sz.bb7yaTg7QYW'); // senha padrão: mudar123

// --- Fuso horário ---
date_default_timezone_set('America/Sao_Paulo');

// --- Caminho do banco de dados SQLite ---
define('DB_PATH', __DIR__ . '/../data/cronograma.sqlite');
