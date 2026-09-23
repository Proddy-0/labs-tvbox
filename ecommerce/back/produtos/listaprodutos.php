<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    $valorMax = $_POST['valor'] ?? '';
    if ($valorMax != "") {
        $select = $conn->prepare("
            SELECT *
            FROM produto
            WHERE valor_unitario <= :paramValor
              AND excluido = false
            ORDER BY nome");
        $select->execute([':paramValor' => $valorMax]);
    } else {
        $select = $conn->query("
            SELECT *
            FROM produto
            WHERE excluido = false
            ORDER BY nome");
    }
    $produtos = $select->fetchAll(PDO::FETCH_ASSOC);

    // Imagem: primeiro a enviada pelo painel (imagens/produtos/<id>.ext), senão o caminho da coluna imagem (arquivo do front)
    function imagemProduto($linha) {
        foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
            $arquivo = "imagens/produtos/{$linha['id_produto']}.$ext";
            if (file_exists($arquivo)) {
                return $arquivo;
            }
        }
        return $linha['imagem'] ? "../../front/" . $linha['imagem'] : "";
    }

    $titulo = "Produtos";
    $ativo = "produtos";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Produtos</h1>
            <p>Cartas à venda na loja.</p>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card">
            <div class="panel-card-head">
                <form action="" method="post" class="inline-form">
                    <label for="filtro-valor" class="sr-only">Valor máximo</label>
                    <input type="number" id="filtro-valor" name="valor" step="0.01" min="0" class="form-control" placeholder="Valor máximo" value="<?= htmlspecialchars($valorMax) ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                </form>
                <a href="Adicionarprodutos.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> Adicionar produto</a>
            </div>

            <div class="table-wrap">
                <table class="data-table data-table--stack">
                    <thead>
                        <tr><th>Foto</th><th>Nome</th><th>Descrição</th><th class="num">Valor</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($produtos as $p) { $id = (int) $p['id_produto']; $img = imagemProduto($p); ?>
                        <tr>
                            <td data-label="Foto"><?= $img ? '<img class="table-thumb" src="' . htmlspecialchars($img) . '" alt="">' : '—' ?></td>
                            <td class="cell-title" data-label="Nome"><?= htmlspecialchars($p['nome'] ?? '') ?></td>
                            <td data-label="Descrição"><?= htmlspecialchars($p['descricao']) ?></td>
                            <td class="num" data-label="Valor">R$ <?= number_format($p['valor_unitario'], 2, ',', '.') ?></td>
                            <td class="cell-actions" data-label="">
                                <a href="Alterarprodutos.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Alterar</a>
                                <a href="Excluirprodutos.php?id=<?= $id ?>" class="btn btn-danger-outline btn-sm" onclick="return confirm('Deseja excluir este produto?')">Excluir</a>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (count($produtos) == 0) { ?>
                        <tr><td colspan="5" class="panel-empty" data-label="">Nenhum produto encontrado.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
