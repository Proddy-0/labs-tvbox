<?php
    // Muda o status de uma encomenda reservada: entregue ou cancelado.
    // Só entrega se existir estoque físico (entrou - entregue) pra cada item.
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $status = $_POST['status'] ?? '';
    $idCompra = (int) ($_POST['id_compra'] ?? 0);
    $busca = trim($_POST['busca'] ?? '');
    $erro = false;

    if (in_array($status, ['entregue', 'cancelado'])) {
        $conn = conecta();
        $conn->beginTransaction();

        if ($status == 'entregue') {
            $itens = $conn->prepare("
                SELECT compra_produto.fk_produto, compra_produto.quantidade
                FROM compra_produto
                INNER JOIN compra ON compra.id_compra = compra_produto.fk_compra
                WHERE compra.id_compra = :id AND compra.status = 'reservado'");
            $itens->execute([':id' => $idCompra]);
            $trava = $conn->prepare("SELECT 1 FROM produto WHERE id_produto = :id FOR UPDATE");
            foreach ($itens->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $trava->execute([':id' => $item['fk_produto']]);
                $estoque = resumoEstoque($conn, $item['fk_produto'])[0] ?? null;
                if ($estoque && $item['quantidade'] > $estoque['emEstoque']) {
                    $erro = true;
                    break;
                }
            }
        }

        if ($erro) {
            $conn->rollBack();
        } else {
            $update = $conn->prepare("
                UPDATE compra
                SET status = :status
                WHERE id_compra = :id_compra
                  AND status = 'reservado'");
            $update->execute([':status' => $status, ':id_compra' => $idCompra]);
            $conn->commit();
        }
    }

    $params = [];
    if ($busca != "") { $params[] = "busca=" . urlencode($busca); }
    if (($_POST['curso'] ?? '') !== '') { $params[] = "curso=" . (int) $_POST['curso']; }
    if ($erro) { $params[] = "erro=estoque"; }
    header("Location: estoque.php" . ($params ? "?" . implode("&", $params) : "") . "#encomendas");
    exit;
?>
