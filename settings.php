<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Accessibility & System Settings - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" 
      x-data="{ 
          sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true',
          fontSize: localStorage.getItem('hr_font_size') || 'large',
          tableDensity: localStorage.getItem('hr_table_density') || 'comfortable',
          themeMode: localStorage.getItem('hr_theme') || 'light',
          currencyFmt: localStorage.getItem('hr_currency_fmt') || 'PKR',
          dateFmt: localStorage.getItem('hr_date_fmt') || 'DD-MMM-YYYY',
          receiptFont: localStorage.getItem('hr_receipt_font') || 'mono',
          
          setFontSize(mode) {
              this.fontSize = mode;
              localStorage.setItem('hr_font_size', mode);
              document.documentElement.setAttribute('data-font-size', mode);
          },
          setTableDensity(mode) {
              this.tableDensity = mode;
              localStorage.setItem('hr_table_density', mode);
              document.documentElement.setAttribute('data-table-density', mode);
          },
          setThemeMode(mode) {
              this.themeMode = mode;
              localStorage.setItem('hr_theme', mode);
              document.documentElement.setAttribute('data-theme', mode);
              if (typeof updateThemeToggleIcons === 'function') {
                  updateThemeToggleIcons(mode);
              }
          },
          setCurrencyFmt(mode) {
              this.currencyFmt = mode;
              localStorage.setItem('hr_currency_fmt', mode);
          },
          setDateFmt(mode) {
              this.dateFmt = mode;
              localStorage.setItem('hr_date_fmt', mode);
          },
          setReceiptFont(mode) {
              this.receiptFont = mode;
              localStorage.setItem('hr_receipt_font', mode);
          }
      }">
    
    <!-- Top Header Navigation -->
    <?php include("includes/nav1.php") ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-4rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include("includes/side-nav.php") ?>

        <!-- Main Settings View -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-8">
            
            <!-- Page Banner Header -->
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <div class="inline-flex items-center space-x-2 text-indigo-600 font-bold text-xs uppercase tracking-wider bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                        <i class="fa-solid fa-sliders"></i>
                        <span>System Preferences</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Accessibility & System Settings</h1>
                    <p class="text-xs text-slate-500 font-medium">Customize text size, table row density, themes, display formats, and thermal printer fonts across the system.</p>
                </div>
            </div>

            <!-- Settings Sections -->
            <div class="space-y-8">
                
                <!-- 1. Text Size & Typography -->
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200/80 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-black text-slate-900 flex items-center space-x-2.5">
                            <i class="fa-solid fa-text-height text-indigo-600"></i>
                            <span>1. Text Size & Typography</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Adjust the overall font scaling across all interface pages</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button @click="setFontSize('large')" 
                                :class="{ 'active': fontSize === 'large' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg">Aa</span>
                                <span x-show="fontSize === 'large'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active (Default)</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">Large Text (18px)</h3>
                                <p class="text-xs text-slate-500 mt-1">Enlarged font size for maximum legibility and clear viewing</p>
                            </div>
                        </button>

                        <button @click="setFontSize('normal')" 
                                :class="{ 'active': fontSize === 'normal' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-base">Aa</span>
                                <span x-show="fontSize === 'normal'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">Normal Text (16px)</h3>
                                <p class="text-xs text-slate-500 mt-1">Standard web typography size for balanced layout density</p>
                            </div>
                        </button>

                        <button @click="setFontSize('compact')" 
                                :class="{ 'active': fontSize === 'compact' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs">Aa</span>
                                <span x-show="fontSize === 'compact'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">Compact Text (14px)</h3>
                                <p class="text-xs text-slate-500 mt-1">Smaller font size to fit maximum data rows and columns on screen</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- 2. Table Row Density (Padding) -->
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200/80 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-black text-slate-900 flex items-center space-x-2.5">
                            <i class="fa-solid fa-table-cells text-indigo-600"></i>
                            <span>2. Table Row Density & Padding</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Choose between spacious row padding or dense table views</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button @click="setTableDensity('comfortable')" 
                                :class="{ 'active': tableDensity === 'comfortable' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm"><i class="fa-solid fa-arrows-up-down"></i></span>
                                <span x-show="tableDensity === 'comfortable'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active (Default)</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">Comfortable Padding</h3>
                                <p class="text-xs text-slate-500 mt-1">Generous cell padding for clear line separation and touch-friendly viewing</p>
                            </div>
                        </button>

                        <button @click="setTableDensity('compact')" 
                                :class="{ 'active': tableDensity === 'compact' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs"><i class="fa-solid fa-compress"></i></span>
                                <span x-show="tableDensity === 'compact'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">Compact Dense Rows</h3>
                                <p class="text-xs text-slate-500 mt-1">Tight row height so you can view 50%+ more employee records without scrolling</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- 3. Dark Mode & Theme -->
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200/80 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-black text-slate-900 flex items-center space-x-2.5">
                            <i class="fa-solid fa-moon text-indigo-600"></i>
                            <span>3. Dark Mode & Theme Color</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Switch between standard clean light mode and dark slate contrast theme</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button @click="setThemeMode('light')" 
                                :class="{ 'active': themeMode === 'light' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm"><i class="fa-solid fa-sun"></i></span>
                                <span x-show="themeMode === 'light'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active (Default)</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">Light Mode</h3>
                                <p class="text-xs text-slate-500 mt-1">Standard clean light layout with high contrast typography</p>
                            </div>
                        </button>

                        <button @click="setThemeMode('dark')" 
                                :class="{ 'active': themeMode === 'dark' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-slate-900 text-indigo-400 flex items-center justify-center font-bold text-sm"><i class="fa-solid fa-moon"></i></span>
                                <span x-show="themeMode === 'dark'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base">Dark Mode</h3>
                                <p class="text-xs text-slate-500 mt-1">Dark slate background with high-contrast text for low-light environments</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- 4. Date & Currency Preferences -->
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200/80 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-black text-slate-900 flex items-center space-x-2.5">
                            <i class="fa-solid fa-coins text-indigo-600"></i>
                            <span>4. Date & Currency Display Preferences</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Set format defaults for dates and currency prefixes</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Currency Prefix -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Currency Prefix Format</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button @click="setCurrencyFmt('PKR')" 
                                        :class="currencyFmt === 'PKR' ? 'border-indigo-600 bg-indigo-50 text-indigo-900 font-extrabold' : 'border-slate-200 text-slate-700'"
                                        class="py-2.5 px-3 rounded-xl border text-xs text-center transition">
                                    PKR (Default)
                                </button>
                                <button @click="setCurrencyFmt('Rs')" 
                                        :class="currencyFmt === 'Rs' ? 'border-indigo-600 bg-indigo-50 text-indigo-900 font-extrabold' : 'border-slate-200 text-slate-700'"
                                        class="py-2.5 px-3 rounded-xl border text-xs text-center transition">
                                    Rs. 75,000
                                </button>
                                <button @click="setCurrencyFmt('none')" 
                                        :class="currencyFmt === 'none' ? 'border-indigo-600 bg-indigo-50 text-indigo-900 font-extrabold' : 'border-slate-200 text-slate-700'"
                                        class="py-2.5 px-3 rounded-xl border text-xs text-center transition">
                                    75,000
                                </button>
                            </div>
                        </div>

                        <!-- Date Format -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Date Format Display</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button @click="setDateFmt('DD-MMM-YYYY')" 
                                        :class="dateFmt === 'DD-MMM-YYYY' ? 'border-indigo-600 bg-indigo-50 text-indigo-900 font-extrabold' : 'border-slate-200 text-slate-700'"
                                        class="py-2.5 px-3 rounded-xl border text-xs text-center transition">
                                    30-Jul-2026
                                </button>
                                <button @click="setDateFmt('YYYY-MM-DD')" 
                                        :class="dateFmt === 'YYYY-MM-DD' ? 'border-indigo-600 bg-indigo-50 text-indigo-900 font-extrabold' : 'border-slate-200 text-slate-700'"
                                        class="py-2.5 px-3 rounded-xl border text-xs text-center transition">
                                    2026-07-30
                                </button>
                                <button @click="setDateFmt('DD/MM/YYYY')" 
                                        :class="dateFmt === 'DD/MM/YYYY' ? 'border-indigo-600 bg-indigo-50 text-indigo-900 font-extrabold' : 'border-slate-200 text-slate-700'"
                                        class="py-2.5 px-3 rounded-xl border text-xs text-center transition">
                                    30/07/2026
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Thermal Voucher Receipt Font -->
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200/80 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-black text-slate-900 flex items-center space-x-2.5">
                            <i class="fa-solid fa-receipt text-indigo-600"></i>
                            <span>5. Thermal Voucher Receipt Font Style</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Select font family for printed 80mm thermal payment vouchers</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button @click="setReceiptFont('mono')" 
                                :class="{ 'active': receiptFont === 'mono' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-mono font-bold text-sm">123</span>
                                <span x-show="receiptFont === 'mono'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active (Default)</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base font-mono">Monospace Font</h3>
                                <p class="text-xs text-slate-500 mt-1">Classic fixed-width thermal receipt look with high numbers alignment</p>
                            </div>
                        </button>

                        <button @click="setReceiptFont('sans')" 
                                :class="{ 'active': receiptFont === 'sans' }"
                                class="setting-card p-5 rounded-2xl border text-left flex flex-col justify-between space-y-4">
                            <div class="flex items-center justify-between w-full">
                                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-sans font-bold text-sm">Aa</span>
                                <span x-show="receiptFont === 'sans'" class="px-2 py-0.5 text-[10px] font-black bg-indigo-600 text-white rounded-full uppercase">Active</span>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base font-sans">Sans-Serif Font</h3>
                                <p class="text-xs text-slate-500 mt-1">Modern clean typography font style for printed receipts</p>
                            </div>
                        </button>
                    </div>
                </div>

            </div>

        </main>
    </div>

</body>
</html>
