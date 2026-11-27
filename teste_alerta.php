<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$conn) {
    die("Erro de conexão com o banco.");
}

$sql = "INSERT INTO notificacoes
        (paciente_id, titulo, mensagem, tipo, data_notificacao, lida)
        SELECT $1::integer, $2::varchar, $3::text, $4::varchar,
               CURRENT_DATE, FALSE
        WHERE EXISTS (
            SELECT 1 FROM notificacoes WHERE paciente_id = $1::integer
        )
        AND NOT EXISTS (
            SELECT 1 FROM notificacoes WHERE titulo = $2::varchar
        )
        RETURNING id, paciente_id, titulo, mensagem, tipo, data_notificacao, lida";

$resultado = pg_query_params($conn, $sql, [
    1,
    'Alerta de teste',
    'Teste de funcionamento do AquiVacina.',
    'INFORMACAO'
]);

if (!$resultado) {
    die("Erro ao inserir: " . pg_last_error($conn));
}

$linha = pg_fetch_assoc($resultado);

if ($linha) {
    echo "Alerta criado com sucesso!\n";
    print_r($linha);
} else {
    echo "Nenhum alerta foi criado. O paciente pode não ter notificações ou o alerta já pode existir.\n";
}

pg_close($conn);
?>
