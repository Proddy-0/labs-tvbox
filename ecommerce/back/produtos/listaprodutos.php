<html>
<body>
    <form action="" method="POST">
        Valor máximo:<br>
        <input type="number"
               name="valor"
               step="0.01"
               min="0">
        <input type="submit" value="Filtrar">
    </form>
    <?php
    include "../util.php";
    $conn = conecta();
    if (
        isset($_POST['valor']) && $_POST['valor'] != "")
    {
        $varSQL = "
            SELECT *
            FROM produto
            WHERE valor_unitario <= :paramValor
              AND excluido = false
            ORDER BY nome";
        $select = $conn->prepare($varSQL);
        $select->bindParam(
            ":paramValor",
            $_POST['valor']);
        $select->execute();
    } else {
        $varSQL = "
            SELECT *
            FROM produto
            WHERE excluido = false
            ORDER BY nome";
        $select = $conn->query($varSQL);
    }
    echo "
        <table border='1'>
            <tr>
                <td>Id</td>
                <td>Nome</td>
                <td>Descrição</td>
                <td>Valor</td>
                <td>Foto</td>
                <td>Alterar</td>
                <td>Excluir</td>
            </tr>";
    while ($linha = $select->fetch(PDO::FETCH_ASSOC)) {
        $id = $linha['id_produto'];
        $nome = htmlspecialchars($linha['nome']);
        $descricao = htmlspecialchars($linha['descricao']);
        $valor = $linha['valor_unitario'];
        $nomeArquivo = "";
        $extensoes = [
            'jpg','jpeg','png','gif'
        ];
        foreach ($extensoes as $ext) {
            $arquivo = "imagens/produtos/$id.$ext";
            if (file_exists($arquivo)) {
                $nomeArquivo = $arquivo;
                break;
            }
        }
        if ($nomeArquivo != "") {
            $foto = "<img src='$nomeArquivo' height='40'>";
        } else {
            $foto = "Sem imagem";
        }
        echo "
            <tr>
                <td>$id</td>
                <td>$nome</td>
                <td>$descricao</td>
                <td>R$ " .
                    number_format($valor,2,',','.')
                . "</td>
                <td>$foto</td>
                <td>
                    <a href='Alterarprodutos.php?id=$id'>
                        Alterar
                    </a>
                </td>
                <td>
                    <a href='Excluirprodutos.php?id=$id'
                        onclick=\"return confirm('Deseja excluir este produto?')\">
                        Excluir
                    </a>
                </td>
            </tr>";
    }
    echo "
        </table>
        <br>
        <a href='Adicionarprodutos.php'>
            Adicionar produto
        </a>";
    ?>
</body>
</html>