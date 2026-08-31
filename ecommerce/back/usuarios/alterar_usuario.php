<html>
<body>
<?php
    include "../util.php";
    $conn = conecta();
    $id = $_GET['id'];
    $varSQL = "
        SELECT *
        FROM usuario
        WHERE id_usuario = :id";
    $select = $conn->prepare($varSQL);
    $select->bindParam(':id', $id);
    $select->execute();
    $linha = $select->fetch(PDO::FETCH_ASSOC);
    if (!$linha) {
        echo "Usuário não encontrado!";
        exit;
    }
    $nome = $linha['nome'];
    $telefone = $linha['telefone'];
    $email = $linha['email'];
?>
    <form
        action="updateUsuario.php"
        method="post"
        enctype="multipart/form-data"
    >
        <input
            type="hidden"
            name="id_usuario"
            value="<?= $id ?>"
        >
        Nome<br>
        <input
            type="text"
            name="nome"
            value="<?= htmlspecialchars($nome) ?>"
            required
        >
        <br><br>
        Email<br>
        <input
            type="email"
            name="email"
            value="<?= htmlspecialchars($email) ?>"
            required
        >
        <br><br>
        Telefone<br>
        <input
            type="text"
            name="telefone"
            value="<?= htmlspecialchars($telefone) ?>"
        >
        <br><br>
<?php
        $imagem = "";
        $extensoes = ['jpg','jpeg','png','gif'];
        foreach ($extensoes as $ext) {
            $arquivoImagem ="imagens/usuarios/$id.$ext";
            if (file_exists($arquivoImagem)) {
                $imagem = $arquivoImagem;
                break;
            }
        }
        if ($imagem != "") {
            echo"
                <img
                    src='$imagem'
                    height='80'>
                <br><br>";
        }
?>
    Nova imagem<br>
    <input
        type="file"
        name="arquivo"
        accept="image/*"
    >
    <br><br>
    <input
        type="submit"
        value="Salvar"
    >
</form>
</body>
</html>