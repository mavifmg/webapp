<?php
// Define que a resposta será em formato JSON
header('Content-Type: application/json; charset=utf-8');

// Inclui o arquivo de conexão com o banco (ajuste o nome se necessário)
require_once "Conexao.php";

// Verifica se a requisição veio via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Pega o conteúdo bruto enviado pelo fetch (JSON)
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    $latitudeUsuario = $data["latitude"] ?? null;
    $longitudeUsuario = $data["longitude"] ?? null;

    // Valida se as coordenadas foram recebidas
    if ($latitudeUsuario === null || $longitudeUsuario === null) {
        echo json_encode(["erro" => "Latitude ou longitude não fornecidas."]);
        exit;
    }

    try {
        // Exemplo de consulta SQL usando a fórmula de Haversine para achar as UBS mais próximas
        // Substitua 'tabela_ubs', 'latitude', 'longitude' e 'nome' pelos nomes reais das suas colunas e tabela no PostgreSQL
        $sql = "SELECT nome, endereco, 
                       (6371 * acos(cos(radians($1)) * cos(radians(latitude)) * cos(radians(longitude) - radians($2)) + sin(radians($1)) * sin(radians(latitude)))) AS distancia 
                FROM tabela_ubs 
                ORDER BY distancia ASC 
                LIMIT 5";

        $resultado = pg_query_params($conn, $sql, array($latitudeUsuario, $longitudeUsuario));

        if ($resultado) {
            $ubsList = [];
            while ($row = pg_fetch_assoc($resultado)) {
                $ubsList[] = [
                    "nome" => $row['nome'],
                    "endereco" => $row['endereco'],
                    "distancia" => round(floatval($row['distancia']), 2) // Arredonda para 2 casas decimais (km)
                ];
            }

            // Devolve a lista de UBS em formato JSON para o JavaScript
            echo json_encode($ubsList);
        } else {
            echo json_encode(["erro" => "Erro ao consultar o banco de dados."]);
        }

    } catch (Exception $e) {
        echo json_encode(["erro" => $e->getMessage()]);
    }

    // Fecha a conexão com o banco
    pg_close($conn);
    exit;
} else {
    // Se acessado via GET ou outro método incorreto
    echo json_encode(["erro" => "Método não permitido."]);
}
?>