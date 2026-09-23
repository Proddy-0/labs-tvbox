<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $entradas = $conn->query("
        SELECT
            entrada.id_entrada,
            entrada.quantidade,
            entrada.custo_unitario,
            entrada.obs,
            entrada.data_entrada,
            produto.nome
        FROM entrada
        INNER JOIN produto ON produto.id_produto = entrada.fk_produto
        ORDER BY entrada.data_entrada DESC")->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Entradas de estoque";
    $ativo = "entradas";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Entradas de estoque</h1>
            <p>Cada lote de cartas que chegou ao estoque.</p>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card">
            <div class="panel-card-head">
                <h2>Histórico</h2>
                <a href="adicionarEntradas.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Registrar entrada</a>
            </div>
            <div class="table-wrap">
                <table class="data-table data-table--stack">
                    <thead>
                        <tr><th>Produto</th><th class="num">Quantidade</th><th class="num">Custo unit.</th><th>Observação</th><th>Data</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($entradas as $e) { $id = (int) $e['id_entrada']; ?>
                        <tr>
                            <td class="cell-title" data-label="Produto"><?= htmlspecialchars($e['nome'] ?? '') ?></td>
                            <td class="num" data-label="Quantidade"><?= (int) $e['quantidade'] ?></td>
                            <td class="num" data-label="Custo unit.">R$ <?= number_format($e['custo_unitario'], 2, ',', '.') ?></td>
                            <td data-label="Observação"><?= htmlspecialchars($e['obs'] ?? '') ?: '—' ?></td>
                            <td data-label="Data"><?= date('d/m/Y H:i', strtotime($e['data_entrada'])) ?></td>
                            <td class="cell-actions" data-label="">
                                <a href="alterarEntradas.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Alterar</a>
                                <a href="excluirEntradas.php?id=<?= $id ?>" class="btn btn-danger-outline btn-sm" onclick="return confirm('Deseja excluir esta entrada?')">Excluir</a>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (count($entradas) == 0) { ?>
                        <tr><td colspan="6" class="panel-empty" data-label="">Nenhuma entrada registrada. Registre o estoque inicial de cada carta.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
