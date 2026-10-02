<?php
session_start();
require_once "Conexao.php";

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

// Transforma o resultado do banco em um array PHP
$ubs = [];

while ($row = pg_fetch_assoc($result)) {
    $ubs[] = $row;
}

// Transforma o array PHP em JSON para o JavaScript
$ubsJson = json_encode($ubs, JSON_UNESCAPED_UNICODE);
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Mapa das UBS</title>

    <!-- CSS do Leaflet -->
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

    <!-- JS do Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>

        // 4. Cria o mapa
        const map = L.map("mapa").setView(
            [-20.5131, -43.7130],
            15
        );

        // 5. Adiciona o mapa OpenStreetMap
        L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                maxZoom: 19,
                attribution: "&copy; OpenStreetMap contributors"
            }
        ).addTo(map);


        // 6. Recebe as UBS que vieram do PHP
        const ubs = <?php echo $ubsJson; ?>;


        // 7. Cria automaticamente um marcador para cada UBS
        ubs.forEach(function (ubs) {

            const latitude = parseFloat(ubs.latitudeubs);
            const longitude = parseFloat(ubs.longitudeubs);

            // Verifica se a UBS possui coordenadas válidas
            if (isNaN(latitude) || isNaN(longitude)) {
                return;
            }

            // Cria o marcador
            L.marker([latitude, longitude])
                .addTo(map)

                // Informações que aparecem ao clicar no pino
                .bindPopup(`
                    <strong>${ubs.nomeubs}</strong><br>
                    ${ubs.endereco}<br>
                    Nº ${ubs.NumUBS}<br>
                    ${ubs.horariofuncio}
                `);
        });

    </script>

</body>

</html>