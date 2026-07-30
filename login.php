<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Footprint HR System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans antialiased relative overflow-hidden select-none">
    
    <!-- Decorative Ambient Background Glows -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-900/80 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-slate-800/80 z-10 space-y-6">
        
        <!-- Header & Branding -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center mx-auto shadow-lg shadow-indigo-500/30 text-white text-2xl">
                <i class="fa-solid fa-users"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight pt-2">Footprint HR Portal</h1>
            <p class="text-xs text-slate-400 font-medium">Enter your authentication credentials to sign in</p>
        </div>

        <!-- Login Form -->
        <form action="includes/auth.php" method="post" class="space-y-4 pt-2">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Username</label>
                <div class="relative flex items-center">
                    <span class="absolute left-4 text-slate-500 text-sm"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" placeholder="Username" id="username" required 
                           class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-950/60 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-100 text-sm placeholder-slate-600 transition outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
                <div class="relative flex items-center">
                    <span class="absolute left-4 text-slate-500 text-sm"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" placeholder="••••••••" id="password" required 
                           class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-950/60 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-100 text-sm placeholder-slate-600 transition outline-none">
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2 mt-2">
                <span>Sign In</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="text-center pt-2">
            <span class="text-[11px] text-slate-500">Footprint HR System &bull; Secure Authentication</span>
        </div>
    </div>

</body>
</html>