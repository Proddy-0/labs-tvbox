<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    // marcar como lida / não lida
    if (isset($_POST['id_contato'])) {
        $update = $conn->prepare("UPDATE contato SET lida = NOT lida WHERE id_contato = :id");
        $update->execute([':id' => (int) $_POST['id_contato']]);
        header("Location: mensagens.php");
        exit;
    }

    $mensagens = $conn->query("
        SELECT *
        FROM contato
        ORDER BY lida, data DESC")->fetchAll(PDO::FETCH_ASSOC);

    $inscritos = $conn->query("
        SELECT email, data
        FROM newsletter
        ORDER BY data DESC")->fetchAll(PDO::FETCH_ASSOC);

    $assuntos = ['duvida' => 'Dúvida sobre produto', 'pedido' => 'Problema com o pedido', 'outro' => 'Outro'];

    $titulo = "Mensagens";
    $ativo = "mensagens";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Mensagens</h1>
            <p>Contatos enviados pelo site e inscritos na newsletter.</p>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card">
            <h2>Contato</h2>
            <?php if (count($mensagens) == 0) { ?>
                <p class="panel-empty">Nenhuma mensagem recebida ainda.</p>
            <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table data-table--stack">
                    <thead>
                        <tr><th>Data</th><th>Nome</th><th>E-mail</th><th>Assunto</th><th>Mensagem</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($mensagens as $m) { ?>
                        <tr<?= $m['lida'] ? ' class="row-muted"' : '' ?>>
                            <td data-label="Data"><?= date('d/m/Y H:i', strtotime($m['data'])) ?></td>
                            <td class="cell-title" data-label="Nome"><?= htmlspecialchars($m['nome']) ?></td>
                            <td data-label="E-mail"><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></td>
                            <td data-label="Assunto"><?= htmlspecialchars($assuntos[$m['assunto']] ?? $m['assunto']) ?></td>
                            <td data-label="Mensagem"><?= nl2br(htmlspecialchars($m['mensagem'])) ?></td>
                            <td class="cell-actions" data-label="">
                                <form class="inline-form" method="post">
                                    <input type="hidden" name="id_contato" value="<?= (int) $m['id_contato'] ?>">
                                    <button class="btn btn-outline btn-sm"><?= $m['lida'] ? 'Marcar não lida' : 'Marcar lida' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
        </section>

        <section class="panel-card">
            <h2>Newsletter (<?= count($inscritos) ?>)</h2>
            <?php if (count($inscritos) == 0) { ?>
                <p class="panel-empty">Nenhum inscrito ainda.</p>
            <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table data-table--compact">
                    <thead>
                        <tr><th>E-mail</th><th>Inscrito em</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inscritos as $i) { ?>
                        <tr>
                            <td><?= htmlspecialchars($i['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($i['data'])) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
