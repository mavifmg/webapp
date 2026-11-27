<?php
$conn = pg_connect(
    "host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula"
);

if (!$conn) {
    exit("Erro ao conectar ao banco.");
}

$sql = "INSERT INTO estoque (nomevacina, quantiaestoque, nomeubs)
        VALUES ($1, $2, $3)";

$resultado = pg_query_params(
    $conn,
    $sql,
    ["BCG", 25, "UBS Pioneiros"]
);

if ($resultado) {
    echo "Vacina BCG cadastrada com 25 doses na UBS Pioneiros!";
} else {
    echo "Erro ao cadastrar: " . pg_last_error($conn);
}

pg_close($conn);
?>
