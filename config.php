<?php

/**
 * GERENCIADOR DE CONFIGURAÇÕES E LOGS
 * Arquivo centralizado para configurações da aplicação
 * 
 * Uso:
 * require_once 'config.php';
 * $baseUrl = Config::get('BASE_URL');
 */

class Config
{
    private static $config = null;

    /**
     * Carrega e retorna configuração
     */
    public static function get($key, $default = null)
    {
        if (self::$config === null) {
            self::$config = self::loadConfig();
        }
        return self::$config[$key] ?? $default;
    }

    /**
     * Carrega todas as configurações
     */
    private static function loadConfig(): array
    {
        $isLocal = self::isLocalEnvironment();
        $appEnv = getenv('APP_ENV') ?: ($isLocal ? 'development' : 'staging');

        return [
            // Ambiente
            'APP_ENV' => $appEnv,
            'IS_LOCAL' => $isLocal,
            'DEBUG' => ($appEnv === 'development'),

            // URLs
            'BASE_URL' => self::getBaseUrl(),
            'SITE_NAME' => 'Boxter Auto Peças',

            // Banco de Dados
            'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
            'DB_USER' => getenv('DB_USER') ?: 'root',
            'DB_PASS' => getenv('DB_PASS') ?: '',
            'DB_NAME' => getenv('DB_NAME') ?: 'oficina_boxter',

            // Mercado Pago
            'MP_MODE' => getenv('APP_PAYMENT_MODE') ?: 'sandbox',
            'MP_ACCESS_TOKEN' => self::getMercadoPagoToken(),
            'MP_PUBLIC_KEY' => self::getMercadoPagoPublicKey(),

            // Email
            'MAIL_HOST' => getenv('MAIL_HOST') ?: 'smtp.seuservidor.com',
            'MAIL_PORT' => (int)(getenv('MAIL_PORT') ?: 587),
            'MAIL_USERNAME' => getenv('MAIL_USERNAME') ?: '',
            'MAIL_PASSWORD' => getenv('MAIL_PASSWORD') ?: '',
            'MAIL_FROM' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@boxter.com.br',
            'MAIL_FROM_NAME' => getenv('MAIL_FROM_NAME') ?: 'Boxter Auto Peças',

            // Caminhos
            'PATH_LOGS' => self::getLogsPath(),
            'PATH_UPLOADS' => self::getUploadsPath(),
            'PATH_PUBLIC' => __DIR__,
        ];
    }

    /**
     * Detecta se é ambiente local
     */
    private static function isLocalEnvironment(): bool
    {
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        $hostSemPorta = explode(':', $httpHost)[0];
        $isCli = php_sapi_name() === 'cli';

        return $isCli || in_array($hostSemPorta, ['localhost', '127.0.0.1', '::1'], true);
    }

    /**
     * Detecta URL base da aplicação
     */
    private static function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Retorna token Mercado Pago correto baseado no modo
     */
    private static function getMercadoPagoToken(): string
    {
        $mode = getenv('APP_PAYMENT_MODE') ?: 'sandbox';
        $tokenGenerico = trim((string)(getenv('MP_ACCESS_TOKEN') ?: ''));
        $tokenTest = trim((string)(getenv('MP_ACCESS_TOKEN_TEST') ?: ''));
        $tokenProd = trim((string)(getenv('MP_ACCESS_TOKEN_PROD') ?: ''));

        if ($mode === 'production') {
            if ($tokenProd && stripos($tokenProd, 'TEST-') !== 0) {
                return $tokenProd;
            }
            if ($tokenGenerico && stripos($tokenGenerico, 'TEST-') !== 0) {
                return $tokenGenerico;
            }
        }

        if ($mode === 'sandbox' || $mode === 'test_simulated') {
            if ($tokenTest && stripos($tokenTest, 'TEST-') === 0) {
                return $tokenTest;
            }
            if ($tokenGenerico && stripos($tokenGenerico, 'TEST-') === 0) {
                return $tokenGenerico;
            }
        }

        return '';
    }

    /**
     * Retorna chave pública Mercado Pago correta
     */
    private static function getMercadoPagoPublicKey(): string
    {
        $mode = getenv('APP_PAYMENT_MODE') ?: 'sandbox';
        $pkGenerico = trim((string)(getenv('MP_PUBLIC_KEY') ?: ''));
        $pkTest = trim((string)(getenv('MP_PUBLIC_KEY_TEST') ?: ''));
        $pkProd = trim((string)(getenv('MP_PUBLIC_KEY_PROD') ?: ''));

        if ($mode === 'production') {
            if ($pkProd && stripos($pkProd, 'TEST-') !== 0) {
                return $pkProd;
            }
            if ($pkGenerico && stripos($pkGenerico, 'TEST-') !== 0) {
                return $pkGenerico;
            }
        }

        if ($mode === 'sandbox' || $mode === 'test_simulated') {
            if ($pkTest && stripos($pkTest, 'TEST-') === 0) {
                return $pkTest;
            }
            if ($pkGenerico && stripos($pkGenerico, 'TEST-') === 0) {
                return $pkGenerico;
            }
        }

        return '';
    }

    /**
     * Retorna caminho seguro para logs (fora da web)
     */
    private static function getLogsPath(): string
    {
        // Em Locaweb, usar diretório fora do public_html
        if (!self::isLocalEnvironment()) {
            $parent = dirname(dirname(__DIR__));
            $logsDir = $parent . '/logs';
            if (is_writable($parent)) {
                return $logsDir;
            }
        }

        // Fallback: usar diretório local
        $logsDir = __DIR__ . '/logs';
        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0755, true);
        }

        return $logsDir;
    }

    /**
     * Retorna caminho para uploads
     */
    private static function getUploadsPath(): string
    {
        $uploadsDir = __DIR__ . '/uploads';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0755, true);
        }
        return $uploadsDir;
    }
}

/**
 * CLASSE PARA LOGGING CENTRALIZADO
 */
class Logger
{
    private static $logsPath = null;

    /**
     * Log de erro
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    /**
     * Log de informação
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    /**
     * Log de aviso
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    /**
     * Log de pagamento
     */
    public static function payment(string $message, array $context = []): void
    {
        self::log('PAYMENT', $message, $context);
    }

    /**
     * Registra log em arquivo
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        $logsPath = Config::get('PATH_LOGS');

        if (!is_dir($logsPath)) {
            @mkdir($logsPath, 0755, true);
        }

        $logFile = $logsPath . '/app_' . date('Y-m-d') . '.log';

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr\n";

        if (is_writable($logsPath) || is_writable(dirname($logsPath))) {
            @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }

        // Também registrar em error_log do PHP se em debug
        if (Config::get('DEBUG')) {
            error_log("[$level] $message$contextStr");
        }
    }
}

// Autoload da classe Config para fácil acesso
if (!function_exists('getConfig')) {
    function getConfig($key, $default = null)
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('logError')) {
    function logError($msg, $context = [])
    {
        Logger::error($msg, $context);
    }
}

if (!function_exists('logPayment')) {
    function logPayment($msg, $context = [])
    {
        Logger::payment($msg, $context);
    }
}
