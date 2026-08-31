<html>
<body>
<form
    action="insertUsuario.php"
    method="post"
    enctype="multipart/form-data"
    onsubmit="return confirmarSenha()"
>
    Nome completo<br>
    <input
        type="text"
        name="nome"
        maxlength="80"
        required
    >
    <br><br>
    Email<br>
    <input
        type="email"
        name="email"
        maxlength="100"
        required
    >
    <br><br>
    Senha<br>
    <input
        type="password"
        id="senha"
        name="senha"
        maxlength="255"
        required
    >
    <br><br>
    Confirmar senha<br>
    <input
        type="password"
        id="confirmaSenha"
        name="confirmaSenha"
        maxlength="255"
        required
    >
    <br><br>
    Telefone<br>
    <input
        type="text"
        name="telefone"
        maxlength="20"
    >
    <br><br>
    Imagem<br>
    <input
        type="file"
        name="imagem"
        accept="image/*"
    >
    <br><br>
    <input
        type="submit"
        value="Cadastrar">
</form>
<script>
function confirmarSenha()
{
    let senha = document.getElementById("senha").value;
    let confirmaSenha = document.getElementById("confirmaSenha").value;
    if (senha !== confirmaSenha)
    {
        alert("As senhas não são iguais!");
        return false;
    }
    return true;
}
</script>
</body>
</html>