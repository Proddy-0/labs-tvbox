<?php
    // Abre pelo link do e-mail: confere o token e deixa o usuário criar a nova senha
    require_once __DIR__ . '/../util.php';
    $conn = conecta();

    $email = trim((string) ($_REQUEST['e'] ?? ''));
    $expira = (int) ($_REQUEST['x'] ?? 0);
    $token = (string) ($_REQUEST['t'] ?? '');

    $valido = false;
    $erro = "";
    $trocou = false;

    // busca a senha atual do usuário e confere o token recebido
    if ($email != '' && $token != '' && $expira > time()) {
        $select = $conn->prepare("
            SELECT id_usuario, senha
            FROM usuario
            WHERE email = :email
            AND excluido = false");
        $select->execute([':email' => $email]);
        $usuario = $select->fetch(PDO::FETCH_ASSOC);

        if ($usuario && hash_equals(geraTokenRecuperacao($email, $usuario['senha'], $expira), $token)) {
            $valido = true;
        }
    }

    // token diferente, vencido ou usuário inexistente: alguém mexeu no link, para por aqui
    if ($valido && $_SERVER["REQUEST_METHOD"] == "POST") {
        $senha = $_POST['senha'] ?? '';
        $confirma = $_POST['confirmaSenha'] ?? '';

        if (strlen($senha) < 6) {
            $erro = "A senha precisa ter pelo menos 6 caracteres.";
        } elseif ($senha !== $confirma) {
            $erro = "As senhas não coincidem.";
        } else {
            $update = $conn->prepare("
                UPDATE usuario
                SET senha = :senha
                WHERE id_usuario = :id");
            $update->execute([
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
                ':id' => $usuario['id_usuario']
            ]);
            $trocou = true;
        }
    }

    $titulo = "Redefinir senha";
    $ativo = "";
    include "../layout/topo.php";
    include __DIR__ . '/../../front/css/recuperacao_estilo.php';
?>
    <main id="conteudo">
        <section class="auth-section container">
            <div class="auth-card recovery-card">

                <?php if ($trocou) { ?>
                <div class="recovery-result is-ok">
                    <div class="result-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <h1>Senha alterada!</h1>
                    <p>Tudo certo. Agora você já pode entrar com a sua nova senha.</p>
                    <div class="recovery-actions">
                        <a href="../../front/login.html" class="btn btn-primary btn-block"><i class="fa-solid fa-right-to-bracket"></i> Ir para o login</a>
                    </div>
                </div>

                <?php } elseif (!$valido) { ?>
                <div class="recovery-result is-erro">
                    <div class="result-icon"><i class="fa-solid fa-link-slash"></i></div>
                    <h1>Link inválido ou expirado</h1>
                    <p>Por segurança, o link vale por 1 hora e só pode ser usado uma vez. Peça um novo para continuar.</p>
                    <div class="recovery-actions">
                        <a href="esqueci.php" class="btn btn-primary btn-block"><i class="fa-solid fa-paper-plane"></i> Pedir novo link</a>
                        <a href="../../front/login.html" class="btn btn-outline btn-block"><i class="fa-solid fa-right-to-bracket"></i> Voltar ao login</a>
                    </div>
                </div>

                <?php } else { ?>
                <header class="auth-header">
                    <div class="auth-icon"><i class="fa-solid fa-lock"></i></div>
                    <h1>Criar nova senha</h1>
                    <p>Escolha uma senha nova para a conta <strong><?= htmlspecialchars($email) ?></strong>.</p>
                </header>

                <?php if ($erro != "") { ?>
                <p class="form-alert form-alert--error" role="alert"><?= htmlspecialchars($erro) ?></p>
                <?php } ?>

                <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>

                <form action="redefinir.php" method="post" id="redefinirForm">
                    <input type="hidden" name="e" value="<?= htmlspecialchars($email) ?>">
                    <input type="hidden" name="x" value="<?= $expira ?>">
                    <input type="hidden" name="t" value="<?= htmlspecialchars($token) ?>">

                    <div class="form-group">
                        <label for="senha">Nova senha <span class="req" aria-hidden="true">*</span></label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="senha" name="senha" class="form-control com-olho" placeholder="Mínimo 6 caracteres" minlength="6" maxlength="255" autocomplete="new-password" required>
                            <button type="button" class="senha-olho" data-alvo="senha" aria-label="Mostrar senha">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="strength" id="forca" data-nivel="0" aria-live="polite">
                            <div class="strength-bars"><span></span><span></span><span></span><span></span></div>
                            <div class="strength-label"><span>Força da senha</span><strong id="forcaTexto">Digite sua senha</strong></div>
                        </div>
                        <p class="field-hint">Dica: misture letras maiúsculas e minúsculas, números e símbolos.</p>
                    </div>

                    <div class="form-group">
                        <label for="confirmaSenha">Confirmar nova senha <span class="req" aria-hidden="true">*</span></label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="confirmaSenha" name="confirmaSenha" class="form-control com-olho" placeholder="Repita a senha" minlength="6" maxlength="255" autocomplete="new-password" required>
                            <button type="button" class="senha-olho" data-alvo="confirmaSenha" aria-label="Mostrar senha">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <p class="match-hint" id="matchHint" aria-live="polite"></p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="btnSalvar">
                        <i class="fa-solid fa-check"></i> <span>Salvar nova senha</span>
                    </button>
                </form>

                <script>
                    (function () {
                        var form = document.getElementById("redefinirForm");
                        var senha = document.getElementById("senha");
                        var confirma = document.getElementById("confirmaSenha");
                        var forca = document.getElementById("forca");
                        var forcaTexto = document.getElementById("forcaTexto");
                        var hint = document.getElementById("matchHint");
                        var rotulos = ["Digite sua senha", "Fraca", "Média", "Boa", "Forte"];

                        // 0 = vazia ... 4 = forte
                        function pontua(v) {
                            if (!v) return 0;
                            var p = 0;
                            if (v.length >= 6) p++;
                            if (v.length >= 10) p++;
                            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) p++;
                            if (/\d/.test(v)) p++;
                            if (/[^A-Za-z0-9]/.test(v)) p++;
                            return Math.min(4, Math.max(1, p - 1));
                        }

                        function atualizaForca() {
                            var n = pontua(senha.value);
                            forca.dataset.nivel = n;
                            forcaTexto.textContent = (senha.value && senha.value.length < 6) ? "Muito curta" : rotulos[n];
                        }

                        function atualizaConfere() {
                            if (!confirma.value) { hint.className = "match-hint"; hint.innerHTML = ""; return; }
                            var ok = confirma.value === senha.value;
                            hint.className = "match-hint show " + (ok ? "ok" : "erro");
                            hint.innerHTML = ok
                                ? '<i class="fa-solid fa-circle-check"></i> As senhas coincidem'
                                : '<i class="fa-solid fa-circle-xmark"></i> As senhas não coincidem';
                        }

                        senha.addEventListener("input", function () { atualizaForca(); atualizaConfere(); });
                        confirma.addEventListener("input", atualizaConfere);

                        // mostrar / ocultar senha
                        document.querySelectorAll(".senha-olho").forEach(function (btn) {
                            btn.addEventListener("click", function () {
                                var campo = document.getElementById(btn.dataset.alvo);
                                var mostrar = campo.type === "password";
                                campo.type = mostrar ? "text" : "password";
                                var icone = btn.querySelector("i");
                                icone.classList.toggle("fa-eye", !mostrar);
                                icone.classList.toggle("fa-eye-slash", mostrar);
                                btn.setAttribute("aria-label", mostrar ? "Ocultar senha" : "Mostrar senha");
                            });
                        });

                        form.addEventListener("submit", function (e) {
                            if (senha.value !== confirma.value) {
                                e.preventDefault();
                                atualizaConfere();
                                confirma.focus();
                                return;
                            }
                            var btn = document.getElementById("btnSalvar");
                            btn.classList.add("is-loading");
                            btn.querySelector("span").textContent = "Salvando...";
                        });
                    })();
                </script>
                <?php } ?>

            </div>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>