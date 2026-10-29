<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "Conexao.php";

/*
 * Geocodificacao2.php
 * Busca as coordenadas das UBS e salva no banco.
 */

function obterCoordenadasUBS($endereco)
{
    $endereco = trim($endereco);

    if ($endereco === '') {
        return false;
    }

    $enderecoCompleto = $endereco . ", Ouro Branco, Minas Gerais, Brasil";

    // Primeira tentativa: endereço completo.
    $consultas = [
        $enderecoCompleto,

        // Segunda tentativa: rua, bairro e cidade, sem número.
        preg_replace(
            '/,\s*\d+\s*(?=-|,)/',
            '',
            $endereco
        ) . ", Ouro Branco, Minas Gerais, Brasil"
    ];

    foreach ($consultas as $indice => $consulta) {

        // Aguarda antes da segunda requisição.
        if ($indice > 0) {
            sleep(1);
        }

        $url = "https://nominatim.openstreetmap.org/search?" .
            http_build_query([
                "q" => $consulta,
                "format" => "jsonv2",
                "limit" => 1,
                "countrycodes" => "br"
            ]);

        $opcoes = [
            "http" => [
                "method" => "GET",
                "header" =>
                    "User-Agent: AquiVacina/1.0 (projeto educacional)\r\n" .
                    "Accept: application/json\r\n",
                "timeout" => 15,
                "ignore_errors" => true
            ]
        ];

        $contexto = stream_context_create($opcoes);

        $resposta = @file_get_contents(
            $url,
            false,
            $contexto
        );

        if ($resposta === false) {
            continue;
        }

        $dados = json_decode($resposta, true);

        // Se encontrar coordenadas, retorna imediatamente.
        if (
            is_array($dados) &&
            isset($dados[0]['lat'], $dados[0]['lon'])
        ) {
            return [
                'latitude' => (float) $dados[0]['lat'],
                'longitude' => (float) $dados[0]['lon']
            ];
        }
    }

    return false;
}



// Verifica a conexão com o banco.
if (!isset($conn) || !$conn) {
    die("Erro na conexão com o banco de dados.");
}

// Busca as UBS cadastradas.
$sql = '
    SELECT
        "nomeubs",
        "endereco",
        "latitudeubs",
        "longitudeubs"
    FROM cadastroubs
    ORDER BY "nomeubs"
';

$resultado = pg_query($conn, $sql);

if ($resultado === false) {
    die("Erro ao consultar UBS: " . pg_last_error($conn));
}

$totalAtualizadas = 0;
$totalFalhas = 0;
$totalIgnoradas = 0;

echo "<h1>Geocodificação das UBS</h1>";

while ($ubs = pg_fetch_assoc($resultado)) {

    $nomeUBS = $ubs['nomeubs'];
    $endereco = trim($ubs['endereco'] ?? '');

    echo "<hr><strong>UBS:</strong> " .
        htmlspecialchars($nomeUBS, ENT_QUOTES, 'UTF-8') . "<br>";

    // Preserva coordenadas já existentes.
    if (
        $ubs['latitudeubs'] !== null &&
        $ubs['longitudeubs'] !== null &&
        $ubs['latitudeubs'] !== '' &&
        $ubs['longitudeubs'] !== ''
    ) {
        echo "Já possui coordenadas. Nenhuma alteração realizada.";
        $totalIgnoradas++;
        continue;
    }

    if ($endereco === '') {
        echo "Endereço não informado.";
        $totalFalhas++;
        continue;
    }

    // Obtém latitude e longitude do endereço.
    $coordenadas = obterCoordenadasUBS($endereco);

    if ($coordenadas === false) {
        echo "Não foi possível localizar o endereço.";
        $totalFalhas++;
        continue;
    }

    // Salva as coordenadas na tabela cadastroubs.
    $sqlAtualizar = '
        UPDATE cadastroubs
        SET
            "latitudeubs" = $1,
            "longitudeubs" = $2
        WHERE "nomeubs" = $3
    ';

    $atualizacao = pg_query_params(
        $conn,
        $sqlAtualizar,
        [
            $coordenadas['latitude'],
            $coordenadas['longitude'],
            $nomeUBS
        ]
    );

    if ($atualizacao === false) {
        echo "Erro ao salvar as coordenadas.";
        $totalFalhas++;
        continue;
    }

    echo "<strong>Latitude:</strong> " .
        htmlspecialchars((string) $coordenadas['latitude']) . "<br>";

    echo "<strong>Longitude:</strong> " .
        htmlspecialchars((string) $coordenadas['longitude']) . "<br>";

    echo "Coordenadas salvas no banco de dados.";

    $totalAtualizadas++;

    // Respeita o intervalo recomendado pelo Nominatim.
    sleep(1);
}

pg_free_result($resultado);

echo "<hr><h2>Resultado</h2>";
echo "UBS atualizadas: " . $totalAtualizadas . "<br>";
echo "UBS com falhas: " . $totalFalhas . "<br>";
echo "UBS ignoradas (já tinham coordenadas): " .
    $totalIgnoradas . "<br>";

?>