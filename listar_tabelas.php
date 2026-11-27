<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$conn) {
    die("Erro de conexão com o banco.");
}

$sql = "SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        AND table_type = 'BASE TABLE'
        ORDER BY table_name";

$resultado = pg_query($conn, $sql);

while ($linha = pg_fetch_assoc($resultado)) {
    echo $linha["table_name"] . PHP_EOL;
}
?>
