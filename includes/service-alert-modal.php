<?php
/**
 * Zazsoft Service Alert Popup Modal
 * Displayed on user login when enabled by administrator.
 */

require_once __DIR__ . '/alert-helper.php';

// Only show if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    return;
}

global $conn;
$zazConfig = get_zazsoft_alert_config($conn);

// If alert is disabled, do not render anything
if (empty($zazConfig['is_active'])) {
    return;
}

$alertVer = (int)($zazConfig['alert_version'] ?? 1);
$userDismissedVer = (int)($_SESSION['zazsoft_alert_dismissed_version'] ?? 0);

// If display_mode is 'once_per_login' and the current version was already dismissed this session
if (($zazConfig['display_mode'] ?? 'once_per_login') === 'once_per_login' && $userDismissedVer >= $alertVer) {
    return;
}

$sev = $zazConfig['severity'] ?? 'critical';
if ($sev === 'warning') {
    $accentBorder = 'border-amber-500/40';
    $accentGlow = 'bg-amber-500/15';
    $badgeBg = 'bg-amber-500/20 text-amber-300 border-amber-500/30';
    $btnGradient = 'from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 shadow-amber-600/30';
    $iconColor = 'text-amber-400';
    $iconClass = 'fa-solid fa-triangle-exclamation';
} elseif ($sev === 'info') {
    $accentBorder = 'border-indigo-500/40';
    $accentGlow = 'bg-indigo-500/15';
    $badgeBg = 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30';
    $btnGradient = 'from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 shadow-indigo-600/30';
    $iconColor = 'text-indigo-400';
    $iconClass = 'fa-solid fa-circle-info';
} else { // critical (default)
    $accentBorder = 'border-rose-500/40';
    $accentGlow = 'bg-rose-500/15';
    $badgeBg = 'bg-rose-500/20 text-rose-300 border-rose-500/30';
    $btnGradient = 'from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 shadow-rose-600/30';
    $iconColor = 'text-rose-400';
    $iconClass = 'fa-solid fa-bell';
}

$cleanWa = preg_replace('/[^0-9]/', '', $zazConfig['whatsapp'] ?? '');
$cleanPhone = preg_replace('/[^0-9+]/', '', $zazConfig['phone'] ?? '');
?>

<!-- Zazsoft Service Alert Popup Modal -->
<div id="zazsoftServiceModal" 
     class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 transition-all duration-300"
     style="font-family: inherit;">
     
    <!-- Backdrop Blur with ambient darkening -->
    <div id="zazsoftModalBackdrop" 
         onclick="closeZazsoftAlertModal()"
         class="absolute inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity duration-300"></div>

    <!-- Ambient glowing light -->
    <div class="absolute w-96 h-96 <?php echo $accentGlow; ?> rounded-full blur-3xl pointer-events-none"></div>

    <!-- Modal Content Card -->
    <div id="zazsoftModalCard" 
         class="relative w-full max-w-xl bg-slate-900/95 border <?php echo $accentBorder; ?> text-slate-100 rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6 z-10 transform transition-all duration-300 scale-100 opacity-100 overflow-hidden">
        
        <!-- Top decorative gradient line -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r <?php echo $btnGradient; ?>"></div>

        <!-- Header: Logos & Close 'X' -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <!-- Zazsoft Logo Container -->
                <div class="p-2 rounded-2xl bg-slate-950/80 border border-slate-800 shadow-inner flex items-center justify-center">
                    <img src="images/zazsoft.png" alt="Zazsoft" class="h-10 w-auto object-contain max-w-[130px]" onerror="this.style.display='none'">
                </div>
                
                <div>
                    <!-- Badge -->
                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase border <?php echo $badgeBg; ?>">
                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-ping"></span>
                        <span><?php echo htmlspecialchars($zazConfig['badge_text'] ?? 'Service Notice'); ?></span>
                    </span>
                    <div class="text-[11px] text-slate-400 mt-1 font-medium">Service Provider Advisory</div>
                </div>
            </div>

            <!-- Top Close Button -->
            <button type="button" 
                    onclick="closeZazsoftAlertModal()" 
                    title="Close notice and continue"
                    class="w-9 h-9 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-400 hover:text-white border border-slate-700/60 flex items-center justify-center transition focus:outline-none shrink-0">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Alert Body -->
        <div class="space-y-3">
            <div class="flex items-center space-x-2.5">
                <i class="<?php echo $iconClass; ?> <?php echo $iconColor; ?> text-xl shrink-0"></i>
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight leading-snug">
                    <?php echo htmlspecialchars($zazConfig['title'] ?? 'Important Service Notice'); ?>
                </h2>
            </div>

            <div class="text-sm text-slate-300 leading-relaxed bg-slate-950/50 p-4 rounded-2xl border border-slate-800/80 max-h-56 overflow-y-auto whitespace-pre-line font-normal">
                <?php echo htmlspecialchars($zazConfig['message']); ?>
            </div>
        </div>

        <!-- Direct Contact Methods -->
        <div class="space-y-2">
            <div class="text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                Official Contact Channels (<?php echo htmlspecialchars($zazConfig['company_name'] ?? 'Zazsoft'); ?>):
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <?php if (!empty($cleanPhone)): ?>
                <a href="tel:<?php echo htmlspecialchars($cleanPhone); ?>" 
                   class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 text-slate-200 hover:text-white transition group text-xs font-semibold">
                    <i class="fa-solid fa-phone text-emerald-400 group-hover:scale-110 transition-transform"></i>
                    <span class="truncate"><?php echo htmlspecialchars($zazConfig['phone']); ?></span>
                </a>
                <?php endif; ?>

                <?php if (!empty($cleanWa)): ?>
                <a href="https://wa.me/<?php echo htmlspecialchars($cleanWa); ?>?text=<?php echo urlencode('Hello Zazsoft, I am contacting you regarding Footprint HR System service.'); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl bg-emerald-950/40 hover:bg-emerald-900/50 border border-emerald-600/40 text-emerald-300 hover:text-emerald-200 transition group text-xs font-semibold">
                    <i class="fa-brands fa-whatsapp text-emerald-400 text-sm group-hover:scale-110 transition-transform"></i>
                    <span>WhatsApp</span>
                </a>
                <?php endif; ?>

                <?php if (!empty($zazConfig['email'])): ?>
                <a href="mailto:<?php echo htmlspecialchars($zazConfig['email']); ?>?subject=<?php echo urlencode('Footprint HR System - Service Inquiry'); ?>" 
                   class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 text-slate-200 hover:text-white transition group text-xs font-semibold">
                    <i class="fa-solid fa-envelope text-indigo-400 group-hover:scale-110 transition-transform"></i>
                    <span class="truncate"><?php echo htmlspecialchars($zazConfig['email']); ?></span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="pt-2 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[11px] text-slate-400 text-center sm:text-left leading-normal">
                You can dismiss this alert and use the system as usual for this session.
            </p>

            <button type="button" 
                    id="zazsoftContinueBtn"
                    onclick="closeZazsoftAlertModal()" 
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r <?php echo $btnGradient; ?> text-white font-bold text-xs uppercase tracking-wider shadow-lg transition transform active:scale-95 flex items-center justify-center space-x-2 shrink-0">
                <span>Close &amp; Continue</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </div>

    </div>
</div>

<script>
    function closeZazsoftAlertModal() {
        const modal = document.getElementById('zazsoftServiceModal');
        const card = document.getElementById('zazsoftModalCard');
        const backdrop = document.getElementById('zazsoftModalBackdrop');

        if (card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
        }
        if (backdrop) {
            backdrop.classList.add('opacity-0');
        }

        setTimeout(function() {
            if (modal) {
                modal.remove();
            }
        }, 300);

        try {
            sessionStorage.setItem('zazsoft_alert_dismissed', '1');
            sessionStorage.setItem('zazsoft_alert_version', '<?php echo $alertVer; ?>');
        } catch (e) {}

        // Notify server asynchronously to save session acknowledgment
        try {
            const formData = new FormData();
            formData.append('version', '<?php echo $alertVer; ?>');
            fetch('includes/dismiss-alert.php', {
                method: 'POST',
                body: formData
            }).catch(function(err) {});
        } catch (e) {}
    }
</script>
