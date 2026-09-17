<?php

    $host = "10.90.24.54";
    $port = "5432";
    $dbname = "AquiVacina";
    $user = "aula";
    $password = "aula";

    $conn = pg_connect(
        "host=$host port=$port dbname=$dbname user=$user password=$password"
    );

    if (!$conn) {
        die("Erro ao conectar ao banco de dados.");
    }
    

?>
<?php
    session_start();
    require_once "Conexao.php";
    
    $CPF = $_POST["CPF"];
    $DataNascimento = $_POST["DataNascimento"];
    
    $sql = "SELECT * FROM cadastro
            WHERE CPF = $1 AND DataNascimento = $2";

    $resultado = pg_query_params(
        $conn,
        $sql, 
        [$CPF, $DataNascimento]
    );

   if (pg_num_rows($resultado) > 0) {
        
       header("location: Menu.html");
         exit();
        
    } else {

        $_SESSION['erro_login'] = "Campos vazios ou informações incorretas";

    }

    pg_close($conn);

?>
<?php
                if(isset($_SESSION['erro_login'])) {
                    echo "<p class='text-danger'>" . $_SESSION['erro_login'] . "</p>";
                    unset($_SESSION['erro_login']);
                }
            ?>