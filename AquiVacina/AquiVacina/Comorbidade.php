<?php

session_start();

require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["cadastrar"])) {

        $Comodidade = $_POST["Comodidade"] ?? "";
        $Modo = $_POST["modo"] ?? "novo";
        $ComodidadeAntiga = $_POST["comodidadeantiga"] ?? "";

        if (empty($Comodidade)) {
            $_SESSION["erro_comorbidade"] = "Informe a comorbidade ou marque que não possui comorbidade";
            header("Location: Comorbidade.php");
            exit();
        }

        if ($Modo === "editar" && !empty($ComodidadeAntiga)) {

            $sql = "
                UPDATE comorbidade
                SET comodidade = $1
                WHERE comodidade = $2
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [$Comodidade, $ComodidadeAntiga]
            );

            if ($resultado) {
                $_SESSION["comorbidade"] = "Comorbidade editada";
            } else {
                $_SESSION["erro_comorbidade"] = "Erro ao editar comorbidade";
            }

        } else {

            $sql = "
                INSERT INTO comorbidade (comodidade)
                VALUES ($1)
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [$Comodidade]
            );

            if ($resultado) {
                $_SESSION["comorbidade"] = "Comorbidade cadastrada";
            } else {
                $_SESSION["erro_comorbidade"] = "Erro ao cadastrar comorbidade";
            }
        }

        header("Location: Comorbidade.php");
        exit();
    }

    if (isset($_POST["excluir"])) {

        $Comodidade = $_POST["inputexcluir"] ?? "";

        if (empty($Comodidade)) {
            $_SESSION["erro_comorbidade"] = "Selecione uma comorbidade";
            header("Location: Comorbidade.php");
            exit();
        }

        $sql = "
            SELECT *
            FROM comorbidade
            WHERE comodidade = $1
        ";

        $resultado = pg_query_params($conn, $sql, [$Comodidade]);

        if (!$resultado || pg_num_rows($resultado) == 0) {
            $_SESSION["erro_comorbidade"] = "A comorbidade selecionada não existe";
            header("Location: Comorbidade.php");
            exit();
        }

        $sql = "
            DELETE FROM comorbidade
            WHERE comodidade = $1
        ";

        $resultado = pg_query_params($conn, $sql, [$Comodidade]);

        if ($resultado) {
            $_SESSION["comorbidade"] = "Comorbidade excluída";
        } else {
            $_SESSION["erro_comorbidade"] = "Erro ao excluir comorbidade";
        }

        header("Location: Comorbidade.php");
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

    <title>AquiVacina | Comorbidade</title>

    <link
        rel="stylesheet"
        href="AquiVacina3.css"
    >

    <script
        src="https://kit.fontawesome.com/cc9bf1d657.js"
        crossorigin="anonymous">
    </script>

</head>

<body class="CadUbs">

    <div class="IconCadUbs">

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

                <a href="HistoricoVacinal.html">
                    Comorbidade
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

    <div class="cadastro-ubs-container">

        <div class="cadastro-ubs-quadro">


            <div class="FundoTipo1">


                <form
                    action="Comorbidade.php"
                    method="POST"
                    id="formCadastroPublico"
                >

                    <label id="comolabel">
                        Comorbidade
                    </label>

                    <input
                        placeholder="Qual a comorbidade?"
                        type="text"
                        name="Comodidade"
                        id="Comodidade"
                    >

                    <input
                        type="checkbox"
                        name="SemComodidade"
                        id="SemComodidade"
                        onchange="marcarSemComodidade()"
                    >

                    <label for="SemComodidade" id="SemComoLabel">
                        Não possui comorbidade
                    </label>

                    <!-- MODO -->

                    <input
                        type="hidden"
                        name="modo"
                        id="modo"
                        value="novo"
                    >

                    <!-- TIPO DE PESSOA ANTIGO -->

                    <input
                        type="hidden"
                        name="comodidadeantiga"
                        id="comodidadeantiga"
                    >

                    <?php

                    if (
                        isset($_SESSION["erro_comorbidade"])
                    ) {

                        echo
                            "<p class='text-danger'>"
                            .
                            htmlspecialchars(
                                $_SESSION["erro_comorbidade"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            .
                            "</p>";

                        unset(
                            $_SESSION["erro_comorbidade"]
                        );
                    }

                    ?>

                    <?php

                    if (
                        isset($_SESSION["comorbidade"])
                    ) {

                        echo
                            "<p class='text-sucess'>"
                            .
                            htmlspecialchars(
                                $_SESSION["comorbidade"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            .
                            "</p>";

                        unset(
                            $_SESSION["comorbidade"]
                        );
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

        <form
            action="Comorbidade.php"
            method="POST"
            id="formExcluirPublico"
            style="display: none;"
        >

            <input
                type="hidden"
                name="inputexcluir"
                id="inputexcluir"
            >


            <button
                type="submit"
                name="excluir"
                id="BEst2"
                style="display: none;"
            >
                Excluir
            </button>

        </form>

        <div class="cadastro-ubs-quadro quadro-tabela-ubs">


            <div class="TabelaCadastroUBS">


                <table>


                    <thead>

                        <tr>

                            <th>
                                Comorbidade
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $sql = "
                            SELECT comodidade
                            FROM comorbidade
                            ORDER BY comodidade
                        ";

                        $query = pg_query($conn, $sql);

                        if ($query) {
                            while ($resultado = pg_fetch_array($query)) {

                                $Comodidade = $resultado["comodidade"] ?? "";

                                $Comodidade_HTML = htmlspecialchars(
                                    $Comodidade,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                echo "
                                    <tr
                                        class='linhaPublico'
                                        data-comodidade='" . $Comodidade_HTML . "'
                                    >
                                        <td>" . $Comodidade_HTML . "
                                            <button
                                                type='button'
                                                class='BotaoExcluirEstoque'
                                                onclick='excluirComorbidade(this, event)'
                                            >
                                                X
                                            </button>
                                        </td>
                                    </tr>
                                ";
                            }
                        }

                        ?>

                    </tbody>


                </table>


            </div>

        </div>

    </div>

    <script src="AquiVacina.js"></script>

    <script>

        function excluirComorbidade(botao, event) {
            event.stopPropagation();

            const linha = botao.closest(".linhaComorbidade");
            const comodidade = linha.getAttribute("data-comodidade");

            const confirmar = confirm(
                "Tem certeza que deseja excluir esta comorbidade?"
            );

            if (confirmar) {
                document.getElementById("inputexcluir").value = comodidade;
                document.getElementById("BEst2").click();
            }
        }

        function marcarSemComodidade() {
            const comodidade = document.getElementById("Comodidade");
            const semComodidade = document.getElementById("SemComodidade");

            if (semComodidade.checked) {
                comodidade.value = "Nenhuma";
                comodidade.readOnly = true;
            } else {
                comodidade.value = "";
                comodidade.readOnly = false;
            }
        }

        document.addEventListener("DOMContentLoaded", function () {

            const linhas = document.querySelectorAll(".linhaPublico");
            const inputComodidade = document.getElementById("Comodidade");
            const inputComodidadeAntiga = document.getElementById("comodidadeantiga");
            const inputModo = document.getElementById("modo");
            const botaoEnviar = document.getElementById("BEnviar");
            const inputExcluir = document.getElementById("inputexcluir");
            const semComodidade = document.getElementById("SemComodidade");

            linhas.forEach(function (linha) {
                linha.addEventListener("click", function () {

                    linhas.forEach(function (item) {
                        item.classList.remove("selecionada");
                    });

                    linha.classList.add("selecionada");

                    const comodidade = linha.getAttribute("data-comodidade");

                    inputComodidade.value = comodidade;
                    inputComodidadeAntiga.value = comodidade;
                    inputExcluir.value = comodidade;
                    inputModo.value = "editar";
                    semComodidade.checked = (comodidade === "Nenhuma");
                    inputComodidade.readOnly = semComodidade.checked;

                    botaoEnviar.textContent = "Salvar alteração";
                });
            });

        });

    </script>


</body>

</html>
