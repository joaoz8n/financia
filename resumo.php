<?php
// resumo.php
if ($method === 'GET') {
    $receitas = 0;
    $despesas = 0;

    foreach ($_SESSION['lancamentos'] as $lanc) {
        if ($lanc['tipo'] === 'receita') $receitas += $lanc['valor'];
        if ($lanc['tipo'] === 'despesa') $despesas += $lanc['valor'];
    }

    echo json_encode([
        "total_receitas" => $receitas,
        "total_despesas" => $despesas,
        "saldo_atual" => $receitas - $despesas
    ]);
}
?>