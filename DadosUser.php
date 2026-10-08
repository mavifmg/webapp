<?php

session_start();
require_once "Conexao.php";


// ======================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO
// ======================================================

if (!isset($_SESSION['CPF'])) {
    header("Location: Login.php");
    exit();
}

$CPF = $_SESSION['CPF'];


// ======================================================
// EDITAR DADOS
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editar"])) {

    // CPF antigo, usado no WHERE
    $CPF_antigo = $_SESSION['CPF'];

    $CPF = $_POST["CPF"] ?? "";
    $DataNascimento = $_POST["DataNascimento"] ?? "";
    $EMAIL = $_POST["EMAIL"] ?? "";
    $Celular = $_POST["Celular"] ?? "";
    $EnderecoUsuario = $_POST["EnderecoUsuario"] ?? "";
    $Comorbidades = $_POST["Comorbidades"] ?? "";

    $Indigena = isset($_POST["Indigena"]) ? 'true' : 'false';


    // ==================================================
    // VERIFICAR CAMPOS OBRIGATÓRIOS
    // ==================================================

    if (
        empty($CPF) ||
        empty($DataNascimento) ||
        empty($EMAIL) ||
        empty($Celular) ||
        empty($EnderecoUsuario) ||
        empty($Comorbidades)
    ) {

        $_SESSION["erro_editar"] =
            "Preencha todos os campos obrigatórios.";

        header("Location: DadosUser.php");
        exit();
    }


    // ==================================================
    // ATUALIZAR NO BANCO
    // ==================================================

    $sql = 'UPDATE cadastro
            SET "CPF" = $1,
                "DataNascimento" = $2,
                "EMAIL" = $3,
                "Celular" = $4,
                "EnderecoUsuario" = $5,
                "Indigena" = $6,
                "Comorbidades" = $7
            WHERE "CPF" = $8';



    $resultado = pg_query_params(
        $conn,
        $sql,
        [
            $CPF,
            $DataNascimento,
            $EMAIL,
            $Celular,
            $EnderecoUsuario,
            $Indigena,
            $Comorbidades,
            $CPF_antigo
        ]
    );


    // ==================================================
    // VERIFICAR RESULTADO
    // ==================================================

    if ($resultado !== false) {

        // Atualizar CPF da sessão
        $_SESSION['CPF'] = $CPF;

        $_SESSION["sucesso_editar"] =
            "Dados atualizados com sucesso!";

    } else {

        $_SESSION["erro_editar"] =
            "Erro ao atualizar os dados: " .
            pg_last_error($conn);
    }


    header("Location: DadosUser.php");
    exit();
}


// ======================================================
// EXCLUIR CONTA
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir"])) {

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


    // Excluir usuário
    $sql = 'DELETE FROM cadastro
            WHERE "CPF" = $1';


    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF]
    );


    if ($resultado !== false) {

        session_destroy();

        header("Location: Cadastro.php");
        exit();

    } else {

        $_SESSION["erro_usuario"] =
            "Erro ao excluir usuário: " .
            pg_last_error($conn);

        header("Location: DadosUser.php");
        exit();
    }
}


// ======================================================
// BUSCAR DADOS DO USUÁRIO
// ======================================================

$sql = 'SELECT
            "CPF",
            "DataNascimento",
            "EMAIL",
            "Celular",
            "EnderecoUsuario",
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AquiVacina | Dados do Usuário</title>

    <link rel="stylesheet" href="AquiVacina.css">

</head>


<body class="DadosUser">


    <div class="LocDadosUser">

        <div class="FundoDadosUser">

            <div class="LocInformacoesUbs">

                <a href="Menu.php">

                    <button type="button">
                        Voltar
                    </button>

                </a>


                <h1>

                    Dados do CPF:

                    <?php
                    echo htmlspecialchars($usuario["CPF"]);
                    ?>

                </h1>


                <div class="Dados">

                    <p>

                        <strong>
                            Data de Nascimento:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $usuario["DataNascimento"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Email:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $usuario["EMAIL"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Celular:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $usuario["Celular"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Endereço:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $usuario["EnderecoUsuario"]
                        );
                        ?>

                    </p>





                    <p>

                        <strong>
                            Indígena:
                        </strong>


                        <?php

                        echo $usuario["Indigena"] === "t"
                            ? "Sim"
                            : "Não";

                        ?>

                    </p>


                    <p>

                        <strong>
                            Comorbidades:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $usuario["Comorbidades"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Autorização Local:
                        </strong>


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
        =================================================== -->

        <div class="DadosUsuario">

            <h1>
                Dados do Usuário
            </h1>


            <form action="DadosUser.php" method="POST" id="formUsuario">


                <!-- CPF -->

                <label for="CPF">
                    CPF
                </label>

                <input type="text" name="CPF" id="CPF" value="<?php echo htmlspecialchars($usuario["CPF"]); ?>"
                    readonly>


                <!-- DATA DE NASCIMENTO -->

                <label for="DataNascimento">
                    Data de Nascimento
                </label>

                <input type="date" name="DataNascimento" id="DataNascimento"
                    value="<?php echo htmlspecialchars($usuario["DataNascimento"]); ?>" readonly>


                <!-- EMAIL -->

                <label for="EMAIL">
                    Email
                </label>

                <input type="email" name="EMAIL" id="EMAIL" value="<?php echo htmlspecialchars($usuario["EMAIL"]); ?>"
                    readonly>


                <!-- CELULAR -->

                <label for="Celular">
                    Celular
                </label>

                <input type="text" name="Celular" id="Celular"
                    value="<?php echo htmlspecialchars($usuario["Celular"]); ?>" readonly>


                <!-- ENDEREÇO -->

                <label for="EnderecoUsuario">
                    Endereço
                </label>

                <input type="text" name="EnderecoUsuario" id="EnderecoUsuario"
                    value="<?php echo htmlspecialchars($usuario["EnderecoUsuario"]); ?>" readonly>


                <!-- COMORBIDADES -->

                <label for="Comorbidades">
                    Comorbidades
                </label>

                <input type="text" name="Comorbidades" id="Comorbidades"
                    value="<?php echo htmlspecialchars($usuario["Comorbidades"]); ?>" readonly>


                <!-- INDÍGENA -->

                <label id="indigena">

                    <input type="checkbox" name="Indigena" id="Indigena" value="true" <?php

                    if ($usuario["Indigena"] === "t") {
                        echo "checked";
                    }

                    ?> disabled>

                    Sou indígena

                </label>


                <br>
                <br>


                <!-- MENSAGEM DE ERRO -->

                <?php

                if (isset($_SESSION["erro_editar"])) {

                    echo "<p class='text-danger'>" .
                        htmlspecialchars(
                            $_SESSION["erro_editar"]
                        ) .
                        "</p>";

                    unset($_SESSION["erro_editar"]);
                }

                ?>


                <!-- MENSAGEM DE SUCESSO -->

                <?php

                if (isset($_SESSION["sucesso_editar"])) {

                    echo "<p class='text-success'>" .
                        htmlspecialchars(
                            $_SESSION["sucesso_editar"]
                        ) .
                        "</p>";

                    unset($_SESSION["sucesso_editar"]);
                }

                ?>


                <!-- BOTÃO EDITAR -->

                <button class="BotaoCadastrar" type="button" id="BEditar">

                    Editar

                </button>


                <!-- BOTÃO SALVAR -->

                <button class="BotaoCadastrar" type="submit" name="editar" id="BSalvar" style="display: none;">

                    Salvar alteração

                </button>


            </form>


            <!-- ==================================================
                 FORMULÁRIO DE EXCLUSÃO
            =================================================== -->

            <form action="DadosUser.php" method="POST"
                onsubmit="return confirm('Tem certeza que deseja excluir sua conta?');">

                <button class="BotaoCadastrar" type="submit" name="excluir">

                    Excluir conta

                </button>

            </form>


        </div>


    </div>


    <!-- ======================================================
         JAVASCRIPT DO BOTÃO EDITAR
    ======================================================= -->

    <script>

        document.addEventListener("DOMContentLoaded", function () {


            const botaoEditar =
                document.getElementById("BEditar");


            const botaoSalvar =
                document.getElementById("BSalvar");


            const campos = [

                document.getElementById("CPF"),

                document.getElementById("DataNascimento"),

                document.getElementById("EMAIL"),

                document.getElementById("Celular"),

                document.getElementById("EnderecoUsuario"),

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


                // Esconder botão Editar

                botaoEditar.style.display = "none";


                // Mostrar botão Salvar

                botaoSalvar.style.display = "inline-block";


            });

        });

    </script>


</body>

</html>