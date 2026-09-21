<?php

session_start();
require_once "Conexao.php";

if (!isset($_SESSION['CPF'])) {
    header("Location: Login.php");
    exit();
}

$CPF = $_SESSION['CPF'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editar"])) {

    $DataNascimento = $_POST["DataNascimento"] ?? "";
    $EMAIL = $_POST["EMAIL"] ?? "";
    $Celular = $_POST["Celular"] ?? "";
    $CEP = $_POST["CEP"] ?? "";
    $Comorbidades = $_POST["Comorbidades"] ?? "";

    $Indigena = isset($_POST["Indigena"]) ? 'true' : 'false';


    // Verificação dos campos obrigatórios

    if (
        empty($DataNascimento) ||
        empty($EMAIL) ||
        empty($Celular) ||
        empty($CEP) ||
        empty($Comorbidades)
    ) {

        $_SESSION["erro_editar"] =
            "Preencha todos os campos obrigatórios.";

        header("Location: DadosUser.php");
        exit();
    }


    // Atualiza os dados do próprio usuário

    $sql = 'UPDATE cadastro
            SET "DataNascimento" = $1,
                "EMAIL" = $2,
                "Celular" = $3,
                "CEP" = $4,
                "Indigena" = $5,
                "Comorbidades" = $6
            WHERE "CPF" = $7';


    $resultado = pg_query_params(
        $conn,
        $sql,
        [
            $DataNascimento,
            $EMAIL,
            $Celular,
            $CEP,
            $Indigena,
            $Comorbidades,
            $CPF
        ]
    );


    if ($resultado !== false) {

        $_SESSION["sucesso_editar"] =
            "Dados atualizados com sucesso!";

    } else {

        $_SESSION["erro_editar"] =
            "Erro ao atualizar os dados: " . pg_last_error($conn);
    }


    header("Location: DadosUser.php");
    exit();
}

if (isset($_POST["excluir"])) {

    // Verifica se o usuário existe

    $sql = 'SELECT "CPF"
            FROM cadastro
            WHERE "CPF" = $1';

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF]
    );


    if (
        $resultado === false ||
        pg_num_rows($resultado) === 0
    ) {

        $_SESSION["erro_usuario"] =
            "Usuário não encontrado.";

        header("Location: DadosUser.php");
        exit();
    }

    $sql = 'DELETE FROM cadastro
            WHERE "CPF" = $1';

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF]
    );


    if ($resultado !== false) {

        session_destroy();

        header("Location: Login.php");
        exit();

    } else {

        $_SESSION["erro_usuario"] =
            "Erro ao excluir usuário: " . pg_last_error($conn);

        header("Location: DadosUser.php");
        exit();
    }
}


$sql = 'SELECT
            "CPF",
            "DataNascimento",
            "EMAIL",
            "Celular",
            "CEP",
            "Indigena",
            "Comorbidades",
            "Autorizacao_Loc"
        FROM cadastro
        WHERE "CPF" = $1';


$result = pg_query_params(
    $conn,
    $sql,
    [$CPF]
);


if ($result === false) {

    die(
        "Erro na consulta: " .
        pg_last_error($conn)
    );
}


$usuario = pg_fetch_assoc($result);


if (!$usuario) {

    die("Usuário não encontrado.");
}

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>AquiVacina | Dados do Usuário</title>

    <link rel="stylesheet"
          href="AquiVacina.css">

</head>


<body class="DadosUser">


    <div class="LocDadosUser">

        <div class="FundoDadosUser">

            <h1>
                Dados do CPF:
                <?php echo $usuario["CPF"]; ?>
            </h1>


            <div class="Dados">

                <p>
                    <strong>Data de Nascimento:</strong>
                    <?php echo $usuario["DataNascimento"]; ?>
                </p>


                <p>
                    <strong>Email:</strong>
                    <?php echo $usuario["EMAIL"]; ?>
                </p>


                <p>
                    <strong>Celular:</strong>
                    <?php echo $usuario["Celular"]; ?>
                </p>


                <p>
                    <strong>CEP:</strong>
                    <?php echo $usuario["CEP"]; ?>
                </p>


                <p>
                    <strong>Indígena:</strong>

                    <?php
                    echo $usuario["Indigena"] === "t"
                        ? "Sim"
                        : "Não";
                    ?>

                </p>


                <p>
                    <strong>Comorbidades:</strong>
                    <?php echo $usuario["Comorbidades"]; ?>
                </p>


                <p>
                    <strong>Autorização Local:</strong>

                    <?php
                    echo $usuario["Autorizacao_Loc"] === "t"
                        ? "Sim"
                        : "Não";
                    ?>

                </p>

            </div>

        </div>

    </div>


    <!-- ==================================================
         FORMULÁRIO DE EDIÇÃO
         ================================================== -->

    <div class="DadosUsuario">

        <h1>Dados do Usuário</h1>


        <form
            action="DadosUser.php"
            method="POST"
            id="formUsuario"
        >


            <label for="CPF">
                CPF
            </label>

            <input
                type="text"
                name="CPF"
                id="CPF"
                value="<?php echo $usuario["CPF"]; ?>"
                readonly
            >


            <label for="DataNascimento">
                Data de Nascimento
            </label>

            <input
                type="date"
                name="DataNascimento"
                id="DataNascimento"
                value="<?php echo $usuario["DataNascimento"]; ?>"
                readonly
            >


            <label for="EMAIL">
                Email
            </label>

            <input
                type="email"
                name="EMAIL"
                id="EMAIL"
                value="<?php echo $usuario["EMAIL"]; ?>"
                readonly
            >


            <label for="Celular">
                Celular
            </label>

            <input
                type="text"
                name="Celular"
                id="Celular"
                value="<?php echo $usuario["Celular"]; ?>"
                readonly
            >


            <label for="CEP">
                CEP
            </label>

            <input
                type="text"
                name="CEP"
                id="CEP"
                value="<?php echo $usuario["CEP"]; ?>"
                readonly
            >


            <label for="Comorbidades">
                Comorbidades
            </label>

            <input
                type="text"
                name="Comorbidades"
                id="Comorbidades"
                value="<?php echo $usuario["Comorbidades"]; ?>"
                readonly
            >


            <label for="Indigena">
                Indígena
            </label>

            <input
                type="checkbox"
                name="Indigena"
                id="Indigena"
                value="true"
                disabled
                <?php
                if ($usuario["Indigena"] === "t") {
                    echo "checked";
                }
                ?>
            >


            <br>
            <br>


            <button
                type="button"
                id="BEditar"
            >
                Editar
            </button>


            <button
                type="submit"
                name="editar"
                id="BSalvar"
                style="display: none;"
            >
                Salvar alteração
            </button>

        </form>


        <!-- ==================================================
             EXCLUSÃO
             ================================================== -->

        <form
            action="DadosUser.php"
            method="POST"
            onsubmit="return confirm('Tem certeza que deseja excluir sua conta?');"
        >

            <button
                type="submit"
                name="excluir"
            >
                Excluir conta
            </button>

        </form>

    </div>


    <!-- ==================================================
         JAVASCRIPT - BOTÃO EDITAR
         ================================================== -->

    <script>

        document.addEventListener("DOMContentLoaded", function () {

            const botaoEditar =
                document.getElementById("BEditar");

            const botaoSalvar =
                document.getElementById("BSalvar");


            const campos = [

                document.getElementById("DataNascimento"),

                document.getElementById("EMAIL"),

                document.getElementById("Celular"),

                document.getElementById("CEP"),

                document.getElementById("Comorbidades"),

                document.getElementById("Indigena")

            ];


            botaoEditar.addEventListener("click", function () {

                campos.forEach(function (campo) {

                    if (campo.type === "checkbox") {

                        campo.disabled = false;

                    } else {

                        campo.removeAttribute("readonly");

                    }

                });


                botaoEditar.style.display = "none";

                botaoSalvar.style.display = "inline-block";

            });

        });

    </script>


</body>

</html>