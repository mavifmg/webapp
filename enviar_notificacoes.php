<?php
$conn = pg_connect("host=10.90.24.54 port=5432 dbname=AquiVacina user=aula password=aula");

if (!$conn) {
    exit("Erro ao conectar ao banco de dados.\n");
}

$notificacoes = [
    [
        "Lembrete de dose de vacina",
        "Confira a caderneta de vacinação e verifique se há alguma dose pendente."
    ],
    [
        "Lembrete de reforço vacinal",
        "Verifique a caderneta de vacinação para conferir se está na época de receber alguma dose de reforço."
    ]
];

foreach ($notificacoes as $n) {
    $sql = "INSERT INTO notificacoes
            (paciente_id, titulo, mensagem, tipo, data_notificacao, lida)
            VALUES ($1, $2, $3, $4, CURRENT_DATE, FALSE)";

    $resultado = pg_query_params(
        $conn,
        $sql,
        [2, $n[0], $n[1], "REFORCO"]
    );

    if (!$resultado) {
        echo "Erro ao enviar: " . pg_last_error($conn) . "\n";
        pg_close($conn);
        exit(1);
    }

    echo "Notificação enviada: " . $n[0] . "\n";
}

pg_close($conn);
echo "Concluído!\n";
?>
