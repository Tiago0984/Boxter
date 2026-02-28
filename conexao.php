<?php

/**
 * ARQUIVO DE CONEXÃO COM BANCO DE DADOS
 * Gerencia conexão com MySQL usando MySQLi
 * 
 * Uso:
 * include_once 'conexao.php';
 * $resultado = mysqli_query($conn, $sql);
 */

// Carrega variáveis de ambiente do arquivo .env
function carregarEnv($arquivoEnv = __DIR__ . '/.env')
{
    if (!file_exists($arquivoEnv)) {
        // Se não existir .env, tenta usar as variáveis do sistema
        return;
    }

    $linhas = file($arquivoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        // Ignora comentários
        if (strpos(trim($linha), '#') === 0) {
            continue;
        }

        // Regex para envars no formato CHAVE=valor
        if (strpos($linha, '=') !== false) {
            list($chave, $valor) = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);

            // Remove aspas se existirem
            $valor = trim($valor, '"\'');

            // Define a variável de ambiente se não estiver definida
            if (!getenv($chave)) {
                putenv("$chave=$valor");
            }
        }
    }
}

// Carrega .env se existir
carregarEnv();

// Obtém configurações do banco de dados
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_nome = getenv('DB_NAME') ?: 'oficina_boxter';

/**
 * Cria conexão MySQLi com tratamento de erro
 */
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_nome);

// Verifica se a conexão foi bem-sucedida
if (!$conn) {
    // Log de erro
    $erro = 'Erro ao conectar com o banco de dados: ' . mysqli_connect_error();
    error_log($erro, 3, '/tmp/boxter_conexao_erro.log');

    // Não expõe detalhes de erro em producão
    $appEnv = getenv('APP_ENV') ?: 'development';
    if ($appEnv === 'development' || $appEnv === 'staging') {
        die('<h2 style="color: red; font-family: Arial;">⚠️ ERRO DE CONEXÃO</h2>' .
            '<p><strong>Erro:</strong> ' . htmlspecialchars(mysqli_connect_error()) . '</p>' .
            '<p><strong>Host:</strong> ' . htmlspecialchars($db_host) . '</p>' .
            '<p><strong>Verifique o arquivo .env com as credenciais corretas.</strong></p>');
    } else {
        die('<h2 style="color: red; font-family: Arial;">⚠️ ERRO DE CONEXÃO</h2>' .
            '<p>Não foi possível conectar ao banco de dados. Entre em contato com o suporte.</p>');
    }
}

/**
 * Define charset para UTF-8
 * Evita problemas com acentuação
 */
if (!mysqli_set_charset($conn, 'utf8mb4')) {
    die('<h2 style="color: red; font-family: Arial;">⚠️ ERRO DE CHARSET</h2>' .
        '<p>Erro ao configurar charset UTF-8: ' . htmlspecialchars(mysqli_error($conn)) . '</p>');
}

/**
 * Força modo STRICT do MySQL (recomendado)
 * Descomenta se o servidor suportar
 */
// mysqli_query($conn, "SET sql_mode = 'STRICT_TRANS_TABLES'");

/**
 * FUNÇÃO AUXILIAR: Prepara e executa queries com parâmetros
 * Evita SQL Injection
 * 
 * Uso:
 * $resultado = executarQuery("SELECT * FROM tbl_clientes WHERE id_cliente = ?", "i", [$id]);
 * $dados = mysqli_fetch_assoc($resultado);
 */
if (!function_exists('executarQuery')) {
    function executarQuery($sql, $tipos = "", $parametros = [])
    {
        global $conn;

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log('Erro ao preparar query: ' . mysqli_error($conn) . ' | SQL: ' . $sql);
            return false;
        }

        // Vincula parâmetros se houver
        if (!empty($parametros) && !empty($tipos)) {
            mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
        }

        // Executa
        if (!mysqli_stmt_execute($stmt)) {
            error_log('Erro ao executar query: ' . mysqli_stmt_error($stmt) . ' | SQL: ' . $sql);
            mysqli_stmt_close($stmt);
            return false;
        }

        return mysqli_stmt_get_result($stmt);
    }
}

/**
 * FUNÇÃO AUXILIAR: Retorna o número de linhas afetadas (INSERT, UPDATE, DELETE)
 */
if (!function_exists('linhasAfetadas')) {
    function linhasAfetadas()
    {
        global $conn;
        return mysqli_affected_rows($conn);
    }
}

/**
 * FUNÇÃO AUXILIAR: Retorna o ID da última inserção
 */
if (!function_exists('ultimoId')) {
    function ultimoId()
    {
        global $conn;
        return mysqli_insert_id($conn);
    }
}

/**
 * FUNÇÃO AUXILIAR: Escapa de valores para queries diretas (não recomendado)
 * Use prepared statements sempre que possível!
 */
if (!function_exists('escaparValor')) {
    function escaparValor($valor)
    {
        global $conn;
        return mysqli_real_escape_string($conn, $valor);
    }
}

// Tudo pronto! A variável $conn está disponível em todo o projeto
