<?php
$conn = pg_connect(
    "host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula"
);

if (!$conn) {
    exit("Erro ao conectar ao banco.");
}

$resultado = pg_query_params(
    $conn,
    "INSERT INTO estoque (nomevacina, quantiaestoque, nomeubs)
     VALUES ($1, $2, $3)",
    ["Tríplice Viral", 5, "UBS Pioneiros"]
);

if ($resultado) {
    echo "Tríplice Viral cadastrada com 5 doses na UBS Pioneiros!";
} else {
    echo "Erro ao cadastrar: " . pg_last_error($conn);
}

pg_close($conn);
?>
