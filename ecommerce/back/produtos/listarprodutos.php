<?php
    header("Content-Type: application/json; charset=utf-8");
    include "../util.php";
    $conn = conecta();

    $select = $conn->query("
        SELECT *
        FROM produto
        WHERE excluido = false
        ORDER BY nome");
    $produtos = $select->fetchAll(PDO::FETCH_ASSOC);

    // Mesma lógica do painel: imagem enviada pelo admin (imagens/produtos/<id>.ext) tem
    // prioridade; senão usa o caminho salvo na coluna imagem (arquivo do front).
    function imagemProduto($linha) {
        foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
            $caminhoFisico = __DIR__ . "/imagens/produtos/{$linha['id_produto']}.$ext";
            if (file_exists($caminhoFisico)) {
                return ["origem" => "upload", "caminho" => "imagens/produtos/{$linha['id_produto']}.$ext"];
            }
        }
        if (!empty($linha['imagem'])) {
            return ["origem" => "coluna", "caminho" => $linha['imagem']];
        }
        return ["origem" => "", "caminho" => ""];
    }

    // Enquanto não existe uma coluna de curso no banco, descobre pelo nome do arquivo de imagem.
    function categoriaProduto($caminhoImagem) {
        $img = strtolower($caminhoImagem);
        if (strpos($img, 'informatica') !== false) return ['informatica', 'is-info', 'Informática'];
        if (strpos($img, 'mecanica') !== false) return ['mecanica', 'is-mec', 'Mecânica'];
        if (strpos($img, 'eletronica') !== false) return ['eletronica', 'is-eletro', 'Eletrônica'];
        return ['outros', '', 'Outros'];
    }

    $resultado = [];
    foreach ($produtos as $p) {
        $img = imagemProduto($p);
        [$categoria, $courseClass, $courseLabel] = categoriaProduto($img['caminho']);

        $resultado[] = [
            "id" => (int) $p['id_produto'],
            "nome" => $p['nome'],
            "descricao" => $p['descricao'],
            "preco" => (float) $p['valor_unitario'],
            "imagemOrigem" => $img['origem'],
            "imagemCaminho" => $img['caminho'],
            "categoria" => $categoria,
            "courseClass" => $courseClass,
            "courseLabel" => $courseLabel
        ];
    }

    echo json_encode($resultado);
?>