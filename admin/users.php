<?php
/**
 * ConnectMe - Admin User Management Controller
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDB();
$search = trim($_GET['q'] ?? '');

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $action       = $_POST['action'] ?? '';

    if ($targetUserId && $targetUserId !== currentUserId()) {
        if ($action === 'toggle_active') {
            $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?")->execute([$targetUserId]);
            setFlashMessage('success', 'User activation status updated.');
        } elseif ($action === 'toggle_premium') {
            $until = date('Y-m-d H:i:s', strtotime('+1 month'));
            $db->prepare("UPDATE profiles SET is_premium = NOT is_premium, premium_until = ? WHERE user_id = ?")->execute([$until, $targetUserId]);
            setFlashMessage('success', 'User premium status updated.');
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$targetUserId]);
            setFlashMessage('success', 'User deleted permanently.');
        }
    }
    header('Location: ' . APP_URL . '/admin/users.php?q=' . urlencode($search));
    exit;
}

// Fetch users list
$query = "
    SELECT u.id, u.email, u.google_id, u.is_admin, u.is_active, u.created_at,
           p.name, p.age, p.gender, p.city, p.photo, p.is_verified, p.is_premium
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
";

$params = [];
if (!empty($search)) {
    $query .= " WHERE u.email LIKE :q OR p.name LIKE :q OR p.city LIKE :q";
    $params['q'] = '%' . $search . '%';
}

$query .= " ORDER BY u.created_at DESC LIMIT 50";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div style="max-width: 1100px; margin: 30px auto; padding: 0 15px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <h2><i class="fa-solid fa-users" style="color: var(--primary);"></i> User Management</h2>
        
        <form method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="q" class="form-control-custom" value="<?php echo e($search); ?>" placeholder="Search name, email, city..." style="width: 260px;">
            <button type="submit" class="btn-primary-custom" style="padding: 8px 16px;">Search</button>
        </form>
    </div>

    <div class="glass-card" style="overflow-x: auto; padding: 0;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color); background: rgba(15, 23, 42, 0.6); color: var(--text-secondary);">
                    <th style="padding: 14px;">User</th>
                    <th style="padding: 14px;">Email</th>
                    <th style="padding: 14px;">Location</th>
                    <th style="padding: 14px;">Status</th>
                    <th style="padding: 14px;">Registered</th>
                    <th style="padding: 14px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 14px; display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo APP_URL . '/uploads/profiles/' . e($u['photo'] ?? 'default.jpg'); ?>" 
                                     alt="Avatar" 
                                     style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover;"
                                     onerror="this.src='https://ui-avatars.com/api/?name=User&background=e11d48&color=fff';">
                                <div>
                                    <strong style="color: var(--text-primary);"><?php echo e($u['name'] ?? 'No Profile'); ?></strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo e($u['gender'] ?? ''); ?> • <?php echo e($u['age'] ?? ''); ?> yrs</div>
                                </div>
                            </td>
                            <td style="padding: 14px;">
                                <?php echo e($u['email']); ?>

                                <?php if ($u['google_id']): ?>
                                    <span style="font-size: 0.7rem; background: #4285F4; color: white; padding: 2px 6px; border-radius: 4px;">Google</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px;"><?php echo e($u['city'] ?? '-'); ?></td>
                            <td style="padding: 14px;">
                                <?php if (!$u['is_active']): ?>
                                    <span style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem;">Deactivated</span>
                                <?php else: ?>
                                    <span style="background: rgba(16, 185, 129, 0.2); color: #a7f3d0; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem;">Active</span>
                                <?php endif; ?>

                                <?php if ($u['is_premium']): ?>
                                    <span class="badge-premium" style="font-size: 0.65rem; margin-left: 4px;">VIP</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px; font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td style="padding: 14px; text-align: right;">
                                <form method="POST" style="display: inline-block;">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    
                                    <button type="submit" name="action" value="toggle_active" class="btn-secondary-custom" style="padding: 4px 10px; font-size: 0.75rem;" title="Toggle Activation">
                                        <?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?>

                                    </button>

                                    <button type="submit" name="action" value="toggle_premium" class="btn-secondary-custom" style="padding: 4px 10px; font-size: 0.75rem; color: var(--gold);" title="Toggle Premium Status">
                                        <i class="fa-solid fa-crown"></i>
                                    </button>

                                    <?php if ($u['id'] !== currentUserId()): ?>
                                        <button type="submit" name="action" value="delete" class="btn-secondary-custom" style="padding: 4px 10px; font-size: 0.75rem; color: var(--danger);" onclick="return confirm('Delete user permanently?');" title="Delete User">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">No users found matching query.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
