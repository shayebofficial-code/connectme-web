<?php
/**
 * ConnectMe - User Logout Handler
 */

require_once __DIR__ . '/../includes/auth.php';

logoutUser();
setFlashMessage('info', 'You have been logged out successfully.');
header('Location: ' . APP_URL . '/auth/login.php');
exit;
