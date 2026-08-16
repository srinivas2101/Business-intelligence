<?php
// Real credentials live in database.local.php — that file is gitignored and
// NEVER committed. Copy database.local.example.php to create it (locally
// and on your InfinityFree hosting via File Manager / FTP).
$localConfig = __DIR__ . '/database.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
} elseif (getenv('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
    define('DB_NAME', getenv('DB_NAME'));
} else {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'CHANGE_ME');
}
define('ML_SERVICE_URL', getenv('ML_SERVICE_URL') ?: 'http://localhost:5000');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die(json_encode(['error' => 'DB Connection failed: ' . $this->conn->connect_error]));
        }
        $this->conn->set_charset('utf8mb4');
    }

    public static function getInstance() {
        if (!self::$instance) self::$instance = new Database();
        return self::$instance;
    }

    public function getConnection() { return $this->conn; }

    public function query($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function execute($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        if ($params) $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }
}

function sendJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Call this at the top of any write-capable endpoint (POST/PUT/DELETE handlers)
// to block requests that don't carry a valid owner/manager token — e.g. the
// guest/read-only role never receives this token, so it can view data but
// never modify it, even by calling the API directly.
function requireWriteAccess() {
    $keysFile = __DIR__ . '/keys.local.php';
    if (file_exists($keysFile)) require_once $keysFile;
    if (!defined('WRITE_TOKEN') && getenv('WRITE_TOKEN')) {
        define('WRITE_TOKEN', getenv('WRITE_TOKEN'));
    }

    $authHeader = '';
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    $token = trim(str_replace('Bearer', '', $authHeader));

    if (!defined('WRITE_TOKEN') || $token === '' || $token !== WRITE_TOKEN) {
        sendJSON(['error' => 'Read-only access — sign in as Owner or Manager to make changes'], 403);
    }
}


function getMLPrediction($endpoint, $data) {
    if (!function_exists('curl_init')) return null;
    $ch = curl_init(ML_SERVICE_URL . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err || !$response) return null;
    return json_decode($response, true);
}
?>