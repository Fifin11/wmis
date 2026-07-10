<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMIS - Secure Authentication</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }

        // Autofill credentials helper
        function autofill(email, password) {
            document.getElementById('email-input').value = email;
            document.getElementById('password-input').value = password;
        }
    </script>
</head>
<body class="bg-emerald-50/50 min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-5xl w-full grid md:grid-cols-2 min-h-[600px] border border-emerald-100/50">
        
        <!-- Brand / Banner Section -->
        <div class="bg-gradient-to-br from-emerald-800 via-emerald-900 to-teal-950 p-10 flex flex-col justify-between text-white relative">
            <!-- Background aesthetic leaves pattern overlay -->
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
            
            <div class="relative">
                <span class="bg-emerald-500/25 text-emerald-300 text-[10px] font-extrabold uppercase tracking-widest px-3.5 py-1.5 rounded-full border border-emerald-500/10 backdrop-blur-md">
                    🏛️ {{ __('Municipal Council Division') }}
                </span>
                <h1 class="text-5xl font-black tracking-tight mt-10 flex items-center gap-2">
                    wmis <span class="text-3xl text-emerald-400">🌿</span>
                </h1>
                <p class="text-emerald-100/80 text-sm mt-3 leading-relaxed max-w-md">
                    {{ __('Smart Municipal Waste Logistics & Citizen Engagement Platform. Digitize collection routes, report dumpings, and participate in green gamification initiatives.') }}
                </p>
            </div>
            
            <div class="border-t border-emerald-800/80 pt-8 relative">
                <p class="text-[10px] text-emerald-400 uppercase tracking-widest font-extrabold">{{ __('Academic Project Submission') }}</p>
                <p class="text-base font-bold text-white mt-1">ATI Ratnapura</p>
                <div class="text-xs text-emerald-300/60 mt-1">HNDIT 4052 • Individual Programming Project</div>
            </div>
        </div>

        <!-- Login Form Section -->
        <div class="p-10 flex flex-col justify-center bg-white">
            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">{{ __('Portal Sign In') }}</h2>
                <p class="text-slate-400 text-xs mt-1">{{ __('Please enter your authorized municipal credentials.') }}</p>
            </div>

            <!-- Error handling -->
            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mb-6 rounded-r-xl">
                    <div class="text-xs font-semibold text-rose-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">{{ __('Email Address') }}</label>
                    <input type="email" id="email-input" name="email" value="{{ old('email') }}" required placeholder="name@wmis.lk"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200/80 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition-all duration-200">
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">{{ __('Password') }}</label>
                    <input type="password" id="password-input" name="password" required placeholder="••••••••"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200/80 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition-all duration-200">
                </div>

                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-emerald-700 hover:bg-emerald-600 active:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-700/10 hover:shadow-emerald-750/20 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-150 mt-4 cursor-pointer">
                    {{ __('Access System Portal') }}
                </button>
            </form>

            <!-- Quick Demo Access Autofills -->
            <div class="mt-8 border-t border-slate-100 pt-6">
                <span class="block text-[9px] font-extrabold uppercase tracking-widest text-slate-400 mb-3">{{ __('Quick Demo Authentication') }}</span>
                <div class="grid grid-cols-3 gap-2">
                    <button onclick="autofill('admin@wmis.lk', 'admin123')" 
                        class="px-2 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg text-[10px] font-extrabold border border-emerald-100/60 transition-colors">
                        👑 {{ __('Admin') }}
                    </button>
                    <button onclick="autofill('silva@wmis.lk', 'driver123')" 
                        class="px-2 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-[10px] font-extrabold border border-amber-100/60 transition-colors">
                        🚚 {{ __('Driver') }}
                    </button>
                    <button onclick="autofill('citizen@wmis.lk', 'citizen123')" 
                        class="px-2 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-850 rounded-lg text-[10px] font-extrabold border border-indigo-100/60 transition-colors">
                        🌐 {{ __('Citizen') }}
                    </button>
                </div>
            </div>

            <!-- Citizen Guest Bypass Link -->
            <div class="mt-5 text-center">
                <a href="{{ route('citizen.dashboard') }}" class="text-xs font-semibold text-slate-450 hover:text-emerald-700 transition-colors">
                    🌍 {{ __('Continue as Anonymous Citizen (Bypass)') }}
                </a>
            </div>
        </div>

    </div>

</body>
</html>