<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { http_response_code(200); exit(0); }

require_once '../config/database.php';

$keysFile = __DIR__ . '/../config/keys.local.php';
if (file_exists($keysFile)) require_once $keysFile;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['error' => 'POST only'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$email    = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

// Fixed accounts for this demo app. Real password lives server-side only —
// the frontend never sees it, it only sends what the person typed.
$USERS = [
    'owner@supermart.in'   => ['password' => 'Owner@Sri2026',   'role' => 'owner',   'label' => 'Store Owner',   'icon' => '👑'],
    'manager@supermart.in' => ['password' => 'Manager@Sri2026', 'role' => 'manager', 'label' => 'Store Manager', 'icon' => '🏪'],
];

if (!isset($USERS[$email]) || !hash_equals($USERS[$email]['password'], $password)) {
    sendJSON(['error' => 'Invalid email or password'], 401);
}

if (!defined('WRITE_TOKEN')) {
    sendJSON(['error' => 'Server misconfigured: missing WRITE_TOKEN'], 500);
}

$u = $USERS[$email];
sendJSON([
    'success' => true,
    'role'    => $u['role'],
    'label'   => $u['label'],
    'icon'    => $u['icon'],
    'token'   => WRITE_TOKEN,
]);