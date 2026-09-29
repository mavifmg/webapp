<?php
session_start();
require_once "Conexao.php";

// Adicionado "latitude" e "longitude" na consulta SQL
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
        <a href="Menu.php">
            <button type="button">Voltar</button>
        </a>

        <?php
        $usuario = ($usuario = pg_fetch_assoc($result))
            ? $usuario
            : [
                "nomeubs" => "Não encontrado",
                "endereco" => "Não encontrado",
                "horariofuncio" => "Não encontrado",
                "infoextra" => "Não encontrado",
                "latitude" => "Não encontrado",
                "longitude" => "Não encontrado"
            ];
        ?>

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

            <a href="ProfissionaisUbs.php">
                <button type="button">Profissionais disponíveis nesta UBS</button>
            </a>

            <!-- Botão que aciona a geolocalização do navegador do usuário (se ainda quiser usar) -->
            <button type="button" onclick="pegarLocalizacao()">
                Obter minha localização atual
            </button>

            <!-- Onde a mensagem do GPS do usuário vai aparecer -->
            <p id="resultado"></p>
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
                            "Sua Latitude: " + latitude + "<br>Sua Longitude: " + longitude;
                    },
                    function (erro) {
                        elementoResultado.innerText = "Não foi possível acessar sua localização.";
                    }
                );
            } else {
                elementoResultado.innerText = "Seu navegador não suporta geolocalização.";
            }
        }
    </script>

</body>

</html>