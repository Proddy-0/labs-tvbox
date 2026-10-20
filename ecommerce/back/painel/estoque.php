<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    // entrou / entregue / reservado / emEstoque / disponivel: ver resumoEstoque() em util.php
    $resumo = resumoEstoque($conn);
    // Filtro por curso: vale pro controle de estoque e pras encomendas (que têm carta do curso)
    $cursos = listaCursos($conn);
    $cursoId = cursoDoFiltro($conn);
    if ($cursoId !== null) {
        $resumo = array_values(array_filter($resumo, function ($p) use ($cursoId) { return (int) $p['fk_curso'] === $cursoId; }));
    }
    $negativo = array_filter($resumo, function ($p) { return $p['emEstoque'] < 0 || $p['disponivel'] < 0; });

    // Busca de encomenda (no stand: cliente diz o telefone, o nome ou o número da encomenda)
    $busca = trim($_GET['busca'] ?? '');
    $filtroBusca = "";
    $paramsBusca = [];
    if ($busca != "") {
        $digitos = preg_replace('/\D/', '', $busca);
        $filtroBusca = "
          AND (usuario.nome ILIKE :texto
               OR usuario.email ILIKE :texto"
            . ($digitos != "" ? "
               OR regexp_replace(COALESCE(usuario.telefone, ''), '\D', '', 'g') LIKE :digitos
               OR CAST(compra.id_compra AS varchar) = :numero" : "") . ")";
        $paramsBusca[':texto'] = "%$busca%";
        if ($digitos != "") {
            $paramsBusca[':digitos'] = "%$digitos%";
            $paramsBusca[':numero'] = $digitos;
        }
    }

    $filtroCurso = "";
    if ($cursoId !== null) {
        $filtroCurso = "
          AND EXISTS (SELECT 1 FROM compra_produto cp
                      INNER JOIN produto p ON p.id_produto = cp.fk_produto
                      WHERE cp.fk_compra = compra.id_compra AND p.fk_curso = :curso)";
        $paramsBusca[':curso'] = $cursoId;
    }

    $selectEncomendas = $conn->prepare("
        SELECT
            compra.id_compra,
            compra.status,
            compra.data,
            usuario.nome AS cliente,
            usuario.telefone,
            SUM(compra_produto.quantidade) AS itens,
            SUM(compra_produto.quantidade * compra_produto.valor_unitario) + COALESCE(compra.acrescimo_total, 0) AS total
        FROM compra
        INNER JOIN compra_produto ON compra_produto.fk_compra = compra.id_compra
        LEFT JOIN usuario ON usuario.id_usuario = compra.fk_usuario
        WHERE compra.status <> 'carrinho'
        $filtroBusca
        $filtroCurso
        GROUP BY compra.id_compra, compra.status, compra.data, compra.acrescimo_total, usuario.nome, usuario.telefone
        ORDER BY compra.status = 'reservado' DESC, compra.data DESC");
    $selectEncomendas->execute($paramsBusca);
    $encomendas = $selectEncomendas->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Estoque e encomendas";
    $ativo = "estoque";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Estoque e encomendas</h1>
            <p>O estoque só diminui quando a encomenda é <strong>entregue</strong>. Reservado é o que já tem dono mas ainda está aqui.</p>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <?php if (isset($_GET['erro'])) { ?>
            <p class="form-alert form-alert--error">Não dá pra entregar: tem item sem estoque suficiente. Registre a entrada do produto antes.</p>
        <?php } ?>
        <?php if (count($negativo) > 0) { ?>
            <p class="form-alert form-alert--error">Saldo negativo em: <?= htmlspecialchars(implode(', ', array_column($negativo, 'nome'))) ?>. Tem mais entrega/reserva do que entrada registrada. Registre as entradas que faltam.</p>
        <?php } ?>

        <section class="panel-card">
            <div class="panel-card-head">
                <h2>Controle de estoque</h2>
                <div class="panel-card-actions">
                    <form action="estoque.php" method="get" class="inline-form">
                        <?= seletorCurso($cursos, $cursoId) ?>
                        <?php if ($busca != "") { ?><input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>"><?php } ?>
                    </form>
                    <a href="../entrada/adicionarEntradas.php<?= $cursoId !== null ? "?curso=$cursoId" : "" ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Registrar entrada</a>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table data-table--compact">
                    <thead>
                        <tr><th>Produto</th><th class="num">Entrou</th><th class="num">Entregue</th><th class="num">Em estoque</th><th class="num">Reservado</th><th class="num">Disponível p/ venda</th></tr>
                    </thead>
                    <tbody>
                    <?php if (count($resumo) == 0) { ?>
                        <tr><td colspan="6" class="panel-empty">Nenhum produto desse curso.</td></tr>
                    <?php } ?>
                    <?php foreach ($resumo as $p) { ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nome'] ?? '') ?></td>
                            <td class="num"><?= $p['entrou'] ?></td>
                            <td class="num"><?= $p['entregue'] ?></td>
                            <td class="num <?= $p['emEstoque'] < 0 ? 'saldo-neg' : 'saldo-ok' ?>"><?= $p['emEstoque'] ?></td>
                            <td class="num"><?= $p['reservado'] ?></td>
                            <td class="num <?= $p['disponivel'] < 0 ? 'saldo-neg' : 'saldo-ok' ?>"><?= $p['disponivel'] ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel-card" id="encomendas">
            <div class="panel-card-head">
                <h2>Encomendas</h2>
                <form action="estoque.php#encomendas" method="get" class="inline-form" role="search">
                    <label for="busca-encomenda" class="sr-only">Procurar encomenda</label>
                    <input type="search" id="busca-encomenda" name="busca" class="form-control" placeholder="Telefone, nome ou nº" value="<?= htmlspecialchars($busca) ?>">
                    <?php if ($cursoId !== null) { ?><input type="hidden" name="curso" value="<?= $cursoId ?>"><?php } ?>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Procurar</button>
                    <?php if ($busca != "") { ?><a href="estoque.php<?= $cursoId !== null ? "?curso=$cursoId" : "" ?>#encomendas" class="btn btn-outline btn-sm">Limpar</a><?php } ?>
                </form>
            </div>
            <?php if (count($encomendas) == 0) { ?>
                <p class="panel-empty"><?= $busca != "" || $cursoId !== null ? "Nenhuma encomenda encontrada para esse filtro." : "Nenhuma encomenda registrada ainda." ?></p>
            <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table data-table--stack">
                    <thead>
                        <tr><th>#</th><th>Data</th><th>Cliente</th><th>Telefone</th><th class="num">Itens</th><th class="num">Total</th><th>Status</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($encomendas as $e) { ?>
                        <tr>
                            <td class="cell-title" data-label="Encomenda">#<?= $e['id_compra'] ?></td>
                            <td data-label="Data"><?= date('d/m/Y H:i', strtotime($e['data'])) ?></td>
                            <td data-label="Cliente"><?= htmlspecialchars($e['cliente'] ?? '—') ?></td>
                            <td data-label="Telefone"><?= htmlspecialchars($e['telefone'] ?: '—') ?></td>
                            <td class="num" data-label="Itens"><?= (int) $e['itens'] ?></td>
                            <td class="num" data-label="Total">R$ <?= number_format($e['total'], 2, ',', '.') ?></td>
                            <td data-label="Status"><span class="status-pill status-<?= htmlspecialchars($e['status']) ?>"><?= htmlspecialchars($e['status']) ?></span></td>
                            <td class="cell-actions" data-label="">
                                <?php if ($e['status'] == 'reservado') { ?>
                                <form class="inline-form" action="status.php" method="post">
                                    <input type="hidden" name="id_compra" value="<?= $e['id_compra'] ?>">
                                    <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">
                                    <input type="hidden" name="curso" value="<?= $cursoId ?? '' ?>">
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
