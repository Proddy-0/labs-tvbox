<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $produtos = $conn->query("
        SELECT id_produto, nome
        FROM produto
        WHERE excluido = false
        ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Registrar entrada";
    $ativo = "entradas";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Registrar entrada</h1>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card panel-form">
            <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>
            <form action="insertEntradas.php" method="post">
                <div class="form-group">
                    <label for="fk_produto">Produto <span class="req" aria-hidden="true">*</span></label>
                    <select id="fk_produto" name="fk_produto" class="form-control" required>
                        <option value="">Selecione um produto</option>
                        <?php foreach ($produtos as $p) { ?>
                            <option value="<?= (int) $p['id_produto'] ?>"><?= htmlspecialchars($p['nome'] ?? '') ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="quantidade">Quantidade <span class="req" aria-hidden="true">*</span></label>
                        <input type="number" id="quantidade" name="quantidade" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="custo_unitario">Custo unitário (R$) <span class="req" aria-hidden="true">*</span></label>
                        <input type="number" id="custo_unitario" name="custo_unitario" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="obs">Observação <span class="opt">(opcional)</span></label>
                    <textarea id="obs" name="obs" class="form-control" maxlength="255"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="entradas.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
