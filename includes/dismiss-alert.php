<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/alert-helper.php';
init_hr_session();

$config = get_zazsoft_alert_config($conn);
$version = isset($_POST['version']) ? (int)$_POST['version'] : (int)($config['alert_version'] ?? 1);

$_SESSION['zazsoft_alert_dismissed_version'] = $version;
$_SESSION['zazsoft_alert_shown'] = true;

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'dismissed' => true,
    'version' => $version
]);
exit;
