<?php
// tests.php
$baseUrl = "http://localhost:8080";
$cookieFile = "cookies.txt"; // Ficheiro para guardar a sessão entre pedidos

// Se o ficheiro de cookies existir de testes antigos, apague-o para começar limpo
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

$totalTestes = 0;
$testesPassaram = 0;

function testar($nome, $resultado_esperado, $resultado_obtido) {
    global $totalTestes, $testesPassaram;
    $totalTestes++;
    if ($resultado_esperado === $resultado_obtido) {
        echo "✅ PASSOU: $nome\n";
        $testesPassaram++;
    } else {
        echo "❌ FALHOU: $nome (Esperado: $resultado_esperado, Obtido: $resultado_obtido)\n";
    }
}

function requisicao($metodo, $url, $dados = null) {
    global $baseUrl, $cookieFile;
    $ch = curl_init($baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    
    // A MÁGICA ACONTECE AQUI: Guardar e enviar os cookies de sessão!
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    
    if ($dados) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    $resposta = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ["status" => $status, "body" => json_decode($resposta, true)];
}

// Limpa os dados antes de testar
requisicao('GET', '/index.php?reset=true');

// --- CENÁRIOS DE TESTE ---

// 1. Criar Categoria (Sucesso)
$res = requisicao('POST', '/index.php?rota=categorias', ['nome' => 'Salário', 'tipo' => 'receita']);
testar("Criar categoria válida", 201, $res['status']);

// 2. Criar Categoria (Nome Duplicado - Erro)
$res = requisicao('POST', '/index.php?rota=categorias', ['nome' => 'Salário', 'tipo' => 'receita']);
testar("Criar categoria com nome duplicado", 422, $res['status']);

// 3. Criar Lançamento (Valor Inválido - Erro)
$res = requisicao('POST', '/index.php?rota=lancamentos', ['descricao' => 'Teste', 'valor' => -10, 'data' => '2023-10-01', 'tipo' => 'receita', 'categoria_id' => 1]);
testar("Criar lançamento com valor negativo", 422, $res['status']);

// 4. Criar Lançamento (Sucesso)
$res = requisicao('POST', '/index.php?rota=lancamentos', ['descricao' => 'Pagamento', 'valor' => 5000, 'data' => '2023-10-01', 'tipo' => 'receita', 'categoria_id' => 1]);
testar("Criar lançamento válido", 201, $res['status']);

// 5. Excluir Categoria Inexistente (Erro)
$res = requisicao('DELETE', '/index.php?rota=categorias&id=999');
testar("Excluir categoria inexistente", 404, $res['status']);

// 6. Buscar Resumo (Sucesso)
$res = requisicao('GET', '/index.php?rota=resumo');
testar("Buscar resumo financeiro", 200, $res['status']);

// Apaga o ficheiro de cookies no final
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

echo "\nResultado: $testesPassaram de $totalTestes testes passaram.\n";
?>