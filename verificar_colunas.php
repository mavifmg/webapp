<?php
include "conexao.php";

if (!$conn) {
    exit("Falha na conexão.");
}

$sql = "SELECT column_name, data_type
        FROM information_schema.columns
        WHERE table_name = 'criancas'
        ORDER BY ordinal_position";

$resultado = pg_query($conn, $sql);

if (!$resultado) {
    exit(pg_last_error($conn));
}

while ($linha = pg_fetch_assoc($resultado)) {
    echo $linha["column_name"] . " - " . $linha["data_type"] . PHP_EOL;
}
?>
