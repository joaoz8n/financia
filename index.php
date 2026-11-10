<?php
// index.php
header("Content-Type: application/json; charset=UTF-8");

// Configuração básica de sessão para evitar avisos no terminal
ini_set('session.cookie_httponly', 1);
session_start();

// Inicializa as listas se não existirem
if (!isset($_SESSION['categorias'])) $_SESSION['categorias'] = [];
if (!isset($_SESSION['lancamentos'])) $_SESSION['lancamentos'] = [];
if (!isset($_SESSION['id_cat'])) $_SESSION['id_cat'] = 1;
if (!isset($_SESSION['id_lanc'])) $_SESSION['id_lanc'] = 1;

// Rota para limpar dados (útil para os testes)
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    $_SESSION['categorias'] = [];
    $_SESSION['lancamentos'] = [];
    $_SESSION['id_cat'] = 1;
    $_SESSION['id_lanc'] = 1;
    echo json_encode(["mensagem" => "Dados limpos"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Captura a rota e o ID de forma simples via URL (?rota=categorias&id=1)
$recurso = $_GET['rota'] ?? '';
$id = $_GET['id'] ?? null;

// Se for a raiz do site, mostra a mensagem
if ($recurso === '') {
    echo json_encode([
        "mensagem" => "API de Controle Financeiro está rodando!",
        "como_usar" => "Adicione ?rota=categorias, ?rota=lancamentos ou ?rota=resumo na URL"
    ]);
    exit;
}

// Encaminha para os ficheiros corretos
if ($recurso === 'categorias') {
    require 'categorias.php';
} elseif ($recurso === 'lancamentos') {
    require 'lancamentos.php';
} elseif ($recurso === 'resumo') {
    require 'resumo.php';
} else {
    http_response_code(404);
    echo json_encode(["erro" => "Rota não encontrada: " . $recurso]);
}
?>