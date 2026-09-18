<?php
    session_start();
    require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $CPF = $_POST["CPF"];
    $DataNascimento = $_POST["DataNascimento"];
    $EMAIL = $_POST["EMAIL"];
    $Celular = $_POST["Celular"];
    $CEP = $_POST["CEP"];
    $Indigena =  isset($_POST["Indigena"]) ? 'true' : 'false';
    $Comorbidades = $_POST["Comorbidades"];
    $Autorizacao_Loc = isset($_POST["Autorizacao_Local"]) ? 'true' : 'false';

    if(

        empty($CPF) ||
        empty($EMAIL) ||
        empty($DataNascimento) ||
        empty($Celular) ||
        empty($CEP) ||
        empty($Indigena) ||
        empty($Comorbidades) ||
        empty($Autorizacao_Loc) 
        
    ) {
        $aceito = isset($_POST["Autorizacao_Loc"]) ? 'true' : 'false';

        if (!$aceito) { $_SESSION['erro_cadastro'] = "Preencha todos os campos e aceite a autorização local para prosseguir com o cadastro."; 
        header("Location: Cadastro.php"); exit(); }

    }

        $sql = "INSERT INTO cadastro (\"CPF\", \"DataNascimento\", \"EMAIL\", \"Celular\", \"CEP\", \"Indigena\", \"Comorbidades\", \"Autorizacao_Loc\")
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF, $DataNascimento, $EMAIL, $Celular, $CEP, $Indigena, $Comorbidades, $Autorizacao_Loc]
    );
    
    if ($resultado !== false) {
        
        $_SESSION['cadastro'] = "Cadastro realizado";

    } else {

        $_SESSION['erro_cadastro'] = "Erro ao cadastrar!";
    }

        header("location: Cadastro.php");
        exit();

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
    
    <a href="Login.html">
        <button id="Posicao"><i class="fa-solid fa-arrow-left"></i></button>
    </a>

    <div class="LocCad">
        <div class="FundoCad">
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

                <label id="cep">CEP</label>
                <input placeholder="Digite seu CEP" type="text" name="CEP"> 

                <label id="comorbidades">Comorbidades</label>
                <input placeholder="Digite suas comorbidades" type="text" name="Comorbidades">

                <label id="indigena">Indígena</label>
                <input type="checkbox" name="Indigena" value="true">

                <label id="autorizacao">Autorização Local</label>
                <input type="checkbox" name="Autorizacao_Loc" value="true">
                <br>
                
 
                <?php
                    if(isset($_SESSION['erro_cadastro'])) {
                        echo "<p class='text-danger'>" . $_SESSION['erro_cadastro'] . "</p>";
                        unset($_SESSION['erro_cadastro']);
                    }
                ?>

                <?php
                    if(isset($_SESSION['cadastro'])) {
                        echo "<p class='text-sucess'>" . $_SESSION['cadastro'] . "</p>";
                        unset($_SESSION['cadastro']);
                    }
                ?>

                <button type="submit">Completar cadastro</button>
                
            </form>
            
        </div>
    </div>
</body>
</html>