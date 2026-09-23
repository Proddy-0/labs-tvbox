<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $id = (int) ($_GET['id'] ?? 0);
    $select = $conn->prepare("
        SELECT *
        FROM produto
        WHERE id_produto = :id");
    $select->execute([':id' => $id]);
    $linha = $select->fetch(PDO::FETCH_ASSOC);
    if (!$linha) {
        echo "Produto não encontrado!";
        exit;
    }

    $imagem = "";
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
        if (file_exists("imagens/produtos/$id.$ext")) {
            $imagem = "imagens/produtos/$id.$ext";
            break;
        }
    }
    if ($imagem == "" && $linha['imagem']) {
        $imagem = "../../front/" . $linha['imagem'];
    }

    $titulo = "Alterar produto";
    $ativo = "produtos";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Alterar produto</h1>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card panel-form">
            <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>
            <form action="Updateprodutos.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_produto" value="<?= $id ?>">
                <div class="form-group">
                    <label for="nome">Nome <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($linha['nome'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="descricao" name="descricao" class="form-control" maxlength="255" value="<?= htmlspecialchars($linha['descricao']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="valor_unitario">Valor unitário (R$) <span class="req" aria-hidden="true">*</span></label>
                    <input type="number" id="valor_unitario" name="valor_unitario" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($linha['valor_unitario']) ?>" required>
                </div>
                <?php if ($imagem != "") { ?>
                <div class="current-image">
                    <img src="<?= htmlspecialchars($imagem) ?>" alt="Imagem atual">
                    <span>Imagem atual</span>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label for="arquivo">Nova imagem <span class="opt">(opcional)</span></label>
                    <input type="file" id="arquivo" name="arquivo" class="form-control" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="listaprodutos.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
