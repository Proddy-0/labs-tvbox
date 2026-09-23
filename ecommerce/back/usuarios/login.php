<?php
    include "../util.php";
    $conn = conecta();
    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $varSQL = "
            SELECT *
            FROM usuario
            WHERE email = :email
            AND excluido = false";
        $select = $conn->prepare($varSQL);
        $select->bindParam(':email', $email);
        $select->execute();
        $usuario = $select->fetch(PDO::FETCH_ASSOC);
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['admin'] = $usuario['admin'];
            header("Location: usuarios.php");
            exit;
        } else {
            echo "Email ou senha incorretos.";
        }
    }
?>
<html>
<body>
    <h2>Login</h2>
    <form method="post">
        Email<br>
        <input
            type="email"
            name="email"
            required
        >
        <br><br>
        Senha<br>
        <input
            type="password"
            name="senha"
            required
        >
        <br><br>
        <input
            type="submit"
            value="Entrar"
        >
        <br>
    <a href="adicionarUsuario.php">
        Criar conta
    </a>
    </form>
</body>
</html>
