<?php
// Garantir que a sessão seja iniciada para as funções de sessão (flash/login)
if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}

// **********************************
// 1. CONFIGURAÇÕES DE CONEXÃO (PDO)
// **********************************
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'barbearia'); 

/**
 * Cria e retorna uma nova conexão PDO com o banco de dados.
 *
 * @return PDO Conexão PDO ativa.
 * @throws Exception Lança uma exceção se a conexão falhar.
 */
function get_db_connection(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (\PDOException $e) {
        // --- CORREÇÃO CRÍTICA: Lançar exceção em vez de morrer. ---
        // Isso permite que a API de agendamentos capture o erro e retorne JSON.
        throw new Exception("Falha na conexão com o banco de dados (PDO): " . $e->getMessage());
    }
}

// **********************************
// 2. FUNÇÕES DE UTILIDADE
// **********************************

/**
 * Define uma mensagem flash para ser exibida na próxima página.
 * @param string $message A mensagem a ser armazenada.
 */
function set_flash(string $message): void {
    $_SESSION['flash'] = $message;
}

/**
 * Redireciona o usuário para uma URL e encerra o script.
 * @param string $url O caminho para redirecionar.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit();
}

/**
 * Verifica se o usuário está logado.
 * @return bool
 */
function is_logged(): bool {
    // Supondo que você armazena o ID do cliente na sessão após o login
    return isset($_SESSION['cliente_id']) && !empty($_SESSION['cliente_id']); 
}

/**
 * Redireciona para o login se o usuário não estiver logado.
 * Útil para proteger páginas que exigem autenticação.
 */
function require_login(): void {
    if (!is_logged()) {
        set_flash("Você precisa estar logado para acessar esta página.");
        redirect('login.php');
    }
}

/**
 * Escapa strings para exibição segura em HTML.
 * @param string|null $string
 * @return string
 */
function esc(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
