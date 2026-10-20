<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $varSQL = "INSERT INTO produto
            (nome, descricao, valor_unitario, fk_curso, detalhes)
            VALUES
            (:nome, :descricao, :valor_unitario, :fk_curso, :detalhes)";
    $insert = $conn->prepare($varSQL);
    $insert->bindParam(':nome', $_POST['nome']);
    $insert->bindParam(':descricao', $_POST['descricao']);
    $insert->bindParam(':valor_unitario', $_POST['valor_unitario']);
    $insert->bindValue(':fk_curso', cursoValido($conn, $_POST['fk_curso'] ?? 0));
    $insert->bindValue(':detalhes', detalhesParaBanco($_POST['detalhes'] ?? ''));
    if ($insert->execute()) {
        $id = $conn->lastInsertId();
        // estoque inicial entra como a primeira entrada do produto (o controle de estoque soma as entradas)
        $estoqueInicial = (int) ($_POST['estoque_inicial'] ?? 0);
        if ($estoqueInicial > 0) {
            $entrada = $conn->prepare("
                INSERT INTO entrada (quantidade, custo_unitario, obs, fk_produto)
                VALUES (:quantidade, :custo_unitario, 'Estoque inicial', :fk_produto)");
            $entrada->execute([
                ':quantidade' => $estoqueInicial,
                ':custo_unitario' => max(0, (float) ($_POST['custo_unitario'] ?? 0)),
                ':fk_produto' => $id
            ]);
        }
        if (!empty($_FILES['arquivo']['name'])) {
            $salvo = salvaUpload($id, "imagens/produtos", $_FILES, 'arquivo');
            if ($salvo) {
                // caminho a partir da raiz do site (convenção do SGE)
                $conn->prepare("UPDATE produto SET imagem = :imagem WHERE id_produto = :id")
                     ->execute([':imagem' => "back/produtos/$salvo", ':id' => $id]);
            }
        }
    }
    header("Location: listaprodutos.php");
    exit;
?>