<?php
    // Cliente cancela a própria encomenda enquanto ela ainda está reservada (antes de ser entregue)
    include "../usuarios/verificaLogin.php";
    include "../util.php";
    $conn = conecta();
    $update = $conn->prepare("
        UPDATE compra
        SET status = 'cancelado'
        WHERE id_compra = :id_compra
          AND fk_usuario = :fk_usuario
          AND status = 'reservado'");
    $update->execute([
        ':id_compra' => $_POST['id_compra'] ?? 0,
        ':fk_usuario' => $_SESSION['id_usuario']
    ]);
    header("Location: encomendas.php" . ($update->rowCount() ? "?cancelada=1" : ""));
    exit;
?>
