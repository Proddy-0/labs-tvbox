<?php
// Exige usuário logado (cliente ou admin). Caminho relativo vale pra qualquer pasta dentro de back/
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../front/login.html");
    exit;
}
?>
