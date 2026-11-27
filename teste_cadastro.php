<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$conn) {
    die("ERRO DE CONEXAO");
}

$resultado = pg_query($conn, "SELECT id, paciente_id, nome, cpf, data_nascimento, indigena_quilombola, comorbidades FROM criancas ORDER BY id");

if (!$resultado) {
    die("ERRO NA CONSULTA: " . pg_last_error($conn));
}

echo "CONSULTA OK - Total de criancas: " . pg_num_rows($resultado);
pg_close($conn);
?>
