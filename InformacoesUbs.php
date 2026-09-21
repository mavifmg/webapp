<?php

session_start();
require_once "Conexao.php";

$sql = 'SELECT 
        "nomeubs",
        "endereco",
        "horariofuncio",
        "infoextra"
        FROM cadastroubs
        ORDER BY "nomeubs"';

$result = pg_query($conn, $sql);

if ($result === false) {
    die("Erro na consulta: " . pg_last_error($conn));
}

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AquiVacina | Dados DA UBS</title>

    <link rel="stylesheet" href="AquiVacina.css">

</head>


<body class="InformacoesUbs">


    <div class="LocInformacoesUbs">

        <div class="LocInformacoesUbs">

            <?php
            $usuario = ($usuario = pg_fetch_assoc($result))
                ? $usuario
                : [
                    "nomeubs" => "Não encontrado",
                    "endereco" => "Não encontrado",
                    "horariofuncio" => "Não encontrado",
                    "infoextra" => "Não encontrado"
                ]; ?>

            <h1>
                Dados da UBS: <?php echo $usuario["nomeubs"]; ?>
            </h1>


            <div class="Dados">

                <p>
                    <strong>Endereço:</strong>
                    <?php echo $usuario["endereco"]; ?>
                </p>


                <p>
                    <strong>Horário de Funcionamento:</strong>
                    <?php echo $usuario["horariofuncio"]; ?>
                </p>


                <p>
                    <strong>Informações Extras:</strong>
                    <?php echo $usuario["infoextra"]; ?>
                </p>

                <a href="VacinasDisp.php">
                    <button type="button">Vacinas Disponíveis</button>
                </a>
                <a href="LocUbs.php">
                    <button type="button">Localização da UBS: <?php echo $usuario["nomeubs"]; ?></button>
                </a>

                <a href="Menu.php">
                    <button type="button">Voltar</button>
                </a>

                <a href="ProfissionaisUbs.php"></a>
                <button type="button">Profissionais disponíveis nesta UBS</button>
                </a>

            </div>

        </div>

    </div>