<html>
<body>
    <form action="Insertprodutos.php" method="post"
        enctype="multipart/form-data">
        Nome<br>
        <input type="text" name="nome" required>
        <br><br>
        Descrição<br>
        <input type="text" name="descricao" maxlength="255" required>
        <br><br>
        Valor unitário<br>
        <input type="number" name="valor_unitario" step="0.01" min="0" required>
        <br><br>
        Imagem<br>
        <input type="file" name="arquivo" accept="image/*">
        <br><br>
        <input type="submit" value="Salvar">
    </form>
</body>
</html>