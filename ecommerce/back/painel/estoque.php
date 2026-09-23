<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    // Entrou = soma das entradas; Saiu = itens de encomendas reservadas ou entregues
    $resumo = $conn->query("
        SELECT
            produto.id_produto,
            produto.nome,
            COALESCE((SELECT SUM(quantidade) FROM entrada
                      WHERE entrada.fk_produto = produto.id_produto), 0) AS entrou,
            COALESCE((SELECT SUM(compra_produto.quantidade)
                      FROM compra_produto
                      INNER JOIN compra ON compra.id_compra = compra_produto.fk_compra
                      WHERE compra_produto.fk_produto = produto.id_produto
                        AND compra.status IN ('reservado', 'entregue')), 0) AS saiu
        FROM produto
        WHERE produto.excluido = false
        ORDER BY produto.nome")->fetchAll(PDO::FETCH_ASSOC);

    $encomendas = $conn->query("
        SELECT
            compra.id_compra,
            compra.status,
            compra.data,
            usuario.nome AS cliente,
            SUM(compra_produto.quantidade) AS itens,
            SUM(compra_produto.quantidade * compra_produto.valor_unitario) AS total
        FROM compra
        INNER JOIN compra_produto ON compra_produto.fk_compra = compra.id_compra
        LEFT JOIN usuario ON usuario.id_usuario = compra.fk_usuario
        WHERE compra.status <> 'carrinho'
        GROUP BY compra.id_compra, compra.status, compra.data, usuario.nome
        ORDER BY compra.data DESC")->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Estoque e encomendas";
    $ativo = "estoque";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Estoque e encomendas</h1>
            <p>O que entrou no estoque, o que saiu em encomendas e o saldo de cada produto.</p>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card">
            <div class="panel-card-head">
                <h2>Controle de estoque</h2>
                <a href="../entrada/adicionarEntradas.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Registrar entrada</a>
            </div>
            <div class="table-wrap">
                <table class="data-table data-table--compact">
                    <thead>
                        <tr><th>Produto</th><th class="num">Entrou</th><th class="num">Saiu</th><th class="num">Saldo</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($resumo as $p) {
                        $saldo = $p['entrou'] - $p['saiu']; ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nome'] ?? '') ?></td>
                            <td class="num"><?= (int) $p['entrou'] ?></td>
                            <td class="num"><?= (int) $p['saiu'] ?></td>
                            <td class="num <?= $saldo < 0 ? 'saldo-neg' : 'saldo-ok' ?>"><?= $saldo ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel-card">
            <h2>Encomendas</h2>
            <?php if (count($encomendas) == 0) { ?>
                <p class="panel-empty">Nenhuma encomenda registrada ainda.</p>
            <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table data-table--stack">
                    <thead>
                        <tr><th>#</th><th>Data</th><th>Cliente</th><th class="num">Itens</th><th class="num">Total</th><th>Status</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($encomendas as $e) { ?>
                        <tr>
                            <td class="cell-title" data-label="Encomenda">#<?= $e['id_compra'] ?></td>
                            <td data-label="Data"><?= date('d/m/Y H:i', strtotime($e['data'])) ?></td>
                            <td data-label="Cliente"><?= htmlspecialchars($e['cliente'] ?? '—') ?></td>
                            <td class="num" data-label="Itens"><?= (int) $e['itens'] ?></td>
                            <td class="num" data-label="Total">R$ <?= number_format($e['total'], 2, ',', '.') ?></td>
                            <td data-label="Status"><span class="status-pill status-<?= htmlspecialchars($e['status']) ?>"><?= htmlspecialchars($e['status']) ?></span></td>
                            <td class="cell-actions" data-label="">
                                <?php if ($e['status'] == 'reservado') { ?>
                                <form class="inline-form" action="status.php" method="post">
                                    <input type="hidden" name="id_compra" value="<?= $e['id_compra'] ?>">
                                    <button class="btn btn-primary btn-sm" name="status" value="entregue">Entregue</button>
                                    <button class="btn btn-danger-outline btn-sm" name="status" value="cancelado" onclick="return confirm('Cancelar esta encomenda?')">Cancelar</button>
                                </form>
                                <?php } else { echo '—'; } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
