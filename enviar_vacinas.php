<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$conn) {
    exit("Erro ao conectar ao banco.\n");
}

$sql = "INSERT INTO notificacoes
        (paciente_id, titulo, mensagem, tipo, data_notificacao, lida)
        VALUES ($1, $2, $3, $4, CURRENT_DATE, FALSE)";

$resultado = pg_query_params($conn, $sql, [
    2,
    "Vacinas disponíveis",
    "Consulte a unidade de saúde para verificar as vacinas disponíveis e conferir quais doses estão indicadas para você.",
    "VACINA"
]);

if ($resultado) {
    echo "Notificação de vacinas disponíveis enviada!\n";
} else {
    echo "Erro: " . pg_last_error($conn) . "\n";
}

pg_close($conn);
?>
