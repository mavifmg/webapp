<?php
    session_start();
    require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $NumeroCorem = $_POST["NumeroCorem"];
    $Email = $_POST["Email"];
    $Telefone = $_POST["Telefone"];
    $CPF = $_POST["CPF"];
    $Senha = $_POST["Senha"];
    $ConfirmeSenha = $_POST["ConfirmeSenha"];
    
    if(

        empty($NumeroCorem) ||
        empty($Email) ||
        empty($Telefone) ||
        empty($CPF) ||
        empty($Senha) ||
        empty($ConfirmeSenha)
        
    ) {

        $_SESSION['erro_cadastro'] = "Campos vazios, preencha todos";
        header ("location: Cadastro.php");
        exit();

    }

    if ($Senha !== $ConfirmeSenha) {
        $_SESSION["erro_cadastro"] = "As senhas informadas não são iguais";
        header ("location: Cadastro.php");
        exit();
        
    }

    $sql = "INSERT INTO cadastro (NumeroCorem, Email, Telefone, CPF, Senha, ConfirmeSenha)
            VALUES ($1, $2, $3, $4, $5, $6)";

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$NumeroCorem, $Email, $Telefone, $CPF, $Senha, $ConfirmeSenha]
    );
    
    if ($resultado) {
        
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
                <label id="corem">Número COREM</label>
                <input placeholder="Digite seu número COREN" type="text" name="NumeroCorem">

                <label id="email">Email</label>
                <input placeholder="Digite seu email" type="email" name="Email">

                <label id="telefone">Telefone</label>
                <input placeholder="Digite seu telefone" type="text" name="Telefone">

                <label id="cpf">CPF</label>
                <input placeholder="Digite seu CPF" type="text" name="CPF">

                <label id="senha">Senha</label>
                <input placeholder="Digite seu senha" type="password" name="Senha">

                <label id="Csenha" >Confirme senha</label>
                <input placeholder="Confirme a senha" type="password" name="ConfirmeSenha">
 
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