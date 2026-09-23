<?php
    // Cadastro de usuário pelo painel. Clientes se cadastram pela tela do site (front/cadastro.html).
    include "verificaAdm.php";
    $titulo = "Adicionar usuário";
    $ativo = "usuarios";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1>Adicionar usuário</h1>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card panel-form">
            <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>
            <form action="insertUsuario.php" method="post" enctype="multipart/form-data" onsubmit="return confirmarSenha()">
                <div class="form-group">
                    <label for="nome">Nome completo <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="nome" name="nome" class="form-control" maxlength="80" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail <span class="req" aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone <span class="opt">(opcional)</span></label>
                    <input type="tel" id="telefone" name="telefone" class="form-control" maxlength="20">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="senha">Senha <span class="req" aria-hidden="true">*</span></label>
                        <input type="password" id="senha" name="senha" class="form-control" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmaSenha">Confirmar senha <span class="req" aria-hidden="true">*</span></label>
                        <input type="password" id="confirmaSenha" name="confirmaSenha" class="form-control" maxlength="255" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="admin" value="1">
                        <span>Administrador <span class="opt">(acessa o painel: estoque, produtos, usuários)</span></span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="imagem">Foto <span class="opt">(opcional)</span></label>
                    <input type="file" id="imagem" name="imagem" class="form-control" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                    <a href="usuarios.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
    <script>
        function confirmarSenha() {
            if (document.getElementById("senha").value !== document.getElementById("confirmaSenha").value) {
                alert("As senhas não são iguais!");
                return false;
            }
            return true;
        }
    </script>
<?php include "../layout/rodape.php"; ?>
