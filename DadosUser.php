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

// ======================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO
// ======================================================

if (!isset($_SESSION['CPF'])) {
    header("Location: Login.php");
    exit();
}

$CPF = $_SESSION['CPF'];

// ======================================================
// EDITAR DADOS
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editar"])) {

    // CPF antigo, usado no WHERE
    $CPF_antigo = $_SESSION['CPF'];

    $CPF = $_POST["CPF"] ?? "";
    $DataNascimento = $_POST["DataNascimento"] ?? "";
    $EMAIL = $_POST["EMAIL"] ?? "";
    $Celular = $_POST["Celular"] ?? "";
    $CEP = $_POST["CEP"] ?? "";
    $NumCasa = $_POST["NumCasa"] ?? "";

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

    $Genero = $_POST["Genero"] ?? "";
    $Indigena = isset($_POST["Indigena"]) ? 'true' : 'false';

    $coordenadas = obterCoordenadas($CEP, $NumCasa);

    // ==================================================
    // VERIFICAR CAMPOS OBRIGATÓRIOS
    // ==================================================

    if (
        empty($CPF) ||
        empty($DataNascimento) ||
        empty($EMAIL) ||
        empty($Celular) ||
        empty($CEP) ||
        empty($NumCasa) ||
        empty($Comorbidades) ||
        empty($Genero)
    ) {
        $_SESSION["erro_editar"] =
            "Preencha todos os campos obrigatórios.";

        header("Location: DadosUser.php");
        exit();
    }

    if (!validarCPF($CPF)) {
        $_SESSION['erro_editar'] =
            "CPF inválido. Verifique os números digitados.";

        header("Location: DadosUser.php");
        exit();
    }

    if ($coordenadas === false) {
        $_SESSION['erro_editar'] = "Não foi possível obter as coordenadas do endereço fornecido. Por favor, verifique o endereço e tente novamente.";

        header("Location: DadosUser.php");
        exit();
    }

    $LatitudeUser = $coordenadas["latitude"];
    $LongitudeUser = $coordenadas["longitude"];

    // PARTE 4: MOSTRAR AS COORDENADAS OBTIDAS PELA API
    // echo "Endereço: " . htmlspecialchars($CEP) . ", " . htmlspecialchars($NumCasa) . "<br>";
    // echo "Latitude: " . htmlspecialchars($LatitudeUser) . "<br>";
    // echo "Longitude: " . htmlspecialchars($LongitudeUser) . "<br>";

    // exit();

    // ==================================================
    // ATUALIZAR NO BANCO
    // ==================================================

    $sql = 'UPDATE cadastro
            SET "CPF" = $1,
                "DataNascimento" = $2,
                "EMAIL" = $3,
                "Celular" = $4,
                "CEP" = $5,
                "NumCasa" = $6,
                "Indigena" = $7,
                "Comorbidades" = $8,
                "Genero" = $9,
                "LatitudeUser" = $10,
                "LongitudeUser" = $11
            WHERE "CPF" = $12
              AND "DataNascimento" = $2';

    $resultado = pg_query_params(
        $conn,
        $sql,
        [
            $CPF,
            $DataNascimento,
            $EMAIL,
            $Celular,
            $CEP,
            $NumCasa,
            $Indigena,
            $Comorbidades,
            $Genero,
            $LatitudeUser,
            $LongitudeUser,
            $CPF_antigo
        ]
    );

    // ==================================================
    // VERIFICAR RESULTADO
    // ==================================================

    if ($resultado !== false) {

        // Atualizar CPF da sessão
        $_SESSION['CPF'] = $CPF;

        $_SESSION["sucesso_editar"] =
            "Dados atualizados com sucesso!";

    } else {

        $_SESSION["erro_editar"] =
            "Erro ao atualizar os dados: " .
            pg_last_error($conn);
    }

    header("Location: DadosUser.php");
    exit();
}

// ======================================================
// EXCLUIR CONTA
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir"])) {

    $sql = 'SELECT "CPF"
            FROM cadastro
            WHERE "CPF" = $1';

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF]
    );

    if (
        $resultado === false ||
        pg_num_rows($resultado) === 0
    ) {
        $_SESSION["erro_usuario"] =
            "Usuário não encontrado.";

        header("Location: DadosUser.php");
        exit();
    }

    // Excluir usuário
    $sql = 'DELETE FROM cadastro
            WHERE "CPF" = $1';

    $resultado = pg_query_params(
        $conn,
        $sql,
        [$CPF]
    );

    if ($resultado !== false) {

        session_destroy();

        header("Location: Cadastro.php");
        exit();

    } else {

        $_SESSION["erro_usuario"] =
            "Erro ao excluir usuário: " .
            pg_last_error($conn);

        header("Location: DadosUser.php");
        exit();
    }
}

// ======================================================
// BUSCAR DADOS DO USUÁRIO
// ======================================================

$sql = 'SELECT
            "CPF",
            "DataNascimento",
            "EMAIL",
            "Celular",
            "CEP",
            "NumCasa",
            "Indigena",
            "Comorbidades",
            "Genero",
            "Autorizacao_Loc",
            "LatitudeUser",
            "LongitudeUser"
        FROM cadastro
        WHERE "CPF" = $1';

$result = pg_query_params(
    $conn,
    $sql,
    [$CPF]
);

if ($result === false) {
    die(
        "Erro na consulta: " .
        pg_last_error($conn)
    );
}

$usuario = pg_fetch_assoc($result);

if (!$usuario) {
    die("Usuário não encontrado.");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AquiVacina | Dados do Usuário</title>

    <link rel="stylesheet" href="AquiVacina.css">
</head>

<body class="Menu">

    <div class="LocDadosUser">
        <div class="LocMenu">
            <div class="FundoLog">

                <?php include "Logo.php"; ?>

                <br>

                <div class="DadosUser">

                    <a href="Menu.php">
                        <a href="Menu.php" class="Voltar">
                            ←
                        </a>
                    </a>

                    <h1>
                        Dados do CPF:
                        <?php
                        echo htmlspecialchars($usuario["CPF"]);
                        ?>
                    </h1>

                    <div class="Dados">

                        <p>
                            <strong>Data de Nascimento:</strong>

                            <?php
                            echo htmlspecialchars(
                                $usuario["DataNascimento"]
                            );
                            ?>
                        </p>

                        <p>
                            <strong>Email:</strong>

                            <?php
                            echo htmlspecialchars(
                                $usuario["EMAIL"]
                            );
                            ?>
                        </p>

                        <p>
                            <strong>Celular:</strong>

                            <?php
                            echo htmlspecialchars(
                                $usuario["Celular"]
                            );
                            ?>
                        </p>

                        <p>
                            <strong>Endereço:</strong>

                            CEP:
                            <?= htmlspecialchars($usuario['CEP'] ?? '') ?>

                            Num:
                            <?= htmlspecialchars($usuario['NumCasa'] ?? '') ?>
                        </p>

                        </p>

                        <p>
                            <strong>Gênero:</strong>

                            <?php
                            echo htmlspecialchars(
                                $usuario["Genero"]
                            );
                            ?>

                        <p>
                            <strong>Comorbidades:</strong>

                            <?php
                            echo htmlspecialchars(
                                $usuario["Comorbidades"]
                            );
                            ?>
                        </p>

                        <p>
                            <strong>Indígena:</strong>

                            <?php
                            echo $usuario["Indigena"] === "t"
                                ? "Sim"
                                : "Não";
                            ?>
                        </p>

                        <p>
                            <strong>Autorização Local:</strong>

                            <?php
                            echo $usuario["Autorizacao_Loc"] === "t"
                                ? "Sim"
                                : "Não";
                            ?>
                        </p>

                    </div>
                </div>

                <!-- ==================================================
                     FORMULÁRIO DE EDIÇÃO
                =================================================== -->

                <div class="DadosUsuario">

                    <h1>Dados do Usuário</h1>

                    <form action="DadosUser.php" method="POST" id="formUsuario">

                        <!-- CPF -->
                        <label for="CPF">CPF</label>

                        <input
                            type="text"
                            name="CPF"
                            id="CPF"
                            value="<?php echo htmlspecialchars($usuario["CPF"]); ?>"
                            readonly
                        >

                        <!-- DATA DE NASCIMENTO -->
                        <label for="DataNascimento">Data de Nascimento</label>

                        <input
                            type="date"
                            name="DataNascimento"
                            id="DataNascimento"
                            value="<?php echo htmlspecialchars($usuario["DataNascimento"]); ?>"
                            readonly
                        >

                        <!-- EMAIL -->
                        <label for="EMAIL">Email</label>

                        <input
                            type="email"
                            name="EMAIL"
                            id="EMAIL"
                            value="<?php echo htmlspecialchars($usuario["EMAIL"]); ?>"
                            readonly
                        >

                        <!-- CELULAR -->
                        <label for="Celular">Celular</label>

                        <input
                            type="text"
                            name="Celular"
                            id="Celular"
                            value="<?php echo htmlspecialchars($usuario["Celular"]); ?>"
                            readonly
                        >

                        <!-- ENDEREÇO -->
                        <label for="CEP">CEP</label>

                        <input
                            type="text"
                            name="CEP"
                            id="CEP"
                            value="<?php echo htmlspecialchars($usuario["CEP"]); ?>"
                            readonly
                        >

                        <label for="numcasa">Número da Casa</label>

                        <input
                            type="text"
                            name="NumCasa"
                            id="NumCasa"
                            value="<?php echo htmlspecialchars($usuario["NumCasa"]); ?>"
                            readonly
                        >

                        <!-- COMORBIDADES -->
                        <div class="CampoComorbidades">

                            <label id="comorbidades">Comorbidades:</label>

                            <div class="ListaComorbidades">

                                <?php if (!empty($listaComorbidades)): ?>

                                    <?php foreach ($listaComorbidades as $comorbidade): ?>

                                        <label class="CheckboxComorbidade">

                                            <input
                                                type="checkbox"
                                                name="Comorbidades[]"
                                                value="<?php echo htmlspecialchars($comorbidade, ENT_QUOTES, 'UTF-8'); ?>"
                                                <?php echo in_array($comorbidade, explode(", ", $usuario["Comorbidades"])) ? "checked" : ""; ?>
                                                disabled
                                            >

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

                        <!-- GÊNERO -->
                        <div class="CampoGenero">

                            <label id="genero">Gênero:</label><br>

                            <label>
                                Masculino

                                <input
                                    type="radio"
                                    name="Genero"
                                    value="Masculino"
                                    <?php echo ($usuario["Genero"] === "Masculino") ? "checked" : ""; ?>
                                    disabled
                                >
                            </label>

                            <label>
                                Feminino

                                <input
                                    type="radio"
                                    name="Genero"
                                    value="Feminino"
                                    <?php echo ($usuario["Genero"] === "Feminino") ? "checked" : ""; ?>
                                    disabled
                                >
                            </label>

                            <label>
                                Outro

                                <input
                                    type="radio"
                                    name="Genero"
                                    value="Outro"
                                    <?php echo ($usuario["Genero"] === "Outro") ? "checked" : ""; ?>
                                    disabled
                                >
                            </label>

                        </div>

                </div>

                <!-- INDÍGENA -->
                <label id="indigena">

                    <input
                        type="checkbox"
                        name="Indigena"
                        id="Indigena"
                        value="true"
                        <?php
                        if ($usuario["Indigena"] === "t") {
                            echo "checked";
                        }
                        ?>
                        disabled
                    >

                    Sou indígena

                </label>

                <br>
                <br>

                <!-- MENSAGEM DE ERRO -->
                <?php
                if (isset($_SESSION["erro_editar"])) {
                    echo "<p class='text-danger'>" .
                        htmlspecialchars(
                            $_SESSION["erro_editar"]
                        ) .
                        "</p>";

                    unset($_SESSION["erro_editar"]);
                }
                ?>

                <!-- MENSAGEM DE SUCESSO -->
                <?php
                if (isset($_SESSION["sucesso_editar"])) {
                    echo "<p class='text-success'>" .
                        htmlspecialchars(
                            $_SESSION["sucesso_editar"]
                        ) .
                        "</p>";

                    unset($_SESSION["sucesso_editar"]);
                }
                ?>

                <!-- BOTÃO EDITAR -->
                <button
                    class="BotaoCadastrar"
                    type="button"
                    id="BEditar"
                >
                    Editar
                </button>

                <!-- BOTÃO SALVAR -->
                <button
                    class="BotaoCadastrar"
                    type="submit"
                    name="editar"
                    id="BSalvar"
                    style="display: none;"
                >
                    Salvar alteração
                </button>

                </form>

                <!-- ==================================================
                     FORMULÁRIO DE EXCLUSÃO
                =================================================== -->

                <form
                    action="DadosUser.php"
                    method="POST"
                    onsubmit="return confirm('Tem certeza que deseja excluir sua conta?');"
                >

                    <button
                        class="BotaoCadastrar"
                        type="submit"
                        name="excluir"
                    >
                        Excluir conta
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>

<!-- ======================================================
     JAVASCRIPT DO BOTÃO EDITAR
======================================================= -->

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const botaoEditar =
            document.getElementById("BEditar");

        const botaoSalvar =
            document.getElementById("BSalvar");

        const campos = [
            document.getElementById("CPF"),
            document.getElementById("DataNascimento"),
            document.getElementById("EMAIL"),
            document.getElementById("Celular"),
            document.getElementById("CEP"),
            document.getElementById("NumCasa"),

            /*document.getElementById("Comorbidades"),*/

            document.getElementById("Indigena")
        ];

        const checkboxesComorbidades = document.querySelectorAll(
            'input[name="Comorbidades[]"]'
        );

        const camposGenero = document.querySelectorAll(
            'input[name="Genero"]'
        );

        botaoEditar.addEventListener("click", function () {

            campos.forEach(function (campo) {
                if (campo) {
                    campo.removeAttribute("readonly");
                    campo.disabled = false;
                }
            });

            // Habilitar as comorbidades
            checkboxesComorbidades.forEach(function (checkbox) {
                checkbox.disabled = false;
            });

            // Habilitar as opções de gênero
            camposGenero.forEach(function (radio) {
                radio.disabled = false;
            });

            botaoSalvar.style.display = "inline-block";
            botaoEditar.style.display = "none";

        });

    });
</script>

<script>
    let cpfVerificado = "";
    let cpfEmVerificacao = false;
    let tentativaVerificacao = 0;

    /*
    const campoCPF = document.getElementById("CPF");
    const erroCPF = document.getElementById("erroCPF");
    const campoData = document.getElementById("DataNascimento");
    */

    const botaoEditar = document.getElementById("BEditar");
    const botaoSalvar = document.getElementById("BSalvar");

    function mostrarResultadoCPF(mensagem, valido) {
        erroCPF.textContent = mensagem;
        erroCPF.hidden = false;

        erroCPF.classList.toggle("text-success", valido);
        erroCPF.classList.toggle("text-danger", !valido);

        campoCPF.setAttribute("aria-invalid", String(!valido));
    }

    async function verificarCPFNoBanco() {
        const cpf = campoCPF.value.replace(/\D/g, "");
        const tentativaAtual = ++tentativaVerificacao;

        cpfVerificado = "";

        if (!cpf) {
            mostrarResultadoCPF("Digite o CPF.", false);
            return false;
        }

        mostrarResultadoCPF("Verificando CPF...", false);

        try {
            const resposta = await fetch("VerificarCPF.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({ CPF: cpf })
            });

            const dados = await resposta.json();

            // Ignora respostas de verificações anteriores
            if (tentativaAtual !== tentativaVerificacao) {
                return false;
            }

            if (!resposta.ok || dados.erro) {
                mostrarResultadoCPF(
                    dados.erro || "Não foi possível verificar o CPF.",
                    false
                );

                return false;
            }

            if (!dados.valido || dados.cadastrado) {
                mostrarResultadoCPF(
                    dados.mensagem || "CPF inválido ou já cadastrado.",
                    false
                );

                return false;
            }

            cpfVerificado = cpf;

            mostrarResultadoCPF(
                dados.mensagem || "CPF válido e disponível!",
                true
            );

            return true;

        } catch (erro) {
            if (tentativaAtual === tentativaVerificacao) {
                mostrarResultadoCPF(
                    "Erro de conexão ao verificar o CPF.",
                    false
                );
            }

            return false;
        }
    }

    // Verifica quando o usuário sai do campo CPF
    campoCPF.addEventListener("blur", verificarCPFNoBanco);

    // Se o CPF mudar, invalida a verificação anterior
    campoCPF.addEventListener("input", function () {
        tentativaVerificacao++;
        cpfVerificado = "";
        erroCPF.hidden = true;
        campoCPF.removeAttribute("aria-invalid");
    });

    // Confere novamente antes de salvar
    document.getElementById("formUsuario").addEventListener(
        "submit",
        async function (evento) {

            const cpf = campoCPF.value.replace(/\D/g, "");

            if (cpfVerificado !== cpf) {
                evento.preventDefault();

                const valido = await verificarCPFNoBanco();

                if (valido) {
                    this.submit();
                }
            }
        }
    );

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

</body>
</html>
