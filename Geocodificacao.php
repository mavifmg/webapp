<?php

function obterCoordenadas($CEP, $NumCasa)
{
    // Remove pontos, espaços e traços do CEP.
    $CEP = preg_replace('/\D/', '', $CEP);
    $NumCasa = trim($NumCasa);

    if (strlen($CEP) !== 8 || $NumCasa === '') {
        return false;
    }

    // 1. Consulta o ViaCEP para descobrir o endereço.
    $urlViaCEP = "https://viacep.com.br/ws/" . $CEP . "/json/";

    $opcoes = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: AquiVacina/1.0\r\n",
            "timeout" => 10
        ]
    ];

    $contexto = stream_context_create($opcoes);
    $respostaCEP = @file_get_contents(
        $urlViaCEP,
        false,
        $contexto
    );

    if ($respostaCEP === false) {
        return false;
    }

    $dadosCEP = json_decode($respostaCEP, true);

    if (
        !is_array($dadosCEP) ||
        isset($dadosCEP["erro"]) ||
        empty($dadosCEP["localidade"]) ||
        empty($dadosCEP["uf"])
    ) {
        return false;
    }

    // 2. Monta o endereço completo.
    $partes = [];

    if (!empty($dadosCEP["logradouro"])) {
        $partes[] = $dadosCEP["logradouro"];
    }

    $partes[] = $NumCasa;

    if (!empty($dadosCEP["bairro"])) {
        $partes[] = $dadosCEP["bairro"];
    }

    $partes[] = $dadosCEP["localidade"];
    $partes[] = $dadosCEP["uf"];
    $partes[] = "Brasil";

    $enderecoCompleto = implode(", ", $partes);

    // 3. Envia o endereço para o OpenStreetMap / Nominatim.
    $urlNominatim =
        "https://nominatim.openstreetmap.org/search?" .
        http_build_query([
            "q" => $enderecoCompleto,
            "format" => "jsonv2",
            "limit" => 1,
            "countrycodes" => "br"
        ]);

    $respostaMapa = @file_get_contents(
        $urlNominatim,
        false,
        $contexto
    );

    if ($respostaMapa === false) {
        return false;
    }

    $dadosMapa = json_decode($respostaMapa, true);

    if (
        !is_array($dadosMapa) ||
        empty($dadosMapa[0]["lat"]) ||
        empty($dadosMapa[0]["lon"])
    ) {
        return false;
    }

    return [
        "latitude" => (float) $dadosMapa[0]["lat"],
        "longitude" => (float) $dadosMapa[0]["lon"],
        "endereco" => $enderecoCompleto
    ];
}