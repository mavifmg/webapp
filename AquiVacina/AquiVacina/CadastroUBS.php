<?php

session_start();

require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["cadastrar"])) {

        $NomeUBS = $_POST["NomeUBS"] ?? "";
        $Endereco = $_POST["Endereco"] ?? "";
        $HorarioFuncio = $_POST["HorarioFuncio"] ?? "";
        $InfoExtra = $_POST["InfoExtra"] ?? "";

        $Modo = $_POST["modo"] ?? "novo";
        $NomeAntigo = $_POST["nomeantigo"] ?? "";

        if (
            empty($NomeUBS) ||
            empty($Endereco) ||
            empty($HorarioFuncio)
        ) {

            $_SESSION["erro_cadastro_ubs"] =
                "Campos vazios, preencha todos os campos obrigatórios";

            header("Location: CadastroUBS.php");

            exit();
        }

        if ($Modo === "editar" && !empty($NomeAntigo)) {

            $sql = "
                UPDATE cadastroubs
                SET nomeubs = $1,
                    endereco = $2,
                    horariofuncio = $3,
                    infoextra = $4
                WHERE nomeubs = $5
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [
                    $NomeUBS,
                    $Endereco,
                    $HorarioFuncio,
                    $InfoExtra,
                    $NomeAntigo
                ]
            );


            if ($resultado) {

                $_SESSION["cadastro_ubs"] =
                    "Cadastro da UBS editado";

            } else {

                $_SESSION["erro_cadastro_ubs"] =
                    "Erro ao editar cadastro da UBS";
            }

        }


        else {

            $sql = "
                INSERT INTO cadastroubs
                (nomeubs, endereco, horariofuncio, infoextra)
                VALUES ($1, $2, $3, $4)
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [
                    $NomeUBS,
                    $Endereco,
                    $HorarioFuncio,
                    $InfoExtra
                ]
            );


            if ($resultado) {

                $_SESSION["cadastro_ubs"] =
                    "UBS cadastrada";

            } else {

                $_SESSION["erro_cadastro_ubs"] =
                    "Erro ao cadastrar UBS";
            }
        }


        header("Location: CadastroUBS.php");

        exit();
    }


    if (isset($_POST["excluir"])) {

        $NomeUBS = $_POST["inputexcluir"] ?? "";


        if (empty($NomeUBS)) {

            $_SESSION["erro_cadastro_ubs"] =
                "Selecione uma UBS";

            header("Location: CadastroUBS.php");

            exit();
        }


        $sql = "
            SELECT *
            FROM cadastroubs
            WHERE nomeubs = $1
        ";

        $resultado = pg_query_params(
            $conn,
            $sql,
            [$NomeUBS]
        );


        if (
            !$resultado ||
            pg_num_rows($resultado) == 0
        ) {

            $_SESSION["erro_cadastro_ubs"] =
                "A UBS selecionada não existe";

            header("Location: CadastroUBS.php");

            exit();
        }

        $sql = "
            DELETE FROM cadastroubs
            WHERE nomeubs = $1
        ";

        $resultado = pg_query_params(
            $conn,
            $sql,
            [$NomeUBS]
        );


        if ($resultado) {

            $_SESSION["cadastro_ubs"] =
                "UBS excluída";

        } else {

            $_SESSION["erro_cadastro_ubs"] =
                "Erro ao excluir UBS";
        }


        header("Location: CadastroUBS.php");

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

    <title>AquiVacina | Cadastrar UBS</title>

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

    <div class="cadastro-ubs-container">

        <div class="cadastro-ubs-quadro">


            <div class="FundoTipo1">


                <form
                    action="CadastroUBS.php"
                    method="POST"
                    id="formCadastroUBS"
                >


                    <label>
                        Nome da UBS
                    </label>

                    <input
                        placeholder="Digite o nome da UBS"
                        type="text"
                        name="NomeUBS"
                        id="NomeUBS"
                    >


                    <label>
                        Endereço
                    </label>

                    <input
                        placeholder="Digite o endereço"
                        type="text"
                        name="Endereco"
                        id="Endereco"
                    >

                    <label>
                        Horários de funcionamento
                    </label>

                    <input
                        placeholder="Digite os horários"
                        type="text"
                        name="HorarioFuncio"
                        id="HorarioFuncio"
                    >

                    <label>
                        Informações extras
                    </label>

                    <input
                        placeholder="Digite aqui"
                        type="text"
                        name="InfoExtra"
                        id="InfoExtra"
                    >

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
                        isset(
                            $_SESSION["erro_cadastro_ubs"]
                        )
                    ) {

                        echo
                            "<p class='text-danger'>"
                            .
                            htmlspecialchars(
                                $_SESSION["erro_cadastro_ubs"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            .
                            "</p>";

                        unset(
                            $_SESSION["erro_cadastro_ubs"]
                        );
                    }

                    ?>


                    <?php

                    if (
                        isset(
                            $_SESSION["cadastro_ubs"]
                        )
                    ) {

                        echo
                            "<p class='text-sucess'>"
                            .
                            htmlspecialchars(
                                $_SESSION["cadastro_ubs"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            .
                            "</p>";

                        unset(
                            $_SESSION["cadastro_ubs"]
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
            action="CadastroUBS.php" 
            method="POST" 
            id="formExcluirUBS"
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
                                Nome UBS
                            </th>

                            <th>
                                Endereço
                            </th>

                            <th>
                                Horário
                            </th>

                            <th>
                                Informações
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php

                        $sql = "
                            SELECT *
                            FROM cadastroubs
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


                                $Endereco =
                                    $resultado["endereco"];


                                $HorarioFuncio =
                                    $resultado["horariofuncio"];


                                $InfoExtra =
                                    $resultado["infoextra"];


                                $NomeUBS_HTML =
                                    htmlspecialchars(
                                        $NomeUBS,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );


                                $Endereco_HTML =
                                    htmlspecialchars(
                                        $Endereco,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );


                                $HorarioFuncio_HTML =
                                    htmlspecialchars(
                                        $HorarioFuncio,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );


                                $InfoExtra_HTML =
                                    htmlspecialchars(
                                        $InfoExtra,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                echo
                                    "<tr
                                        class='linhaCadastroUBS'

                                        data-ubs='"
                                        .
                                        $NomeUBS_HTML
                                        .
                                        "'

                                        data-endereco='"
                                        .
                                        $Endereco_HTML
                                        .
                                        "'

                                        data-horario='"
                                        .
                                        $HorarioFuncio_HTML
                                        .
                                        "'

                                        data-info='"
                                        .
                                        $InfoExtra_HTML
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
                                    $Endereco_HTML
                                    .
                                    "</td>";


                                echo
                                    "<td>"
                                    .
                                    $HorarioFuncio_HTML
                                    .
                                    "</td>";


                                echo
                                    "<td>"
                                    .
                                    $InfoExtra_HTML
                                    .
                                    "<button type='button'
                                        class='BotaoExcluirEstoque'
                                        onclick='excluirCadastroUBS(this, event)'>
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

        </div>

    </div>



    <script src="AquiVacina.js"></script>

    <script>

        function excluirCadastroUBS(botao, event) {

            event.stopPropagation();

            const linha = botao.closest(".linhaCadastroUBS");
            const ubs = linha.getAttribute("data-ubs");

            const confirmar = confirm(
                "Tem certeza que deseja excluir a UBS de " + ubs + "?"
            );

            if (confirmar) {

                document.getElementById("inputexcluir").value = ubs;

                document.getElementById("BEst2").click();

            }

        }

        document.addEventListener(
            "DOMContentLoaded",
            function () {


                const linhas =
                    document.querySelectorAll(
                        ".linhaCadastroUBS"
                    );


                const inputUBS =
                    document.getElementById(
                        "NomeUBS"
                    );


                const inputEndereco =
                    document.getElementById(
                        "Endereco"
                    );


                const inputHorario =
                    document.getElementById(
                        "HorarioFuncio"
                    );


                const inputInfo =
                    document.getElementById(
                        "InfoExtra"
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

                                const ubs =
                                    linha.getAttribute(
                                        "data-ubs"
                                    );


                                const endereco =
                                    linha.getAttribute(
                                        "data-endereco"
                                    );


                                const horario =
                                    linha.getAttribute(
                                        "data-horario"
                                    );


                                const info =
                                    linha.getAttribute(
                                        "data-info"
                                    );


                                /*
                                COLOCA OS DADOS NOS INPUTS
                                */

                                inputUBS.value =
                                    ubs;

                                inputEndereco.value =
                                    endereco;

                                inputHorario.value =
                                    horario;

                                inputInfo.value =
                                    info;

                                /*
                                GUARDA O NOME ANTIGO
                                */

                                inputNomeAntigo.value =
                                    ubs;

                                /*
                                MUDA PARA MODO EDITAR
                                */

                                inputModo.value =
                                    "editar";

                                /*
                                NOME PARA EXCLUSÃO
                                */

                                inputExcluir.value =
                                    ubs;


                                /*
                                MUDA O BOTÃO
                                */

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