<?php
/**
 * ConnectMe - API Endpoint: Paginated Search & Discover Feed
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
}

$userId = currentUserId();
$db = getDB();

$gender   = trim($_GET['gender'] ?? 'everyone');
$city     = trim($_GET['city'] ?? '');
$minAge   = (int)($_GET['min_age'] ?? 18);
$maxAge   = (int)($_GET['max_age'] ?? 80);
$page     = (int)($_GET['page'] ?? 1);
$limit    = 10;
$offset   = ($page - 1) * $limit;

$query = "
    SELECT p.*, u.id as user_id
    FROM profiles p
    JOIN users u ON p.user_id = u.id
    WHERE u.id != :current_user
      AND u.is_active = 1
      AND u.id NOT IN (SELECT to_user_id FROM likes WHERE from_user_id = :current_user)
      AND u.id NOT IN (SELECT blocked_id FROM blocks WHERE blocker_id = :current_user)
      AND u.id NOT IN (SELECT blocker_id FROM blocks WHERE blocked_id = :current_user)
";

$params = ['current_user' => $userId];

if ($gender !== 'everyone' && in_array($gender, ['male', 'female', 'non-binary', 'other'])) {
    $query .= " AND p.gender = :gender";
    $params['gender'] = $gender;
}

if (!empty($city)) {
    $query .= " AND p.city LIKE :city";
    $params['city'] = '%' . $city . '%';
}

if ($minAge > 18) {
    $query .= " AND p.age >= :min_age";
    $params['min_age'] = $minAge;
}

if ($maxAge < 80) {
    $query .= " AND p.age <= :max_age";
    $params['max_age'] = $maxAge;
}

$query .= " ORDER BY p.is_premium DESC, RAND() LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$profiles = $stmt->fetchAll();

jsonResponse([
    'success'  => true,
    'page'     => $page,
    'profiles' => $profiles
]);
