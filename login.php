<?php
session_start();
require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $CPF = preg_replace('/\D/', '', $_POST["CPF"] ?? "");
    $DataNascimento = $_POST["DataNascimento"] ?? "";

    // Verifica se o CPF está cadastrado
    $sql = 'SELECT "CPF", "DataNascimento"
            FROM cadastro
            WHERE "CPF" = $1';

    $resultado = pg_query_params($conn, $sql, [$CPF]);

    if ($resultado === false) {
        error_log(pg_last_error($conn));

        $_SESSION['erro_login'] =
            "Não foi possível verificar seus dados. Tente novamente.";

        header("Location: Login.php");
        exit();
    }

    // Se o CPF não existir, encaminha para o cadastro
    if (pg_num_rows($resultado) === 0) {
        $_SESSION['erro_cadastro'] =
            "Este CPF não está cadastrado. Faça seu cadastro.";

        header("Location: Cadastro.php");
        exit();
    }

    // O CPF existe: verifica a data de nascimento correspondente
    $usuario = pg_fetch_assoc($resultado);

    if (
        empty($DataNascimento) ||
        $usuario["DataNascimento"] !== $DataNascimento
    ) {
        $_SESSION['erro_login'] =
            "A data de nascimento não corresponde ao CPF informado.";

        header("Location: Login.php");
        exit();
    }

    // CPF e data de nascimento correspondem
    $_SESSION['CPF'] = $usuario["CPF"];
    $_SESSION['DataNascimento'] = $usuario["DataNascimento"];

    header("Location: Menu.php");
    exit();
}

pg_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquiVacina | Site Para Usuarios</title>
    <link rel="stylesheet" href="AquiVacina.css">
</head>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<body class="Login">

    <div class="LocLogin">
        <div class="FundoLog">
            <?php include "Logo.php"; ?>

            <br>

            <form action="Login.php" method="POST">

                <label id="LogCPF">CPF</label>
                <input placeholder="Digite seu CPF:" type="text" name="CPF">

                <label id="LogDataNascimento">
                    Data de Nascimento
                </label>

                <input placeholder="Data de nascimento" type="date" name="DataNascimento">

                <div style="text-align: center;">

                    <br>

                    <a class="LetraLogin" href="EsqueciSenha.php">
                        Esqueci a senha
                    </a>

                    <br>

                    <?php
                    if (isset($_SESSION['erro_login'])) {
                        echo "<p class='text-danger'>"
                            . $_SESSION['erro_login']
                            . "</p>";

                        unset($_SESSION['erro_login']);
                    }
                    ?>

                    <button type="submit" class="BotaoCadastrar">Login</button>

                </div>
            </form>

            <div style="text-align: center;">
                <a href="Cadastro.php">
                    <button type="button" class="BotaoCadastrar">Cadastro</button>
                </a>
            </div>

        </div>
    </div>

</body>

</html>