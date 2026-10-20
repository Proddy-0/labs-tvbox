<?php
    // Recebe o carrinho do site (itens[id_produto] = quantidade) e grava a encomenda.
    // Encomenda = registro em compra com status 'reservado' + itens em compra_produto.
    session_start();
    if (!isset($_SESSION['id_usuario'])) {
        // sem login: manda entrar e volta pro carrinho (o carrinho continua salvo no navegador)
        header("Location: ../../front/login.html?next=carrinho");
        exit;
    }
    include "../util.php";
    $conn = conecta();

    $itens = $_POST['itens'] ?? [];
    if (!is_array($itens) || count($itens) == 0) {
        header("Location: ../../front/carrinho.html");
        exit;
    }

    // Preço vem do banco, nunca do navegador
    $select = $conn->prepare("
        SELECT valor_unitario
        FROM produto
        WHERE id_produto = :id AND excluido = false
        FOR UPDATE");   // trava a linha do produto: duas reservas ao mesmo tempo não furam o estoque

    $conn->beginTransaction();
    try {
        $insertCompra = $conn->prepare("
            INSERT INTO compra (status, sessao, fk_usuario)
            VALUES ('reservado', :sessao, :fk_usuario)
            RETURNING id_compra");
        $insertCompra->execute([
            ':sessao' => session_id(),
            ':fk_usuario' => $_SESSION['id_usuario']
        ]);
        $idCompra = $insertCompra->fetchColumn();

        $insertItem = $conn->prepare("
            INSERT INTO compra_produto (fk_compra, fk_produto, valor_unitario, quantidade)
            VALUES (:fk_compra, :fk_produto, :valor_unitario, :quantidade)");

        $gravados = 0;
        $faltando = [];
        foreach ($itens as $idProduto => $quantidade) {
            $idProduto = (int) $idProduto;
            $quantidade = (int) $quantidade;
            if ($idProduto <= 0 || $quantidade <= 0 || $quantidade > 100) {
                continue;
            }
            $select->execute([':id' => $idProduto]);
            $valor = $select->fetchColumn();
            if ($valor === false) {
                continue; // produto inexistente ou excluído
            }
            // só reserva o que está disponível (entradas - entregues - já reservados)
            $estoque = resumoEstoque($conn, $idProduto)[0];
            if ($quantidade > $estoque['disponivel']) {
                $faltando[] = [$estoque['nome'], max(0, $estoque['disponivel'])];
                continue;
            }
            $insertItem->execute([
                ':fk_compra' => $idCompra,
                ':fk_produto' => $idProduto,
                ':valor_unitario' => $valor,
                ':quantidade' => $quantidade
            ]);
            $gravados++;
        }

        if ($faltando) {
            $conn->rollBack();
            header("Location: ../../front/carrinho.html?erro=estoque&falta=" . urlencode(json_encode($faltando, JSON_UNESCAPED_UNICODE)));
            exit;
        }

        if ($gravados == 0) {
            $conn->rollBack();
            header("Location: ../../front/carrinho.html");
            exit;
        }
        $conn->commit();
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Erro ao registrar a encomenda.";
        exit;
    }

    header("Location: ../../front/carrinho.html?encomenda=ok");
    exit;
?>
