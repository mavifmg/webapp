
<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$host = "10.90.24.54";
$port = "5432";
$dbname = "AquiVacina";
$user = "aula";
$password = "aula";

$conn = @pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);

function responderErro($mensagem, $json = false) {
    http_response_code(500);

    if ($json) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(
            ["erro" => $mensagem],
            JSON_UNESCAPED_UNICODE
        );
    } else {
        echo "<h2>Erro no AquiVacina</h2>";
        echo "<p>" . htmlspecialchars(
            $mensagem,
            ENT_QUOTES,
            "UTF-8"
        ) . "</p>";
    }

    exit;
}

/* Mostra um erro quando a conexão falhar */
if (!$conn) {
    $mensagem = error_get_last();

    $detalhe = $mensagem["message"] ?? "Verifique o servidor, a rede e as credenciais do banco.";

    if (isset($_GET["atualizar"])) {
        responderErro(
            "Falha na conexão com o PostgreSQL: " . $detalhe,
            true
        );
    }

    responderErro(
        "Não foi possÃ­vel conectar ao banco de dados. " . $detalhe
    );
}

/* Busca as notificações */
function buscarNotificacoes($conn) {
    $sql = 'SELECT
                id,
                paciente_id,
                titulo,
                mensagem,
                tipo,
                data_notificacao,
                lida
            FROM notificacoes
            ORDER BY id DESC';

    $resultado = @pg_query($conn, $sql);

    if ($resultado === false) {
        responderErro(
            "Erro ao consultar notificações: " .
            pg_last_error($conn),
            isset($_GET["atualizar"])
        );
    }

    return pg_fetch_all($resultado) ?: [];
}

/* Endpoint usado pelo JavaScript */
if (isset($_GET["atualizar"])) {
    header("Content-Type: application/json; charset=UTF-8");

    echo json_encode(
        buscarNotificacoes($conn),
        JSON_UNESCAPED_UNICODE
    );

    pg_close($conn);
    exit;
}

$notificacoes = buscarNotificacoes($conn);
pg_close($conn);

function escapar($valor) {
    return htmlspecialchars(
        (string)($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquiVacina | Alertas</title>

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
            min-height: 100vh;
            padding: 20px;
        }

        .ContainerAlertas {
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
            box-shadow: 0 4px 12px rgba(70, 130, 160, 0.12);
            margin-bottom: 15px;
        }

        .AlertasCard {
            background: white;
            border-radius: 28px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(70, 130, 160, 0.15);
        }

        .Logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .Logo img {
            width: 150px;
            max-width: 100%;
            height: auto;
        }

        header {
            margin-bottom: 25px;
        }

        header h2 {
            color: #245b78;
            font-size: 25px;
            margin-bottom: 8px;
        }

        header p {
            color: #6b8291;
            font-size: 14px;
            line-height: 1.5;
        }

        main {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        section {
            background: #f8fcff;
            border: 1px solid #dceef6;
            border-radius: 20px;
            padding: 18px;
            box-shadow: 0 3px 12px rgba(70, 130, 160, 0.08);
        }

        section > h2 {
            color: #286985;
            font-size: 17px;
            margin-bottom: 14px;
        }

        .Epidemia,
        .Vacina,
        .Reforco,
        .Campanha {
            border-left: 5px solid #4da8d1;
        }

        .AlertaConteudo {
            background: white;
            border-radius: 15px;
            padding: 16px;
            border: 1px solid #e4f1f6;
            margin-bottom: 10px;
            overflow-wrap: anywhere;
        }

        .AlertaConteudo:last-child {
            margin-bottom: 0;
        }

        .AlertaConteudo h3 {
            color: #36596d;
            font-size: 16px;
            margin: 8px 0;
        }

        .AlertaConteudo p {
            color: #6b8291;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 13px;
        }

        .AlertaConteudo small {
            display: block;
            color: #7b929f;
            font-size: 12px;
            line-height: 1.5;
            margin-top: 10px;
        }

        .Status {
            display: inline-block;
            background: #e8f5fb;
            color: #286985;
            border-radius: 20px;
            padding: 5px 9px;
            font-size: 11px;
        }

        .Botao {
            display: inline-block;
            border: none;
            border-radius: 11px;
            padding: 10px 17px;
            background: #4da8d1;
            color: white;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .Botao:hover {
            background: #378fb8;
        }

        .Vazio {
            color: #6b8291;
            font-size: 14px;
            line-height: 1.5;
        }

        footer {
            text-align: center;
            margin-top: 25px;
            color: #7b929f;
            font-size: 13px;
            line-height: 1.7;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .AlertasCard {
                padding: 20px;
                border-radius: 24px;
            }

            .Logo img {
                width: 135px;
            }

            header h2 {
                font-size: 23px;
            }

            section {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
<div class="ContainerAlertas">

    <header>
        <h2>Alertas de epidemias</h2>
    </header>

    <main class="AlertasCard">
        <section class="Epidemia">
            <div id="epidemias">
                <p class="Vazio">Carregando notificações...</p>
            </div>
        </section>
            <section class="Vacina">
                <h2>ðŸ’‰ Vacinas disponÃ­veis para a criança</h2>
                <div id="vacinas">
                    <p class="Vazio">Carregando notificações...</p>
                </div>
                <a href="VacinasDisponiveis.php" class="Botao">
                    Ver vacinas
                </a>
            </section>

            <section class="Reforco">
                <h2>ðŸ“… Doses e reforços</h2>
                <div id="reforcos">
                    <p class="Vazio">Carregando notificações...</p>
                </div>
                <a href="Calendario.php" class="Botao">
                    Ver calendário
                </a>
            </section>

            <section class="Campanha">
                <h2>ðŸ“¢ Campanhas de vacinação</h2>
                <div id="campanhas">
                    <p class="Vazio">Carregando notificações...</p>
                </div>
                <a href="Campanhas.php" class="Botao">
                    Ver campanhas
                </a>
            </section>
        </main>

        <footer>
            <p>AquiVacina</p>
            <p id="ultimaAtualizacao">Aguardando atualização...</p>
        </footer>

    </div>
</div>

<script>
function escaparHTML(valor) {
    return String(valor ?? "").replace(/[&<>"']/g, function (caractere) {
        return {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        }[caractere];
    });
}

function classificar(tipo) {
    const texto = String(tipo ?? "").toLowerCase();

    if (texto.includes("epidem")) {
        return "epidemias";
    }

    if (texto.includes("vacina")) {
        return "vacinas";
    }

    if (
        texto.includes("reforço") ||
        texto.includes("reforco") ||
        texto.includes("dose") ||
        texto.includes("calendário") ||
        texto.includes("calendario")
    ) {
        return "reforcos";
    }

    if (texto.includes("campanha")) {
        return "campanhas";
    }

    return "campanhas";
}

function formatarData(valor) {
    if (!valor) {
        return "Data não informada";
    }

    const data = new Date(String(valor).replace(" ", "T"));

    if (Number.isNaN(data.getTime())) {
        return escaparHTML(valor);
    }

    return data.toLocaleString("pt-BR");
}

function montarCartao(item) {
    const lida = ["t", "true", "1"].includes(
        String(item.lida).toLowerCase()
    );

    return `
        <article class="AlertaConteudo">
            <span class="Status">${lida ? "Lida" : "Não lida"}</span>
            <h3>${escaparHTML(item.titulo || "Notificação")}</h3>
            <p>${escaparHTML(item.mensagem || "")}</p>
            <small>
                Tipo: ${escaparHTML(item.tipo || "Geral")}<br>
                ${formatarData(item.data_notificacao)}
            </small>
        </article>
    `;
}

async function atualizarNotificacoes() {
    try {
        const resposta = await fetch(
            "Alertas.php?atualizar=1&_=" + Date.now(),
            { cache: "no-store" }
        );

        const dados = await resposta.json();

        if (!resposta.ok || !Array.isArray(dados)) {
            throw new Error(
                dados.erro || "Erro ao carregar notificações."
            );
        }

        const grupos = {
            epidemias: [],
            vacinas: [],
            reforcos: [],
            campanhas: []
        };

        dados.forEach(item => {
            grupos[classificar(item.tipo)].push(item);
        });

        Object.keys(grupos).forEach(grupo => {
            const elemento = document.getElementById(grupo);
            const itens = grupos[grupo];

            elemento.innerHTML = itens.length
                ? itens.map(montarCartao).join("")
                : '<p class="Vazio">Nenhuma notificação registrada nesta categoria.</p>';
        });

        document.getElementById("ultimaAtualizacao").textContent =
            "Atualizado Ã s " + new Date().toLocaleTimeString("pt-BR");

    } catch (erro) {
        document.getElementById("ultimaAtualizacao").textContent =
            "Erro ao atualizar: " + erro.message;

        console.error(erro);
    }
}

atualizarNotificacoes();
setInterval(atualizarNotificacoes, 30000);
</script>
</body>
</html>
