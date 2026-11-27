
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/* =====================================================
   CONEXÃO COM O BANCO DE DADOS
   ===================================================== */

$host = "10.90.24.54";
$port = "5432";
$dbname = "AquiVacina";
$user = "aula";
$password = "aula";

$conn = @pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);

if (!$conn) {
    die("Não foi possível conectar ao banco de dados. Verifique a conexão com a rede da escola.");
}

/* =====================================================
   MENSAGENS
   ===================================================== */

$mensagem = "";
$erro = "";
$criancaEditar = null;

/* =====================================================
   CADASTRAR CRIANÇA
   ===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["acao"] ?? "") === "cadastrar"
) {
    $paciente_id = $_POST["paciente_id"] ?? "";
    $nome = trim($_POST["nome"] ?? "");
    $cpf = trim($_POST["cpf"] ?? "");
    $data_nascimento = $_POST["data_nascimento"] ?? "";
    $indigena_quilombola = $_POST["indigena_quilombola"] ?? "false";
    $comorbidades = trim($_POST["comorbidades"] ?? "");

    if (
        $paciente_id === "" ||
        $nome === "" ||
        $data_nascimento === ""
    ) {
        $erro = "Preencha os campos obrigatórios.";
    } else {
        $resultado = pg_query_params(
            $conn,
            "INSERT INTO criancas
                (paciente_id, nome, cpf, data_nascimento,
                 indigena_quilombola, comorbidades)
             VALUES ($1, $2, $3, $4, $5, $6)",
            [
                $paciente_id,
                $nome,
                $cpf !== "" ? $cpf : null,
                $data_nascimento,
                $indigena_quilombola === "true" ? "true" : "false",
                $comorbidades
            ]
        );

        if ($resultado) {
            $mensagem = "Criança cadastrada com sucesso!";
        } else {
            $erro = "Erro ao cadastrar a criança: " . pg_last_error($conn);
        }
    }
}

/* =====================================================
   REMOVER CRIANÇA
   ===================================================== */

if (
    ($_GET["acao"] ?? "") === "remover" &&
    isset($_GET["id"]) &&
    is_numeric($_GET["id"])
) {
    $id = (int) $_GET["id"];

    $resultado = pg_query_params(
        $conn,
        "DELETE FROM criancas WHERE id = $1",
        [$id]
    );

    if ($resultado && pg_affected_rows($resultado) > 0) {
        $mensagem = "Criança removida com sucesso.";
    } elseif ($resultado) {
        $erro = "Criança não encontrada.";
    } else {
        $erro = "Erro ao remover a criança: " . pg_last_error($conn);
    }
}

/* =====================================================
   CARREGAR CRIANÇA PARA EDITAR
   ===================================================== */

if (
    isset($_GET["editar"]) &&
    is_numeric($_GET["editar"])
) {
    $idEditar = (int) $_GET["editar"];

    $resultadoEditar = pg_query_params(
        $conn,
        "SELECT * FROM criancas WHERE id = $1",
        [$idEditar]
    );

    if ($resultadoEditar && pg_num_rows($resultadoEditar) > 0) {
        $criancaEditar = pg_fetch_assoc($resultadoEditar);
    } else {
        $erro = "Criança não encontrada.";
    }
}

/* =====================================================
   ATUALIZAR CRIANÇA
   ===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["acao"] ?? "") === "editar"
) {
    $id = $_POST["id"] ?? "";
    $paciente_id = $_POST["paciente_id"] ?? "";
    $nome = trim($_POST["nome"] ?? "");
    $cpf = trim($_POST["cpf"] ?? "");
    $data_nascimento = $_POST["data_nascimento"] ?? "";
    $indigena_quilombola = $_POST["indigena_quilombola"] ?? "false";
    $comorbidades = trim($_POST["comorbidades"] ?? "");

    if (
        !is_numeric($id) ||
        $paciente_id === "" ||
        $nome === "" ||
        $data_nascimento === ""
    ) {
        $erro = "Preencha corretamente os campos obrigatórios.";
    } else {
        $resultado = pg_query_params(
            $conn,
            "UPDATE criancas
             SET paciente_id = $1,
                 nome = $2,
                 cpf = $3,
                 data_nascimento = $4,
                 indigena_quilombola = $5,
                 comorbidades = $6
             WHERE id = $7",
            [
                $paciente_id,
                $nome,
                $cpf !== "" ? $cpf : null,
                $data_nascimento,
                $indigena_quilombola === "true" ? "true" : "false",
                $comorbidades,
                (int) $id
            ]
        );

        if ($resultado && pg_affected_rows($resultado) >= 0) {
            $mensagem = "Dados da criança atualizados com sucesso.";
            $criancaEditar = null;
        } else {
            $erro = "Erro ao atualizar a criança: " . pg_last_error($conn);

            $criancaEditar = [
                "id" => $id,
                "paciente_id" => $paciente_id,
                "nome" => $nome,
                "cpf" => $cpf,
                "data_nascimento" => $data_nascimento,
                "indigena_quilombola" => $indigena_quilombola === "true" ? "t" : "f",
                "comorbidades" => $comorbidades
            ];
        }
    }
}

/* =====================================================
   BUSCAR CRIANÇAS
   ===================================================== */

$resultadoCriancas = pg_query(
    $conn,
    "SELECT id, paciente_id, nome, cpf, data_nascimento,
            indigena_quilombola, comorbidades
     FROM criancas
     ORDER BY id"
);

if (!$resultadoCriancas) {
    die("Erro ao buscar crianças: " . pg_last_error($conn));
}

/* =====================================================
   VALORES DO FORMULÁRIO
   ===================================================== */

$valor = static function ($campo) use ($criancaEditar) {
    return htmlspecialchars(
        (string) ($criancaEditar[$campo] ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
};

$valorIndigena = $criancaEditar["indigena_quilombola"] ?? "f";
$indigenaSim = in_array($valorIndigena, ["t", "true", true, 1, "1"], true);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AquiVacina | Cadastro de Crianças</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #eef8ff;
            color: #24445c;
            padding: 20px;
        }

        .ContainerCadastro {
            max-width: 700px;
            margin: auto;
        }

        .Voltar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: white;
            color: #287cae;
            text-decoration: none;
            font-size: 25px;
            box-shadow: 0 4px 12px rgba(70,130,160,0.12);
            margin-bottom: 15px;
        }

        .CadastroCard {
            background: white;
            border-radius: 28px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(70,130,160,0.15);
        }

        .Logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .Logo img {
            width: 150px;
            height: auto;
        }

        h1 {
            text-align: center;
            color: #245b78;
            font-size: 25px;
            margin-bottom: 8px;
        }

        .Descricao {
            text-align: center;
            color: #6b8291;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            color: #36596d;
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5e8f0;
            border-radius: 12px;
            background: #f8fcff;
            color: #36596d;
            font-size: 14px;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #4da8d1;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .BotaoCadastrar {
            margin-top: 18px;
            border: none;
            border-radius: 13px;
            padding: 13px;
            background: #4da8d1;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .BotaoCadastrar:hover {
            background: #378fb8;
        }

        .Mensagem,
        .Erro {
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .Mensagem {
            background: #e5f7ef;
            color: #277557;
        }

        .Erro {
            background: #fff0f0;
            color: #b34d4d;
        }

        .ListaCriancas {
            margin-top: 35px;
        }

        .ListaCriancas h2 {
            color: #245b78;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .CriancaCard {
            background: #f8fcff;
            border: 1px solid #dceef6;
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .CriancaCard h3 {
            color: #286985;
            margin-bottom: 10px;
        }

        .CriancaCard p {
            color: #617985;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .Acoes {
            display: flex;
            gap: 8px;
            margin-top: 14px;
        }

        .Editar,
        .Remover {
            padding: 9px 13px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .Editar {
            background: #e5f5fc;
            color: #287cae;
        }

        .Remover {
            background: #fff0f0;
            color: #c75c5c;
        }

        footer {
            text-align: center;
            margin-top: 25px;
            color: #7b929f;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .CadastroCard {
                padding: 20px;
            }

            .Logo img {
                width: 135px;
            }
        }
    </style>
</head>

<body>
<div class="ContainerCadastro">

    <a href="Menu.html" class="Voltar">←</a>

    <div class="CadastroCard">

        <div class="Logo">
            <img src="imagens/logo.png" alt="Logo AquiVacina">
        </div>

        <h1>Cadastro de Crianças</h1>

        <p class="Descricao">
            Cadastre uma criança para acompanhar
            suas informações de vacinação.
        </p>

        <?php if ($mensagem !== ""): ?>
            <div class="Mensagem">
                <?php echo htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <?php if ($erro !== ""): ?>
            <div class="Erro">
                <?php echo htmlspecialchars($erro, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="cadastrocrian%C3%A7as.php">

            <input
                type="hidden"
                name="acao"
                value="<?php echo $criancaEditar ? 'editar' : 'cadastrar'; ?>"
            >

            <?php if ($criancaEditar): ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?php echo (int) $criancaEditar['id']; ?>"
                >
            <?php endif; ?>

            <label for="paciente_id">ID do paciente</label>
            <input
                type="number"
                id="paciente_id"
                name="paciente_id"
                placeholder="Digite o ID do paciente"
                value="<?php echo $valor('paciente_id'); ?>"
                required
            >

            <label for="nome">Nome da criança</label>
            <input
                type="text"
                id="nome"
                name="nome"
                placeholder="Digite o nome da criança"
                value="<?php echo $valor('nome'); ?>"
                required
            >

            <label for="cpf">CPF</label>
            <input
                type="text"
                id="cpf"
                name="cpf"
                maxlength="11"
                placeholder="Digite o CPF"
                value="<?php echo $valor('cpf'); ?>"
            >

            <label for="data_nascimento">Data de nascimento</label>
            <input
                type="date"
                id="data_nascimento"
                name="data_nascimento"
                value="<?php echo $valor('data_nascimento'); ?>"
                required
            >

            <label for="indigena_quilombola">Indígena / Quilombola</label>
            <select id="indigena_quilombola" name="indigena_quilombola">
                <option value="false" <?php echo !$indigenaSim ? 'selected' : ''; ?>>
                    Não
                </option>
                <option value="true" <?php echo $indigenaSim ? 'selected' : ''; ?>>
                    Sim
                </option>
            </select>

            <label for="comorbidades">Comorbidades</label>
            <textarea
                id="comorbidades"
                name="comorbidades"
                placeholder="Digite as comorbidades, se houver"
            ><?php echo $valor('comorbidades'); ?></textarea>

            <button type="submit" class="BotaoCadastrar">
                <?php echo $criancaEditar ? 'Salvar alterações' : 'Cadastrar criança'; ?>
            </button>

            <?php if ($criancaEditar): ?>
                <a href="cadastrocrian%C3%A7as.php"
                   style="text-align:center; margin-top:8px; color:#287cae;">
                    Cancelar edição
                </a>
            <?php endif; ?>

        </form>

        <div class="ListaCriancas">
            <h2>Crianças cadastradas</h2>

            <?php while ($crianca = pg_fetch_assoc($resultadoCriancas)): ?>

                <div class="CriancaCard">

                    <h3>
                        <?php echo htmlspecialchars($crianca["nome"] ?? "", ENT_QUOTES, "UTF-8"); ?>
                    </h3>

                    <p>
                        <strong>ID do paciente:</strong>
                        <?php echo htmlspecialchars((string) $crianca["paciente_id"], ENT_QUOTES, "UTF-8"); ?>
                    </p>

                    <p>
                        <strong>CPF:</strong>
                        <?php echo htmlspecialchars($crianca["cpf"] ?? "", ENT_QUOTES, "UTF-8"); ?>
                    </p>

                    <p>
                        <strong>Data de nascimento:</strong>
                        <?php
                        echo !empty($crianca["data_nascimento"])
                            ? date("d/m/Y", strtotime($crianca["data_nascimento"]))
                            : "";
                        ?>
                    </p>

                    <p>
                        <strong>Indígena/Quilombola:</strong>
                        <?php echo ($crianca["indigena_quilombola"] ?? "f") === "t" ? "Sim" : "Não"; ?>
                    </p>

                    <p>
                        <strong>Comorbidades:</strong>
                        <?php echo htmlspecialchars($crianca["comorbidades"] ?? "", ENT_QUOTES, "UTF-8"); ?>
                    </p>

                    <div class="Acoes">
                        <a
                            class="Editar"
                            href="cadastrocrian%C3%A7as.php?editar=<?php echo (int) $crianca["id"]; ?>"
                        >
                            Editar
                        </a>

                        <a
                            class="Remover"
                            href="cadastrocrian%C3%A7as.php?acao=remover&id=<?php echo (int) $crianca["id"]; ?>"
                            onclick="return confirm('Tem certeza que deseja remover esta criança?');"
                        >
                            Remover
                        </a>
                    </div>

                </div>

            <?php endwhile; ?>
        </div>

        <footer>AquiVacina</footer>

    </div>
</div>
</body>
</html>
