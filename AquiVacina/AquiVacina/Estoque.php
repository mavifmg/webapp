<?php

session_start();

require_once "Conexao.php";

$ubs = pg_query(
    $conn,
    "SELECT nomeubs FROM cadastroubs ORDER BY nomeubs"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["cadastrar"])) {

        $NomeVacina = $_POST["NomeVacina"] ?? "";
        $QuantiaEstoque = $_POST["QuantiaEstoque"] ?? "";
        $NomeUBS = $_POST["NomeUBS"] ?? "";
        $Modo = $_POST["modo"] ?? "novo";
        $NomeAntigo = $_POST["nomeantigo"] ?? "";

        if (
            empty($NomeVacina) ||
            empty($QuantiaEstoque) ||
            empty($NomeUBS) 

   
        ) {

            $_SESSION["erro_estoque"] =
                "Campos vazios, preencha todos";

            header("Location: Estoque.php");

            exit();
        }


        if ($Modo === "editar" && !empty($NomeAntigo)) {

            $sql = "
                UPDATE estoque
                SET nomevacina = $1,
                    nomeubs = $2,
                    quantiaestoque = $3
                 WHERE nomevacina = $4
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [
                    $NomeVacina,
                    $NomeUBS,
                    $QuantiaEstoque,
                    $NomeAntigo
                ]
            );

            if ($resultado) {

                $_SESSION["cadastro_estoque"] =
                    "Estoque editado";

            } else {

                $_SESSION["erro_estoque"] =
                    "Erro ao editar estoque";
            }

        }

        else {

            $sql = "
                INSERT INTO estoque
                (NomeVacina, QuantiaEstoque, NomeUBS)
                VALUES ($1, $2, $3)
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [
                    $NomeVacina,
                    $QuantiaEstoque,
                    $NomeUBS

                ]
            );

            if ($resultado) {

                $_SESSION["cadastro_estoque"] =
                    "Estoque cadastrado";

            } else {

                $_SESSION["erro_estoque"] =
                    "Erro ao cadastrar estoque";
            }
        }

        header("Location: Estoque.php");

        exit();
    }

    if (isset($_POST["excluir"])) {

        $NomeVacina = $_POST["inputexcluir"] ?? "";

        if (empty($NomeVacina)) {

            $_SESSION["erro_estoque"] =
                "Digite o nome da vacina";

            header("Location: Estoque.php");

            exit();
        }

        $sql = "
            SELECT *
            FROM estoque
            WHERE nomevacina = $1
        ";

        $resultado = pg_query_params(
            $conn,
            $sql,
            [$NomeVacina]
        );

        if (
            !$resultado ||
            pg_num_rows($resultado) == 0
        ) {

            $_SESSION["erro_estoque"] =
                "Digite uma vacina que esteja no estoque";

            header("Location: Estoque.php");

            exit();
        }

        $sql = "
            DELETE FROM estoque
            WHERE nomevacina = $1
        ";

        $resultado = pg_query_params(
            $conn,
            $sql,
            [$NomeVacina]
        );

        if ($resultado) {

            $_SESSION["cadastro_estoque"] =
                "Vacina excluida";

        } else {

            $_SESSION["erro_estoque"] =
                "Erro ao excluir estoque";
        }

        header("Location: Estoque.php");

        exit();
    }
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>AquiVacina | Estoque UBS</title>
    
    <link
        rel="stylesheet"
        href="AquiVacina2.css"
    >

    <script
        src="https://kit.fontawesome.com/cc9bf1d657.js"
        crossorigin="anonymous">
    </script>

</head>

<body class="EstoqueUbs">

    <header class="CabecalhoEstoque">

        <img
            src="Imagens/LogoAquiVacina.png"
            alt="AquiVacina"
            class="LogoEstoque"
        >

    </header>


    <div class="IconEstUbs">

        <i
            id="Perfil"
            class="fa-solid fa-user">
        </i>

        <a href="Menu.html">

            <button id="Home">

                <i class="fa-regular fa-house"></i>

            </button>

        </a>

        <i
            id="Burger"
            class="fa-solid fa-bars"
            onclick="clickMenu()"
        >
            Menu
        </i>


        <menu id="Itens">

            <ul>

                <a href="Informacao.html">
                    Informações
                </a>

                <a href="ComoUsar.html">
                    Como Usar
                </a>

                <a href="Publico.php">
                    Cadastrar público
                </a>

                <a href="Alertas.html">
                    Alertas
                </a>

                <a href="Estoque.php">
                    Estoque da UBS
                </a>

                <a href="Registrar.html">
                    Registrar aplicações
                </a>

                <a href="CadastroUBS.php">
                    Cadastrar UBS
                </a>

            </ul>

        </menu>

    </div>


    <div class="estoque-container">

        <div class="estoque-quadro quadro-cadastro">

            <div class="LocEstUbs">

                <div class="FundoTipo1">

                    <form
                        action="Estoque.php"
                        method="POST"
                        id="formEstoque"
                    >

                        <div id="titulo1"> Nome da vacina </div>

                        <input
                            placeholder="Digite o nome da vacina"
                            type="text"
                            name="NomeVacina"
                            id="NomeVacina"
                        >

                        <div id="titulo2"> Quantia de estoque </div>

                        <input
                            placeholder="Digite a quantia em estoque"
                            type="number"
                            name="QuantiaEstoque"
                            id="QuantiaEstoque"
                        >

                        <div id="titulo3"> Nome UBS </div>

                        <select name="NomeUBS" id="NomeUBS">

                            <option value="">
                                Selecione uma UBS
                            </option>

                            <?php

                            if ($ubs) {

                                while ($ubsResultado = pg_fetch_assoc($ubs)) {

                                    $nomeUBS = htmlspecialchars(
                                        $ubsResultado["nomeubs"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    echo "
                                        <option value='$nomeUBS'>
                                            $nomeUBS
                                        </option>
                                    ";
                                }
                            }

                            ?>

                        </select>

                        <input
                            type="hidden"
                            name="modo"
                            id="modo"
                            value="novo"
                        >

                        <input
                            type="hidden"
                            name="nomeantigo"
                            id="nomeantigo"
                        >
                     
                        <?php

                        if (
                            isset($_SESSION["cadastro_estoque"])
                        ) {

                            echo
                                "<p class='text-sucess'>"
                                .
                                htmlspecialchars(
                                    $_SESSION["cadastro_estoque"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                .
                                "</p>";

                            unset(
                                $_SESSION["cadastro_estoque"]
                            );
                        }

                        ?>

                        <?php
                        if (isset($_SESSION["erro_estoque"])) {
                            echo "<p class='text-danger'>" .
                                htmlspecialchars(
                                    $_SESSION["erro_estoque"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) .
                                "</p>";

                            unset($_SESSION["erro_estoque"]);
                        }
                        ?>

                        <button
                            type="submit"
                            name="cadastrar"
                            id="BEnviar"
                        >
                            Enviar
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="estoque-quadro quadro-tabela">

            <div class="TabelaEstoque">

                <table>

                    <thead>

                        <tr>

                            <th class="LetraCad1">
                                Nome UBS
                            </th>

                            <th class="LetraCad2">
                                Nome da vacina
                            </th>

                            <th class="LetraCad3">
                                Estoque
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $sql = "
                            SELECT *
                            FROM estoque
                        ";

                        $query = pg_query(
                            $conn,
                            $sql
                        );


                        if ($query) {

                            while (
                                $resultado =
                                pg_fetch_array($query)
                            ) {

                                $NomeUBS =
                                    $resultado["nomeubs"];

                                $NomeVacina =
                                    $resultado["nomevacina"];

                                $QuantiaEstoque =
                                    $resultado["quantiaestoque"];

                                $NomeUBS_HTML =
                                    htmlspecialchars(
                                        $NomeUBS,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );


                                $NomeVacina_HTML =
                                    htmlspecialchars(
                                        $NomeVacina,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );


                                $QuantiaEstoque_HTML =
                                    htmlspecialchars(
                                        $QuantiaEstoque,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                echo
                                    "<tr
                                        class='linhaEstoque'

                                        data-vacina='"
                                        .
                                        $NomeVacina_HTML
                                        .
                                        "'

                                        data-quantidade='"
                                        .
                                        $QuantiaEstoque_HTML
                                        .
                                        "'

                                        data-ubs='"
                                        .
                                        $NomeUBS_HTML
                                        .
                                        "'

                                    >";


                                echo
                                    "<td>"
                                    .
                                    $NomeUBS_HTML
                                    .
                                    "</td>";


                                echo
                                    "<td>"
                                    .
                                    $NomeVacina_HTML
                                    .
                                    "</td>";

                                echo
                                    "<td>"
                                    .
                                    $QuantiaEstoque_HTML
                                    .
                                    "<button type='button'
                                        class='BotaoExcluirEstoque'
                                        onclick='excluirEstoque(this, event)'>
                                        X
                                    </button>"
                                    .
                                    "</td>";

                                echo "</tr>";
                            }
                        }

                        ?>

                    </tbody>

                </table>

            </div>

            <form
                action="Estoque.php"
                method="POST"
            >

                <input
                    placeholder="Digite o nome da vacina"
                    type="hidden"
                    name="inputexcluir"
                    id="inputexcluir"
                >

                <button
                    type="submit"
                    id="BEst2"
                    name="excluir"
                    type="hidden"
                    style="display: none;"
                >
                    Excluir
                </button>

            </form>

        </div>

    </div>


    <?php

    pg_close($conn);

    ?>


    <script src="AquiVacina.js"></script>


    <script>

        function excluirEstoque(botao, event) {

            event.stopPropagation();

            const linha = botao.closest(".linhaEstoque");

            const vacina = linha.getAttribute("data-vacina");

            const confirmar = confirm(
                "Tem certeza que deseja excluir a vacina de " + vacina + "?"
            );

            if (confirmar) {

                document.getElementById("inputexcluir").value = vacina;

                document.getElementById("BEst2").click();

            }

        }

        document.addEventListener(
            "DOMContentLoaded",
            function () {

                const linhas =
                    document.querySelectorAll(
                        ".linhaEstoque"
                    );


                const inputNome =
                    document.getElementById(
                        "NomeVacina"
                    );


                const inputUBS =
                    document.getElementById(
                        "NomeUBS"
                    );


                const inputEstoque =
                    document.getElementById(
                        "QuantiaEstoque"
                    );


                const inputNomeAntigo =
                    document.getElementById(
                        "nomeantigo"
                    );


                const inputModo =
                    document.getElementById(
                        "modo"
                    );


                const botaoEnviar =
                    document.getElementById(
                        "BEnviar"
                    );

                const inputExcluir =
                    document.getElementById(
                        "inputexcluir"
                    );

                linhas.forEach(
                    function (linha) {

                        linha.addEventListener(
                            "click",
                            function () {


                                linhas.forEach(
                                    function (item) {

                                        item.classList.remove(
                                            "selecionada"
                                        );

                                    }
                                );


                                linha.classList.add(
                                    "selecionada"
                                );


                                const vacina =
                                    linha.getAttribute(
                                        "data-vacina"
                                    );


                                const quantidade =
                                    linha.getAttribute(
                                        "data-quantidade"
                                    );


                                const ubs =
                                    linha.getAttribute(
                                        "data-ubs"
                                    );


                                inputExcluir.value = vacina;

                                inputNome.value =
                                    vacina;


                                inputUBS.value =
                                    ubs;


                                inputEstoque.value =
                                    quantidade;


                                inputNomeAntigo.value =
                                    vacina;


                                inputModo.value =
                                    "editar";


                                botaoEnviar.textContent =
                                    "Salvar alteração";

                            }
                        );

                    }
                );

            }
        );

    </script>


</body>

</html>

