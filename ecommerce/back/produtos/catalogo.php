<?php
    // Catálogo público em JSON: o site (front/js/script.js) monta filtros e cards a partir daqui.
    // Preço, nome, descrição, curso e imagem vêm sempre do banco (o que o painel cadastra).
    header("Content-Type: application/json; charset=utf-8");
    include "../util.php";
    $conn = conecta();

    $produtos = $conn->query("
        SELECT *
        FROM produto
        WHERE excluido = false
        ORDER BY id_produto")->fetchAll(PDO::FETCH_ASSOC);


    $cursos = [];
    foreach (listaCursos($conn) as $c) {
        $cursos[] = [
            "id" => (int) $c['id_curso'],
            "nome" => $c['nome'],
            "cor" => $c['cor'],
            "classe" => $c['classe'] ?? ''
        ];
    }

    // disponível = entradas - entregues - reservados (o que ainda pode ser reservado)
    $disponivel = [];
    foreach (resumoEstoque($conn) as $e) {
        $disponivel[$e['id_produto']] = max(0, $e['disponivel']);
    }

    $saida = [];
    foreach ($produtos as $p) {
        $detalhes = array_values(array_filter(array_map('trim', explode('|', $p['detalhes'] ?? ''))));
        $saida[] = [
            "id" => (int) $p['id_produto'],
            "nome" => $p['nome'] ?? '',
            "descricao" => $p['descricao'],
            "valor" => (float) $p['valor_unitario'],
            "estoque" => $disponivel[$p['id_produto']] ?? 0,
            "curso" => $p['fk_curso'] === null ? null : (int) $p['fk_curso'],
            "detalhes" => $detalhes,
            "imagem" => imagemDoProduto($p, "../")   // relativo a front/, onde ficam as páginas
        ];
    }

    echo json_encode(["cursos" => $cursos, "produtos" => $saida], JSON_UNESCAPED_UNICODE);
?>
