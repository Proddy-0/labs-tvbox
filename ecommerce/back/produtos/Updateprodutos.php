<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $varSQL = "
        UPDATE produto
        SET
            nome = :nome,
            descricao = :descricao,
            valor_unitario = :valor_unitario,
            fk_curso = :fk_curso,
            detalhes = :detalhes
        WHERE id_produto = :id_produto";
    $update = $conn->prepare($varSQL);
    $update->bindParam(':nome', $_POST['nome']);
    $update->bindParam(':descricao', $_POST['descricao']);
    $update->bindParam(':valor_unitario', $_POST['valor_unitario']);
    $update->bindValue(':fk_curso', cursoValido($conn, $_POST['fk_curso'] ?? 0));
    $update->bindValue(':detalhes', detalhesParaBanco($_POST['detalhes'] ?? ''));
    $update->bindParam(':id_produto', $_POST['id_produto']);
    if ($update->execute()) {
        if (!empty($_FILES['arquivo']['name'])) {
            $id = (int) $_POST['id_produto'];
            $salvo = salvaUpload($id, "imagens/produtos", $_FILES, 'arquivo');
            if ($salvo) {
                // caminho a partir da raiz do site (convenção do SGE)
                $conn->prepare("UPDATE produto SET imagem = :imagem WHERE id_produto = :id")
                     ->execute([':imagem' => "back/produtos/$salvo", ':id' => $id]);
            }
        }
        header("Location: listaprodutos.php");
        exit;
    }
    echo "Erro ao alterar o produto.";
?>
