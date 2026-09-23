<?php
    include "verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    $filtro = trim($_POST['nome'] ?? '');
    if ($filtro != "") {
        $select = $conn->prepare("
            SELECT *
            FROM usuario
            WHERE nome ILIKE :paramNome
              AND excluido = false
            ORDER BY nome");
        $select->execute([':paramNome' => "%$filtro%"]);
    } else {
        $select = $conn->query("
            SELECT *
            FROM usuario
            WHERE excluido = false
            ORDER BY nome");
    }
    $usuarios = $select->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Usuários";
    $ativo = "usuarios";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Usuários</h1>
            <p>Clientes e administradores cadastrados na loja.</p>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card">
            <div class="panel-card-head">
                <form action="" method="post" class="inline-form">
                    <label for="filtro-nome" class="sr-only">Filtrar por nome</label>
                    <input type="text" id="filtro-nome" name="nome" class="form-control" placeholder="Filtrar por nome" value="<?= htmlspecialchars($filtro) ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                </form>
                <a href="adicionarUsuario.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-user-plus"></i> Adicionar usuário</a>
            </div>

            <div class="table-wrap">
                <table class="data-table data-table--stack">
                    <thead>
                        <tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Perfil</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $u) { $id = (int) $u['id_usuario']; ?>
                        <tr>
                            <td class="cell-title" data-label="Nome"><?= htmlspecialchars($u['nome']) ?></td>
                            <td data-label="E-mail"><?= htmlspecialchars($u['email']) ?></td>
                            <td data-label="Telefone"><?= htmlspecialchars($u['telefone'] ?? '') ?: '—' ?></td>
                            <td data-label="Perfil"><?= $u['admin'] ? '<span class="status-pill status-entregue">Admin</span>' : 'Cliente' ?></td>
                            <td class="cell-actions" data-label="">
                                <a href="alterar_usuario.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Alterar</a>
                                <?php if ($id != $_SESSION['id_usuario']) { ?>
                                <a href="excluir_usuarios.php?id=<?= $id ?>" class="btn btn-danger-outline btn-sm" onclick="return confirm('Deseja excluir este usuário?')">Excluir</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (count($usuarios) == 0) { ?>
                        <tr><td colspan="5" class="panel-empty">Nenhum usuário encontrado.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
