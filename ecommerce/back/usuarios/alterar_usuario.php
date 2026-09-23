<?php
    include "verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $id = (int) ($_GET['id'] ?? 0);
    $select = $conn->prepare("SELECT * FROM usuario WHERE id_usuario = :id");
    $select->execute([':id' => $id]);
    $linha = $select->fetch(PDO::FETCH_ASSOC);
    if (!$linha) {
        echo "Usuário não encontrado!";
        exit;
    }

    $imagem = "";
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
        if (file_exists("imagens/usuarios/$id.$ext")) {
            $imagem = "imagens/usuarios/$id.$ext";
            break;
        }
    }

    $titulo = "Alterar usuário";
    $ativo = "usuarios";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Alterar usuário</h1>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card panel-form">
            <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>
            <form action="updateUsuario.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_usuario" value="<?= $id ?>">
                <div class="form-group">
                    <label for="nome">Nome <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($linha['nome']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail <span class="req" aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($linha['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone <span class="opt">(opcional)</span></label>
                    <input type="tel" id="telefone" name="telefone" class="form-control" value="<?= htmlspecialchars($linha['telefone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <?php $ehVoce = $id == $_SESSION['id_usuario']; ?>
                    <label class="form-check">
                        <input type="checkbox" name="admin" value="1"<?= $linha['admin'] ? ' checked' : '' ?><?= $ehVoce ? ' disabled' : '' ?>>
                        <span>Administrador <span class="opt"><?= $ehVoce ? '(você não pode tirar o seu próprio acesso)' : '(acessa o painel: estoque, produtos, usuários)' ?></span></span>
                    </label>
                </div>
                <?php if ($imagem != "") { ?>
                <div class="current-image">
                    <img src="<?= htmlspecialchars($imagem) ?>" alt="Foto atual">
                    <span>Foto atual</span>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label for="arquivo">Nova foto <span class="opt">(opcional)</span></label>
                    <input type="file" id="arquivo" name="arquivo" class="form-control" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="usuarios.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
