<?php
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $conn = conecta();
    $id = (int) ($_GET['id'] ?? 0);

    // curso com produto ativo não pode sair (o produto ficaria sem filtro/cor na vitrine)
    $emUso = $conn->prepare("
        SELECT COUNT(*) FROM produto
        WHERE fk_curso = :id AND excluido = false");
    $emUso->execute([':id' => $id]);
    if ($emUso->fetchColumn() > 0) {
        header("Location: cursos.php?emuso=1");
        exit;
    }

    $delete = $conn->prepare("
        UPDATE curso
        SET excluido = true, data_exclusao = CURRENT_TIMESTAMP
        WHERE id_curso = :id");
    $delete->execute([':id' => $id]);
    header("Location: cursos.php");
    exit;
?>
