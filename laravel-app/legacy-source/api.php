<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	sendJson(['ok' => true, 'message' => 'Preflight OK']);
}

function hasColumn(PDO $pdo, string $table, string $column): bool
{
	static $cache = [];
	$key = $table . '.' . $column;
	if (isset($cache[$key])) {
		return $cache[$key];
	}

	$stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
	$stmt->execute([$column]);
	$cache[$key] = (bool)$stmt->fetch();
	return $cache[$key];
}

try {
	$pdo = getPDO();
	$action = $_GET['action'] ?? '';
	$input = getJsonInput();
	$partsHasActive = hasColumn($pdo, 'pc_parts', 'is_active');
	$buildsHasActive = hasColumn($pdo, 'pc_builds', 'is_active');

	// ===== GUEST & CUSTOMER ENDPOINTS =====

	// List all PC parts
	if ($action === 'list_parts') {
		$sql = 'SELECT id, name, category, brand, model, price, stock, is_stock_empty, image_url, specifications FROM pc_parts';
		if ($partsHasActive) {
			$sql .= ' WHERE is_active = 1';
		}
		$sql .= ' ORDER BY category, name ASC';
		$stmt = $pdo->query($sql);
		$parts = $stmt->fetchAll();
		$parts = array_map(function($p) {
			$p['specifications'] = $p['specifications'] ? json_decode($p['specifications'], true) : [];
			return $p;
		}, $parts);
		sendJson(['ok' => true, 'data' => $parts]);
	}

	// List all PC builds with components
	if ($action === 'list_builds') {
		$sql = 'SELECT id, name, description, total_price, image_url FROM pc_builds';
		if ($buildsHasActive) {
			$sql .= ' WHERE is_active = 1';
		}
		$sql .= ' ORDER BY name ASC';
		$stmt = $pdo->query($sql);
		$builds = $stmt->fetchAll();
		sendJson(['ok' => true, 'data' => $builds]);
	}

	// Get single PC build with components
	if ($action === 'get_build') {
		$buildId = (int)($_GET['id'] ?? 0);
		if ($buildId <= 0) sendJson(['ok' => false, 'message' => 'Build ID harus valid'], 422);

		$sql = 'SELECT id, name, description, total_price FROM pc_builds WHERE id = ?';
		if ($buildsHasActive) {
			$sql .= ' AND is_active = 1';
		}
		$sql .= ' LIMIT 1';
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$buildId]);
		$build = $stmt->fetch();
		if (!$build) sendJson(['ok' => false, 'message' => 'Build tidak ditemukan'], 404);

		$compStmt = $pdo->prepare('
			SELECT pbc.qty, pp.id, pp.name, pp.category, pp.brand, pp.model, pp.price
			FROM pc_build_components pbc
			JOIN pc_parts pp ON pp.id = pbc.pc_part_id
			WHERE pbc.pc_build_id = ?
		');
		$compStmt->execute([$buildId]);
		$build['components'] = $compStmt->fetchAll();

		sendJson(['ok' => true, 'data' => $build]);
	}

	// Search parts by keyword
	if ($action === 'search_parts') {
		$q = trim($_GET['q'] ?? '');
		if (strlen($q) < 2) sendJson(['ok' => true, 'data' => []]);

		$q = '%' . $q . '%';
		$sql = '
			SELECT id, name, category, brand, model, price, stock, is_stock_empty, image_url
			FROM pc_parts
			WHERE (name LIKE ? OR brand LIKE ? OR category LIKE ? OR model LIKE ?)';
		if ($partsHasActive) {
			$sql .= ' AND is_active = 1';
		}
		$sql .= '
			ORDER BY category, name ASC
		';
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$q, $q, $q, $q]);
		sendJson(['ok' => true, 'data' => $stmt->fetchAll()]);
	}

	// AI recommendation (simple for now)
	if ($action === 'ai_recommend' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$prompt = trim($input['prompt'] ?? '');
		if (!$prompt) sendJson(['ok' => false, 'message' => 'Prompt wajib diisi'], 422);

		// For now, return all parts as recommendation
		$sql = 'SELECT id, name, category, price, image_url FROM pc_parts';
		if ($partsHasActive) {
			$sql .= ' WHERE is_active = 1';
		}
		$sql .= ' LIMIT 10';
		$stmt = $pdo->query($sql);
		$parts = $stmt->fetchAll();
		sendJson(['ok' => true, 'data' => ['parts' => $parts, 'reason' => 'Rekomendasi AI: ' . substr($prompt, 0, 50)]]);
	}

	// ===== CART ENDPOINTS =====

	// Add to cart (part or build)
	if ($action === 'add_to_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$customerId = (int)($input['customer_id'] ?? 0);
		$itemType = trim($input['item_type'] ?? '');
		$itemId = (int)($input['item_id'] ?? 0);
		$qty = max(1, (int)($input['qty'] ?? 1));

		if ($customerId <= 0 || !in_array($itemType, ['part', 'build']) || $itemId <= 0) {
			sendJson(['ok' => false, 'message' => 'Parameter tidak valid'], 422);
		}

		// Check if item exists
		if ($itemType === 'part') {
			$sql = 'SELECT id FROM pc_parts WHERE id = ?';
			if ($partsHasActive) {
				$sql .= ' AND is_active = 1';
			}
			$check = $pdo->prepare($sql);
			$check->execute([$itemId]);
			if (!$check->fetch()) sendJson(['ok' => false, 'message' => 'Part tidak ditemukan'], 404);
		} else {
			$sql = 'SELECT id FROM pc_builds WHERE id = ?';
			if ($buildsHasActive) {
				$sql .= ' AND is_active = 1';
			}
			$check = $pdo->prepare($sql);
			$check->execute([$itemId]);
			if (!$check->fetch()) sendJson(['ok' => false, 'message' => 'Build tidak ditemukan'], 404);
		}

		// Add to cart_items
		$stmt = $pdo->prepare('
			INSERT INTO cart_items (customer_id, item_type, item_id, quantity, created_at)
			VALUES (?, ?, ?, ?, NOW())
		');
		$stmt->execute([$customerId, $itemType, $itemId, $qty]);

		sendJson(['ok' => true, 'message' => 'Item ditambahkan ke keranjang']);
	}

	// Get cart for customer
	if ($action === 'get_cart') {
		$customerId = (int)($_GET['customer_id'] ?? 0);
		if ($customerId <= 0) sendJson(['ok' => false, 'message' => 'Customer ID harus valid'], 422);

		$stmt = $pdo->prepare('
			SELECT ci.id as cart_id, ci.item_type, ci.item_id, ci.quantity,
				   pp.name, pp.price
			FROM cart_items ci
			LEFT JOIN pc_parts pp ON ci.item_type = "part" AND pp.id = ci.item_id
			WHERE ci.customer_id = ?
		');
		$stmt->execute([$customerId]);
		$items = $stmt->fetchAll();

		$total = 0;
		foreach ($items as &$item) {
			$subtotal = $item['price'] * $item['quantity'];
			$item['subtotal'] = $subtotal;
			$total += $subtotal;
		}

		sendJson(['ok' => true, 'data' => ['items' => $items, 'total' => $total]]);
	}

	// Remove from cart
	if ($action === 'remove_from_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$cartId = (int)($input['cart_id'] ?? 0);
		if ($cartId <= 0) sendJson(['ok' => false, 'message' => 'Cart ID harus valid'], 422);

		$stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ?');
		$stmt->execute([$cartId]);
		sendJson(['ok' => true, 'message' => 'Item dihapus dari keranjang']);
	}

	// Clear cart
	if ($action === 'clear_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$customerId = (int)($input['customer_id'] ?? 0);
		if ($customerId <= 0) sendJson(['ok' => false, 'message' => 'Customer ID harus valid'], 422);

		$stmt = $pdo->prepare('DELETE FROM cart_items WHERE customer_id = ?');
		$stmt->execute([$customerId]);
		sendJson(['ok' => true, 'message' => 'Keranjang dikosongkan']);
	}

	// ===== ORDER ENDPOINTS =====

	// Checkout (create order)
	if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$customerId = (int)($input['customer_id'] ?? 0);
		if ($customerId <= 0) sendJson(['ok' => false, 'message' => 'Customer ID harus valid'], 422);

		// Get cart items
		$cartStmt = $pdo->prepare('
			SELECT ci.id, ci.item_type, ci.item_id, ci.quantity, pp.price
			FROM cart_items ci
			LEFT JOIN pc_parts pp ON ci.item_type = "part" AND pp.id = ci.item_id
			WHERE ci.customer_id = ?
		');
		$cartStmt->execute([$customerId]);
		$cartItems = $cartStmt->fetchAll();

		if (!$cartItems) sendJson(['ok' => false, 'message' => 'Keranjang kosong'], 422);

		// Calculate total
		$total = 0;
		foreach ($cartItems as $item) {
			$total += $item['price'] * $item['quantity'];
		}

		// Create order
		$orderNumber = 'ORD-' . time() . '-' . $customerId;
		$qrisCode = 'QRIS-' . bin2hex(random_bytes(8)); // Mock QRIS code
		
		$orderStmt = $pdo->prepare('
			INSERT INTO orders (customer_id, order_number, total_price, status, qris_code, created_at)
			VALUES (?, ?, ?, ?, ?, NOW())
		');
		$orderStmt->execute([$customerId, $orderNumber, $total, 'Menunggu Pembayaran', $qrisCode]);
		$orderId = $pdo->lastInsertId();

		// Move cart items to order_items
		foreach ($cartItems as $item) {
			$itemStmt = $pdo->prepare('
				INSERT INTO order_items (order_id, item_type, quantity, unit_price, subtotal, created_at)
				VALUES (?, ?, ?, ?, ?, NOW())
			');
			$itemStmt->execute([$orderId, $item['item_type'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']]);
		}

		// Clear cart
		$clearStmt = $pdo->prepare('DELETE FROM cart_items WHERE customer_id = ?');
		$clearStmt->execute([$customerId]);

		sendJson(['ok' => true, 'message' => 'Checkout berhasil', 'data' => [
			'order_id' => $orderId,
			'order_number' => $orderNumber,
			'total' => $total,
			'qris_code' => $qrisCode,
			'status' => 'Menunggu Pembayaran'
		]]);
	}

	// List customer orders
	if ($action === 'list_orders') {
		$customerId = (int)($_GET['customer_id'] ?? 0);
		if ($customerId <= 0) sendJson(['ok' => false, 'message' => 'Customer ID harus valid'], 422);

		$stmt = $pdo->prepare('
			SELECT id, order_number, total_price, status, qris_code, tracking_number, created_at
			FROM orders
			WHERE customer_id = ?
			ORDER BY created_at DESC
		');
		$stmt->execute([$customerId]);
		sendJson(['ok' => true, 'data' => $stmt->fetchAll()]);
	}

	// Get order detail
	if ($action === 'get_order') {
		$orderId = (int)($_GET['id'] ?? 0);
		if ($orderId <= 0) sendJson(['ok' => false, 'message' => 'Order ID harus valid'], 422);

		$orderStmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
		$orderStmt->execute([$orderId]);
		$order = $orderStmt->fetch();
		if (!$order) sendJson(['ok' => false, 'message' => 'Order tidak ditemukan'], 404);

		$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
		$itemsStmt->execute([$orderId]);
		$order['items'] = $itemsStmt->fetchAll();

		sendJson(['ok' => true, 'data' => $order]);
	}

	// ===== ADMIN ENDPOINTS =====

	// List all PC parts (admin)
	if ($action === 'admin_list_parts') {
		$stmt = $pdo->query('SELECT * FROM pc_parts ORDER BY category, name ASC');
		sendJson(['ok' => true, 'data' => $stmt->fetchAll()]);
	}

	// Add PC part (admin)
	if ($action === 'admin_add_part' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($input['name'] ?? '');
		$category = trim($input['category'] ?? '');
		$brand = trim($input['brand'] ?? '');
		$model = trim($input['model'] ?? '');
		$price = (float)($input['price'] ?? 0);
		$stock = (int)($input['stock'] ?? 0);

		if (!$name || !$category || !$brand || $price <= 0) {
			sendJson(['ok' => false, 'message' => 'Data part belum lengkap'], 422);
		}

		if ($partsHasActive) {
			$stmt = $pdo->prepare('
				INSERT INTO pc_parts (name, category, brand, model, price, stock, is_stock_empty, is_active, created_at)
				VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
			');
		} else {
			$stmt = $pdo->prepare('
				INSERT INTO pc_parts (name, category, brand, model, price, stock, is_stock_empty, created_at)
				VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
			');
		}
		$isStockEmpty = $stock === 0 ? 1 : 0;
		$stmt->execute([$name, $category, $brand, $model, $price, $stock, $isStockEmpty]);

		sendJson(['ok' => true, 'message' => 'PC Part berhasil ditambahkan', 'data' => [
			'id' => $pdo->lastInsertId(),
			'name' => $name
		]]);
	}

	// Update PC part (admin)
	if ($action === 'admin_update_part' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$partId = (int)($input['id'] ?? 0);
		$stock = (int)($input['stock'] ?? 0);
		$price = (float)($input['price'] ?? 0);

		if ($partId <= 0) sendJson(['ok' => false, 'message' => 'Part ID harus valid'], 422);

		$isStockEmpty = $stock === 0 ? 1 : 0;
		$stmt = $pdo->prepare('UPDATE pc_parts SET stock = ?, price = ?, is_stock_empty = ? WHERE id = ?');
		$stmt->execute([$stock, $price, $isStockEmpty, $partId]);

		sendJson(['ok' => true, 'message' => 'PC Part berhasil diperbarui']);
	}

	// Delete PC part (admin)
	if ($action === 'admin_delete_part' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$partId = (int)($input['id'] ?? 0);
		if ($partId <= 0) sendJson(['ok' => false, 'message' => 'Part ID harus valid'], 422);

		$stmt = $pdo->prepare('DELETE FROM pc_parts WHERE id = ?');
		$stmt->execute([$partId]);

		sendJson(['ok' => true, 'message' => 'PC Part berhasil dihapus']);
	}

	// List PC builds (admin)
	if ($action === 'admin_list_builds') {
		$stmt = $pdo->query('SELECT * FROM pc_builds ORDER BY name ASC');
		$builds = $stmt->fetchAll();

		foreach ($builds as &$build) {
			$compStmt = $pdo->prepare('
				SELECT pbc.qty, pp.id, pp.name, pp.price
				FROM pc_build_components pbc
				JOIN pc_parts pp ON pp.id = pbc.pc_part_id
				WHERE pbc.pc_build_id = ?
			');
			$compStmt->execute([$build['id']]);
			$build['components'] = $compStmt->fetchAll();
		}

		sendJson(['ok' => true, 'data' => $builds]);
	}

	// Add PC build (admin)
	if ($action === 'admin_add_build' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($input['name'] ?? '');
		$description = trim($input['description'] ?? '');
		$totalPrice = (float)($input['total_price'] ?? 0);

		if (!$name || $totalPrice <= 0) {
			sendJson(['ok' => false, 'message' => 'Nama dan harga build wajib diisi'], 422);
		}

		if ($buildsHasActive) {
			$stmt = $pdo->prepare('
				INSERT INTO pc_builds (name, description, total_price, is_active, created_at)
				VALUES (?, ?, ?, 1, NOW())
			');
		} else {
			$stmt = $pdo->prepare('
				INSERT INTO pc_builds (name, description, total_price, created_at)
				VALUES (?, ?, ?, NOW())
			');
		}
		$stmt->execute([$name, $description, $totalPrice]);

		sendJson(['ok' => true, 'message' => 'PC Build berhasil ditambahkan', 'data' => [
			'id' => $pdo->lastInsertId(),
			'name' => $name
		]]);
	}

	// Delete PC build (admin)
	if ($action === 'admin_delete_build' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$buildId = (int)($input['id'] ?? 0);
		if ($buildId <= 0) sendJson(['ok' => false, 'message' => 'Build ID harus valid'], 422);

		$pdo->prepare('DELETE FROM pc_build_components WHERE pc_build_id = ?')->execute([$buildId]);
		$pdo->prepare('DELETE FROM pc_builds WHERE id = ?')->execute([$buildId]);

		sendJson(['ok' => true, 'message' => 'PC Build berhasil dihapus']);
	}

	// List all orders (admin)
	if ($action === 'admin_list_orders') {
		$stmt = $pdo->query('
			SELECT o.id, o.customer_id, o.order_number, o.total_price, o.status, o.qris_code, o.tracking_number, o.created_at, u.name as customer_name
			FROM orders o
			JOIN users u ON u.id = o.customer_id
			ORDER BY o.created_at DESC
		');
		sendJson(['ok' => true, 'data' => $stmt->fetchAll()]);
	}

	// Update order status (admin)
	if ($action === 'admin_update_order_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$orderId = (int)($input['id'] ?? 0);
		$status = trim($input['status'] ?? '');
		$validStatuses = ['Menunggu Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Sampai di Tujuan', 'Selesai', 'Dibatalkan'];

		if ($orderId <= 0 || !in_array($status, $validStatuses)) {
			sendJson(['ok' => false, 'message' => 'Parameter tidak valid'], 422);
		}

		$stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
		$stmt->execute([$status, $orderId]);

		sendJson(['ok' => true, 'message' => 'Status order berhasil diperbarui']);
	}

	// List users
	if ($action === 'list_users') {
		$stmt = $pdo->query('SELECT id, name, email, role, is_active FROM users WHERE role IN ("customer", "admin") ORDER BY id ASC');
		sendJson(['ok' => true, 'data' => $stmt->fetchAll()]);
	}

	// List all customers (admin)
	if ($action === 'admin_list_customers') {
		$stmt = $pdo->query('
			SELECT u.id, u.name, u.email, u.role, u.is_active, COUNT(o.id) as total_orders
			FROM users u
			LEFT JOIN orders o ON o.customer_id = u.id
			WHERE u.role = "customer"
			GROUP BY u.id
			ORDER BY u.id DESC
		');
		sendJson(['ok' => true, 'data' => $stmt->fetchAll()]);
	}

	sendJson(['ok' => false, 'message' => 'Endpoint tidak ditemukan'], 404);

} catch (Throwable $e) {
	sendJson([
		'ok' => false,
		'message' => 'Terjadi error pada server',
		'detail' => $e->getMessage(),
	], 500);
}

function getTopups(PDO $pdo): array
{
	if (hasTopupExtendedColumns($pdo)) {
		$stmt = $pdo->query('SELECT id, game_title, package_name, platform, value_amount, value_unit, diamonds_amount, price, stock FROM topup_products ORDER BY created_at DESC');
		$rows = $stmt->fetchAll();
		return array_map(static function ($row) {
			if ((int)$row['value_amount'] <= 0) {
				$row['value_amount'] = (int)$row['diamonds_amount'];
			}
			if (trim((string)$row['value_unit']) === '') {
				$row['value_unit'] = 'Diamonds';
			}
			if (trim((string)$row['platform']) === '') {
				$row['platform'] = 'Mobile';
			}
			return $row;
		}, $rows);
	}

	$stmt = $pdo->query('SELECT id, game_title, package_name, diamonds_amount, price, stock FROM topup_products ORDER BY created_at DESC');
	$rows = $stmt->fetchAll();
	return array_map(static function ($row) {
		$row['platform'] = 'Mobile';
		$row['value_amount'] = (int)$row['diamonds_amount'];
		$row['value_unit'] = 'Diamonds';
		return $row;
	}, $rows);
}

function hasGamesRatingColumn(PDO $pdo): bool
{
	static $hasColumn = null;
	if ($hasColumn !== null) {
		return $hasColumn;
	}

	$stmt = $pdo->query("SHOW COLUMNS FROM games LIKE 'rating'");
	$hasColumn = (bool)$stmt->fetch();
	return $hasColumn;
}

function hasTopupExtendedColumns(PDO $pdo): bool
{
	static $hasColumns = null;
	if ($hasColumns !== null) {
		return $hasColumns;
	}

	$required = ['platform', 'value_amount', 'value_unit'];
	$ok = 0;
	foreach ($required as $col) {
		$stmt = $pdo->query("SHOW COLUMNS FROM topup_products LIKE '" . $col . "'");
		if ($stmt->fetch()) {
			$ok += 1;
		}
	}

	$hasColumns = ($ok === count($required));
	return $hasColumns;
}

function parseJsonFromText(string $text): ?array
{
	$decoded = json_decode($text, true);
	if (is_array($decoded)) {
		return $decoded;
	}

	if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
		$decoded = json_decode($matches[0], true);
		if (is_array($decoded)) {
			return $decoded;
		}
	}

	return null;
}

function fallbackMatchGames(array $games, string $prompt): array
{
	$prompt = mb_strtolower($prompt);
	$tokens = preg_split('/\s+/', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $prompt));
	$tokens = array_filter($tokens, static function ($t) {
		return mb_strlen($t) >= 3;
	});

	$scored = [];
	foreach ($games as $game) {
		$haystack = mb_strtolower($game['title'] . ' ' . $game['genre'] . ' ' . $game['platform'] . ' ' . $game['description']);
		$score = 0;
		foreach ($tokens as $token) {
			if (mb_strpos($haystack, $token) !== false) {
				$score += 1;
			}
		}

		if ($score > 0) {
			$scored[] = ['id' => (int)$game['id'], 'score' => $score];
		}
	}

	usort($scored, static function ($a, $b) {
		return $b['score'] <=> $a['score'];
	});

	$ids = array_slice(array_column($scored, 'id'), 0, 4);
	if (!$ids && count($games) > 0) {
		$ids = [(int)$games[0]['id']];
	}

	return $ids;
}

function agentFilterGameIds(PDO $pdo, string $prompt): array
{
	$games = getGames($pdo, '');
	if (!$games) {
		return ['ids' => [], 'reason' => 'Belum ada data game'];
	}

	$gameList = array_map(static function ($g) {
		return [
			'id' => (int)$g['id'],
			'title' => $g['title'],
			'genre' => $g['genre'],
			'platform' => $g['platform'],
			'price' => (float)$g['price'],
			'stock' => (int)$g['stock'],
		];
	}, $games);

	$systemPrompt = "Anda adalah AI shopping agent untuk toko game.\n" .
		"Tugas: pilih maksimal 4 game paling relevan berdasarkan prompt user.\n" .
		"Kembalikan HANYA JSON valid dengan format:\n" .
		"{\"game_ids\":[1,2],\"reason\":\"alasan singkat\"}.\n" .
		"Jangan sertakan teks lain di luar JSON.";

	$userPrompt = "Prompt pengguna: " . $prompt . "\n\nDaftar game:\n" . json_encode($gameList, JSON_UNESCAPED_UNICODE);
	$raw = callOllama($systemPrompt . "\n\n" . $userPrompt);

	if ($raw !== null) {
		$parsed = parseJsonFromText($raw);
		if (is_array($parsed) && isset($parsed['game_ids']) && is_array($parsed['game_ids'])) {
			$validIds = array_map('intval', $parsed['game_ids']);
			$validIds = array_values(array_unique(array_filter($validIds, static function ($id) {
				return $id > 0;
			})));

			if ($validIds) {
				return [
					'ids' => array_slice($validIds, 0, 4),
					'reason' => isset($parsed['reason']) ? (string)$parsed['reason'] : 'Dipilih AI agent',
					'mode' => 'ollama',
					'raw' => $raw,
				];
			}
		}
	}

	return [
		'ids' => fallbackMatchGames($games, $prompt),
		'reason' => 'Fallback pencocokan lokal karena Ollama tidak tersedia/format output tidak valid',
		'mode' => 'fallback',
		'raw' => $raw,
	];
}

function callOllamaStrict(string $prompt): array
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
	$curlErr = curl_error($ch);
	$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($response === false || $curlErr) {
		return ['ok' => false, 'error' => 'Curl error: ' . ($curlErr ?: 'unknown')];
	}

	$data = json_decode($response, true);
	if (!is_array($data)) {
		return ['ok' => false, 'error' => 'Respons Ollama bukan JSON valid'];
	}

	if ($httpCode >= 400) {
		return ['ok' => false, 'error' => isset($data['error']) ? (string)$data['error'] : ('HTTP ' . $httpCode)];
	}

	if (isset($data['error']) && $data['error'] !== '') {
		return ['ok' => false, 'error' => (string)$data['error']];
	}

	if (!isset($data['response'])) {
		return ['ok' => false, 'error' => 'Field response dari Ollama tidak ditemukan'];
	}

	return ['ok' => true, 'response' => trim((string)$data['response'])];
}

function parseSellerPromptStrictByAgent(string $prompt): array
{
	$systemPrompt = "Anda adalah AI asisten penjual game.\n" .
		"Ekstrak prompt menjadi data terstruktur JSON.\n" .
		"Pilih target_type: game/topup/requirement.\n" .
		"Kembalikan HANYA JSON valid format:\n" .
		"{\"target_type\":\"game\",\"data\":{...},\"reason\":\"...\"}.\n" .
		"Untuk game gunakan field: title, genre, platform, price, stock, rating, image_url, description.\n" .
		"Untuk topup gunakan field: game_title, package_name, platform, value_amount, value_unit, diamonds_amount, price, stock.\n" .
		"Untuk requirement gunakan field: requirement_name, description, qty_estimate, cost_estimate.\n" .
		"Jangan output markdown/code block, hanya JSON.";

	$ollama = callOllamaStrict($systemPrompt . "\n\nPrompt: " . $prompt);
	if (!$ollama['ok']) {
		throw new RuntimeException('Ollama error: ' . $ollama['error']);
	}

	$raw = (string)$ollama['response'];

	$parsed = parseJsonFromText($raw);
	if (!is_array($parsed) || !isset($parsed['target_type']) || !isset($parsed['data']) || !is_array($parsed['data'])) {
		$repairPrompt = "Ubah teks berikut menjadi JSON valid TANPA teks tambahan. " .
			"Pertahankan schema: {\"target_type\":\"game|topup|requirement\",\"data\":{...},\"reason\":\"...\"}.\n\n" .
			"Teks sumber:\n" . $raw;

		$repair = callOllamaStrict($repairPrompt);
		if ($repair['ok']) {
			$parsed = parseJsonFromText((string)$repair['response']);
		}
	}

	if (!is_array($parsed) || !isset($parsed['target_type']) || !isset($parsed['data']) || !is_array($parsed['data'])) {
		throw new RuntimeException('Output AI tidak valid setelah retry. Coba prompt lebih terstruktur: nama, genre/platform, harga, stok.');
	}

	$type = (string)$parsed['target_type'];
	if (!in_array($type, ['game', 'topup', 'requirement'], true)) {
		throw new RuntimeException('target_type dari AI tidak didukung.');
	}

	return [
		'target_type' => $type,
		'data' => $parsed['data'],
		'reason' => isset($parsed['reason']) ? (string)$parsed['reason'] : 'Diekstrak oleh agent',
		'mode' => 'ollama',
	];
}

function executeSellerParsedObject(PDO $pdo, int $sellerId, array $parsed): array
{
	$type = (string)$parsed['target_type'];
	$d = $parsed['data'];

	if ($type === 'game') {
		$title = trim((string)($d['title'] ?? ''));
		$genre = trim((string)($d['genre'] ?? ''));
		$platform = trim((string)($d['platform'] ?? ''));
		$price = (float)($d['price'] ?? 0);
		$stock = (int)($d['stock'] ?? 0);
		$rating = (float)($d['rating'] ?? 0);
		$imageUrl = trim((string)($d['image_url'] ?? ''));
		$description = trim((string)($d['description'] ?? ''));

		if ($title === '' || $genre === '' || $platform === '' || $price <= 0) {
			throw new RuntimeException('Data game dari AI belum lengkap. Tambahkan nama, genre, platform, harga.');
		}

		if (hasGamesRatingColumn($pdo)) {
			$stmt = $pdo->prepare('INSERT INTO games (seller_id, title, genre, platform, price, stock, rating, image_url, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $title, $genre, $platform, $price, $stock, max(0, min(5, $rating)), $imageUrl, $description]);
		} else {
			$descriptionWithRating = $description;
			if ($rating > 0) {
				$descriptionWithRating = trim($description . "\nRating: " . max(0, min(5, $rating)));
			}
			$stmt = $pdo->prepare('INSERT INTO games (seller_id, title, genre, platform, price, stock, image_url, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $title, $genre, $platform, $price, $stock, $imageUrl, $descriptionWithRating]);
		}

		return ['target_type' => 'game', 'name' => $title];
	}

	if ($type === 'topup') {
		$gameTitle = trim((string)($d['game_title'] ?? ''));
		$packageName = trim((string)($d['package_name'] ?? ''));
		$platform = trim((string)($d['platform'] ?? ''));
		$valueAmount = (int)($d['value_amount'] ?? 0);
		$valueUnit = trim((string)($d['value_unit'] ?? ''));
		$diamonds = (int)($d['diamonds_amount'] ?? $valueAmount);
		$price = (float)($d['price'] ?? 0);
		$stock = (int)($d['stock'] ?? 0);

		if ($valueAmount <= 0) {
			$valueAmount = max(0, $diamonds);
		}
		if ($diamonds <= 0) {
			$diamonds = max(0, $valueAmount);
		}
		if ($valueUnit === '') {
			$valueUnit = 'Diamonds';
		}
		if ($platform === '') {
			$platform = 'Mobile';
		}

		if ($gameTitle === '' || $packageName === '' || $valueAmount <= 0 || $price <= 0) {
			throw new RuntimeException('Data top up dari AI belum lengkap. Tambahkan nama game, paket, nominal, dan harga.');
		}

		if (hasTopupExtendedColumns($pdo)) {
			$stmt = $pdo->prepare('INSERT INTO topup_products (seller_id, game_title, package_name, platform, value_amount, value_unit, diamonds_amount, price, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $gameTitle, $packageName, $platform, $valueAmount, $valueUnit, $diamonds, $price, $stock]);
		} else {
			$stmt = $pdo->prepare('INSERT INTO topup_products (seller_id, game_title, package_name, diamonds_amount, price, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $gameTitle, $packageName, $diamonds, $price, $stock]);
		}

		return ['target_type' => 'topup', 'name' => $gameTitle . ' - ' . $packageName];
	}

	if ($type === 'requirement') {
		$name = trim((string)($d['requirement_name'] ?? ''));
		$description = trim((string)($d['description'] ?? ''));
		$qty = (int)($d['qty_estimate'] ?? 1);
		$cost = (float)($d['cost_estimate'] ?? 0);

		if ($name === '') {
			throw new RuntimeException('Data kebutuhan dari AI belum lengkap. Tambahkan nama kebutuhan.');
		}

		$stmt = $pdo->prepare('INSERT INTO seller_requirements (seller_id, requirement_name, description, qty_estimate, cost_estimate, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
		$stmt->execute([$sellerId, $name, $description, max(1, $qty), max(0, $cost)]);

		return ['target_type' => 'requirement', 'name' => $name];
	}

	throw new RuntimeException('Objek dari AI tidak dikenali.');
}

function addGameToCart(PDO $pdo, int $userId, int $gameId, int $qty = 1): void
{
	$qty = max(1, $qty);

	$check = $pdo->prepare('SELECT id, qty FROM cart_items WHERE user_id = ? AND item_type = "game" AND game_id = ? LIMIT 1');
	$check->execute([$userId, $gameId]);
	$exists = $check->fetch();

	if ($exists) {
		$upd = $pdo->prepare('UPDATE cart_items SET qty = qty + ?, updated_at = NOW() WHERE id = ?');
		$upd->execute([$qty, $exists['id']]);
		return;
	}

	$ins = $pdo->prepare('INSERT INTO cart_items (user_id, item_type, game_id, qty, created_at, updated_at) VALUES (?, "game", ?, ?, NOW(), NOW())');
	$ins->execute([$userId, $gameId, $qty]);
}

function addTopupToCart(PDO $pdo, int $userId, int $topupId, int $qty = 1): void
{
	$qty = max(1, $qty);

	$check = $pdo->prepare('SELECT id FROM cart_items WHERE user_id = ? AND item_type = "topup" AND topup_id = ? LIMIT 1');
	$check->execute([$userId, $topupId]);
	$exists = $check->fetch();

	if ($exists) {
		$upd = $pdo->prepare('UPDATE cart_items SET qty = qty + ?, updated_at = NOW() WHERE id = ?');
		$upd->execute([$qty, $exists['id']]);
		return;
	}

	$ins = $pdo->prepare('INSERT INTO cart_items (user_id, item_type, topup_id, qty, created_at, updated_at) VALUES (?, "topup", ?, ?, NOW(), NOW())');
	$ins->execute([$userId, $topupId, $qty]);
}

function getCart(PDO $pdo, int $userId): array
{
	$sql = 'SELECT
				c.id,
				c.item_type,
				c.qty,
				g.id AS game_id,
				g.title AS game_title,
				g.price AS game_price,
				t.id AS topup_id,
				t.game_title AS topup_game,
				t.package_name,
				t.price AS topup_price
			FROM cart_items c
			LEFT JOIN games g ON g.id = c.game_id
			LEFT JOIN topup_products t ON t.id = c.topup_id
			WHERE c.user_id = ?
			ORDER BY c.created_at DESC';
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$userId]);
	$rows = $stmt->fetchAll();

	$items = [];
	$total = 0;
	foreach ($rows as $row) {
		if ($row['item_type'] === 'game') {
			$name = $row['game_title'];
			$price = (float)$row['game_price'];
		} else {
			$name = $row['topup_game'] . ' - ' . $row['package_name'];
			$price = (float)$row['topup_price'];
		}

		$subtotal = $price * (int)$row['qty'];
		$total += $subtotal;

		$items[] = [
			'cart_id' => (int)$row['id'],
			'item_type' => $row['item_type'],
			'name' => $name,
			'qty' => (int)$row['qty'],
			'price' => $price,
			'subtotal' => $subtotal,
		];
	}

	return ['items' => $items, 'total' => $total];
}

try {
	$pdo = getPDO();
	$action = $_GET['action'] ?? '';
	$input = getJsonInput();

	if ($action === 'seed_status') {
		$counts = [
			'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
			'games' => (int)$pdo->query('SELECT COUNT(*) FROM games')->fetchColumn(),
			'topups' => (int)$pdo->query('SELECT COUNT(*) FROM topup_products')->fetchColumn(),
		];
		sendJson(['ok' => true, 'data' => $counts]);
	}

	if ($action === 'list_users') {
		$rows = $pdo->query('SELECT id, name, role FROM users ORDER BY id ASC')->fetchAll();
		sendJson(['ok' => true, 'data' => $rows]);
	}

	if ($action === 'list_games') {
		$keyword = trim((string)($_GET['q'] ?? ''));
		sendJson(['ok' => true, 'data' => getGames($pdo, $keyword)]);
	}

	if ($action === 'list_topups') {
		sendJson(['ok' => true, 'data' => getTopups($pdo)]);
	}

	if ($action === 'agent_filter' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$prompt = trim((string)($input['prompt'] ?? ''));
		if ($prompt === '') {
			sendJson(['ok' => false, 'message' => 'Prompt wajib diisi'], 422);
		}

		$result = agentFilterGameIds($pdo, $prompt);
		if (!$result['ids']) {
			sendJson(['ok' => true, 'data' => ['games' => [], 'reason' => $result['reason'], 'mode' => $result['mode'] ?? 'fallback']]);
		}

		$placeholders = implode(',', array_fill(0, count($result['ids']), '?'));
		$stmt = $pdo->prepare("SELECT id, title, genre, platform, price, stock, image_url, description FROM games WHERE id IN ($placeholders)");
		$stmt->execute($result['ids']);
		$games = $stmt->fetchAll();

		sendJson([
			'ok' => true,
			'data' => [
				'games' => $games,
				'reason' => $result['reason'],
				'mode' => $result['mode'] ?? 'fallback',
			],
		]);
	}

	if ($action === 'agent_add_to_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$userId = (int)($input['user_id'] ?? 0);
		$prompt = trim((string)($input['prompt'] ?? ''));
		$qtyEach = max(1, (int)($input['qty_each'] ?? 1));

		if ($userId <= 0 || $prompt === '') {
			sendJson(['ok' => false, 'message' => 'user_id dan prompt wajib diisi'], 422);
		}

		requireRole($pdo, $userId, 'buyer');

		$result = agentFilterGameIds($pdo, $prompt);
		$ids = $result['ids'];

		if (!$ids) {
			sendJson(['ok' => false, 'message' => 'Tidak ada game yang cocok untuk dimasukkan ke keranjang'], 404);
		}

		foreach ($ids as $gameId) {
			addGameToCart($pdo, $userId, (int)$gameId, $qtyEach);
		}

		sendJson([
			'ok' => true,
			'message' => 'Agent berhasil memasukkan game ke keranjang',
			'data' => [
				'inserted_game_ids' => $ids,
				'reason' => $result['reason'],
				'mode' => $result['mode'] ?? 'fallback',
			],
		]);
	}

	if ($action === 'add_game_to_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$userId = (int)($input['user_id'] ?? 0);
		$gameId = (int)($input['game_id'] ?? 0);
		$qty = max(1, (int)($input['qty'] ?? 1));
		requireRole($pdo, $userId, 'buyer');

		addGameToCart($pdo, $userId, $gameId, $qty);
		sendJson(['ok' => true, 'message' => 'Game ditambahkan ke keranjang']);
	}

	if ($action === 'add_topup_to_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$userId = (int)($input['user_id'] ?? 0);
		$topupId = (int)($input['topup_id'] ?? 0);
		$qty = max(1, (int)($input['qty'] ?? 1));
		requireRole($pdo, $userId, 'buyer');

		addTopupToCart($pdo, $userId, $topupId, $qty);
		sendJson(['ok' => true, 'message' => 'Top up ditambahkan ke keranjang']);
	}

	if ($action === 'get_cart') {
		$userId = (int)($_GET['user_id'] ?? 0);
		if ($userId <= 0) {
			sendJson(['ok' => false, 'message' => 'user_id wajib diisi'], 422);
		}
		requireRole($pdo, $userId, 'buyer');

		sendJson(['ok' => true, 'data' => getCart($pdo, $userId)]);
	}

	if ($action === 'clear_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$userId = (int)($input['user_id'] ?? 0);
		requireRole($pdo, $userId, 'buyer');
		$stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
		$stmt->execute([$userId]);
		sendJson(['ok' => true, 'message' => 'Keranjang dikosongkan']);
	}

	if ($action === 'seller_add_game' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$sellerId = (int)($input['seller_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');

		$title = trim((string)($input['title'] ?? ''));
		$genre = trim((string)($input['genre'] ?? ''));
		$platform = trim((string)($input['platform'] ?? ''));
		$price = (float)($input['price'] ?? 0);
		$stock = (int)($input['stock'] ?? 0);
		$rating = (float)($input['rating'] ?? 0);
		$imageUrl = trim((string)($input['image_url'] ?? ''));
		$description = trim((string)($input['description'] ?? ''));

		if ($title === '' || $genre === '' || $platform === '' || $price <= 0) {
			sendJson(['ok' => false, 'message' => 'Data game belum lengkap'], 422);
		}

		if (hasGamesRatingColumn($pdo)) {
			$stmt = $pdo->prepare('INSERT INTO games (seller_id, title, genre, platform, price, stock, rating, image_url, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $title, $genre, $platform, $price, $stock, max(0, min(5, $rating)), $imageUrl, $description]);
		} else {
			$descriptionWithRating = $description;
			if ($rating > 0) {
				$descriptionWithRating = trim($description . "\nRating: " . max(0, min(5, $rating)));
			}
			$stmt = $pdo->prepare('INSERT INTO games (seller_id, title, genre, platform, price, stock, image_url, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $title, $genre, $platform, $price, $stock, $imageUrl, $descriptionWithRating]);
		}

		sendJson(['ok' => true, 'message' => 'Game berhasil ditambahkan']);
	}

	if ($action === 'seller_add_topup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$sellerId = (int)($input['seller_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');

		$gameTitle = trim((string)($input['game_title'] ?? ''));
		$packageName = trim((string)($input['package_name'] ?? ''));
		$platform = trim((string)($input['platform'] ?? ''));
		$valueAmount = (int)($input['value_amount'] ?? 0);
		$valueUnit = trim((string)($input['value_unit'] ?? ''));
		$diamonds = (int)($input['diamonds_amount'] ?? $valueAmount);
		$price = (float)($input['price'] ?? 0);
		$stock = (int)($input['stock'] ?? 0);

		if ($valueAmount <= 0) {
			$valueAmount = max(0, $diamonds);
		}
		if ($diamonds <= 0) {
			$diamonds = max(0, $valueAmount);
		}
		if ($valueUnit === '') {
			$valueUnit = 'Diamonds';
		}
		if ($platform === '') {
			$platform = 'Mobile';
		}

		if ($gameTitle === '' || $packageName === '' || $valueAmount <= 0 || $price <= 0) {
			sendJson(['ok' => false, 'message' => 'Data top up belum lengkap'], 422);
		}

		if (hasTopupExtendedColumns($pdo)) {
			$stmt = $pdo->prepare('INSERT INTO topup_products (seller_id, game_title, package_name, platform, value_amount, value_unit, diamonds_amount, price, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $gameTitle, $packageName, $platform, $valueAmount, $valueUnit, $diamonds, $price, $stock]);
		} else {
			$stmt = $pdo->prepare('INSERT INTO topup_products (seller_id, game_title, package_name, diamonds_amount, price, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$sellerId, $gameTitle, $packageName, $diamonds, $price, $stock]);
		}

		sendJson(['ok' => true, 'message' => 'Produk top up berhasil ditambahkan']);
	}

	if ($action === 'seller_add_requirement' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$sellerId = (int)($input['seller_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');

		$name = trim((string)($input['requirement_name'] ?? ''));
		$description = trim((string)($input['description'] ?? ''));
		$qty = (int)($input['qty_estimate'] ?? 1);
		$cost = (float)($input['cost_estimate'] ?? 0);

		if ($name === '') {
			sendJson(['ok' => false, 'message' => 'Nama kebutuhan wajib diisi'], 422);
		}

		$stmt = $pdo->prepare('INSERT INTO seller_requirements (seller_id, requirement_name, description, qty_estimate, cost_estimate, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
		$stmt->execute([$sellerId, $name, $description, max(1, $qty), max(0, $cost)]);

		sendJson(['ok' => true, 'message' => 'Kebutuhan penjual berhasil disimpan']);
	}

	if ($action === 'seller_requirements') {
		$sellerId = (int)($_GET['seller_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');
		$stmt = $pdo->prepare('SELECT id, requirement_name, description, qty_estimate, cost_estimate, created_at FROM seller_requirements WHERE seller_id = ? ORDER BY created_at DESC');
		$stmt->execute([$sellerId]);
		sendJson(['ok' => true, 'data' => $stmt->fetchAll()]);
	}

	if (($action === 'seller_agent_command' || $action === 'seller_parse_prompt') && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$sellerId = (int)($input['seller_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');
		$prompt = trim((string)($input['prompt'] ?? ''));
		if ($prompt === '') {
			sendJson(['ok' => false, 'message' => 'Prompt seller wajib diisi'], 422);
		}

		$parsed = parseSellerPromptStrictByAgent($prompt);
		$created = executeSellerParsedObject($pdo, $sellerId, $parsed);

		sendJson([
			'ok' => true,
			'message' => 'Perintah prompt berhasil dijalankan dan disimpan ke database',
			'data' => [
				'target_type' => $created['target_type'],
				'name' => $created['name'],
				'mode' => 'ollama',
				'reason' => $parsed['reason'],
			],
		]);
	}

	if ($action === 'seller_objects') {
		$sellerId = (int)($_GET['seller_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');

		$gamesStmt = $pdo->prepare('SELECT id, title, genre, platform, price, stock, image_url, description, created_at FROM games WHERE seller_id = ? ORDER BY created_at DESC');
		$gamesStmt->execute([$sellerId]);

		if (hasTopupExtendedColumns($pdo)) {
			$topupsStmt = $pdo->prepare('SELECT id, game_title, package_name, platform, value_amount, value_unit, diamonds_amount, price, stock, created_at FROM topup_products WHERE seller_id = ? ORDER BY created_at DESC');
			$topupsStmt->execute([$sellerId]);
			$topupRows = $topupsStmt->fetchAll();
			$topupRows = array_map(static function ($row) {
				if ((int)$row['value_amount'] <= 0) {
					$row['value_amount'] = (int)$row['diamonds_amount'];
				}
				if (trim((string)$row['value_unit']) === '') {
					$row['value_unit'] = 'Diamonds';
				}
				if (trim((string)$row['platform']) === '') {
					$row['platform'] = 'Mobile';
				}
				return $row;
			}, $topupRows);
		} else {
			$topupsStmt = $pdo->prepare('SELECT id, game_title, package_name, diamonds_amount, price, stock, created_at FROM topup_products WHERE seller_id = ? ORDER BY created_at DESC');
			$topupsStmt->execute([$sellerId]);
			$topupRows = array_map(static function ($row) {
				$row['platform'] = 'Mobile';
				$row['value_amount'] = (int)$row['diamonds_amount'];
				$row['value_unit'] = 'Diamonds';
				return $row;
			}, $topupsStmt->fetchAll());
		}

		$reqStmt = $pdo->prepare('SELECT id, requirement_name, description, qty_estimate, cost_estimate, created_at FROM seller_requirements WHERE seller_id = ? ORDER BY created_at DESC');
		$reqStmt->execute([$sellerId]);

		sendJson([
			'ok' => true,
			'data' => [
				'games' => $gamesStmt->fetchAll(),
				'topups' => $topupRows,
				'requirements' => $reqStmt->fetchAll(),
			],
		]);
	}

	if ($action === 'seller_delete_game' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$sellerId = (int)($input['seller_id'] ?? 0);
		$gameId = (int)($input['game_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');

		$stmt = $pdo->prepare('DELETE FROM games WHERE id = ? AND seller_id = ?');
		$stmt->execute([$gameId, $sellerId]);
		sendJson(['ok' => true, 'message' => 'Game berhasil dihapus']);
	}

	if ($action === 'seller_delete_topup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$sellerId = (int)($input['seller_id'] ?? 0);
		$topupId = (int)($input['topup_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');

		$stmt = $pdo->prepare('DELETE FROM topup_products WHERE id = ? AND seller_id = ?');
		$stmt->execute([$topupId, $sellerId]);
		sendJson(['ok' => true, 'message' => 'Top up berhasil dihapus']);
	}

	if ($action === 'seller_delete_requirement' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$sellerId = (int)($input['seller_id'] ?? 0);
		$requirementId = (int)($input['requirement_id'] ?? 0);
		requireRole($pdo, $sellerId, 'seller');

		$stmt = $pdo->prepare('DELETE FROM seller_requirements WHERE id = ? AND seller_id = ?');
		$stmt->execute([$requirementId, $sellerId]);
		sendJson(['ok' => true, 'message' => 'Kebutuhan berhasil dihapus']);
	}

	sendJson(['ok' => false, 'message' => 'Endpoint tidak ditemukan'], 404);
} catch (Throwable $e) {
	sendJson([
		'ok' => false,
		'message' => 'Terjadi error pada server',
		'detail' => $e->getMessage(),
	], 500);
}

