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

<header class="h-12 bg-white border-b border-slate-200/80 px-4 flex items-center justify-between shrink-0 shadow-sm z-20">
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
                <img src="images/zazsoft.png" alt="Zazsoft" class="h-8 max-h-8 w-auto object-contain">
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
        <a href="includes/logout.php" title="Sign Out" class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-[11px] font-semibold transition border border-slate-200/60">
            <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
            <span class="hidden md:inline">Sign Out</span>
        </a>
    </div>
</header>

<script>
    function updatePKTClock() {
        const options = { timeZone: 'Asia/Karachi', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const pktTime = new Intl.DateTimeFormat('en-US', options).format(new Date());
        const el = document.getElementById('pktClock');
        if (el) el.innerText = pktTime + ' PKT';
    }
    setInterval(updatePKTClock, 1000);
    updatePKTClock();
</script>