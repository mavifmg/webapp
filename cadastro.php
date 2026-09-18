<?php
    session_start();
    require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $CPF = $_POST["CPF"];
    $DataNascimento = $_POST["DataNascimento"];
    $EMAIL = $_POST["Email"];
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

        $_SESSION['erro_cadastro'] = "Campos vazios, preencha todos";
        header ("location: Cadastro.php");
        exit();

    }

    if ($Senha !== $ConfirmeSenha) {
        $_SESSION["erro_cadastro"] = "As senhas informadas não são iguais";
        header ("location: Cadastro.php");
        exit();
        
    }

    $sql = "INSERT INTO cadastro (CPF, DataNascimento, EMAIL, Celular, CEP, Indigena, Comorbidades, Autorizacao_Loc)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF, $DataNascimento, $EMAIL, $Celular, $CEP, $Indigena, $Comorbidades, $Autorizacao_Loc]
    );
    
    if (pg_num_rows($resultado) > 0) {
        
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

                <label id="telefone">Telefone</label>
                <input placeholder="Digite seu telefone" type="text" name="Telefone">

                <label id="celular">Celular</label>
                <input placeholder="Digite seu celular" type="text" name="Celular"> 

                <label id="cep">CEP</label>
                <input placeholder="Digite seu CEP" type="text" name="CEP"> 

                <label id="indigena">Indígena</label>
                <select name="Indigena">
                    <option value="">Selecione</option>
                    <option value="Sim">Sim</option>
                    <option value="Não">Não</option>
                </select>

                <label id="comorbidades">Comorbidades</label>
                <input placeholder="Digite suas comorbidades" type="text" name="Comorbidades">

                <label id="autorizacao">Autorização Local</label>
                <select name="Autorizacao_Loc">   
                    <option value="">Selecione</option>
                    <option value="Sim">Sim</option>
                    <option value="Não">Não</option>    
                </select>
                
 
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