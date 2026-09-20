<?php
/**
 * ConnectMe - Google Login Redirect Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/google.php';
require_once __DIR__ . '/../includes/auth.php';

$oauthState = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $oauthState;

header('Location: ' . getGoogleAuthUrl($oauthState));
exit;
