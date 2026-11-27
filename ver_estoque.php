<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$conn) {
    die("Erro de conexão com o banco.");
}

$resultado = pg_query($conn, 'SELECT nomevacina, quantiaestoque, nomeubs FROM estoque ORDER BY nomeubs, nomevacina');

if (!$resultado) {
    die("Erro na consulta: " . pg_last_error($conn));
}

while ($linha = pg_fetch_assoc($resultado)) {
    echo $linha["nomevacina"] . " | ";
    echo $linha["quantiaestoque"] . " doses | ";
    echo $linha["nomeubs"] . PHP_EOL;
}
?>
