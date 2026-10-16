
<?php
session_start();
require_once "Conexao.php";

$erro = "";
$mensagem = "";
$etapa = "verificar";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Etapa 1: verificar os dados do cadastro
    if (isset($_POST["verificar"])) {

        $CPF = preg_replace('/\D/', '', $_POST["CPF"] ?? "");
        $DataNascimento = $_POST["DataNascimento"] ?? "";

        $sql = 'SELECT "CPF"
                FROM cadastro
                WHERE regexp_replace("CPF", \'\\D\', \'\', \'g\') = $1
                AND "DataNascimento" = $2::date';

        $resultado = pg_query_params(
            $conn,
            $sql,
            [$CPF, $DataNascimento]
        );

        if ($resultado && pg_num_rows($resultado) > 0) {
            $_SESSION["cpf_recuperacao"] = $CPF;
            $etapa = "nova_senha";
        } else {
            $erro = "Dados não encontrados. Confira o CPF e a data de nascimento.";
        }
    }

    // Etapa 2: salvar a nova senha
    if (isset($_POST["alterar_senha"])) {

        $senha = $_POST["Senha"] ?? "";
        $confirmacao = $_POST["Confirmacao"] ?? "";
        $CPF = $_SESSION["cpf_recuperacao"] ?? "";

        if (empty($CPF)) {
            $erro = "Sua sessão expirou. Comece novamente.";
        } elseif (strlen($senha) < 8) {
            $erro = "A senha deve ter pelo menos 8 caracteres.";
            $etapa = "nova_senha";
        } elseif ($senha !== $confirmacao) {
            $erro = "As senhas não são iguais.";
            $etapa = "nova_senha";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = 'UPDATE cadastro
                    SET "Senha" = $1
                    WHERE regexp_replace("CPF", \'\\D\', \'\', \'g\') = $2';

            $resultado = pg_query_params(
                $conn,
                $sql,
                [$hash, $CPF]
            );

            if ($resultado && pg_affected_rows($resultado) === 1) {
                unset($_SESSION["cpf_recuperacao"]);
                $mensagem = "Senha alterada com sucesso! Você já pode entrar.";
            } else {
                $erro = "Não foi possível alterar a senha.";
                $etapa = "nova_senha";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha | AquiVacina</title>
    <link rel="stylesheet" href="AquiVacina.css">
</head>

<body class="Login">
    <div class="LocLogin">
        <div class="FundoLog">

            <h2>Recuperar senha</h2>

            <?php if ($erro !== ""): ?>
                <p style="color:red;">
                    <?= htmlspecialchars($erro) ?>
                </p>
            <?php endif; ?>

            <?php if ($mensagem !== ""): ?>
                <p style="color:green;">
                    <?= htmlspecialchars($mensagem) ?>
                </p>
                <a href="Login.php">Voltar para o login</a>

            <?php elseif ($etapa === "verificar"): ?>

                <form method="POST" action="EsqueciSenha.php">
                    <label>CPF</label>
                    <input
                        type="text"
                        name="CPF"
                        placeholder="Digite seu CPF"
                        required
                    >

                    <label>Data de nascimento</label>
                    <input
                        type="date"
                        name="DataNascimento"
                        required
                    >

                    <button
                        type="submit"
                        name="verificar"
                        class="BotaoCadastrar"
                    >
                        Verificar cadastro
                    </button>
                </form>

            <?php else: ?>

                <form method="POST" action="EsqueciSenha.php">
                    <label>Nova senha</label>
                    <input
                        type="password"
                        name="Senha"
                        minlength="8"
                        required
                    >

                    <label>Confirme a nova senha</label>
                    <input
                        type="password"
                        name="Confirmacao"
                        minlength="8"
                        required
                    >

                    <button
                        type="submit"
                        name="alterar_senha"
                        class="BotaoCadastrar"
                    >
                        Salvar nova senha
                    </button>
                </form>

            <?php endif; ?>

        </div>
    </div>
</body>
</html>
