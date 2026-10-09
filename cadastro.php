<?php
session_start();
require_once "Conexao.php";
require_once "Geocodificacao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $CPF = $_POST["CPF"];
    $DataNascimento = $_POST["DataNascimento"];
    $EMAIL = $_POST["EMAIL"];
    $Celular = $_POST["Celular"];
    $CEP = $_POST["CEP"];
    $NumCasa = $_POST["NumCasa"];
    //$EnderecoUsuario = $_POST["EnderecoUsuario"];
    $Indigena = isset($_POST["Indigena"]) ? 'true' : 'false';
    $Comorbidades = $_POST["Comorbidades"];
    $Autorizacao_Loc = isset($_POST["Autorizacao_Loc"]) ? 'true' : 'false';

    //    $EnderecoUsuario = $CEP . ", " . $NumCasa . ", " . $EnderecoUsuario;

    $coordenadas = obterCoordenadas($CEP, $NumCasa);


    if (
        empty($CPF) ||
        empty($DataNascimento) ||
        empty($EMAIL) ||
        empty($Celular) ||
        //empty($EnderecoUsuario) ||
        empty($CEP) ||
        empty($NumCasa) ||
        empty($Comorbidades) ||
        !isset($_POST["Autorizacao_Loc"])
    ) {
        $_SESSION['erro_cadastro'] = "Preencha todos os campos obrigatórios e aceite a autorização local para prosseguir com o cadastro.";
        header("Location: Cadastro.php");
        exit();
    }

    if ($coordenadas === false) {
        $_SESSION['erro_cadastro'] = "Não foi possível obter as coordenadas do endereço fornecido. Por favor, verifique o endereço e tente novamente.";
        header("Location: Cadastro.php");
        exit();
    }

    $LatitudeUser = $coordenadas["latitude"];
    $LongitudeUser = $coordenadas["longitude"];

    // PARTE 4: MOSTRAR AS COORDENADAS OBTIDAS PELA API
    // echo "Endereço: " . htmlspecialchars($CEP) . ", " . htmlspecialchars($NumCasa) . "<br>";
    //echo "Latitude: " . htmlspecialchars($LatitudeUser) . "<br>";
    //echo "Longitude: " . htmlspecialchars($LongitudeUser) . "<br>";

    //exit();

    //,/*\"EnderecoUsuario\"*/
    $sql = "INSERT INTO cadastro (\"CPF\", \"DataNascimento\", \"EMAIL\", \"Celular\",\"CEP\",\"NumCasa\", \"Indigena\", \"Comorbidades\", \"Autorizacao_Loc\", \"LatitudeUser\", \"LongitudeUser\")
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF, $DataNascimento, $EMAIL, $Celular, $CEP, $NumCasa, $Indigena, $Comorbidades, $Autorizacao_Loc, $LatitudeUser, $LongitudeUser]
    );

    if ($resultado !== false) {

        $_SESSION['CPF'] = $CPF;
        $_SESSION['DataNascimento'] = $DataNascimento;

        header("location: Menu.php");
        exit();

    } else {

        $erroBanco = pg_last_error($conn);

        die(
            "<h2>Erro ao cadastrar no banco de dados</h2>" .
            "<p>Mensagem retornada pelo PostgreSQL:</p>" .
            "<pre>" . htmlspecialchars($erroBanco) . "</pre>"
        );
    }


}

pg_close($conn);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquiVacina | Cadastro</title>
    <link rel="stylesheet" href="AquiVacina.css">
    <script src="https://kit.fontawesome.com/cc9bf1d657.js" crossorigin="anonymous"></script>

</head>

<body class="Cadastro">

    <a href="Login.php">
        <button id="Posicao"><i class="fa-solid fa-arrow-left"></i></button>
    </a>

    <h2> Usuário não cadastrado</h2>


    <div class="LocCad">
        <div class="CadastroCard">

            <br>
            <form action="Cadastro.php" method="POST">

                <label id="cpf">CPF</label>
                <input placeholder="Digite seu CPF" type="text" name="CPF">

                <label id="senha">Data de Nascimento</label>
                <input placeholder="Digite sua data de nascimento" type="date" name="DataNascimento">

                <label id="email">Email</label>
                <input placeholder="Digite seu email" type="email" name="EMAIL">

                <label id="celular">Celular</label>
                <input placeholder="Digite seu celular" type="text" name="Celular">

                <div class="CepNumero">

                    <div>
                        <label id="cep">CEP</label>
                        <input placeholder="Digite seu CEP" type="text" name="CEP">

                        <label id="numcasa">Número da Casa</label>
                        <input placeholder="Digite o número da casa" type="text" name="NumCasa">

                    </div>

                </div>

                <label id="comorbidades">Comorbidades</label>
                <input placeholder="Digite suas comorbidades" type="text" name="Comorbidades">

                <label id="indigena">
                    <input type="checkbox" name="Indigena" value="true">
                    Sou indígena
                </label>

                <label id="autorizacao">
                    <input type="checkbox" name="Autorizacao_Loc" value="true">
                    Autorizo meu local atual
                </label>
                <br>


                <?php
                if (isset($_SESSION['erro_cadastro'])) {
                    echo "<p class='text-danger'>" . $_SESSION['erro_cadastro'] . "</p>";
                    unset($_SESSION['erro_cadastro']);
                }
                ?>

                <?php
                if (isset($_SESSION['cadastro'])) {
                    echo "<p class='text-sucess'>" . $_SESSION['cadastro'] . "</p>";
                    unset($_SESSION['cadastro']);
                }
                ?>

                <button type="submit" class="BotaoCadastrar">Completar cadastro</button>

            </form>


        </div>
    </div>
</body>

</html>