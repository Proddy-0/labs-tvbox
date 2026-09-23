<?php
    include "../usuarios/verificaLogin.php";
    include "../util.php";
    $conn = conecta();

    $select = $conn->prepare("
        SELECT
            compra.id_compra,
            compra.status,
            compra.data,
            produto.nome,
            compra_produto.quantidade,
            compra_produto.valor_unitario
        FROM compra
        INNER JOIN compra_produto ON compra_produto.fk_compra = compra.id_compra
        INNER JOIN produto ON produto.id_produto = compra_produto.fk_produto
        WHERE compra.fk_usuario = :id_usuario
          AND compra.status <> 'carrinho'
        ORDER BY compra.data DESC, produto.nome");
    $select->execute([':id_usuario' => $_SESSION['id_usuario']]);

    // agrupa os itens por encomenda
    $encomendas = [];
    while ($linha = $select->fetch(PDO::FETCH_ASSOC)) {
        $id = $linha['id_compra'];
        if (!isset($encomendas[$id])) {
            $encomendas[$id] = [
                'status' => $linha['status'],
                'data' => $linha['data'],
                'itens' => [],
                'total' => 0
            ];
        }
        $encomendas[$id]['itens'][] = $linha;
        $encomendas[$id]['total'] += $linha['quantidade'] * $linha['valor_unitario'];
    }

    $titulo = "Minhas encomendas";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Área do cliente</span>
            <h1>Minhas encomendas</h1>
            <p>Olá, <?= htmlspecialchars($_SESSION['nome']) ?>. Aqui ficam as cartas que você encomendou para retirar no CTI.</p>
        </section>

        <?php if (isset($_GET['cancelada'])) { ?>
            <p class="form-alert form-alert--ok">Encomenda cancelada.</p>
        <?php } ?>

        <?php if (count($encomendas) == 0) { ?>
            <div class="empty-cart">
                <i class="fa-solid fa-box-open"></i>
                <h2>Nenhuma encomenda ainda</h2>
                <p>Escolha suas cartas e finalize pelo carrinho.</p>
                <a href="../../front/produtos.html" class="btn btn-primary"><i class="fa-solid fa-arrow-right"></i> Ver produtos</a>
            </div>
        <?php } ?>

        <?php foreach ($encomendas as $id => $enc) { ?>
            <article class="order-card">
                <div class="order-head">
                    <div>
                        <strong>Encomenda #<?= $id ?></strong>
                        <span> · <?= date('d/m/Y H:i', strtotime($enc['data'])) ?></span>
                    </div>
                    <span class="status-pill status-<?= htmlspecialchars($enc['status']) ?>"><?= htmlspecialchars($enc['status']) ?></span>
                </div>
                <ul class="order-items">
                    <?php foreach ($enc['itens'] as $item) { ?>
                        <li>
                            <span><?= (int) $item['quantidade'] ?>× <?= htmlspecialchars($item['nome']) ?></span>
                            <span>R$ <?= number_format($item['quantidade'] * $item['valor_unitario'], 2, ',', '.') ?></span>
                        </li>
                    <?php } ?>
                </ul>
                <div class="order-total">
                    <span>Total</span>
                    <span>R$ <?= number_format($enc['total'], 2, ',', '.') ?></span>
                </div>
                <?php if ($enc['status'] == 'reservado') { ?>
                <form class="order-actions" action="cancelar.php" method="post" onsubmit="return confirm('Cancelar a encomenda #<?= $id ?>?')">
                    <input type="hidden" name="id_compra" value="<?= $id ?>">
                    <button type="submit" class="btn btn-danger-outline btn-sm"><i class="fa-solid fa-xmark"></i> Cancelar encomenda</button>
                </form>
                <?php } ?>
            </article>
        <?php } ?>
    </main>
<?php include "../layout/rodape.php"; ?>
