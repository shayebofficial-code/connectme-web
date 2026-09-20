<?php
/**
 * ConnectMe - General Utility Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escapes HTML output safely
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Calculate age from Date of Birth (YYYY-MM-DD)
 */
function calculateAge(string $dob): int {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

/**
 * Flash messaging helpers
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function displayFlashMessages(): void {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show role="alert">';
        echo e($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="this.parentElement.remove()">&times;</button>';
        echo '</div>';
    }
}

/**
 * Return JSON response for API calls
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Secure Profile Image Upload Helper
 * Validates file size, MIME type server-side via finfo, generates random hex filename
 */
function uploadProfileImage(array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed with error code ' . $file['error']];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds 5 MB limit.'];
    }

    // Verify MIME type using PHP finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mimeType, ALLOWED_MIME_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, and WebP are allowed.'];
    }

    // Double check image validity
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'error' => 'Uploaded file is not a valid image.'];
    }

    $extension = ALLOWED_MIME_TYPES[$mimeType];
    $randomFilename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = UPLOAD_DIR . $randomFilename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file.'];
    }

    return ['success' => true, 'filename' => $randomFilename];
}

/**
 * Check if a user is currently premium
 */
function checkUserPremiumStatus(int $userId): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT is_premium, premium_until FROM profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();

    if (!$profile) return false;

    if ($profile['is_premium']) {
        if ($profile['premium_until']) {
            $now = new DateTime();
            $until = new DateTime($profile['premium_until']);
            if ($now > $until) {
                // Subscription expired, downgrade automatically
                $update = $db->prepare("UPDATE profiles SET is_premium = 0 WHERE user_id = ?");
                $update->execute([$userId]);
                return false;
            }
        }
        return true;
    }

    return false;
}

/**
 * Fetch complete User Profile Data
 */
function getUserProfileData(int $userId): ?array {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.id as user_id, u.email, u.google_id, u.email_verified, u.is_admin, u.is_active, u.created_at as registered_at,
               p.name, p.dob, p.age, p.gender, p.looking_for, p.city, p.bio, p.interests, p.photo, p.is_verified, p.is_premium, p.premium_until
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

/**
 * Relative time ago formatter
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}
