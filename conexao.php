<?php

    $host = "200.18.128.54";
    $port = "5432";
    $dbname = "AquiVacina";
    $user = "aula";
    $password = "aula";

    $conn = pg_connect(
        "host=$host port=$port dbname=$dbname user=$user password=$password"
    );

    if (!$conn) {
        die("Erro ao conectar ao banco de dados.");
    }
    
?>
