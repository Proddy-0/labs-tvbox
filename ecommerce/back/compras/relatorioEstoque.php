<?php
use Dompdf\Dompdf;

// Gera o PDF do controle de estoque (só admin). Precisa do Dompdf:
//   composer require dompdf/dompdf   (rodar na raiz do projeto, onde fica o .env)
include "../usuarios/verificaAdm.php";
include "../util.php";

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    echo "Dompdf não encontrado. Rode <b>composer require dompdf/dompdf</b> na raiz do projeto.";
    exit;
}
require_once $autoload;

$conn = conecta();

// Mesma base da tela de estoque: entradas e saídas (reservado + entregue) de cada produto
$resumo = $conn->query("
    SELECT
        produto.id_produto,
        produto.nome,
        COALESCE((SELECT SUM(quantidade) FROM entrada
                  WHERE entrada.fk_produto = produto.id_produto), 0) AS entrou,
        COALESCE((SELECT SUM(compra_produto.quantidade)
                  FROM compra_produto
                  INNER JOIN compra ON compra.id_compra = compra_produto.fk_compra
                  WHERE compra_produto.fk_produto = produto.id_produto
                    AND compra.status IN ('reservado', 'entregue')), 0) AS saiu
    FROM produto
    WHERE produto.excluido = false
    ORDER BY produto.nome")->fetchAll(PDO::FETCH_ASSOC);

// Prepara o HTML que vira PDF
$linhas = "";
foreach ($resumo as $p) {
    $erro = "";
    $saldo = qtd_prod($conn, $p['id_produto'], $erro);
    if ($erro != "") {
        echo "Erro ao calcular o estoque: " . htmlspecialchars($erro);
        exit;
    }
    $classe = $saldo < 0 ? "neg" : "";
    $linhas .= "<tr>"
        . "<td>" . htmlspecialchars($p['nome'] ?? '') . "</td>"
        . "<td class='num'>" . (int) $p['entrou'] . "</td>"
        . "<td class='num'>" . (int) $p['saiu'] . "</td>"
        . "<td class='num $classe'>" . (int) $saldo . "</td>"
        . "</tr>";
}
if ($linhas == "") {
    $linhas = "<tr><td colspan='4' style='text-align:center'>Nenhum produto cadastrado.</td></tr>";
}

$html = "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <style>
        body  { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1A2330; }
        h1    { color: #14537D; font-size: 20px; margin: 0 0 4px; }
        p     { margin: 0 0 14px; color: #556274; }
        table { width: 100%; border-collapse: collapse; }
        th    { background: #14537D; color: #fff; text-align: left; padding: 7px 8px; }
        td    { padding: 6px 8px; border-bottom: 1px solid #d5dce6; }
        .num  { text-align: right; }
        th.num { text-align: right; }
        .neg  { color: #BA1313; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Deck Logic - Controle de estoque</h1>
    <p>Gerado em " . date('d/m/Y H:i') . "</p>
    <table>
        <thead>
            <tr><th>Produto</th><th class='num'>Entrou</th><th class='num'>Saiu</th><th class='num'>Saldo</th></tr>
        </thead>
        <tbody>$linhas</tbody>
    </table>
</body>
</html>";

// Gera o PDF e abre no navegador
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("estoque.pdf", ["Attachment" => false]);
