<?php
    // Mesmo formulário para adicionar (sem ?id) e alterar (?id=)
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    $id = (int) ($_GET['id'] ?? 0);
    $curso = ['nome' => '', 'cor' => '#14537D', 'classe' => ''];
    if ($id > 0) {
        $select = $conn->prepare("SELECT * FROM curso WHERE id_curso = :id AND excluido = false");
        $select->execute([':id' => $id]);
        $curso = $select->fetch(PDO::FETCH_ASSOC);
        if (!$curso) {
            echo "Curso não encontrado!";
            exit;
        }
    }

    // Estilos prontos do site (cores ajustadas pro tema claro/escuro). Sem estilo pronto, o card usa a cor escolhida.
    $estilos = [
        '' => 'Usar a cor escolhida',
        'is-info' => 'Azul do site (Informática)',
        'is-mec' => 'Vermelho do site (Mecânica)',
        'is-eletro' => 'Amarelo do site (Eletrônica)'
    ];

    $titulo = $id > 0 ? "Alterar curso" : "Adicionar curso";
    $ativo = "cursos";
    include "../layout/topo.php";
?>
    <main id="conteudo" class="container">
        <section class="page-hero">
            <span class="section-eyebrow">Painel administrativo</span>
            <h1><?= $titulo ?></h1>
        </section>

        <?php include "../layout/painel-nav.php"; ?>

        <section class="panel-card panel-form">
            <p class="form-legend"><span class="req" aria-hidden="true">*</span> campo obrigatório</p>
            <form action="salvarCurso.php" method="post">
                <input type="hidden" name="id_curso" value="<?= $id ?>">
                <div class="form-group">
                    <label for="nome">Nome <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="nome" name="nome" class="form-control" maxlength="60" value="<?= htmlspecialchars($curso['nome']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="cor">Cor <span class="req" aria-hidden="true">*</span></label>
                    <input type="color" id="cor" name="cor" class="form-control" value="<?= htmlspecialchars($curso['cor']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="classe">Estilo <span class="opt">(opcional)</span></label>
                    <select id="classe" name="classe" class="form-control">
                        <?php foreach ($estilos as $valor => $texto) { ?>
                        <option value="<?= $valor ?>"<?= ($curso['classe'] ?? '') == $valor ? ' selected' : '' ?>><?= $texto ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="cursos.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
<?php include "../layout/rodape.php"; ?>
