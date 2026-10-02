<?php

session_start();

require_once "Conexao.php";

if (!isset($_SESSION['CPF']) || !isset($_SESSION['DataNascimento'])) {

    header("Location: Login.php");

    exit();
}

$CPF = $_SESSION['CPF'];
$DataNascimento = $_SESSION['DataNascimento'];

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AquiVacina | Site Para Usuarios</title>

    <link rel="stylesheet" href="AquiVacina.css">


</head>

<body class="Menu">

    <div class="LocMenu">

        <div class="FundoLog">

            <br>

            <div class="InformacoesUbs">

                <a href="javascript:history.back()" class="Voltar">
                    ←
                </a>

                <form action="Menu.php">

                    <div>
                        <a href="Cadastrocriancas.php">
                            <button type="button" class="BotaoCadastrar">Cadastro de Crianças</button>
                        </a>

                        <a href="DadosUser.php">
                            <button type="button" class="BotaoCadastrar">Meus Dados</button>
                        </a>

                        <a href="Mapa.php">
                            <button type="button" class="BotaoCadastrar">Informações da UBS</button>
                        </a>
                    </div>

                </form>

                <div class="CartaoLocalizacao" style="text-align: center;">
                    <h3>Sua localização</h3>
                    <div id="resultado">
                        Obtendo sua localização...
                    </div>
                </div>

                <script>

                    function pegarLocalizacao() {

                        const elementoResultado = document.getElementById("resultado");

                        elementoResultado.innerText = "Obtendo sua localização...";

                        if (navigator.geolocation) {

                            navigator.geolocation.getCurrentPosition(

                                function (posicao) {

                                    const latitude = posicao.coords.latitude;
                                    const longitude = posicao.coords.longitude;

                                    elementoResultado.innerHTML =
                                        "Sua Latitude: " + latitude +
                                        "<br>Sua Longitude: " + longitude;

                                },

                                function (erro) {

                                    elementoResultado.innerText =
                                        "Não foi possível acessar sua localização.";

                                }

                            );

                        } else {

                            elementoResultado.innerText =
                                "Seu navegador não suporta geolocalização.";

                        }

                    }

                    // Executa automaticamente quando o Menu abrir
                    window.onload = function () {
                        pegarLocalizacao();
                    };

                </script>

            </div>

        </div>

    </div>

</body>

</html>