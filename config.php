<?php
date_default_timezone_set('Asia/Jakarta');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'pc_store_ai');
define('DB_USER', 'root');
define('DB_PASS', '');

define('OLLAMA_URL', 'http://127.0.0.1:11434/api/generate');
define('OLLAMA_MODEL', 'qwen3:32b');
define('OLLAMA_TIMEOUT', 300);

function getPDO()
{
	static $pdo = null;
	if ($pdo === null) {
		$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
		$pdo = new PDO($dsn, DB_USER, DB_PASS, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
	}

	return $pdo;
}

function sendJson($data, int $statusCode = 200)
{
	http_response_code($statusCode);
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit;
}

function getJsonInput(): array
{
	$raw = file_get_contents('php://input');
	if (!$raw) {
		return [];
	}

	$decoded = json_decode($raw, true);
	return is_array($decoded) ? $decoded : [];
}

function callOllama(string $prompt): ?string
{
	$payload = [
		'model' => OLLAMA_MODEL,
		'prompt' => $prompt,
		'stream' => false,
		'options' => [
			'temperature' => 0.2,
			'num_predict' => 120,
		],
	];

	$ch = curl_init(OLLAMA_URL);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
		CURLOPT_POSTFIELDS => json_encode($payload),
		CURLOPT_TIMEOUT => OLLAMA_TIMEOUT,
	]);

	$response = curl_exec($ch);
	$err = curl_error($ch);
	curl_close($ch);

	if ($response === false || $err) {
		return null;
	}

	$data = json_decode($response, true);
	if (!is_array($data) || !isset($data['response'])) {
		return null;
	}

	return trim((string)$data['response']);
}

function requireRole(PDO $pdo, int $userId, string $requiredRole): array
{
	$stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ? LIMIT 1');
	$stmt->execute([$userId]);
	$user = $stmt->fetch();

	if (!$user) {
		sendJson(['ok' => false, 'message' => 'User tidak ditemukan'], 404);
	}

	if ($user['role'] !== $requiredRole) {
		sendJson(['ok' => false, 'message' => 'Akses ditolak untuk role ini'], 403);
	}

	return $user;
}

