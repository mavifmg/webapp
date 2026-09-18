<?php
session_start();
require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $CPF = $_POST["CPF"];
    $DataNascimento = $_POST["DataNascimento"];

    $sql = 'SELECT * FROM cadastro
            WHERE "CPF" = $1
            AND "DataNascimento" = $2::date';

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF, $DataNascimento]
    );

   if ($resultado === false) {
        die("Erro na consulta: " . pg_last_error($conn));
    }


    if (pg_num_rows($resultado) > 0) {

        header("Location: Menu.html");
        exit();

    } else {

        $_SESSION['erro_login'] = "informações incorretas";
        header("Location: Login.php");
        exit();
    }

    pg_close($conn);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquiVacina | Site Para Usuarios</title>
    <link rel="stylesheet" href="AquiVacina.css">
</head>

<body class="Login">

    <div class="LocLogin">
        <div class="FundoLog">

            <br>

            <form action="Login.php" method="POST">

                <label id="LogCPF">CPF</label>
                <input placeholder="CPF" type="text" name="CPF">

                <label id="LogDataNascimento">
                    Data de Nascimento
                </label>

                <input
                    placeholder="Data de nascimento"
                    type="date"
                    name="DataNascimento"
                >

                <div style="text-align: center;">

                    <br>

                    <a class="LetraLogin" href="EsqueciSenha.html">
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

                    <button type="submit">Login</button>

                </div>
            </form>

            <div style="text-align: center;">
                <a href="Cadastro.php">
                    <button type="button">Cadastro</button>
                </a>
            </div>

        </div>
    </div>

</body>
</html>