<?php

// Carrega as variaveis do .env, se ainda nao tiverem sido carregadas
function carregaEnv($paramCaminho)
{
    if (!file_exists($paramCaminho)) {
        return;
    }

    foreach (file($paramCaminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);

        // pula comentarios
        if ($linha === "" || $linha[0] === "#") {
            continue;
        }

        [$nome, $valor] = explode("=", $linha, 2);
        $nome  = trim($nome);
        $valor = trim($valor);

        putenv("$nome=$valor");
        $_ENV[$nome] = $valor;
    }
}

// util.php esta em back/, e o .env esta na raiz do projeto (um nivel acima)
carregaEnv(dirname(__DIR__) . "/.env");

function conecta($paramString = "")
{
    // nao mandou nada, monta a string a partir das variaveis do .env
    if ($paramString == "") {
        $host     = $_ENV['DB_HOST'];
        $port     = $_ENV['DB_PORT'];
        $database = $_ENV['DB_DATABASE'];
        $user     = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        $string_conexao = "pgsql:host=$host;port=$port;dbname=$database;user=$user;password=$password";
    } else {
        // assume a string recebida
        $string_conexao = $paramString;
    }

    try { //tente
        $c = new PDO($string_conexao);
    } catch (PDOException $e) { // se der erro ...
        echo "Serviço indisponivel no momento,
                tente mais tarde !<br>" . $e->getMessage();
        exit;
    }
    return $c;
}

function salvaUpload($id, $paramCaminho, $paramFiles, $paramCampo)
{
    if (isset($paramFiles[$paramCampo])) {
        // obtem a extensão do arquivo
        $ext = pathinfo(
            $paramFiles[$paramCampo]['name'],
            PATHINFO_EXTENSION
        );
        $arquivoImagem = "$paramCaminho/$id.$ext";
        try {
            if (move_uploaded_file(
                $paramFiles[$paramCampo]['tmp_name'],
                $arquivoImagem
            )) {
                echo "<br>Arquivo $arquivoImagem criado com sucesso.\n";
            }
        } catch (PDOException $e) { // se der erro ...
            echo "Erro, verifique o arquivo se a pasta imagens existe";
        }
    }
}
