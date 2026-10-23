
<?php

session_start();

require_once "Conexao.php";

$comorbidades = pg_query(
    $conn,
    "SELECT comodidade FROM comorbidade ORDER BY comodidade"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["cadastrar"])) {

        $TipoAlerta = $_POST["TipoAlerta"] ?? "";
        $Mensagem = $_POST["Mensagem"] ?? "";
        $IdadeInicial = $_POST["IdadeInicial"] ?? "";
        $IdadeFinal = $_POST["IdadeFinal"] ?? "";
        $Tipopessoa = $_POST["Tipopessoa"] ?? "";
        $Comodidade = $_POST["Comodidade"] ?? "";
        $Modo = $_POST["modo"] ?? "novo";
        $TipoAlertaAntigo = $_POST["tipoalertaantigo"] ?? "";

        if (
            empty($TipoAlerta) ||
            empty($Mensagem) ||
            $IdadeInicial === "" ||
            $IdadeFinal === "" ||
            empty($Tipopessoa) ||
            empty($Comodidade)
        ) {

            $_SESSION["erro_alerta"] =
                "Campos vazios, preencha todos";

            header("Location: Alertas.php");
            exit();
        }

        if (
            !is_numeric($IdadeInicial) ||
            !is_numeric($IdadeFinal) ||
            $IdadeInicial < 0 ||
            $IdadeFinal < $IdadeInicial
        ) {
            $_SESSION["erro_alerta"] = "Confira as idades informadas!";
            header("Location: Alertas.php");
            exit();
        }

        if ($Modo === "editar" && !empty($TipoAlertaAntigo)) {

            $sql = "
                UPDATE alertas
                SET tipoalerta = $1,
                    mensagem = $2,
                    idadeinicial = $3,
                    idadefinal = $4,
                    tipopessoa = $5,
                    comodidade = $6
                WHERE tipoalerta = $7
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [
                    $TipoAlerta,
                    $Mensagem,
                    $IdadeInicial,
                    $IdadeFinal,
                    $Tipopessoa,
                    $Comodidade,
                    $TipoAlertaAntigo
                ]
            );

            if ($resultado) {
                $_SESSION["cadastro_alerta"] = "Alerta editado";
            } else {
                $_SESSION["erro_alerta"] = "Erro ao editar alerta";
            }

        } else {

            $sql = "
                INSERT INTO alertas
                (tipoalerta, mensagem, tipopessoa, idadeinicial, idadefinal, comodidade)
                VALUES ($1, $2, $3, $4, $5, $6)
            ";

            $resultado = pg_query_params(
                $conn,
                $sql,
                [
                    $TipoAlerta,
                    $Mensagem,
                    $IdadeInicial,
                    $IdadeFinal,
                    $Tipopessoa,
                    $Comodidade
                ]
            );

            if ($resultado) {
                $_SESSION["cadastro_alerta"] = "Alerta cadastrado";
            } else {
                $_SESSION["erro_alerta"] = "Erro ao cadastrar alerta";
            }
        }

        header("Location: Alertas.php");
        exit();
    }

    if (isset($_POST["excluir"])) {

        $TipoAlerta = $_POST["inputexcluir"] ?? "";

        if (empty($TipoAlerta)) {
            $_SESSION["erro_alerta"] = "Selecione um alerta";
            header("Location: Alertas.php");
            exit();
        }

        $sql = "
            DELETE FROM alertas
            WHERE tipoalerta = $1
        ";

        $resultado = pg_query_params(
            $conn,
            $sql,
            [$TipoAlerta]
        );

        if ($resultado) {
            $_SESSION["cadastro_alerta"] = "Alerta excluído";
        } else {
            $_SESSION["erro_alerta"] = "Erro ao excluir alerta";
        }

        header("Location: Alertas.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>AquiVacina | Alertas</title>

    <link
        rel="stylesheet"
        href="AquiVacina2.css"
    >

    <script
        src="https://kit.fontawesome.com/cc9bf1d657.js"
        crossorigin="anonymous">
    </script>

</head>

<body class="EstoqueUbs">

    <header class="CabecalhoEstoque">

        <img
            src="Imagens/LogoAquiVacina.png"
            alt="AquiVacina"
            class="LogoEstoque"
        >

    </header>

    <div class="IconEstUbs">

        <i id="Perfil" class="fa-solid fa-user"></i>

        <a href="Menu.html">
            <button id="Home">
                <i class="fa-regular fa-house"></i>
            </button>
        </a>

        <i
            id="Burger"
            class="fa-solid fa-bars"
            onclick="clickMenu()"
        >
            Menu
        </i>

        <menu id="Itens">

            <ul>
                <a href="Informacao.html">Informações</a>
                <a href="ComoUsar.html">Como Usar</a>
                <a href="Publico.php">Cadastrar público</a>
                <a href="Alertas.php">Alertas</a>
                <a href="Estoque.php">Estoque da UBS</a>
                <a href="Registrar.html">Registrar aplicações</a>
                <a href="CadastroUBS.php">Cadastrar UBS</a>
            </ul>

        </menu>

    </div>

    <div class="estoque-container">

        <div class="estoque-quadro quadro-cadastro">

            <div class="LocEstUbs">

                <div class="FundoTipo1">

                    <form
                        action="Alertas.php"
                        method="POST"
                        id="formEstoque"
                    >

                        <div id="titulo1">Tipo de alerta</div>

                        <input
                            placeholder="Digite o tipo de alerta"
                            type="text"
                            name="TipoAlerta"
                            id="TipoAlerta"
                            required
                        >

                        <div id="titulo2">Mensagem</div>

                        <textarea
                            placeholder="Digite a mensagem do alerta"
                            name="Mensagem"
                            id="Mensagem"
                            required
                        ></textarea>

                        <div id="titulo3">Público alvo</div>

                        <input
                            placeholder="Digite para quem é o alerta"
                            type="text"
                            name="Tipopessoa"
                            id="Tipopessoa"
                            required
                        >

                        <div id="titulo5">Idade inicial</div>

                        <input
                            placeholder="Digite"
                            type="text"
                            name="Idadei"
                            id="Idadei"
                            required
                        >

                        <div id="titulo6">Idade Final</div>

                        <input
                            placeholder="Digite"
                            type="text"
                            name="Idadef"
                            id="Idadef"
                            required
                        >

                        <div id="titulo4">Comorbidade</div>

                        <select
                            name="Comodidade"
                            id="Comodidade"
                            required
                        >

                            <option value="">
                                Selecione a comorbidade
                            </option>

                            <?php

                            if ($comorbidades) {

                                while (
                                    $comorbidade = pg_fetch_assoc($comorbidades)
                                ) {

                                    $nomeComorbidade = htmlspecialchars(
                                        $comorbidade["comodidade"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    echo "
                                        <option value='$nomeComorbidade'>
                                            $nomeComorbidade
                                        </option>
                                    ";
                                }
                            }

                            ?>

                        </select>

                        <input
                            type="hidden"
                            name="modo"
                            id="modo"
                            value="novo"
                        >

                        <input
                            type="hidden"
                            name="tipoalertaantigo"
                            id="tipoalertaantigo"
                        >

                        <?php

                        if (isset($_SESSION["cadastro_alerta"])) {

                            echo
                                "<p class='text-sucess'>" .
                                htmlspecialchars(
                                    $_SESSION["cadastro_alerta"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) .
                                "</p>";

                            unset($_SESSION["cadastro_alerta"]);
                        }

                        if (isset($_SESSION["erro_alerta"])) {

                            echo
                                "<p class='text-danger'>" .
                                htmlspecialchars(
                                    $_SESSION["erro_alerta"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) .
                                "</p>";

                            unset($_SESSION["erro_alerta"]);
                        }

                        ?>

                        <button
                            type="submit"
                            name="cadastrar"
                            id="BEnviar"
                        >
                            Enviar
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="estoque-quadro quadro-tabela">

            <div class="TabelaEstoque">

                <table>

                    <thead>

                        <tr>

                            <th class="LetraCad1">
                                Tipo de alerta
                            </th>

                            <th class="LetraCad2">
                                Mensagem
                            </th>

                            <th class="LetraCad3">
                                Público
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $sql = "
                            SELECT tipoalerta, mensagem, idadeinicial, idadefinal, tipopessoa, comodidade
                            FROM alertas
                            ORDER BY tipoalerta
                        ";

                        $query = pg_query($conn, $sql);

                        if ($query) {

                            while ($resultado = pg_fetch_assoc($query)) {

                                $TipoAlerta_HTML = htmlspecialchars(
                                    $resultado["tipoalerta"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                $Mensagem_HTML = htmlspecialchars(
                                    $resultado["mensagem"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                $IdadeInicial_HTML = htmlspecialchars(
                                    $resultado["idadeinicial"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                
                                $IdadeFinal_HTML = htmlspecialchars(
                                    $resultado["idadefinal"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                $Tipopessoa_HTML = htmlspecialchars(
                                    $resultado["tipopessoa"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                $Comodidade_HTML = htmlspecialchars(
                                    $resultado["comodidade"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                $Publico_HTML = $Tipopessoa_HTML .
                                    " - " . $IdadeInicial_HTML .
                                    " a " . $IdadeFinal_HTML .
                                    " anos - " . $Comodidade_HTML;

                                echo "
                                    <tr
                                        class='linhaEstoque'
                                        data-tipoalerta='$TipoAlerta_HTML'
                                        data-mensagem='$Mensagem_HTML'
                                        data-comodidade='$Comodidade_HTML'
                                    >
                                ";

                                echo "<td>" . $TipoAlerta_HTML . "
                                    <button
                                        type='button'
                                        class='BotaoExcluirEstoque'
                                        onclick='excluirAlerta(this, event)'
                                    >X</button>
                                </td>";

                                echo "<td>" . $Mensagem_HTML . "</td>";

                                echo "<td>" . $Publico_HTML . "</td>";

                                echo "</tr>";
                            }

                        } else {

                            echo "<tr><td colspan='3'>Erro ao consultar os alertas.</td></tr>";
                        }

                        ?>

                    </tbody>

                </table>

            </div>

            <form action="Alertas.php" method="POST">

                <input
                    type="hidden"
                    name="inputexcluir"
                    id="inputexcluir"
                >

                <button
                    type="submit"
                    id="BEst2"
                    name="excluir"
                    style="display: none;"
                >
                    Excluir
                </button>

            </form>

        </div>

    </div>

    <?php
    pg_close($conn);
    ?>

    <script src="AquiVacina.js"></script>

    <script>

        function excluirAlerta(botao, event) {

            event.stopPropagation();

            const linha = botao.closest(".linhaEstoque");

            const tipoAlerta = linha.getAttribute("data-tipoalerta");

            const confirmar = confirm(
                "Tem certeza que deseja excluir o alerta " +
                tipoAlerta + "?"
            );

            if (confirmar) {

                document.getElementById("inputexcluir").value = tipoAlerta;

                document.getElementById("BEst2").click();
            }
        }

        document.addEventListener("DOMContentLoaded", function () {

            const linhas = document.querySelectorAll(".linhaEstoque");

            const inputTipoAlerta = document.getElementById("TipoAlerta");
            const inputMensagem = document.getElementById("Mensagem");
            const inputComodidade = document.getElementById("Comodidade");
            const inputTipoAntigo = document.getElementById("tipoalertaantigo");
            const inputModo = document.getElementById("modo");
            const botaoEnviar = document.getElementById("BEnviar");

            linhas.forEach(function (linha) {

                linha.addEventListener("click", function () {

                    linhas.forEach(function (item) {
                        item.classList.remove("selecionada");
                    });

                    linha.classList.add("selecionada");

                    const tipoAlerta =
                        linha.getAttribute("data-tipoalerta");

                    const mensagem =
                        linha.getAttribute("data-mensagem");

                    const comodidade =
                        linha.getAttribute("data-comodidade");

                    inputTipoAlerta.value = tipoAlerta || "";
                    inputMensagem.value = mensagem || "";
                    inputComodidade.value = comodidade || "";

                    inputTipoAntigo.value = tipoAlerta || "";
                    inputModo.value = "editar";

                    botaoEnviar.textContent = "Salvar alteração";
                });
            });
        });

    </script>

</body>
</html>
