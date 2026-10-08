<?php

function obterCoordenadas($enderecoUsuario)
{
    /*
     * OpenStreetMap / Nominatim
     *
     * O endereço recebido deve ser algo como:
     * Rua Afonso Sardinha, 90, Ouro Branco, Minas Gerais, Brasil
     */

    $endereco = urlencode($enderecoUsuario);

    $url = "https://nominatim.openstreetmap.org/search?"
         . "q=" . $endereco
         . "&format=json"
         . "&limit=1"
         . "&countrycodes=br";

    /*
     * Nominatim exige um User-Agent.
     */
    $opcoes = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: AquiVacina/1.0\r\n"
        ]
    ];

    $contexto = stream_context_create($opcoes);

    /*
     * Faz a requisição.
     */
    $resposta = @file_get_contents($url, false, $contexto);

    /*
     * Verifica se conseguiu acessar o Nominatim.
     */
    if ($resposta === false) {
        return false;
    }

    /*
     * Converte o JSON.
     */
    $dados = json_decode($resposta, true);

    /*
     * Verifica se encontrou algum resultado.
     */
    if (
        !is_array($dados) ||
        empty($dados)
    ) {
        return false;
    }

    /*
     * Verifica latitude e longitude.
     */
    if (
        !isset($dados[0]["lat"]) ||
        !isset($dados[0]["lon"])
    ) {
        return false;
    }

    /*
     * Pega latitude.
     */
    $latitude = $dados[0]["lat"];

    /*
     * Pega longitude.
     */
    $longitude = $dados[0]["lon"];

    /*
     * Retorna as coordenadas.
     */
    return [
        "latitude" => $latitude,
        "longitude" => $longitude
    ];
}
