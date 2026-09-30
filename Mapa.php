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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mapa Simples</title>

    <!-- CSS do Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        #mapa {
            width: 100%;
            height: 500px;
        }
    </style>
</head>
<body>

    <div id="mapa"></div>

    <!-- JS do Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php
$dadosUBS = [
    "nome" => $usuario["nomeubs"] ?? "UBS",
    "endereco" => $usuario["endereco"] ?? "Endereço não informado",
    "horario" => $usuario["horariofuncio"] ?? "Horário não informado",
    "informacoes" => $usuario["infoextra"] ?? "",
    "latitude" => is_numeric($usuario["latitude"] ?? null)
        ? (float) $usuario["latitude"] : null,
    "longitude" => is_numeric($usuario["longitude"] ?? null)
        ? (float) $usuario["longitude"] : null
];
?>

<script>
    const ubs = <?= json_encode(
        $dadosUBS,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;

    // Cria o mapa
    const map = L.map("mapa");

    // Adiciona as imagens do OpenStreetMap
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    // Verifica se a UBS possui coordenadas cadastradas
    if (ubs.latitude !== null && ubs.longitude !== null) {

        // Centraliza o mapa na UBS
        map.setView([ubs.latitude, ubs.longitude], 16);

        // Cria o marcador da UBS
        L.marker([ubs.latitude, ubs.longitude])
            .addTo(map)
            .bindPopup(`
                <strong>${ubs.nome}</strong><br>
                <strong>Endereço:</strong> ${ubs.endereco}<br>
                <strong>Horário:</strong> ${ubs.horario}<br>
                <strong>Informações:</strong> ${ubs.informacoes}
            `)
            .openPopup();

    } else {
        // Sem coordenadas cadastradas, mostra Ouro Branco como referência
        map.setView([-20.5197, -43.6905], 14);

        L.popup()
            .setLatLng([-20.5197, -43.6905])
            .setContent(
                "A UBS ainda não possui latitude e longitude cadastradas."
            )
            .openOn(map);
    }
</script>

</body>
</html>