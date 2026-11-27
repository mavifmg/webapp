<?php


$host = "10.90.24.54";
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




$vacinas = [

    [
        "nome" => "BCG",
        "quantidade" => 25,
        "situacao" => "Disponível"
    ],

    [
        "nome" => "Hepatite B",
        "quantidade" => 18,
        "situacao" => "Disponível"
    ],

    [
        "nome" => "Poliomielite",
        "quantidade" => 30,
        "situacao" => "Disponível"
    ],

    [
        "nome" => "Pentavalente",
        "quantidade" => 20,
        "situacao" => "Disponível"
    ],

    [
        "nome" => "Tríplice Viral",
        "quantidade" => 12,
        "situacao" => "Estoque baixo"
    ],

    [
        "nome" => "Febre Amarela",
        "quantidade" => 15,
        "situacao" => "Disponível"
    ],

    [
        "nome" => "Rotavírus",
        "quantidade" => 10,
        "situacao" => "Estoque baixo"
    ],

    [
        "nome" => "Pneumocócica",
        "quantidade" => 22,
        "situacao" => "Disponível"
    ],

    [
        "nome" => "Meningocócica",
        "quantidade" => 16,
        "situacao" => "Disponível"
    ],

    [
        "nome" => "DTP",
        "quantidade" => 8,
        "situacao" => "Estoque baixo"
    ]

];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        AquiVacina | Vacinas Disponíveis
    </title>

    <link
        rel="stylesheet"
        href="AquiVacina.css"
    >

</head>


<body>


    <!-- BOTÃO VOLTAR -->

    <a href="Menu.php">

        <button type="button">
            ←
        </button>

    </a>


    <!-- CABEÇALHO -->

    <header>

        <h1>
            AquiVacina
        </h1>

        <h2>
            Vacinas Disponíveis
        </h2>

        <p>
            Consulte as vacinas disponíveis na unidade de saúde
            e acompanhe a quantidade de doses em estoque.
        </p>

    </header>


    <!-- CONTEÚDO -->

    <main>


        <section>

            <h2>
                Vacinas em estoque
            </h2>


            <?php foreach ($vacinas as $vacina): ?>


                <div>

                    <h3>
                        <?php echo $vacina["nome"]; ?>
                    </h3>


                    <p>

                        <strong>
                            Quantidade disponível:
                        </strong>

                        <?php echo $vacina["quantidade"]; ?>
                        doses

                    </p>


                    <p>

                        <strong>
                            Situação:
                        </strong>

                        <?php echo $vacina["situacao"]; ?>

                    </p>

                </div>


            <?php endforeach; ?>


        </section>


        <!-- INFORMAÇÕES -->

        <section>

            <h2>
                Informações do estoque
            </h2>

            <p>
                As quantidades apresentadas correspondem às doses
                disponíveis na unidade de saúde.
            </p>

            <p>
                Consulte esta tela antes do atendimento para verificar
                a disponibilidade das vacinas necessárias.
            </p>

        </section>


    </main>


    <!-- RODAPÉ -->

    <footer>

        <p>
            AquiVacina
        </p>

    </footer>


</body>

</html>