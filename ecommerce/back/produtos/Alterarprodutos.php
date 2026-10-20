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

    $imagem = imagemDoProduto($linha, "../../");

    $cursos = listaCursos($conn);

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
                <div class="form-group">
                    <label for="fk_curso">Curso <span class="req" aria-hidden="true">*</span></label>
                    <select id="fk_curso" name="fk_curso" class="form-control" required>
                        <option value="">Selecione</option>
                        <?php foreach ($cursos as $c) { ?>
                        <option value="<?= $c['id_curso'] ?>"<?= $linha['fk_curso'] == $c['id_curso'] ? ' selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="detalhes">Detalhes <span class="opt">(opcional · um por linha, aparecem no "Ver detalhes")</span></label>
                    <textarea id="detalhes" name="detalhes" class="form-control" rows="4" maxlength="500"><?= htmlspecialchars(str_replace('|', "\n", $linha['detalhes'] ?? '')) ?></textarea>
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
