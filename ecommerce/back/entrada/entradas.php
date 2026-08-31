<html>
<body>
    <h2>Entradas de estoque</h2>
    <?php
    include "../util.php";
    $conn = conecta();
    $varSQL = "
        SELECT
            entrada.id_entrada,
            entrada.quantidade,
            entrada.custo_unitario,
            entrada.obs,
            entrada.data_entrada,
            entrada.fk_produto,
            produto.descricao
        FROM entrada
        INNER JOIN produto
            ON produto.id_produto =
               entrada.fk_produto
        ORDER BY entrada.data_entrada DESC
    ";
    $select = $conn->query($varSQL);
    echo "
        <table border='1'>
            <tr>
                <td>ID</td>
                <td>Produto</td>
                <td>Quantidade</td>
                <td>Custo unitário</td>
                <td>Observação</td>
                <td>Data</td>
                <td>Alterar</td>
                <td>Excluir</td>
            </tr>";
    while ($linha =$select->fetch(PDO::FETCH_ASSOC))
    {
        $id =$linha['id_entrada'];
        $produto = htmlspecialchars($linha['descricao']);
        $quantidade = $linha['quantidade'];
        $custo = $linha['custo_unitario'];
        $obs = htmlspecialchars($linha['obs'] ?? '');
        $data = date( 'd/m/Y H:i', strtotime($linha['data_entrada']) );
        echo "
            <tr>
                <td>$id</td>
                <td>$produto</td>
                <td>$quantidade</td>
                <td>R$ " .
                    number_format(
                        $custo,2,',','.')
                . "</td>
                <td>$obs</td>
                <td>$data</td>
                <td>
                    <a href='alterarEntradas.php?id=$id'>
                        Alterar
                    </a>
                </td>
                <td>
                    <a
                        href='excluirEntradas.php?id=$id'
                        onclick=\"return confirm('Deseja excluir esta entrada?')\">
                        Excluir
                    </a>
                </td>
            </tr>
        ";
    }
    echo "
        </table>
        <br>
        <a href='adicionarEntradas.php'>
            Adicionar entrada
        </a>";
    ?>
</body>
</html>