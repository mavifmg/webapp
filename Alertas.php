<?php

// ======================================
// CONEXÃO COM O BANCO
// ======================================

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
        AquiVacina | Alertas
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
            Alertas
        </h2>

        <p>
            Acompanhe avisos importantes sobre a vacinação.
        </p>

    </header>


    <!-- CONTEÚDO -->

    <main>


        <!-- ALERTAS DE EPIDEMIAS -->

        <section>

            <h2>
                ⚠️ Alertas de epidemias
            </h2>

            <div>

                <h3>
                    Aviso de saúde
                </h3>

                <p>
                    Fique atento aos avisos sobre epidemias
                    e campanhas de vacinação na sua região.
                </p>

            </div>

        </section>


        <!-- VACINAS DISPONÍVEIS -->

        <section>

            <h2>
                💉 Vacinas disponíveis para a criança
            </h2>

            <div>

                <h3>
                    Maria Silva
                </h3>

                <p>
                    Existem vacinas disponíveis para atualização
                    do calendário de vacinação.
                </p>

                <a href="VacinasDisponiveis.php">

                    <button type="button">
                        Ver vacinas
                    </button>

                </a>

            </div>

        </section>


        <!-- DOSES E REFORÇOS -->

        <section>

            <h2>
                📅 Doses e reforços
            </h2>

            <div>

                <h3>
                    Próxima dose
                </h3>

                <p>
                    Consulte as próximas doses e reforços
                    necessários para cada criança cadastrada.
                </p>

                <button type="button">
                    Ver calendário
                </button>

            </div>

        </section>


        <!-- CAMPANHAS -->

        <section>

            <h2>
                📢 Campanhas de vacinação
            </h2>

            <div>

                <h3>
                    Campanha de vacinação
                </h3>

                <p>
                    Confira as campanhas de vacinação
                    disponíveis na unidade de saúde.
                </p>

                <button type="button">
                    Ver campanhas
                </button>

            </div>

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