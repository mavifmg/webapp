
<?php
header('Content-Type: application/json; charset=utf-8');

require_once "Conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "erro" => "Método não permitido."
    ]);
    exit();
}

$CPF = preg_replace('/\D/', '', $_POST["CPF"] ?? '');

if (strlen($CPF) !== 11) {
    echo json_encode([
        "valido" => false,
        "cadastrado" => false,
        "mensagem" => "Digite um CPF com 11 números."
    ]);
    exit();
}

// Verifica se o CPF é matematicamente válido
function validarCPF($cpf)
{
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    $soma = 0;

    for ($i = 0; $i < 9; $i++) {
        $soma += (int)$cpf[$i] * (10 - $i);
    }

    $resto = ($soma * 10) % 11;

    if ($resto === 10) {
        $resto = 0;
    }

    if ($resto !== (int)$cpf[9]) {
        return false;
    }

    $soma = 0;

    for ($i = 0; $i < 10; $i++) {
        $soma += (int)$cpf[$i] * (11 - $i);
    }

    $resto = ($soma * 10) % 11;

    if ($resto === 10) {
        $resto = 0;
    }

    return $resto === (int)$cpf[10];
}

if (!validarCPF($CPF)) {
    echo json_encode([
        "valido" => false,
        "cadastrado" => false,
        "mensagem" => "CPF inválido. Confira os números digitados."
    ]);
    exit();
}

// Consulta o CPF no PostgreSQL
$sql = 'SELECT 1 FROM cadastro WHERE "CPF" = $1 LIMIT 1';

$resultado = pg_query_params($conn, $sql, [$CPF]);

if ($resultado === false) {
    http_response_code(500);

    error_log("Erro ao verificar CPF: " . pg_last_error($conn));

    echo json_encode([
        "erro" => "Não foi possível verificar o CPF agora."
    ]);

    exit();
}

$cadastrado = pg_num_rows($resultado) > 0;

echo json_encode([
    "valido" => true,
    "cadastrado" => $cadastrado,
    "mensagem" => $cadastrado
        ? "Este CPF já está cadastrado."
        : "CPF válido e ainda não cadastrado."
]);

pg_close($conn);
?>
