<?php
// lancamentos.php

if (!function_exists('getJsonInput')) {
    function getJsonInput() {
        $json = file_get_contents('php://input');
        $dados = json_decode($json, true);
        if (!$dados) { $dados = $_POST; }
        return $dados;
    }
}

// GET /lancamentos
if ($method === 'GET' && !$id) {
    $resultado = $_SESSION['lancamentos'];

    if (isset($_GET['tipo'])) {
        $resultado = array_filter($resultado, fn($l) => $l['tipo'] === $_GET['tipo']);
    }
    if (isset($_GET['categoria_id'])) {
        $resultado = array_filter($resultado, fn($l) => $l['categoria_id'] == $_GET['categoria_id']);
    }
    if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
        $resultado = array_filter($resultado, function($l) {
            return $l['data'] >= $_GET['data_inicio'] && $l['data'] <= $_GET['data_fim'];
        });
    }

    echo json_encode(array_values($resultado));
    exit;
} 

// GET /lancamentos/1
if ($method === 'GET' && $id) {
    foreach ($_SESSION['lancamentos'] as $lanc) {
        if ($lanc['id'] == $id) {
            echo json_encode($lanc);
            exit;
        }
    }
    http_response_code(404);
    echo json_encode(["erro" => "Lançamento não encontrado"]);
    exit;
}

// POST /lancamentos
if ($method === 'POST' && !$id) {
    $dados = getJsonInput();

    // Verificação segura de campos
    if (empty($dados['descricao']) || !isset($dados['valor']) || empty($dados['data']) || empty($dados['tipo']) || empty($dados['categoria_id'])) {
        http_response_code(400);
        echo json_encode(["erro" => "Todos os campos são obrigatórios"]);
        exit;
    }

    // Força o valor para número (evita o erro 500)
    $valor = floatval($dados['valor']);
    if ($valor <= 0) {
        http_response_code(422);
        echo json_encode(["erro" => "O valor deve ser maior que zero"]);
        exit;
    }

    $categoria_encontrada = null;
    foreach ($_SESSION['categorias'] as $cat) {
        if ($cat['id'] == $dados['categoria_id']) {
            $categoria_encontrada = $cat;
            break;
        }
    }
    
    if (!$categoria_encontrada) {
        http_response_code(404);
        echo json_encode(["erro" => "Categoria não encontrada"]);
        exit;
    }

    if ($categoria_encontrada['tipo'] !== $dados['tipo']) {
        http_response_code(422);
        echo json_encode(["erro" => "O tipo do lançamento não corresponde ao tipo da categoria"]);
        exit;
    }

    $novo = [
        "id" => $_SESSION['id_lanc']++,
        "descricao" => $dados['descricao'],
        "valor" => $valor,
        "data" => $dados['data'],
        "tipo" => $dados['tipo'],
        "categoria_id" => intval($dados['categoria_id'])
    ];
    $_SESSION['lancamentos'][] = $novo;
    
    http_response_code(201);
    echo json_encode($novo);
    exit;
}

// PUT /lancamentos/1
if ($method === 'PUT' && $id) {
    $dados = getJsonInput();
    
    foreach ($_SESSION['lancamentos'] as $index => $lanc) {
        if ($lanc['id'] == $id) {
            if (isset($dados['valor'])) {
                $valor = floatval($dados['valor']);
                if ($valor <= 0) {
                    http_response_code(422);
                    echo json_encode(["erro" => "O valor deve ser maior que zero"]);
                    exit;
                }
                $_SESSION['lancamentos'][$index]['valor'] = $valor;
            }
            if (isset($dados['descricao'])) $_SESSION['lancamentos'][$index]['descricao'] = $dados['descricao'];
            if (isset($dados['data'])) $_SESSION['lancamentos'][$index]['data'] = $dados['data'];
            if (isset($dados['tipo'])) $_SESSION['lancamentos'][$index]['tipo'] = $dados['tipo'];
            if (isset($dados['categoria_id'])) $_SESSION['lancamentos'][$index]['categoria_id'] = intval($dados['categoria_id']);

            echo json_encode($_SESSION['lancamentos'][$index]);
            exit;
        }
    }
    http_response_code(404);
    echo json_encode(["erro" => "Lançamento não encontrado"]);
    exit;
}

// DELETE /lancamentos/1
if ($method === 'DELETE' && $id) {
    foreach ($_SESSION['lancamentos'] as $index => $lanc) {
        if ($lanc['id'] == $id) {
            array_splice($_SESSION['lancamentos'], $index, 1);
            echo json_encode(["mensagem" => "Lançamento excluído com sucesso"]);
            exit;
        }
    }
    http_response_code(404);
    echo json_encode(["erro" => "Lançamento não encontrado"]);
    exit;
}
