<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    // quantos produtos ativos usam cada curso (não deixa excluir curso em uso)
    $cursos = $conn->query("
        SELECT
            curso.*,
            (SELECT COUNT(*) FROM produto
             WHERE produto.fk_curso = curso.id_curso
               AND produto.excluido = false) AS produtos
        FROM curso
        WHERE curso.excluido = false
        ORDER BY curso.nome")->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Cursos";
    $ativo = "cursos";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Cursos</h1>
            <p>Cada curso vira um filtro na vitrine e dá a cor dos cards dos produtos dele.</p>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <?php if (isset($_GET['emuso'])) { ?>
            <p class="form-alert form-alert--error">Esse curso tem produtos cadastrados. Troque o curso dos produtos antes de excluir.</p>
        <?php } ?>

        <section class="panel-card">
            <div class="panel-card-head">
                <h2>Cursos cadastrados</h2>
                <a href="formCurso.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> Adicionar curso</a>
            </div>

            <div class="table-wrap">
                <table class="data-table data-table--stack">
                    <thead>
                        <tr><th>Cor</th><th>Nome</th><th class="num">Produtos</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cursos as $c) { $id = (int) $c['id_curso']; ?>
                        <tr>
                            <td data-label="Cor"><span class="color-swatch" style="background: <?= htmlspecialchars($c['cor']) ?>"></span> <?= htmlspecialchars($c['cor']) ?></td>
                            <td class="cell-title" data-label="Nome"><?= htmlspecialchars($c['nome']) ?></td>
                            <td class="num" data-label="Produtos"><?= (int) $c['produtos'] ?></td>
                            <td class="cell-actions" data-label="">
                                <a href="formCurso.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Alterar</a>
                                <a href="excluirCurso.php?id=<?= $id ?>" class="btn btn-danger-outline btn-sm" onclick="return confirm('Deseja excluir este curso?')">Excluir</a>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (count($cursos) == 0) { ?>
                        <tr><td colspan="4" class="panel-empty" data-label="">Nenhum curso cadastrado.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
