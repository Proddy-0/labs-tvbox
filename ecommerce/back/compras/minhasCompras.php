<?php
    // Encomendas do usuário logado em JSON (usado no front/perfil.html)
    session_start();
    header("Content-Type: application/json; charset=utf-8");
    if (!isset($_SESSION['id_usuario'])) {
        http_response_code(401);
        echo json_encode(["erro" => "Não autenticado"]);
        exit;
    }

    include "../util.php";
    $conn = conecta();

    // valor_unitario vem de compra_produto: é o preço do dia da compra, não o preço atual do produto
    $select = $conn->prepare("
        SELECT
            compra.id_compra,
            compra.status,
            compra.data,
            COALESCE(compra.acrescimo_total, 0) AS acrescimo,
            produto.nome,
            compra_produto.quantidade,
            compra_produto.valor_unitario
        FROM compra
        INNER JOIN compra_produto ON compra_produto.fk_compra = compra.id_compra
        INNER JOIN produto ON produto.id_produto = compra_produto.fk_produto
        WHERE compra.fk_usuario = :id_usuario
          AND compra.status <> 'carrinho'
        ORDER BY compra.data DESC, produto.nome");
    $select->execute([':id_usuario' => $_SESSION['id_usuario']]);

    $compras = [];
    while ($linha = $select->fetch(PDO::FETCH_ASSOC)) {
        $id = (int) $linha['id_compra'];
        if (!isset($compras[$id])) {
            $compras[$id] = [
                "id" => $id,
                "status" => $linha['status'],
                "data" => date('d/m/Y H:i', strtotime($linha['data'])),
                "itens" => [],
                "acrescimo" => (float) $linha['acrescimo'],
                "total" => (float) $linha['acrescimo']
            ];
        }
        $subtotal = $linha['quantidade'] * $linha['valor_unitario'];
        $compras[$id]['itens'][] = [
            "nome" => $linha['nome'],
            "quantidade" => (int) $linha['quantidade'],
            "valor_unitario" => (float) $linha['valor_unitario'],
            "subtotal" => round($subtotal, 2)
        ];
        $compras[$id]['total'] = round($compras[$id]['total'] + $subtotal, 2);
    }

    echo json_encode(array_values($compras), JSON_UNESCAPED_UNICODE);
?>
