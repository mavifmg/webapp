<?php

function obterCoordenadas($enderecoUsuario)
{
    // Coloque sua chave da DistanceMatrix.ai aqui
    $chaveAPI = "3fXbxQhlneld7t4fe8ILwGceGr97bRS4i9VkWFkGe1W6tgWXB5NAPsi8BLL6Hyjq";

    // Monta a URL corretamente
    $url = "https://api.distancematrix.ai/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=3fXbxQhlneld7t4fe8ILwGceGr97bRS4i9VkWFkGe1W6tgWXB5NAPsi8BLL6Hyjq"
         . "address=" . urlencode($enderecoUsuario)
         . "&key=" . urlencode($chaveAPI);

    // Faz a requisição
    $resposta = file_get_contents($url);

    // Verifica se conseguiu acessar a API
    if ($resposta === false) {

    die("Erro ao acessar a API de geocodificação.");
        //return false;
    }

    // Converte a resposta JSON para array
    $dados = json_decode($resposta, true);

    // Verifica se o JSON foi convertido corretamente
    if ($dados === null) {
        
        die("ERRO: A API não retornou um JSON válido.<br><br>"
            . htmlspecialchars($resposta));
        return false;
    }

    // Verifica o status retornado pela API
    if (
        isset($dados["status"]) &&
        $dados["status"] !== "OK"
    ) {
        die("ERRO: A API retornou o status: " . htmlspecialchars($dados["status"]));
    }

    // Verifica se existem resultados
    if (
    !isset($dados["results"]) ||
    empty($dados["results"])
) {
    die(
        "A DistanceMatrix.ai não encontrou o endereço:<br><br>"
        . htmlspecialchars($enderecoUsuario)
        . "<br><br>"
        . "Tente informar o endereço sem o número da residência."
    );
}

    // Verifica latitude e longitude
    if (
        !isset($dados["results"][0]["geometry"]["location"]["lat"]) ||
        !isset($dados["results"][0]["geometry"]["location"]["lng"])
    ) {
        return false;
    }

    // Pega latitude
    $latitude = $dados["results"][0]["geometry"]["location"]["lat"];

    // Pega longitude
    $longitude = $dados["results"][0]["geometry"]["location"]["lng"];

    // Retorna as coordenadas
    return [
        "latitude" => $latitude,
        "longitude" => $longitude
    ];
}