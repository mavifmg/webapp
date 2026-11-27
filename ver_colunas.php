<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$conn) {
    die("Erro de conexão com o banco.");
}

foreach (["estoque", "vacinas"] as $tabela) {
    echo "\n=== " . $tabela . " ===\n";

    $sql = "SELECT column_name, data_type
            FROM information_schema.columns
            WHERE table_schema = 'public'
            AND table_name = $1
            ORDER BY ordinal_position";

    $resultado = pg_query_params($conn, $sql, [$tabela]);

    while ($linha = pg_fetch_assoc($resultado)) {
        echo $linha["column_name"] . " (" . $linha["data_type"] . ")\n";
    }
}
?>
