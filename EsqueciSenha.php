
<?php
session_start();
require_once "Conexao.php";

$erro = "";
$mensagem = "";
$etapa = "verificar";
$usuario = null;

function ocultarEmail($email)
{
    $partes = explode("@", $email, 2);

    if (count($partes) !== 2) {
        return "E-mail cadastrado";
    }

    $nome = $partes[0];
    $dominio = $partes[1];

    return substr($nome, 0, 1)
        . str_repeat("*", max(3, strlen($nome) - 1))
        . "@"
        . $dominio;
}

function ocultarCelular($celular)
{
    $numero = preg_replace('/\D/', '', $celular);

    if (strlen($numero) < 4) {
        return "Celular cadastrado";
    }

    return str_repeat("*", max(0, strlen($numero) - 4))
        . substr($numero, -4);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ETAPA 1: verificar CPF e data de nascimento
    if (isset($_POST["verificar"])) {

        $CPF = preg_replace('/\D/', '', $_POST["CPF"] ?? "");
        $DataNascimento = $_POST["DataNascimento"] ?? "";

        $sql = 'SELECT "CPF", "EMAIL", "Celular"
                FROM cadastro
                WHERE regexp_replace("CPF", \'\\D\', \'\', \'g\') = $1
                AND "DataNascimento" = $2::date';

        $resultado = pg_query_params(
            $conn,
            $sql,
            [$CPF, $DataNascimento]
        );

        if ($resultado && pg_num_rows($resultado) === 1) {

            $usuario = pg_fetch_assoc($resultado);

            $_SESSION["cpf_recuperacao"] = $usuario["CPF"];
            $etapa = "nova_senha";

        } else {
            $erro = "CPF ou data de nascimento incorretos.";
        }
    }

    // ETAPA 2: salvar a nova senha
    if (isset($_POST["alterar_senha"])) {

        $senha = $_POST["Senha"] ?? "";
        $confirmacao = $_POST["Confirmacao"] ?? "";
        $CPF = $_SESSION["cpf_recuperacao"] ?? "";

        if (empty($CPF)) {

            $erro = "Sessão expirada. Verifique seus dados novamente.";

        } elseif (strlen($senha) < 8) {

            $erro = "A senha deve ter pelo menos 8 caracteres.";
            $etapa = "nova_senha";

        } elseif ($senha !== $confirmacao) {

            $erro = "As senhas não são iguais.";
            $etapa = "nova_senha";

        } else {

            $hash = password_hash($senha, PASSWORD_DEFAULT);

            // Confirme se a coluna no seu banco é "senha"
            // ou "Senha". O PostgreSQL diferencia maiúsculas
            // e minúsculas em nomes entre aspas.
            $sql = 'UPDATE cadastro
                    SET "senha" = $1
                    WHERE "CPF" = $2';

            $resultado = pg_query_params(
                $conn,
                $sql,
                [$hash, $CPF]
            );

            if ($resultado && pg_affected_rows($resultado) === 1) {

                unset($_SESSION["cpf_recuperacao"]);

                $mensagem = "Senha alterada com sucesso! Você já pode entrar.";

            } else {

                $erro = "Não foi possível alterar a senha. Verifique o nome da coluna no banco.";
                $etapa = "nova_senha";
            }
        }

        // Recarrega os dados para mostrar os contatos
        // caso seja necessário corrigir algum erro.
        if ($etapa === "nova_senha" && $CPF !== "") {

            $sql = 'SELECT "CPF", "EMAIL", "Celular"
                    FROM cadastro
                    WHERE "CPF" = $1';

            $resultadoUsuario = pg_query_params(
                $conn,
                $sql,
                [$CPF]
            );

            if ($resultadoUsuario &&
                pg_num_rows($resultadoUsuario) === 1) {
                $usuario = pg_fetch_assoc($resultadoUsuario);
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

                    <label for="CPF">CPF</label>
                    <input
                        id="CPF"
                        type="text"
                        name="CPF"
                        placeholder="Digite seu CPF"
                        required
                    >

                    <label for="DataNascimento">
                        Data de nascimento
                    </label>

                    <input
                        id="DataNascimento"
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

                <?php if ($usuario !== null): ?>

                    <div class="DadosRecuperacao">

                        <h3>Dados do cadastro</h3>

                        <p>
                            <strong>E-mail:</strong>
                            <?= htmlspecialchars(
                                ocultarEmail($usuario["EMAIL"] ?? "")
                            ) ?>
                        </p>

                        <p>
                            <strong>Celular:</strong>
                            <?= htmlspecialchars(
                                ocultarCelular($usuario["Celular"] ?? "")
                            ) ?>
                        </p>

                    </div>

                <?php endif; ?>

                <p>Cadastre uma nova senha para sua conta.</p>

                <form method="POST" action="EsqueciSenha.php">

                    <label for="Senha">Nova senha</label>
                    <input
                        id="Senha"
                        type="password"
                        name="Senha"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <label for="Confirmacao">
                        Confirme a nova senha
                    </label>

                    <input
                        id="Confirmacao"
                        type="password"
                        name="Confirmacao"
                        minlength="8"
                        autocomplete="new-password"
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