<?php
    // "Esqueci a senha": recebe o e-mail e envia o link para redefinir.php 
    require_once __DIR__ . '/../util.php';
    $conn = conecta();
    $enviado = false;
    $email = "";
 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email'] ?? '');
 
        // procura o usuário pelo e-mail
        $select = $conn->prepare("
            SELECT id_usuario, senha
            FROM usuario
            WHERE email = :email
            AND excluido = false");
        $select->execute([':email' => $email]);
        $usuario = $select->fetch(PDO::FETCH_ASSOC);
 
        // se não achou, não faz nada (e não avisa, para não revelar quem tem cadastro)
        if ($usuario) {
            $expira = time() + 3600; // o link vale por 1 hora
            $token = geraTokenRecuperacao($email, $usuario['senha'], $expira);
 
            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
            $pasta = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $link = "$protocolo://{$_SERVER['HTTP_HOST']}$pasta/redefinir.php"
                  . "?e=" . urlencode($email) . "&x=$expira&t=$token";
 
            $html = "<div style='font-family:Arial,sans-serif;max-width:480px;margin:auto;color:#1A2330'>
                        <h2 style='color:#14537D'>Deck Logic</h2>
                        <p>Recebemos um pedido para redefinir a sua senha.</p>
                        <p><a href='$link' style='display:inline-block;padding:12px 22px;background:#1D51C1;color:#fff;border-radius:999px;text-decoration:none;font-weight:bold'>Criar nova senha</a></p>
                        <p style='color:#556274;font-size:13px'>O link vale por 1 hora e só pode ser usado uma vez. Se não foi você, ignore este e-mail.</p>
                     </div>";
 
            EnviaEmail($email, "Redefinição de senha", $html);
        }
        $enviado = true;
    }
 
    $titulo = "Esqueci a senha";
    $ativo = "";
    include "../layout/topo.php";
 
?>
    <?php include __DIR__ . '/../../front/css/recuperacao_estilo.php'; ?>
    <main id="conteudo">
        <section class="auth-section container">
            <div class="auth-card recovery-card">
 
                <?php if ($enviado) { ?>
                <div class="recovery-result is-ok">
                    <div class="result-icon"><i class="fa-solid fa-paper-plane"></i></div>
                    <h1>Confira seu e-mail</h1>
                    <p>Se <strong><?= htmlspecialchars($email) ?></strong> estiver cadastrado, enviamos um link para você criar uma nova senha.</p>
                    <ul class="recovery-tips">
                        <li><i class="fa-solid fa-clock"></i><span>O link vale por 1 hora e só pode ser usado uma vez.</span></li>
                        <li><i class="fa-solid fa-inbox"></i><span>Não chegou? Olhe também a caixa de spam.</span></li>
                    </ul>
                    <div class="recovery-actions">
                        <a href="../../front/login.html" class="btn btn-primary btn-block"><i class="fa-solid fa-right-to-bracket"></i> Voltar ao login</a>
                        <a href="esqueci.php" class="btn btn-outline btn-block"><i class="fa-solid fa-rotate-left"></i> Usar outro e-mail</a>
                    </div>
                </div>
 
                <?php } else { ?>
                <header class="auth-header">
                    <div class="auth-icon"><i class="fa-solid fa-key"></i></div>
                    <h1>Esqueceu a senha?</h1>
                    <p>Sem problema. Informe o e-mail da sua conta e enviaremos um link para criar uma nova.</p>
                </header>
 
                <ol class="recovery-steps">
                    <li>Informe o e-mail cadastrado</li>
                    <li>Abra o link que enviaremos</li>
                    <li>Crie a sua nova senha</li>
                </ol>
 
                <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>
 
                <form action="esqueci.php" method="post" id="esqueciForm">
                    <div class="form-group">
                        <label for="email">E-mail <span class="req" aria-hidden="true">*</span></label>
                        <div class="input-icon">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" maxlength="100" autocomplete="email" required>
                        </div>
                    </div>
 
                    <button type="submit" class="btn btn-primary btn-block" id="btnEnviar">
                        <i class="fa-solid fa-paper-plane"></i> <span>Enviar link</span>
                    </button>
                </form>
 
                <p class="auth-footer-link">Lembrou a senha? <a href="../../front/login.html">Fazer login</a></p>
 
                <script>
                    // mostra "Enviando..." enquanto o servidor manda o e-mail
                    document.getElementById("esqueciForm").addEventListener("submit", function () {
                        var btn = document.getElementById("btnEnviar");
                        btn.classList.add("is-loading");
                        btn.querySelector("span").textContent = "Enviando...";
                    });
                </script>
                <?php } ?>
 
            </div>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>