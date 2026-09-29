<?php
    include "verificaLogin.php";
    include "../util.php";
    $conn = conecta();

    $select = $conn->prepare("
        SELECT *
        FROM usuario
        WHERE id_usuario = :id");
    $select->execute([':id' => $_SESSION['id_usuario']]);
    $usuario = $select->fetch(PDO::FETCH_ASSOC);

    $titulo = "Meu Perfil";
    $ativo = "perfil";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Minha conta</span>
            <h1>Meu Perfil</h1>
            <p>Dados cadastrados na sua conta.</p>
        </section>

        <section class="auth-section" style="padding-top:0;">
            <div class="form-card" style="max-width:520px; width:100%;">

                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?: '—' ?>" disabled>
                </div>

                <?php if (array_key_exists('curso', $usuario) && !empty($usuario['curso'])) { ?>
                <div class="form-group">
                    <label>Curso</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['curso']) ?>" disabled>
                </div>
                <?php } ?>

                <div class="form-group">
                    <label>Perfil</label>
                    <input type="text" class="form-control" value="<?= $usuario['admin'] ? 'Administrador' : 'Cliente' ?>" disabled>
                </div>

                <a href="alterar_usuario.php?id=<?= (int) $usuario['id_usuario'] ?>" class="btn btn-primary btn-block" style="margin-top:0.5rem;">
                    <i class="fa-solid fa-pen"></i> Editar meus dados
                </a>
            </div>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>