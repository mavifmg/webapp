<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");
if (!$conn) {
    die("Erro de conexão: " . pg_last_error());
}
$sql = "SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'notificacoes'
        ORDER BY ordinal_position";
$resultado = pg_query($conn, $sql);
if (!$resultado) {
    die(pg_last_error($conn));
}
while ($linha = pg_fetch_assoc($resultado)) {
    echo implode(" | ", $linha) . PHP_EOL;
}
pg_close($conn);
?>
