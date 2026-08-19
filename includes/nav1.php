<link rel="stylesheet" href="css/styles.css?v=1.4">
<style>
  /* Global Slim & Dark Scrollbar */
  ::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  ::-webkit-scrollbar-track {
    background: #0f172a;
  }
  ::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 9999px;
  }
  ::-webkit-scrollbar-thumb:hover {
    background: #475569;
  }
  /* Firefox Scrollbar Support */
  * {
    scrollbar-width: thin;
    scrollbar-color: #334155 #0f172a;
  }
  /* Alpine.js Cloak to prevent FOUC / flash of modals on page load */
  [x-cloak] {
    display: none !important;
  }
</style>

<header class="h-16 bg-white border-b border-slate-200/80 px-4 flex items-center justify-between shrink-0 shadow-sm z-20">
    <div class="flex items-center space-x-3">
        <!-- Sidebar Toggle Button -->
        <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('hr_sidebar_collapsed', sidebarCollapsed)" 
                title="Toggle Sidebar"
                class="w-8 h-8 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center transition focus:outline-none border border-slate-200/60">
            <i class="fa-solid fa-bars text-xs"></i>
        </button>

        <!-- System logo / status -->
        <div class="flex items-center space-x-3 text-xs font-medium text-slate-500">
            <a href="index.php" class="inline-flex items-center group">
                <img src="images/zazsoft.png" alt="Zazsoft" class="h-16 max-h-16 w-auto object-contain">
            </a>
            <span class="text-slate-300 hidden sm:inline">/</span>
            <span class="text-slate-600 font-bold uppercase tracking-wider text-[10px] hidden sm:inline"><?php echo date('M Y'); ?></span>
        </div>
    </div>

    <!-- Header Actions -->
    <div class="flex items-center space-x-3">
        <div class="text-right hidden sm:flex items-center space-x-1.5 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200/60">
            <i class="fa-solid fa-clock text-indigo-600 text-[10px]"></i>
            <span id="pktClock" class="text-[11px] font-mono font-bold text-slate-700"><?php echo date('h:i:s A'); ?> PKT</span>
        </div>

        <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>

        <!-- Dark / Light Mode Top Bar Toggle Button -->
        <button id="topBarThemeToggle" 
                onclick="toggleTopBarTheme()" 
                title="Toggle Light / Dark Mode" 
                class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 text-xs font-bold transition border border-slate-200/80 focus:outline-none">
            <i id="themeToggleIcon" class="fa-solid fa-moon text-indigo-500"></i>
            <span id="themeToggleLabel" class="hidden md:inline">Theme</span>
        </button>

        <a href="includes/logout.php" title="Sign Out" class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-[11px] font-semibold transition border border-slate-200/60">
            <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
            <span class="hidden md:inline">Sign Out</span>
        </a>
    </div>
</header>

<script>
    (function() {
        const savedFontSize = localStorage.getItem('hr_font_size') || 'large';
        const savedDensity = localStorage.getItem('hr_table_density') || 'comfortable';
        const savedTheme = localStorage.getItem('hr_theme') || 'light';

        document.documentElement.setAttribute('data-font-size', savedFontSize);
        document.documentElement.setAttribute('data-table-density', savedDensity);
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();

    function toggleTopBarTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('hr_theme', newTheme);
        updateThemeToggleIcons(newTheme);
    }

    function updateThemeToggleIcons(theme) {
        const icon = document.getElementById('themeToggleIcon');
        const label = document.getElementById('themeToggleLabel');
        if (icon) {
            if (theme === 'dark') {
                icon.className = 'fa-solid fa-sun text-amber-400';
                if (label) label.innerText = 'Light Mode';
            } else {
                icon.className = 'fa-solid fa-moon text-indigo-500';
                if (label) label.innerText = 'Dark Mode';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const currentTheme = localStorage.getItem('hr_theme') || 'light';
        updateThemeToggleIcons(currentTheme);
    });

    function updatePKTClock() {
        const options = { timeZone: 'Asia/Karachi', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const pktTime = new Intl.DateTimeFormat('en-US', options).format(new Date());
        const el = document.getElementById('pktClock');
        if (el) el.innerText = pktTime + ' PKT';
    }
    setInterval(updatePKTClock, 1000);
    updatePKTClock();
</script>