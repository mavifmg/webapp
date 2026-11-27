<?php
session_start();
require_once "Conexao.php";

if (!isset($_SESSION['CPF'])) {
    header("Location: Login.php");
    exit();
}

$sql = 'SELECT 
            "nomeubs",
            "endereco",
            "horariofuncio",
            "infoextra",
            "NumUBS",
            "latitudeubs",
            "longitudeubs"
        FROM cadastroubs
        ORDER BY "nomeubs"';

$result = pg_query($conn, $sql);

if ($result === false) {
    die("Erro na consulta: " . pg_last_error($conn));
}

$ubs = [];

while ($row = pg_fetch_assoc($result)) {
    $ubs[] = $row;
}

$ubsJson = json_encode($ubs, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <title>Mapa das UBS</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        #mapa {
            width: 100%;
            height: 500px;
        }
    </style>
</head>

<body>

    <a href="Menu.php">
        <button type="button">Voltar</button>
    </a>

    <div id="mapa"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>

        // Cria o mapa
        const map = L.map("mapa");

        // Adiciona o OpenStreetMap
        L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                maxZoom: 19,
                attribution: "© OpenStreetMap contributors"
            }
        ).addTo(map);

        // UBS vindas diretamente do PostgreSQL
        const ubs = <?php echo $ubsJson; ?>;

        // Guarda os pontos das UBS
        const pontos = [];

        // Cria um marcador para cada UBS
        ubs.forEach(function (ubs) {

            const latitude = parseFloat(ubs.latitudeubs);
            const longitude = parseFloat(ubs.longitudeubs);

            // Ignora UBS sem coordenadas válidas
            if (isNaN(latitude) || isNaN(longitude)) {
                return;
            }

            // Guarda a localização
            pontos.push([latitude, longitude]);

            // Cria o marcador
            L.marker([latitude, longitude])
                .addTo(map)

                .bindPopup(`
                    <strong>${ubs.nomeubs}</strong><br>
                   Endereço: ${ubs.endereco}
                    Nº ${ubs.NumUBS}<br>
                   Horário de funcionamento: ${ubs.horariofuncio}<br>
                   Informações extras: ${ubs.infoextra}<br>

                `);
        });

        // Se existirem UBS, ajusta o mapa para mostrar todas
        if (pontos.length > 0) {

            const limites = L.latLngBounds(pontos);

            map.fitBounds(limites, {
                padding: [50, 50]
            });

        } else {

            // Caso não exista nenhuma UBS
            map.setView(
                [-20.5131, -43.7130],
                15
            );
        }

    </script>

</body>

</html>