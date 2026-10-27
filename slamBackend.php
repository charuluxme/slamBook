<?php
session_start();
require_once 'db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

function respondJson(bool $success, string $message, array $data = [], int $statusCode = 200): void
{
	http_response_code($statusCode);
	echo json_encode([
		'success' => $success,
		'message' => $message,
		'data' => $data
	]);
	exit();
}

function sanitizeText(string $value): string
{
	return trim($value);
}

function bindParamsDynamic(mysqli_stmt $stmt, string $types, array $params): void
{
	if ($types === '' || empty($params)) {
		return;
	}

	$bindArgs = [$stmt, $types];
	foreach ($params as $index => $value) {
		$bindArgs[] = &$params[$index];
	}

	call_user_func_array('mysqli_stmt_bind_param', $bindArgs);
}

function buildMemoryFilterClause(string $filterRole, string $searchValue): array
{
	$whereClause = ' WHERE 1=1';
	$types = '';
	$params = [];

	if ($filterRole !== '' && $filterRole !== 'All') {
		$whereClause .= ' AND relationType = ?';
		$types .= 's';
		$params[] = $filterRole;
	}

	if ($searchValue !== '') {
		$whereClause .= ' AND (writerName LIKE ? OR relationType LIKE ? OR nickName LIKE ? OR firstImpression LIKE ? OR favoriteMemory LIKE ? OR learnedThing LIKE ? OR futureMessage LIKE ? OR contactInfo LIKE ? OR batchYear LIKE ? OR dateOfBirth LIKE ?)';
		$searchParam = '%' . $searchValue . '%';
		$types .= 'ssssssssss';
		for ($i = 0; $i < 10; $i++) {
			$params[] = $searchParam;
		}
	}

	return [$whereClause, $types, $params];
}

function buildMonthlyTrendMap(array $labels): array
{
	$trendMap = [];
	foreach ($labels as $label) {
		$trendMap[$label] = 0;
	}
	return $trendMap;
}

function ensureDefaultAdmin(mysqli $conn): void
{
	$defaultUserName = 'admin';
	$checkStmt = mysqli_prepare($conn, 'SELECT adminId FROM adminUsers WHERE userName = ? LIMIT 1');
	mysqli_stmt_bind_param($checkStmt, 's', $defaultUserName);
	mysqli_stmt_execute($checkStmt);
	$result = mysqli_stmt_get_result($checkStmt);
	$row = mysqli_fetch_assoc($result);
	mysqli_stmt_close($checkStmt);

	if ($row) {
		return;
	}

	$defaultPasswordHash = password_hash('slam@2026', PASSWORD_DEFAULT);
	$insertStmt = mysqli_prepare($conn, 'INSERT INTO adminUsers (userName, passwordHash, status) VALUES (?, ?, "active")');
	mysqli_stmt_bind_param($insertStmt, 'ss', $defaultUserName, $defaultPasswordHash);
	mysqli_stmt_execute($insertStmt);
	mysqli_stmt_close($insertStmt);
}

function validateImageUpload(array $photoFile): array
{
	if (!isset($photoFile['error']) || $photoFile['error'] !== UPLOAD_ERR_OK) {
		return [false, 'Photo upload is required.'];
	}

	if (($photoFile['size'] ?? 0) > (5 * 1024 * 1024)) {
		return [false, 'Photo size must be 5MB or less.'];
	}

	$tmpName = $photoFile['tmp_name'] ?? '';
	$imageInfo = @getimagesize($tmpName);
	if (!$imageInfo) {
		return [false, 'Only valid image files are allowed.'];
	}

	$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
	if (!in_array($imageInfo['mime'], $allowedMimeTypes, true)) {
		return [false, 'Only JPG, PNG, and WEBP images are allowed.'];
	}

	return [true, ''];
}

function saveUploadedPhoto(array $photoFile): string
{
	$uploadDir = 'uploads/writerPhotos/';
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir, 0775, true);
	}

	$originalName = $photoFile['name'] ?? '';
	$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
	if ($extension === '') {
		$extension = 'jpg';
	}

	$safeName = 'writerPhoto_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
	$targetPath = $uploadDir . $safeName;

	if (!move_uploaded_file($photoFile['tmp_name'], $targetPath)) {
		respondJson(false, 'Failed to store uploaded photo.', [], 500);
	}

	return $targetPath;
}

ensureDefaultAdmin($conn);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$validRelationTypes = ['Friend', 'Junior', 'Classmate', 'Colleague', 'Faculty'];

$csrfProtectedActions = ['admin_login', 'admin_logout', 'submit_memory'];
if ($requestMethod === 'POST' && in_array($action, $csrfProtectedActions, true)) {
	requireCsrfTokenJson();
}

switch ($action) {
	case 'admin_login':
		$userName = sanitizeText($_POST['userName'] ?? '');
		$password = $_POST['password'] ?? '';

		if ($userName === '' || $password === '') {
			respondJson(false, 'Both username and password are required.', [], 422);
		}

		$stmt = mysqli_prepare($conn, 'SELECT adminId, userName, passwordHash, status FROM adminUsers WHERE userName = ? LIMIT 1');
		mysqli_stmt_bind_param($stmt, 's', $userName);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$admin = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);

		if (!$admin || !password_verify($password, $admin['passwordHash'])) {
			respondJson(false, 'Invalid username or password.', [], 401);
		}

		if ($admin['status'] !== 'active') {
			respondJson(false, 'Your admin account is inactive.', [], 403);
		}

		$_SESSION['adminId'] = (int)$admin['adminId'];
		$_SESSION['adminUserName'] = $admin['userName'];
		$_SESSION['adminLoginTime'] = time();

		respondJson(true, 'Login successful.', [
			'redirectUrl' => 'adminDashboard.php'
		]);
		break;

	case 'admin_logout':
		session_unset();
		session_destroy();
		respondJson(true, 'Logged out successfully.', [
			'redirectUrl' => 'adminLogin.php'
		]);
		break;

	case 'check_admin_session':
		if (!isAdminLoggedIn()) {
			respondJson(false, 'Not authenticated.', [
				'isLoggedIn' => false
			], 401);
		}

		respondJson(true, 'Session active.', [
			'isLoggedIn' => true,
			'adminUserName' => $_SESSION['adminUserName']
		]);
		break;

	case 'submit_memory':
		$writerName = sanitizeText($_POST['name'] ?? '');
		$relationType = sanitizeText($_POST['relation'] ?? '');
		$dateOfBirth = sanitizeText($_POST['dateOfBirth'] ?? '');
		$nickName = sanitizeText($_POST['nickname'] ?? '');
		$firstImpression = sanitizeText($_POST['impression'] ?? '');
		$favoriteMemory = sanitizeText($_POST['memory'] ?? '');
		$learnedThing = sanitizeText($_POST['learning'] ?? '');
		$futureMessage = sanitizeText($_POST['futureMessage'] ?? '');
		$contactInfo = sanitizeText($_POST['contact'] ?? '');
		$batchYear = sanitizeText($_POST['batch'] ?? '');
		$consent = isset($_POST['consent']) ? $_POST['consent'] : '';

		if (
			$writerName === '' ||
			$relationType === '' ||
			$dateOfBirth === '' ||
			$nickName === '' ||
			$firstImpression === '' ||
			$favoriteMemory === '' ||
			$learnedThing === '' ||
			$futureMessage === '' ||
			$contactInfo === '' ||
			$batchYear === '' ||
			$consent === ''
		) {
			respondJson(false, 'All writer fields are mandatory.', [], 422);
		}

		if (!in_array($relationType, $validRelationTypes, true)) {
			respondJson(false, 'Invalid relation type selected.', [], 422);
		}

		$dateCheck = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
		if (!$dateCheck || $dateCheck->format('Y-m-d') !== $dateOfBirth) {
			respondJson(false, 'Invalid date of birth format.', [], 422);
		}

		if (!isset($_FILES['photo'])) {
			respondJson(false, 'Photo upload is required.', [], 422);
		}

		[$isValidPhoto, $photoError] = validateImageUpload($_FILES['photo']);
		if (!$isValidPhoto) {
			respondJson(false, $photoError, [], 422);
		}

		$photoPath = saveUploadedPhoto($_FILES['photo']);

		$insertQuery = 'INSERT INTO slamMemories (writerName, relationType, dateOfBirth, photoPath, nickName, firstImpression, favoriteMemory, learnedThing, futureMessage, contactInfo, batchYear) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$stmt = mysqli_prepare($conn, $insertQuery);
		mysqli_stmt_bind_param(
			$stmt,
			'sssssssssss',
			$writerName,
			$relationType,
			$dateOfBirth,
			$photoPath,
			$nickName,
			$firstImpression,
			$favoriteMemory,
			$learnedThing,
			$futureMessage,
			$contactInfo,
			$batchYear
		);

		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			respondJson(false, 'Failed to save memory. Please try again.', [], 500);
		}

		$memoryId = mysqli_insert_id($conn);
		mysqli_stmt_close($stmt);

		respondJson(true, 'Memory saved successfully.', [
			'memoryId' => $memoryId
		]);
		break;

	case 'fetch_memories':
		requireAdminSessionJson();

		$searchValue = sanitizeText($_GET['searchValue'] ?? $_POST['searchValue'] ?? '');
		$filterRole = sanitizeText($_GET['filterRole'] ?? $_POST['filterRole'] ?? 'All');
		if ($filterRole !== 'All' && !in_array($filterRole, $validRelationTypes, true)) {
			respondJson(false, 'Invalid filter role selected.', [], 422);
		}

		$page = (int)($_GET['page'] ?? $_POST['page'] ?? 1);
		$pageSize = (int)($_GET['pageSize'] ?? $_POST['pageSize'] ?? 12);
		$page = max($page, 1);
		$pageSize = max($pageSize, 1);
		$pageSize = min($pageSize, 50);

		[$whereClause, $types, $params] = buildMemoryFilterClause($filterRole, $searchValue);

		$countSql = 'SELECT COUNT(*) AS totalCount FROM slamMemories' . $whereClause;
		$countStmt = mysqli_prepare($conn, $countSql);
		bindParamsDynamic($countStmt, $types, $params);
		mysqli_stmt_execute($countStmt);
		$countResult = mysqli_stmt_get_result($countStmt);
		$totalCountRow = mysqli_fetch_assoc($countResult);
		mysqli_stmt_close($countStmt);
		$totalCount = (int)($totalCountRow['totalCount'] ?? 0);

		$totalPages = (int)max(1, (int)ceil($totalCount / $pageSize));
		if ($page > $totalPages) {
			$page = $totalPages;
		}
		$offset = ($page - 1) * $pageSize;

		$listSql = 'SELECT memoryId, writerName, relationType, dateOfBirth, photoPath, nickName, firstImpression, favoriteMemory, learnedThing, futureMessage, contactInfo, batchYear, createdAt FROM slamMemories' . $whereClause . ' ORDER BY createdAt DESC LIMIT ? OFFSET ?';
		$listStmt = mysqli_prepare($conn, $listSql);
		$listTypes = $types . 'ii';
		$listParams = $params;
		$listParams[] = $pageSize;
		$listParams[] = $offset;
		bindParamsDynamic($listStmt, $listTypes, $listParams);
		mysqli_stmt_execute($listStmt);
		$listResult = mysqli_stmt_get_result($listStmt);

		$memories = [];
		while ($row = mysqli_fetch_assoc($listResult)) {
			$memories[] = [
				'memoryId' => (int)$row['memoryId'],
				'name' => $row['writerName'],
				'relation' => $row['relationType'],
				'dateOfBirth' => $row['dateOfBirth'],
				'photoPath' => $row['photoPath'],
				'nickname' => $row['nickName'],
				'impression' => $row['firstImpression'],
				'memory' => $row['favoriteMemory'],
				'learning' => $row['learnedThing'],
				'futureMessage' => $row['futureMessage'],
				'contact' => $row['contactInfo'],
				'batch' => $row['batchYear'],
				'createdAt' => $row['createdAt']
			];
		}
		mysqli_stmt_close($listStmt);

		$statsSql = 'SELECT
			COUNT(*) AS totalCount,
			SUM(CASE WHEN relationType = "Friend" THEN 1 ELSE 0 END) AS friendCount,
			SUM(CASE WHEN relationType = "Junior" THEN 1 ELSE 0 END) AS juniorCount,
			SUM(CASE WHEN relationType = "Classmate" THEN 1 ELSE 0 END) AS classmateCount,
			SUM(CASE WHEN relationType = "Colleague" THEN 1 ELSE 0 END) AS colleagueCount,
			SUM(CASE WHEN relationType = "Faculty" THEN 1 ELSE 0 END) AS facultyCount,
			SUM(CASE WHEN YEAR(createdAt) = YEAR(CURRENT_DATE()) AND MONTH(createdAt) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS thisMonthCount
		FROM slamMemories' . $whereClause;

		$statsStmt = mysqli_prepare($conn, $statsSql);
		bindParamsDynamic($statsStmt, $types, $params);
		mysqli_stmt_execute($statsStmt);
		$statsResult = mysqli_stmt_get_result($statsStmt);
		$statsRow = mysqli_fetch_assoc($statsResult) ?: [];
		mysqli_stmt_close($statsStmt);

		$monthlyLabels = [];
		for ($i = 5; $i >= 0; $i--) {
			$monthlyLabels[] = date('Y-m', strtotime('-' . $i . ' month'));
		}
		$monthlyTrendMap = buildMonthlyTrendMap($monthlyLabels);

		$monthlySql = 'SELECT DATE_FORMAT(createdAt, "%Y-%m") AS monthKey, COUNT(*) AS monthCount FROM slamMemories' . $whereClause . ' AND createdAt >= DATE_SUB(DATE_FORMAT(CURRENT_DATE(), "%Y-%m-01"), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(createdAt, "%Y-%m")';
		$monthlyStmt = mysqli_prepare($conn, $monthlySql);
		bindParamsDynamic($monthlyStmt, $types, $params);
		mysqli_stmt_execute($monthlyStmt);
		$monthlyResult = mysqli_stmt_get_result($monthlyStmt);
		while ($row = mysqli_fetch_assoc($monthlyResult)) {
			$key = $row['monthKey'];
			if (isset($monthlyTrendMap[$key])) {
				$monthlyTrendMap[$key] = (int)$row['monthCount'];
			}
		}
		mysqli_stmt_close($monthlyStmt);

		$monthlyTrendLabels = [];
		$monthlyTrendCounts = [];
		foreach ($monthlyLabels as $label) {
			$monthlyTrendLabels[] = date('M', strtotime($label . '-01'));
			$monthlyTrendCounts[] = $monthlyTrendMap[$label] ?? 0;
		}

		respondJson(true, 'Memories fetched successfully.', [
			'memories' => $memories,
			'pagination' => [
				'page' => $page,
				'pageSize' => $pageSize,
				'totalCount' => $totalCount,
				'totalPages' => $totalPages,
				'hasPrev' => $page > 1,
				'hasNext' => $page < $totalPages
			],
			'stats' => [
				'total' => (int)($statsRow['totalCount'] ?? 0),
				'thisMonth' => (int)($statsRow['thisMonthCount'] ?? 0),
				'roleCounts' => [
					'Friend' => (int)($statsRow['friendCount'] ?? 0),
					'Junior' => (int)($statsRow['juniorCount'] ?? 0),
					'Classmate' => (int)($statsRow['classmateCount'] ?? 0),
					'Colleague' => (int)($statsRow['colleagueCount'] ?? 0),
					'Faculty' => (int)($statsRow['facultyCount'] ?? 0)
				],
				'monthlyTrend' => [
					'labels' => $monthlyTrendLabels,
					'counts' => $monthlyTrendCounts
				]
			]
		]);
		break;

	case 'delete_memory':
	case 'clear_memories':
		requireAdminSessionJson();
		respondJson(false, 'Delete options are disabled for this project.', [], 403);
		break;

	default:
		respondJson(false, 'Invalid action.', [], 400);
		break;
}
