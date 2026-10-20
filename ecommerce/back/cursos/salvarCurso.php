<?php
    // Insere (id_curso = 0) ou altera um curso
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();

    $id = (int) ($_POST['id_curso'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $cor = preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['cor'] ?? '') ? strtoupper($_POST['cor']) : '#14537D';
    $classe = in_array($_POST['classe'] ?? '', ['is-info', 'is-mec', 'is-eletro']) ? $_POST['classe'] : null;

    if ($nome == '') {
        header("Location: formCurso.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    if ($id > 0) {
        $varSQL = "
            UPDATE curso
            SET nome = :nome, cor = :cor, classe = :classe
            WHERE id_curso = :id";
        $params = [':nome' => $nome, ':cor' => $cor, ':classe' => $classe, ':id' => $id];
    } else {
        $varSQL = "
            INSERT INTO curso (nome, cor, classe)
            VALUES (:nome, :cor, :classe)";
        $params = [':nome' => $nome, ':cor' => $cor, ':classe' => $classe];
    }
    $conn->prepare($varSQL)->execute($params);
    header("Location: cursos.php");
    exit;
?>
