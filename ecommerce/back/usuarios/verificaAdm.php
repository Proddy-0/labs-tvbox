<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    // caminho relativo que funciona de qualquer pasta dentro de back/
    header("Location: ../../front/login.html");
    exit;
}
?>
