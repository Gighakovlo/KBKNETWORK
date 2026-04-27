<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Command Center - KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            background-color: #0f172a; 
            background-image: radial-gradient(circle at top right, #1e3a8a, #0f172a); 
        }
        .glass-panel { 
            background: rgba(30, 41, 59, 0.7); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
        }
    </style>
</head>
<body class="h-screen flex justify-center items-center font-sans text-gray-200 overflow-hidden">

    <div class="glass-panel p-10 rounded-2xl shadow-2xl w-full max-w-md relative overflow-hidden border-t-4 border-blue-500">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-600 rounded-full blur-3xl opacity-40"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-teal-600 rounded-full blur-3xl opacity-20"></div>

        <div class="text-center mb-10 relative z-10">
        <div class="flex justify-center mb-10">
            <img src="{{ asset('img/KBK LOGO PUTIH.png') }}" alt="Logo PT KBK" class="h-16 w-auto object-contain transform hover:scale-105 transition-all duration-300 drop-shadow-[0_0_25px_rgba(255,255,255,0.4)]">
        </div>
            <h1 class="text-3xl font-black text-white tracking-wide">Network <span class="text-blue-500">Center</span></h1>
            <p class="text-gray-400 mt-2 text-sm font-medium tracking-wide">Secure Authentication Protocol</p>
        </div>

        @if($errors->any())
            <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6 text-sm font-semibold text-center relative z-10">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST" class="space-y-6 relative z-10" autocomplete="off">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Email Address</label>
                <input type="email" name="email" required autocomplete="off" class="w-full bg-slate-900/80 border border-slate-600 text-white px-4 py-3.5 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition placeholder-gray-600" placeholder="admin@kantor.com">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Security Key</label>
                <input type="password" name="password" required autocomplete="new-password" class="w-full bg-slate-900/80 border border-slate-600 text-white px-4 py-3.5 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition placeholder-gray-600" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-500 hover:to-blue-700 text-white py-4 rounded-xl font-bold transition shadow-[0_0_15px_rgba(37,99,235,0.5)] mt-2 uppercase tracking-widest text-sm">
                Initialize Access &rarr;
            </button>
        </form>
        
        <p class="text-center text-xs text-gray-500 mt-10 relative z-10">&copy; {{ date('Y') }} PT. Krakatau Baja Konstruksi<br>IT Infrastructure & Network Department</p>
    </div>

</body>
</html>