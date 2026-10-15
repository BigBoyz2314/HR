<?php
/**
 * Zazsoft Service Provider - Hidden Control Center
 * Allows the service provider to toggle and customize system service notices.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/alert-helper.php';
init_hr_session();

// Fetch current configuration
global $conn;
$config = get_zazsoft_alert_config($conn);

$passkeyError = '';
$successMessage = '';
$errorMessage = '';

// Handle Master Passkey Lock/Logout
if (isset($_GET['action']) && $_GET['action'] === 'lock') {
    unset($_SESSION['zaz_admin_auth']);
    header('Location: zaz-admin.php');
    exit;
}

// Handle Passkey Authentication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $submittedPin = trim($_POST['passkey'] ?? '');
    $correctPin = $config['admin_pin'] ?? 'zazsoft@2026';

    if ($submittedPin === $correctPin) {
        $_SESSION['zaz_admin_auth'] = true;
        header('Location: zaz-admin.php');
        exit;
    } else {
        $passkeyError = 'Invalid master passkey. Access denied.';
    }
}

// Check authentication
$isAuthenticated = !empty($_SESSION['zaz_admin_auth']);

// Handle Configuration Updates
if ($isAuthenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Quick Toggle Action
    if ($_POST['action'] === 'quick_toggle') {
        $newStatus = !empty($config['is_active']) ? 0 : 1;
        $config = save_zazsoft_alert_config(['is_active' => $newStatus], $conn);
        $successMessage = $newStatus ? 'Service Notice is now ACTIVE and broadcasting to users upon login!' : 'Service Notice has been DISABLED.';
    }

    // Full Settings Update
    elseif ($_POST['action'] === 'save_settings') {
        $updateData = [
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'title' => trim($_POST['title'] ?? ''),
            'badge_text' => trim($_POST['badge_text'] ?? ''),
            'message' => trim($_POST['message'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? 'Zazsoft Technologies'),
            'phone' => trim($_POST['phone'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'severity' => in_array($_POST['severity'] ?? '', ['critical', 'warning', 'info']) ? $_POST['severity'] : 'critical',
            'display_mode' => ($_POST['display_mode'] ?? '') === 'every_page' ? 'every_page' : 'once_per_login'
        ];

        // Handle Pin Change if provided
        $newPin = trim($_POST['new_admin_pin'] ?? '');
        $confirmPin = trim($_POST['confirm_admin_pin'] ?? '');
        if (!empty($newPin)) {
            if ($newPin === $confirmPin) {
                if (strlen($newPin) >= 6) {
                    $updateData['admin_pin'] = $newPin;
                } else {
                    $errorMessage = 'Master passkey must be at least 6 characters long.';
                }
            } else {
                $errorMessage = 'New master passkeys do not match.';
            }
        }

        if (empty($errorMessage)) {
            $config = save_zazsoft_alert_config($updateData, $conn);
            $successMessage = 'Alert configurations saved successfully!';
        }
    }

    // Re-trigger alert for all active sessions
    elseif ($_POST['action'] === 'retrigger') {
        $config = save_zazsoft_alert_config(['is_active' => 1, 'alert_version' => time()], $conn);
        $successMessage = 'Alert version refreshed! All users will see the alert immediately on next page load or login.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Zazsoft Control Center - Service Notice Manager</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        /* Modern custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0b0f19; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased relative selection:bg-indigo-500 selection:text-white">

    <!-- Ambient glowing backdrop -->
    <div class="fixed -top-40 -left-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed -bottom-40 -right-40 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <?php if (!$isAuthenticated): ?>
    <!-- ==================== PASSKEY LOCK SCREEN ==================== -->
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-slate-900/90 backdrop-blur-2xl border border-slate-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden space-y-6">
            
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-blue-500"></div>

            <div class="text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-indigo-950/80 border border-indigo-500/30 flex items-center justify-center mx-auto text-indigo-400 text-2xl shadow-lg shadow-indigo-500/10">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">Zazsoft Service Portal</h1>
                <p class="text-xs text-slate-400">Restricted administrative access. Enter master passkey to continue.</p>
            </div>

            <?php if (!empty($passkeyError)): ?>
            <div class="p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                <span><?php echo htmlspecialchars($passkeyError); ?></span>
            </div>
            <?php endif; ?>

            <form action="zaz-admin.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Master Passkey</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-slate-500 text-sm"><i class="fa-solid fa-key"></i></span>
                        <input type="password" 
                               name="passkey" 
                               placeholder="Enter master passkey..." 
                               required 
                               autofocus
                               class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-white text-sm outline-none transition">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5 flex items-center space-x-1">
                        <i class="fa-solid fa-info-circle text-[10px]"></i>
                        <span>Default: <code class="text-indigo-400">zazsoft@2026</code></span>
                    </p>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition transform active:scale-95 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-lock-open text-xs"></i>
                    <span>Unlock Control Center</span>
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800/80 text-center">
                <a href="index.php" class="text-xs text-slate-500 hover:text-slate-300 transition inline-flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Return to HR Portal</span>
                </a>
            </div>

        </div>
    </div>

    <?php else: ?>
    <!-- ==================== CONTROL CENTER DASHBOARD ==================== -->
    <div class="min-h-screen flex flex-col">
        
        <!-- Top Navbar -->
        <header class="h-16 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800 px-6 flex items-center justify-between shrink-0 sticky top-0 z-40">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-sm shadow-md shadow-indigo-600/30">
                    <i class="fa-solid fa-toggle-on"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black text-white tracking-wide uppercase">Zazsoft Control Center</h1>
                    <p class="text-[11px] text-slate-400">Service Provider Alert Management</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button type="button" 
                        onclick="triggerPreviewModal()"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-eye text-indigo-400 text-xs"></i>
                    <span>Live Preview</span>
                </button>

                <a href="index.php" 
                   target="_blank"
                   class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-external-link text-xs"></i>
                    <span>Open HR App</span>
                </a>

                <a href="zaz-admin.php?action=lock" 
                   class="px-3 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 hover:text-white border border-rose-500/30 text-xs font-bold transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>Lock Panel</span>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Toast Messages -->
            <?php if (!empty($successMessage)): ?>
            <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between shadow-lg">
                <div class="flex items-center space-x-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                    <span class="font-medium"><?php echo htmlspecialchars($successMessage); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
            <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between shadow-lg">
                <div class="flex items-center space-x-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 text-lg"></i>
                    <span class="font-medium"><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Big Status Hero Card with 1-Click Quick Toggle -->
            <div class="bg-gradient-to-br <?php echo !empty($config['is_active']) ? 'from-rose-950/60 via-slate-900 to-slate-900 border-rose-500/40' : 'from-slate-900 via-slate-900 to-slate-950 border-slate-800'; ?> border rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden transition-all duration-300">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2.5">
                            <?php if (!empty($config['is_active'])): ?>
                            <span class="px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-bold uppercase tracking-wider flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                <span>LIVE BROADCASTING</span>
                            </span>
                            <?php else: ?>
                            <span class="px-3 py-1 rounded-full bg-slate-800 text-slate-400 border border-slate-700 text-xs font-bold uppercase tracking-wider flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                <span>INACTIVE / STANDBY</span>
                            </span>
                            <?php endif; ?>
                            <span class="text-xs text-slate-500 font-mono">Ver. <?php echo (int)($config['alert_version'] ?? 1); ?></span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            <?php echo !empty($config['is_active']) ? 'Service Alert is Currently Active' : 'Service Alert is Currently Off'; ?>
                        </h2>
                        <p class="text-sm text-slate-400 max-w-xl">
                            <?php if (!empty($config['is_active'])): ?>
                                When users log in to the HR portal, a prominent modal will immediately pop up informing them to contact service provider Zazsoft. They can close the modal to use the system as usual.
                            <?php else: ?>
                                No alert is shown to users upon login. The system operates with standard behavior.
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Quick 1-Click Toggle Form -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                        <form action="zaz-admin.php" method="POST" class="inline">
                            <input type="hidden" name="action" value="quick_toggle">
                            <button type="submit" 
                                    class="w-full sm:w-auto px-6 py-4 rounded-2xl font-black text-sm uppercase tracking-wider shadow-xl transition transform active:scale-95 flex items-center justify-center space-x-3 <?php echo !empty($config['is_active']) ? 'bg-gradient-to-r from-slate-800 to-slate-700 hover:from-slate-700 hover:to-slate-600 text-rose-300 border border-rose-500/30' : 'bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white shadow-rose-600/30'; ?>">
                                <i class="fa-solid <?php echo !empty($config['is_active']) ? 'fa-power-off text-rose-400' : 'fa-bell'; ?> text-base"></i>
                                <span><?php echo !empty($config['is_active']) ? 'Turn Alert OFF' : 'ACTIVATE ALERT NOW'; ?></span>
                            </button>
                        </form>

                        <?php if (!empty($config['is_active'])): ?>
                        <form action="zaz-admin.php" method="POST" class="inline">
                            <input type="hidden" name="action" value="retrigger">
                            <button type="submit" 
                                    title="Forces all active users to see the alert on next page refresh"
                                    class="w-full sm:w-auto px-4 py-4 rounded-2xl bg-slate-800/80 hover:bg-slate-700/80 text-amber-300 hover:text-amber-200 border border-amber-500/30 text-xs font-bold transition flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-arrows-rotate text-sm"></i>
                                <span>Re-trigger for Everyone</span>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <form action="zaz-admin.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="save_settings">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left: Alert Content Configuration (2 Cols) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-5">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-pen-to-square text-indigo-400 text-base"></i>
                                    <h3 class="text-base font-bold text-white">Alert Content &amp; Messaging</h3>
                                </div>
                                <span class="text-xs text-slate-500">Customized for Zazsoft</span>
                            </div>

                            <!-- Enable Checkbox Switch -->
                            <div class="flex items-center justify-between bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                <div>
                                    <div class="text-sm font-bold text-white">Enable Alert Broadcast</div>
                                    <div class="text-xs text-slate-400">Toggle whether this alert pops up for users</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" <?php echo !empty($config['is_active']) ? 'checked' : ''; ?> class="sr-only peer">
                                    <div class="w-12 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                </label>
                            </div>

                            <!-- Title & Badge -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Headline / Title</label>
                                    <input type="text" 
                                           name="title" 
                                           id="input_title"
                                           value="<?php echo htmlspecialchars($config['title'] ?? ''); ?>" 
                                           required
                                           placeholder="e.g. Important Service Notice"
                                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-white text-sm outline-none transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Badge Pill Text</label>
                                    <input type="text" 
                                           name="badge_text" 
                                           id="input_badge_text"
                                           value="<?php echo htmlspecialchars($config['badge_text'] ?? ''); ?>" 
                                           placeholder="e.g. Service Provider Advisory"
                                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-white text-sm outline-none transition">
                                </div>
                            </div>

                            <!-- Body Message -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Notice Message Body</label>
                                <textarea name="message" 
                                          id="input_message"
                                          rows="5" 
                                          required
                                          placeholder="Type notice message here..."
                                          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-white text-sm outline-none transition leading-relaxed"><?php echo htmlspecialchars($config['message'] ?? ''); ?></textarea>
                                <p class="text-[11px] text-slate-500 mt-1">This message is shown directly to the user when they sign in.</p>
                            </div>

                            <!-- Severity & Frequency -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Color Severity</label>
                                    <select name="severity" 
                                            id="input_severity"
                                            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-sm outline-none transition">
                                        <option value="critical" <?php echo ($config['severity'] ?? '') === 'critical' ? 'selected' : ''; ?>>Critical (Red / Rose)</option>
                                        <option value="warning" <?php echo ($config['severity'] ?? '') === 'warning' ? 'selected' : ''; ?>>Warning (Amber / Orange)</option>
                                        <option value="info" <?php echo ($config['severity'] ?? '') === 'info' ? 'selected' : ''; ?>>Informational (Indigo / Blue)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Popup Frequency</label>
                                    <select name="display_mode" 
                                            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-sm outline-none transition">
                                        <option value="once_per_login" <?php echo ($config['display_mode'] ?? '') === 'once_per_login' ? 'selected' : ''; ?>>On Login (Close &amp; use system normally)</option>
                                        <option value="every_page" <?php echo ($config['display_mode'] ?? '') === 'every_page' ? 'selected' : ''; ?>>Every Page (Continuous reminder)</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <!-- Contact Channels Card -->
                        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-5">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-address-book text-emerald-400 text-base"></i>
                                    <h3 class="text-base font-bold text-white">Service Provider Contact Channels</h3>
                                </div>
                                <span class="text-xs text-slate-500">Zazsoft Details</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Provider Name</label>
                                    <input type="text" 
                                           name="company_name" 
                                           id="input_company_name"
                                           value="<?php echo htmlspecialchars($config['company_name'] ?? 'Zazsoft Technologies'); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-sm outline-none transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phone / Call</label>
                                    <input type="text" 
                                           name="phone" 
                                           id="input_phone"
                                           value="<?php echo htmlspecialchars($config['phone'] ?? '+92 300 1234567'); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-sm outline-none transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">WhatsApp Number</label>
                                    <input type="text" 
                                           name="whatsapp" 
                                           id="input_whatsapp"
                                           value="<?php echo htmlspecialchars($config['whatsapp'] ?? '+92 300 1234567'); ?>" 
                                           placeholder="e.g. +923001234567"
                                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-sm outline-none transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Support Email</label>
                                    <input type="email" 
                                           name="email" 
                                           id="input_email"
                                           value="<?php echo htmlspecialchars($config['email'] ?? 'support@zazsoft.com'); ?>" 
                                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-sm outline-none transition">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Security Passkey & Quick Actions (1 Col) -->
                    <div class="space-y-6">

                        <!-- Passkey Card -->
                        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                            <div class="flex items-center space-x-2.5 border-b border-slate-800 pb-3">
                                <i class="fa-solid fa-key text-amber-400 text-sm"></i>
                                <h3 class="text-sm font-bold text-white">Master Access Passkey</h3>
                            </div>
                            <p class="text-xs text-slate-400">Update the secret passkey required to access this hidden page.</p>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">New Passkey</label>
                                    <input type="password" 
                                           name="new_admin_pin" 
                                           placeholder="Leave blank to keep unchanged" 
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-xs outline-none transition">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Confirm New Passkey</label>
                                    <input type="password" 
                                           name="confirm_admin_pin" 
                                           placeholder="Repeat new passkey" 
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 focus:border-indigo-500 text-white text-xs outline-none transition">
                                </div>
                            </div>
                        </div>

                        <!-- System Information Card -->
                        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                            <div class="flex items-center space-x-2.5 border-b border-slate-800 pb-3">
                                <i class="fa-solid fa-server text-blue-400 text-sm"></i>
                                <h3 class="text-sm font-bold text-white">System Diagnostics</h3>
                            </div>
                            
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Database Sync:</span>
                                    <span class="text-emerald-400 font-bold flex items-center space-x-1">
                                        <i class="fa-solid fa-check text-[10px]"></i>
                                        <span>Connected</span>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Fallback Config:</span>
                                    <span class="text-indigo-400 font-mono text-[11px]">zaz_alert_config.json</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Current Version:</span>
                                    <span class="text-white font-mono"><?php echo (int)($config['alert_version'] ?? 1); ?></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Server Time:</span>
                                    <span class="text-slate-300 font-mono"><?php echo date('Y-m-d H:i'); ?> PKT</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sticky Save Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-600 hover:from-indigo-500 hover:to-blue-500 text-white font-black text-sm uppercase tracking-wider shadow-xl shadow-indigo-600/30 transition transform active:scale-95 flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-floppy-disk text-sm"></i>
                                <span>Save All Settings</span>
                            </button>
                        </div>

                    </div>

                </div>
            </form>

        </main>
    </div>

    <!-- Live Preview Modal Script and Container -->
    <div id="livePreviewContainer" class="hidden"></div>

    <script>
        function triggerPreviewModal() {
            const title = document.getElementById('input_title').value || 'Important Service Notice';
            const badge = document.getElementById('input_badge_text').value || 'Service Provider Advisory';
            const message = document.getElementById('input_message').value || 'Please contact service provider Zazsoft.';
            const company = document.getElementById('input_company_name').value || 'Zazsoft Technologies';
            const phone = document.getElementById('input_phone').value || '+92 300 1234567';
            const whatsapp = document.getElementById('input_whatsapp').value || '+92 300 1234567';
            const email = document.getElementById('input_email').value || 'support@zazsoft.com';
            const severity = document.getElementById('input_severity').value || 'critical';

            let accentBorder = 'border-rose-500/40';
            let accentGlow = 'bg-rose-500/15';
            let badgeBg = 'bg-rose-500/20 text-rose-300 border-rose-500/30';
            let btnGradient = 'from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 shadow-rose-600/30';
            let iconClass = 'fa-solid fa-bell text-rose-400';

            if (severity === 'warning') {
                accentBorder = 'border-amber-500/40';
                accentGlow = 'bg-amber-500/15';
                badgeBg = 'bg-amber-500/20 text-amber-300 border-amber-500/30';
                btnGradient = 'from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 shadow-amber-600/30';
                iconClass = 'fa-solid fa-triangle-exclamation text-amber-400';
            } else if (severity === 'info') {
                accentBorder = 'border-indigo-500/40';
                accentGlow = 'bg-indigo-500/15';
                badgeBg = 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30';
                btnGradient = 'from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 shadow-indigo-600/30';
                iconClass = 'fa-solid fa-circle-info text-indigo-400';
            }

            const cleanWa = whatsapp.replace(/[^0-9]/g, '');

            const modalHtml = `
                <div id="previewModalWrap" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6">
                    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md" onclick="closePreviewModal()"></div>
                    <div class="absolute w-96 h-96 ${accentGlow} rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative w-full max-w-xl bg-slate-900/95 border ${accentBorder} text-slate-100 rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6 z-10">
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r ${btnGradient}"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center space-x-3.5">
                                <div class="p-2 rounded-2xl bg-slate-950/80 border border-slate-800 shadow-inner flex items-center justify-center">
                                    <img src="images/zazsoft.png" alt="Zazsoft" class="h-10 w-auto object-contain max-w-[130px]" onerror="this.style.display='none'">
                                </div>
                                <div>
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase border ${badgeBg}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-ping"></span>
                                        <span>${badge}</span>
                                    </span>
                                    <div class="text-[11px] text-slate-400 mt-1 font-medium">Service Provider Advisory</div>
                                </div>
                            </div>
                            <button type="button" onclick="closePreviewModal()" class="w-9 h-9 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-400 hover:text-white border border-slate-700/60 flex items-center justify-center transition">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2.5">
                                <i class="${iconClass} text-xl shrink-0"></i>
                                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight leading-snug">${title}</h2>
                            </div>
                            <div class="text-sm text-slate-300 leading-relaxed bg-slate-950/50 p-4 rounded-2xl border border-slate-800/80 max-h-56 overflow-y-auto whitespace-pre-line font-normal">${message}</div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-[11px] uppercase font-bold text-slate-400 tracking-wider">Official Contact Channels (${company}):</div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                ${phone ? `<a href="tel:${phone}" class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-200 text-xs font-semibold"><i class="fa-solid fa-phone text-emerald-400"></i><span class="truncate">${phone}</span></a>` : ''}
                                ${whatsapp ? `<a href="https://wa.me/${cleanWa}" target="_blank" class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl bg-emerald-950/40 border border-emerald-600/40 text-emerald-300 text-xs font-semibold"><i class="fa-brands fa-whatsapp text-emerald-400 text-sm"></i><span>WhatsApp</span></a>` : ''}
                                ${email ? `<a href="mailto:${email}" class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-200 text-xs font-semibold"><i class="fa-solid fa-envelope text-indigo-400"></i><span class="truncate">${email}</span></a>` : ''}
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <p class="text-[11px] text-slate-400 text-center sm:text-left">You can dismiss this alert and use the system as usual for this session.</p>
                            <button type="button" onclick="closePreviewModal()" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r ${btnGradient} text-white font-bold text-xs uppercase tracking-wider shadow-lg transition transform active:scale-95 flex items-center justify-center space-x-2 shrink-0">
                                <span>Close &amp; Continue</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            const container = document.getElementById('livePreviewContainer');
            container.innerHTML = modalHtml;
            container.classList.remove('hidden');
        }

        function closePreviewModal() {
            const container = document.getElementById('livePreviewContainer');
            container.innerHTML = '';
            container.classList.add('hidden');
        }
    </script>
    <?php endif; ?>

</body>
</html>
