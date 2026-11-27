<?php
$c = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$c) {
    die("Erro na conexão com o banco: " . pg_last_error() . PHP_EOL);
}

$sql = "ALTER TABLE criancas
        ADD COLUMN IF NOT EXISTS genero VARCHAR(30),
        ADD COLUMN IF NOT EXISTS cpf_responsavel VARCHAR(14)";

$r = pg_query($c, $sql);

if ($r) {
    echo "Campos genero e cpf_responsavel criados com sucesso!" . PHP_EOL;
} else {
    echo "Erro: " . pg_last_error($c) . PHP_EOL;
}

pg_close($c);
?>
