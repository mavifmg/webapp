<?php

function obterCoordenadasUBS($endereco)
{
    $endereco = trim($endereco);

    if ($endereco === '') {
        return false;
    }

    $enderecoCompleto = $endereco . ", Ouro Branco, Minas Gerais, Brasil";

    $consultas = [
        $enderecoCompleto,
        preg_replace(
            '/,\s*\d+\s*(?=-|,)/',
            '',
            $endereco
        ) . ", Ouro Branco, Minas Gerais, Brasil"
    ];

    foreach ($consultas as $indice => $consulta) {
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
        $resposta = @file_get_contents($url, false, $contexto);

        if ($resposta === false) {
            continue;
        }

        $dados = json_decode($resposta, true);

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
