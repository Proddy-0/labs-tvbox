<html>
<body>
    <form action="" method="POST">
        Nome:<br>
        <input
            type="text"
            name="nome"
            value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>"
        >
        <input type="submit" value="Filtrar">
    </form>
<?php
    include "../util.php";
    $conn = conecta();
    if (isset($_POST['nome']) && $_POST['nome'] != "") { 
        $varSQL = "
            SELECT *
            FROM usuario
            WHERE nome ILIKE :paramNome
            AND excluido = false
            ORDER BY nome";
        $select = $conn->prepare($varSQL);
        $nomeFiltro = "%" . $_POST['nome'] . "%";
        $select->bindParam(
            ":paramNome",
            $nomeFiltro);
        $select->execute();
    } else {
        $varSQL = "
            SELECT *
            FROM usuario
            WHERE excluido = false
            ORDER BY nome";
        $select = $conn->query($varSQL);
    }
    echo "
    <table border='1'>
        <tr>
            <td>Nome</td>
            <td>Email</td>
            <td>Telefone</td>
            <td>Foto</td>
            <td>Alterar</td>
            <td>Excluir</td>
        </tr>
    ";
    while ($linha = $select->fetch(PDO::FETCH_ASSOC)) {
        $id = $linha['id_usuario'];
        $nome = htmlspecialchars($linha['nome']);
        $email = htmlspecialchars($linha['email']);
        $telefone = htmlspecialchars($linha['telefone']);
        // Procura a imagem pelo ID
        $imagem = "";
        $extensoes = ['jpg', 'jpeg', 'png', 'gif'];
        foreach ($extensoes as $ext) {
            $arquivoImagem = "imagens/usuarios/$id.$ext";
            if (file_exists($arquivoImagem)) {
                $imagem = $arquivoImagem;
                break;
            }
        }
        if ($imagem == "") {
            $imagem = "imagens/semnome.jpg";
        }
        echo "
        <tr>
            <td>$nome</td>
            <td>$email</td>
            <td>$telefone</td>
            <td>
                <img
                    src='$imagem'
                    height='40'
                >
            </td>
            <td>
                <a href='alterar_usuario.php?id=$id'>
                    Alterar
                </a>
            </td>
            <td>
                <a
                    href='excluir_usuarios.php?id=$id'
                    onclick=\"return confirm('Deseja excluir este usuário?')\">
                    Excluir
                </a>
            </td>
        </tr>
        ";
    }
    echo "
    </table>
    <br>
    <a href='adicionarUsuario.php'>
        Adicionar usuário
    </a>
    ";
?>
</body>
</html>