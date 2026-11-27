<?php
header("Content-Type: text/html; charset=UTF-8");

$host = "10.90.24.54";
$port = "5432";
$dbname = "AquiVacina";
$user = "aula";
$password = "aula";

$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);

if (!$conn) {
    http_response_code(500);
    exit("Não foi possível conectar ao banco de dados.");
}

$resultado = pg_query(
    $conn,
    'SELECT nomevacina, quantiaestoque, nomeubs
     FROM estoque
     ORDER BY nomevacina'
);

if (!$resultado) {
    http_response_code(500);
    exit("Erro ao consultar o estoque. Verifique a tabela estoque.");
}

function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

$cartoes = "";

while ($vacina = pg_fetch_assoc($resultado)) {
    $nome = escapar($vacina["nomevacina"]);
    $quantidade = (int)$vacina["quantiaestoque"];
    $ubs = escapar($vacina["nomeubs"] ?? "");

    $situacao = $quantidade < 10
        ? "Estoque baixo"
        : "Disponível";

    $classe = $quantidade < 10
        ? "EstoqueBaixo"
        : "Disponivel";

    $cartoes .= '
        <div class="VacinaItem">
            <h3>' . $nome . '</h3>

            <p>
                <strong>Quantidade disponível:</strong>
                ' . $quantidade . ' doses
            </p>

            <p>
                <strong>Situação:</strong>
                <span class="' . $classe . '">' . $situacao . '</span>
            </p>';

    if ($ubs !== "") {
        $cartoes .= '
            <p>
                <strong>Unidade de saúde:</strong>
                ' . $ubs . '
            </p>';
    }

    $cartoes .= '</div>';
}

if ($cartoes === "") {
    $cartoes = '<p>Nenhuma vacina cadastrada no estoque.</p>';
}

$html = file_get_contents(__DIR__ . "/VacinasDisponiveis.html");

if ($html === false) {
    http_response_code(500);
    exit("Não foi possível carregar o modelo da página.");
}

$padrao = '/<!--\s*VACINAS\s*-->\s*<section>.*?<\/section>/s';

$substituto = '
<section>
    <h2>💉 Vacinas em estoque</h2>
    ' . $cartoes . '
</section>';

$htmlNovo = preg_replace($padrao, $substituto, $html, 1, $total);

if ($htmlNovo === null || $total !== 1) {
    http_response_code(500);
    exit("Não foi possível localizar a lista de vacinas no HTML. O arquivo original foi preservado.");
}

$html = $htmlNovo;

/* Corrige textos que estejam salvos com codificação incorreta. */
$html = str_replace(
    [
        "Vacinas DisponÃveis",
        "Vacinas disponÃveis",
        "disponÃveis",
        "DisponÃvel",
        "situaÃ§Ã£o",
        "SituaÃ§Ã£o",
        "saÃºde",
        "InformaÃ§Ãµes",
        "INFORMAÃ‡Ã•ES",
        "BOTÃƒO",
        "CONTEÃšDO",
        "CABEÃ‡ALHO",
        "RODAPÃ‰",
        "TrÃplice",
        "RotavÃrus",
        "PneumocÃ³cica",
        "MeningocÃ³cica",
        "â†"
    ],
    [
        "Vacinas Disponíveis",
        "Vacinas disponíveis",
        "disponíveis",
        "Disponível",
        "situação",
        "Situação",
        "saúde",
        "Informações",
        "INFORMAÇÕES",
        "BOTÃO",
        "CONTEÚDO",
        "CABEÇALHO",
        "RODAPÉ",
        "Tríplice",
        "Rotavírus",
        "Pneumocócica",
        "Meningocócica",
        "←"
    ],
    $html
);

echo $html;
?>
