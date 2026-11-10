<?php
// categorias.php

// Função robusta para ler JSON independentemente do método
function getJsonInput() {
    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);
    // Se o php://input falhar (comum no servidor nativo), tenta o $_POST
    if (!$dados) {
        $dados = $_POST;
    }
    return $dados;
}

// GET /categorias ou GET /categorias/1
if ($method === 'GET' && !$id) {
    echo json_encode(array_values($_SESSION['categorias']));
    exit;
} elseif ($method === 'GET' && $id) {
    foreach ($_SESSION['categorias'] as $cat) {
        if ($cat['id'] == $id) {
            echo json_encode($cat);
            exit;
        }
    }
    http_response_code(404);
    echo json_encode(["erro" => "Categoria não encontrada"]);
    exit;
}

// POST /categorias
if ($method === 'POST' && !$id) {
    $dados = getJsonInput();
    
    if (!isset($dados['nome']) || !isset($dados['tipo'])) {
        http_response_code(400);
        echo json_encode(["erro" => "Nome e tipo são obrigatórios"]);
        exit;
    }
    
    if (!in_array($dados['tipo'], ['receita', 'despesa'])) {
        http_response_code(422);
        echo json_encode(["erro" => "Tipo deve ser 'receita' ou 'despesa'"]);
        exit;
    }

    // Regra (RN03): Nome único (comparação simples e direta)
    foreach ($_SESSION['categorias'] as $cat) {
        if ($cat['nome'] == $dados['nome']) { 
            http_response_code(422);
            echo json_encode(["erro" => "Já existe uma categoria com este nome"]);
            exit;
        }
    }

    $nova = [
        "id" => $_SESSION['id_cat']++,
        "nome" => $dados['nome'],
        "tipo" => $dados['tipo']
    ];
    $_SESSION['categorias'][] = $nova;
    
    http_response_code(201);
    echo json_encode($nova);
    exit;
}

// PUT /categorias/1
if ($method === 'PUT' && $id) {
    $dados = getJsonInput();
    
    foreach ($_SESSION['categorias'] as $index => $cat) {
        if ($cat['id'] == $id) {
            if (isset($dados['tipo']) && !in_array($dados['tipo'], ['receita', 'despesa'])) {
                http_response_code(422);
                echo json_encode(["erro" => "Tipo inválido"]);
                exit;
            }

            if (isset($dados['nome'])) {
                foreach ($_SESSION['categorias'] as $outra_cat) {
                    if ($outra_cat['id'] != $id && $outra_cat['nome'] == $dados['nome']) {
                        http_response_code(422);
                        echo json_encode(["erro" => "Já existe outra categoria com este nome"]);
                        exit;
                    }
                }
                $_SESSION['categorias'][$index]['nome'] = $dados['nome'];
            }
            if (isset($dados['tipo'])) {
                $_SESSION['categorias'][$index]['tipo'] = $dados['tipo'];
            }

            echo json_encode($_SESSION['categorias'][$index]);
            exit;
        }
    }
    http_response_code(404);
    echo json_encode(["erro" => "Categoria não encontrada"]);
    exit;
}

// DELETE /categorias/1
if ($method === 'DELETE' && $id) {
    foreach ($_SESSION['lancamentos'] as $lanc) {
        if ($lanc['categoria_id'] == $id) {
            http_response_code(422);
            echo json_encode(["erro" => "Não é possível excluir categoria com lançamentos vinculados"]);
            exit;
        }
    }

    foreach ($_SESSION['categorias'] as $index => $cat) {
        if ($cat['id'] == $id) {
            array_splice($_SESSION['categorias'], $index, 1);
            echo json_encode(["mensagem" => "Categoria excluída com sucesso"]);
            exit;
        }
    }
    
    http_response_code(404);
    echo json_encode(["erro" => "Categoria não encontrada"]);
    exit;
}
?>