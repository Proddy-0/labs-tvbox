<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $cursos = listaCursos($conn);
    $titulo = "Adicionar produto";
    $ativo = "produtos";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Adicionar produto</h1>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card panel-form">
            <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>
            <form action="Insertprodutos.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nome">Nome <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="descricao" name="descricao" class="form-control" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label for="valor_unitario">Valor unitário (R$) <span class="req" aria-hidden="true">*</span></label>
                    <input type="number" id="valor_unitario" name="valor_unitario" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="estoque_inicial">Estoque inicial <span class="opt">(opcional · vira a 1ª entrada)</span></label>
                        <input type="number" id="estoque_inicial" name="estoque_inicial" class="form-control" min="0" step="1" value="0">
                    </div>
                    <div class="form-group">
                        <label for="custo_unitario">Custo unitário (R$) <span class="opt">(opcional)</span></label>
                        <input type="number" id="custo_unitario" name="custo_unitario" class="form-control" min="0" step="0.01" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label for="fk_curso">Curso <span class="req" aria-hidden="true">*</span></label>
                    <select id="fk_curso" name="fk_curso" class="form-control" required>
                        <option value="">Selecione</option>
                        <?php foreach ($cursos as $c) { ?>
                        <option value="<?= $c['id_curso'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="detalhes">Detalhes <span class="opt">(opcional · um por linha, aparecem no "Ver detalhes")</span></label>
                    <textarea id="detalhes" name="detalhes" class="form-control" rows="4" maxlength="500"><?= '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="arquivo">Imagem <span class="opt">(opcional · jpg, png, gif ou webp)</span></label>
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
