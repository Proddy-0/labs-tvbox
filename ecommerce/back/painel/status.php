<?php
    // Muda o status de uma encomenda reservada: entregue ou cancelado
    include "../usuarios/verificaAdm.php";
    include "../util.php";
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['entregue', 'cancelado'])) {
        $conn = conecta();
        $update = $conn->prepare("
            UPDATE compra
            SET status = :status
            WHERE id_compra = :id_compra
              AND status = 'reservado'");
        $update->execute([
            ':status' => $status,
            ':id_compra' => $_POST['id_compra'] ?? 0
        ]);
    }
    header("Location: estoque.php");
    exit;
?>
