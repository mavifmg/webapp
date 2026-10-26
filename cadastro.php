<?php
session_start();
require_once "Conexao.php";
require_once "Geocodificacao.php";


// BUSCAR AS COMORBIDADES CADASTRADAS NO BANCO
$listaComorbidades = [];

$sqlComorbidades = 'SELECT "comorbidade" FROM comorbidade ORDER BY "comorbidade"';

$resultadoComorbidades = pg_query($conn, $sqlComorbidades);

if ($resultadoComorbidades !== false) {
    while ($linha = pg_fetch_assoc($resultadoComorbidades)) {
        $listaComorbidades[] = $linha["comorbidade"];
    }
}


function validarCPF($cpf)
{
    $cpf = preg_replace('/\D/', '', $cpf);

    if (strlen($cpf) !== 11) {
        return false;
    }

    // Impede sequências como 00000000000 e 11111111111
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    // Calcula o primeiro dígito verificador
    $soma = 0;

    for ($i = 0; $i < 9; $i++) {
        $soma += (int) $cpf[$i] * (10 - $i);
    }

    $resto = ($soma * 10) % 11;

    if ($resto === 10) {
        $resto = 0;
    }

    if ($resto !== (int) $cpf[9]) {
        return false;
    }

    // Calcula o segundo dígito verificador
    $soma = 0;

    for ($i = 0; $i < 10; $i++) {
        $soma += (int) $cpf[$i] * (11 - $i);
    }

    $resto = ($soma * 10) % 11;

    if ($resto === 10) {
        $resto = 0;
    }

    return $resto === (int) $cpf[10];
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $CPF = $_POST["CPF"];
    $DataNascimento = $_POST["DataNascimento"];
    $EMAIL = $_POST["EMAIL"];
    $Celular = $_POST["Celular"];
    $CEP = $_POST["CEP"];
    $NumCasa = $_POST["NumCasa"];
    $Indigena = isset($_POST["Indigena"]) ? 'true' : 'false';
    // RECEBER AS COMORBIDADES SELECIONADAS
    $ComorbidadesSelecionadas = $_POST["Comorbidades"] ?? [];

    // Garantir que o valor recebido seja um array
    if (!is_array($ComorbidadesSelecionadas)) {
        $ComorbidadesSelecionadas = [];
    }

    // Aceitar somente comorbidades existentes no banco de dados
    $ComorbidadesSelecionadas = array_values(
        array_intersect(
            $ComorbidadesSelecionadas,
            $listaComorbidades
        )
    );

    // Transformar as opções selecionadas em uma única string
    $Comorbidades = implode(", ", $ComorbidadesSelecionadas);
    $Genero = isset($_POST["Genero"]) ? $_POST["Genero"] : null;
    $Autorizacao_Loc = isset($_POST["Autorizacao_Loc"]) ? 'true' : 'false';

    $coordenadas = obterCoordenadas($CEP, $NumCasa);


    if (
        empty($CPF) ||
        empty($DataNascimento) ||
        empty($EMAIL) ||
        empty($Celular) ||
        empty($CEP) ||
        empty($NumCasa) ||
        empty($Comorbidades) ||
        empty($Genero) ||

        !isset($_POST["Autorizacao_Loc"])
    ) {
        $_SESSION['erro_cadastro'] = "Preencha todos os campos obrigatórios e aceite a autorização local para prosseguir com o cadastro.";
        header("Location: Cadastro.php");
        exit();
    }

    if (!validarCPF($CPF)) {
        $_SESSION['erro_cadastro'] =
            "CPF inválido. Verifique os números digitados.";

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


    $sql = "INSERT INTO cadastro (\"CPF\", \"DataNascimento\", \"EMAIL\", \"Celular\",\"CEP\",\"NumCasa\", \"Indigena\", \"Comorbidades\", \"Autorizacao_Loc\", \"LatitudeUser\", \"LongitudeUser\", \"Genero\")
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)";

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF, $DataNascimento, $EMAIL, $Celular, $CEP, $NumCasa, $Indigena, $Comorbidades, $Autorizacao_Loc, $LatitudeUser, $LongitudeUser, $Genero]
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
    <link rel="stylesheet" href="AquiVacina.css?v=<?php echo time(); ?>">
    <script src="https://kit.fontawesome.com/cc9bf1d657.js" crossorigin="anonymous"></script>

</head>

<body class="Cadastro">

    <div class="LocDadosUser">
        <div class="LocMenu">
            <div class="FundoLog">
                <?php include "Logo.php"; ?>
                <?php
                if (isset($_SESSION['erro_cadastro'])) {
                    echo '<p class="MensagemTopo">'
                        . htmlspecialchars($_SESSION['erro_cadastro'])
                        . '</p>';

                    unset($_SESSION['erro_cadastro']);
                }
                ?>

                <br>

                <h1>Bem-vindo ao AquiVacina!</h1>
                <br>
                <h2>Tela de Cadastro</h2>

                <form action="Cadastro.php" method="POST">

                    <label id="cpf">CPF</label>
                    <input id="CPF" placeholder="Digite seu CPF" type="text" name="CPF" maxlength="14"
                        inputmode="numeric" autocomplete="off" required>

                    <p id="erroCPF" class="text-danger" hidden></p>

                    <label id="senha">Data de Nascimento</label>
                    <input placeholder="Digite sua data de nascimento" type="date" name="DataNascimento">

                    <p id="erroEmail" class="text-danger" hidden></p>

                    <label id="email">Email</label>
                    <input id="EMAIL" placeholder="Digite seu e-mail" type="email" name="EMAIL" autocomplete="email"
                        required>

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

                    <!-- COMORBIDADES -->
                    <div class="CampoComorbidades">

                        <label id="comorbidades">Comorbidades:</label>

                        <div class="ListaComorbidades">

                            <?php if (!empty($listaComorbidades)): ?>

                                <?php foreach ($listaComorbidades as $comorbidade): ?>

                                    <label class="CheckboxComorbidade">

                                        <input type="checkbox" name="Comorbidades[]"
                                            value="<?php echo htmlspecialchars($comorbidade, ENT_QUOTES, 'UTF-8'); ?>">

                                        <span>
                                            <?php echo htmlspecialchars($comorbidade, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>

                                    </label>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <p>Nenhuma comorbidade cadastrada no momento.</p>

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="CampoGenero">
                        <label id="genero">Gêneros:</label>

                        <label>Masculino
                            <input type="radio" name="Genero" value="Masculino">
                        </label>

                        <label>Feminino
                            <input type="radio" name="Genero" value="Feminino">
                        </label>

                        <label>Outro
                            <input type="radio" name="Genero" value="Outro">
                        </label>
                    </div>

                    <label id="autorizacao">
                        <input type="checkbox" name="Autorizacao_Loc" value="true">
                        Autorizo meu local atual
                    </label>

                    <label id="indigena">
                        <input type="checkbox" name="Indigena" value="true">
                        Sou indígena
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
    </div>
</body>


<script>
    const campoCPF = document.getElementById("CPF");
    const campoData = document.getElementById("DataNascimento");
    const erroCPF = document.getElementById("erroCPF");

    let cpfVerificado = "";
    let cpfEmVerificacao = false;
    let tentativaVerificacao = 0;

    async function verificarCPFNoBanco() {
        const cpf = campoCPF.value.replace(/\D/g, "");

        // Não consulta se o campo estiver vazio
        if (cpf === "") {
            erroCPF.hidden = true;
            cpfVerificado = "";
            return false;
        }

        tentativaVerificacao++;
        const tentativaAtual = tentativaVerificacao;

        cpfEmVerificacao = true;
        cpfVerificado = "";
        campoCPF.setAttribute("aria-invalid", "true");

        erroCPF.textContent = "Verificando CPF...";
        erroCPF.hidden = false;

        try {
            const resposta = await fetch("VerificarCPF.php", {
                method: "POST",
                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({ CPF: cpf })
            });

            const dados = await resposta.json();

            // Ignora respostas antigas se o usuário alterou o CPF
            if (tentativaAtual !== tentativaVerificacao) {
                return false;
            }

            if (!resposta.ok || dados.erro) {
                erroCPF.textContent =
                    dados.erro || "Não foi possível verificar o CPF.";
                erroCPF.hidden = false;
                return false;
            }

            if (!dados.valido || dados.cadastrado) {
                erroCPF.textContent = dados.mensagem;
                erroCPF.hidden = false;
                return false;
            }

            erroCPF.textContent = dados.mensagem;
            erroCPF.hidden = false;
            erroCPF.classList.remove("text-danger");
            erroCPF.classList.add("text-success");

            campoCPF.removeAttribute("aria-invalid");
            cpfVerificado = cpf;

            return true;

        } catch (erro) {
            if (tentativaAtual === tentativaVerificacao) {
                erroCPF.textContent =
                    "Erro de conexão ao verificar o CPF. Tente novamente.";
                erroCPF.hidden = false;
            }

            return false;

        } finally {
            if (tentativaAtual === tentativaVerificacao) {
                cpfEmVerificacao = false;
            }
        }
    }

    function mostrarErro(mensagem) {
        erroCPF.textContent = mensagem;
        erroCPF.hidden = false;
        erroCPF.classList.remove("text-success");
        erroCPF.classList.add("text-danger");
        campoCPF.setAttribute("aria-invalid", "true");
    }

    campoCPF.addEventListener("blur", async function () {
        erroCPF.classList.remove("text-success");
        erroCPF.classList.add("text-danger");
        await verificarCPFNoBanco();
    });

    campoCPF.addEventListener("input", function () {
        // Obriga a verificar novamente se o CPF for alterado
        cpfVerificado = "";
        tentativaVerificacao++;
        cpfEmVerificacao = false;

        erroCPF.hidden = true;
        campoCPF.removeAttribute("aria-invalid");
        erroCPF.classList.remove("text-success");
        erroCPF.classList.add("text-danger");
    });

    campoData.addEventListener("focus", async function (evento) {
        const cpf = campoCPF.value.replace(/\D/g, "");

        if (cpfEmVerificacao) {
            evento.preventDefault();
            campoCPF.focus();
            return;
        }

        if (cpfVerificado !== cpf) {
            evento.preventDefault();

            const valido = await verificarCPFNoBanco();

            if (valido && document.activeElement === campoCPF) {
                campoData.focus();
            } else {
                campoCPF.focus();
            }
        }
    });
</script>
<script>
    const campoEmail = document.getElementById("EMAIL");
    const erroEmail = document.getElementById("erroEmail");

    function validarFormatoEmail() {
        const email = campoEmail.value.trim();

        if (
            email === "" ||
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
        ) {
            erroEmail.textContent =
                "Digite um endereço de e-mail válido.";
            erroEmail.hidden = false;
            campoEmail.setAttribute("aria-invalid", "true");
            return false;
        }

        erroEmail.hidden = true;
        campoEmail.removeAttribute("aria-invalid");
        return true;
    }

    campoEmail.addEventListener("blur", validarFormatoEmail);
</script>


</html>