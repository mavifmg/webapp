<?php
session_start();
require_once "Conexao.php";

// 1. Verifica se o usuário está logado.
if (
    !isset($_SESSION["CPF"]) ||
    !isset($_SESSION["DataNascimento"])
) {
    header("Location: Login.php");
    exit();
}

$CPF = $_SESSION["CPF"];
$DataNascimento = $_SESSION["DataNascimento"];

// 2. Busca as coordenadas do usuário logado.
$sqlUsuario = '
    SELECT
        "LatitudeUser",
        "LongitudeUser"
    FROM cadastro
    WHERE "CPF" = $1
      AND "DataNascimento" = $2
    LIMIT 1
';

$resultUsuario = pg_query_params(
    $conn,
    $sqlUsuario,
    [$CPF, $DataNascimento]
);

if ($resultUsuario === false) {
    die("Erro ao buscar usuário: " . pg_last_error($conn));
}

$usuario = pg_fetch_assoc($resultUsuario);

// 3. Busca as UBS cadastradas.
$sqlUBS = '
    SELECT
        "nomeubs",
        "endereco",
        "horariofuncio",
        "infoextra",
        "NumUBS",
        "latitudeubs",
        "longitudeubs"
    FROM cadastroubs
    ORDER BY "nomeubs"
';

$resultUBS = pg_query($conn, $sqlUBS);

if ($resultUBS === false) {
    die("Erro ao buscar UBS: " . pg_last_error($conn));
}

$ubs = [];

while ($row = pg_fetch_assoc($resultUBS)) {
    $ubs[] = $row;
}

// 4. Converte os dados para JavaScript com segurança.
$usuarioJson = json_encode(
    $usuario ?: null,
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

$ubsJson = json_encode(
    $ubs,
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AquiVacina | Mapa das UBS</title>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        #mapa {
            width: 100%;
            height: 500px;
        }

        .legenda {
            margin: 12px 0;
        }

        .ponto-usuario {
            display: inline-block;
            width: 12px;
            height: 12px;
            background: #1677ff;
            border: 2px solid white;
            border-radius: 50%;
            margin-right: 5px;
        }
    </style>
</head>

<body>

    <a href="Menu.php">
        <button type="button">Voltar</button>
    </a>

    <h2>UBS próximas de você</h2>

    <div class="legenda">
        <span class="ponto-usuario"></span>
        Sua localização cadastrada
    </div>

    <div id="mapa"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // 5. Recebe os dados do PHP.
        const usuario = <?php echo $usuarioJson ?: 'null'; ?>;
        const ubs = <?php echo $ubsJson ?: '[]'; ?>;

        // Inicializa o mapa.
        const map = L.map("mapa");

        L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                maxZoom: 19,
                attribution: "&copy; OpenStreetMap contributors"
            }
        ).addTo(map);

        // Guarda as localizações para ajustar o enquadramento.
        const pontos = [];

        // 6. Adiciona o marcador do usuário.
        if (
            usuario &&
            usuario.LatitudeUser !== null &&
            usuario.LongitudeUser !== null
        ) {
            const latitudeUsuario = Number(usuario.LatitudeUser);
            const longitudeUsuario = Number(usuario.LongitudeUser);

            if (
                Number.isFinite(latitudeUsuario) &&
                Number.isFinite(longitudeUsuario) &&
                Math.abs(latitudeUsuario) <= 90 &&
                Math.abs(longitudeUsuario) <= 180
            ) {
                pontos.push([latitudeUsuario, longitudeUsuario]);

                L.circleMarker(
                    [latitudeUsuario, longitudeUsuario],
                    {
                        radius: 9,
                        color: "#ffffff",
                        weight: 3,
                        fillColor: "#ff5c16",
                        fillOpacity: 1
                    }
                )
                .addTo(map)
                .bindPopup("<strong>Sua localização cadastrada</strong>")
                .openPopup();
            }
        }

        // 7. Adiciona os marcadores de todas as UBS.
        ubs.forEach(function (unidade) {
            const latitude = Number(unidade.latitudeubs);
            const longitude = Number(unidade.longitudeubs);

            if (
                unidade.latitudeubs === null ||
                unidade.longitudeubs === null ||
                unidade.latitudeubs === "" ||
                unidade.longitudeubs === "" ||
                !Number.isFinite(latitude) ||
                !Number.isFinite(longitude) ||
                Math.abs(latitude) > 90 ||
                Math.abs(longitude) > 180
            ) {
                return;
            }

            pontos.push([latitude, longitude]);

            const marcador = L.marker([latitude, longitude])
                .addTo(map);

            // Cria o conteúdo do popup como texto seguro.
            const conteudo = document.createElement("div");

            function adicionarTexto(texto, negrito = false) {
                const linha = document.createElement("div");

                if (negrito) {
                    const forte = document.createElement("strong");
                    forte.textContent = texto;
                    linha.appendChild(forte);
                } else {
                    linha.textContent = texto;
                }

                conteudo.appendChild(linha);
            }

            adicionarTexto(unidade.nomeubs || "UBS", true);
            adicionarTexto("Endereço: " + (unidade.endereco || "Não informado"));
            adicionarTexto("Número: " + (unidade.NumUBS || "Não informado"));
            adicionarTexto("Horário: " + (unidade.horariofuncio || "Não informado"));
            adicionarTexto("Informações: " + (unidade.infoextra || "Não informado"));

            marcador.bindPopup(conteudo);
        });

        // 8. Ajusta o mapa para mostrar usuário e UBS.
        if (pontos.length > 0) {
            map.fitBounds(L.latLngBounds(pontos), {
                padding: [40, 40],
                maxZoom: 16
            });
        } else {
            map.setView([-20.5131, -43.7130], 14);
        }
    </script>

</body>
</html>