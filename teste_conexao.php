<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if ($conn) {
    echo "Conexao com o banco OK";
} else {
    echo "Falha na conexao com o banco";
}
?>
