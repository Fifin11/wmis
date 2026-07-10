<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMIS - @yield('role-title', 'Municipal Portal')</title>
    <!-- Google Fonts for premium typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'Noto Sans Sinhala', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .eco-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(16, 185, 129, 0.15);
        }
        .eco-gradient {
            background: linear-gradient(135deg, #064e3b 0%, #022c22 100%);
        }
    </style>
    <!-- Leaflet.js Assets (Loaded globally for maps) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body class="bg-emerald-50/40 text-slate-800 font-sans flex flex-col md:flex-row min-h-screen">

    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 eco-gradient text-white flex flex-col justify-between p-5 shadow-2xl shrink-0">
        <div>
            <!-- Header Seal / Logo -->
            <div class="flex items-center gap-2.5 mb-8 border-b border-emerald-800/60 pb-5">
                <span class="text-3xl">🌿</span>
                <div>
                    <div class="text-sm font-extrabold tracking-wider text-amber-400 uppercase">Rathnapura</div>
                    <div class="text-[10px] text-emerald-300 font-medium tracking-widest uppercase">{{ __('Waste Logistics') }}</div>
                </div>
            </div>
            
            <!-- Dynamic Navigation Links based on user session role -->
            <nav class="space-y-2">
                @auth
                    @if(Auth::user()->role === 'Admin')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-850 hover:bg-emerald-800 text-white font-semibold transition-all shadow-md {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-800 border-l-4 border-amber-400' : 'opacity-85' }}">
                            <span>📊</span> {{ __('Dashboard') }}
                        </a>
                        <div class="text-[10px] font-bold tracking-wider text-emerald-500 uppercase px-4 pt-4 pb-1">{{ __('Administrative Functions') }}</div>
                        <a href="#manage-routes" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-emerald-900 text-emerald-100 hover:text-white transition-all text-sm">
                            <span>🗺️</span> {{ __('Route Management') }}
                        </a>
                        <a href="#assign-drivers" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-emerald-900 text-emerald-100 hover:text-white transition-all text-sm">
                            <span>🚚</span> {{ __('Drivers & Schedules') }}
                        </a>
                        <a href="#broadcaster" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-emerald-900 text-emerald-100 hover:text-white transition-all text-sm">
                            <span>📢</span> {{ __('Post Announcement') }}
                        </a>
                    @elseif(Auth::user()->role === 'Driver')
                        <a href="{{ route('driver.portal') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-850 hover:bg-emerald-800 text-white font-semibold transition-all shadow-md {{ request()->routeIs('driver.portal') ? 'bg-emerald-800 border-l-4 border-amber-400' : 'opacity-85' }}">
                            <span>🚚</span> {{ __('Driver Portal') }}
                        </a>
                    @endif
                @endauth
                
                <div class="text-[10px] font-bold tracking-wider text-emerald-500 uppercase px-4 pt-4 pb-1">{{ __('Citizen Portal') }}</div>
                <a href="{{ route('citizen.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-emerald-900 text-emerald-100 hover:text-white transition-all text-sm {{ request()->routeIs('citizen.dashboard') ? 'bg-emerald-800 border-l-4 border-amber-400 text-white' : '' }}">
                    <span>🌐</span> {{ __('Public Live Map') }}
                </a>
            </nav>
        </div>

        <!-- Footer / Session Status -->
        <div class="border-t border-emerald-800/80 pt-4 mt-8 flex flex-col gap-3">
            <!-- Language switcher -->
            <div class="flex items-center justify-between bg-emerald-950/80 px-2.5 py-2 rounded-lg border border-emerald-800/40">
                <span class="text-xs text-emerald-400 flex items-center gap-1">🌐 {{ __('Language') }}:</span>
                <div class="flex gap-1.5">
                    <a href="{{ route('locale.switch', 'en') }}" class="px-2 py-1 rounded text-[10px] font-extrabold uppercase {{ app()->getLocale() == 'en' ? 'bg-amber-400 text-emerald-950' : 'bg-emerald-900 text-emerald-300 hover:bg-emerald-800' }}">EN</a>
                    <a href="{{ route('locale.switch', 'si') }}" class="px-2 py-1 rounded text-[10px] font-extrabold {{ app()->getLocale() == 'si' ? 'bg-amber-400 text-emerald-950' : 'bg-emerald-900 text-emerald-300 hover:bg-emerald-800' }}">සිංහල</a>
                </div>
            </div>

            <div class="flex items-center justify-between px-1">
                @auth
                    <div class="max-w-[140px] truncate">
                        <p class="text-[10px] text-emerald-500 font-medium">Signed in as:</p>
                        <p class="text-xs font-semibold text-emerald-100 truncate">{{ Auth::user()->name }}</p>
                        <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-900 text-amber-400 font-bold border border-amber-500/20 mt-1 inline-block">{{ Auth::user()->role }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs bg-rose-700/80 hover:bg-rose-600 px-3 py-2 rounded-lg font-bold transition-all text-white shadow-md cursor-pointer border border-rose-500/20">
                            🚪
                        </button>
                    </form>
                @else
                    <div>
                        <p class="text-[10px] text-emerald-500 font-medium">{{ __('Public Mode') }}</p>
                        <a href="{{ route('login') }}" class="text-xs font-bold text-amber-400 hover:underline">🔐 {{ __('Staff Sign In') }}</a>
                    </div>
                @endauth
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 md:p-8 flex flex-col overflow-y-auto">
        <!-- Dashboard Top Banner -->
        <header class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6 bg-white/70 border border-emerald-100 p-5 rounded-2xl shadow-sm backdrop-blur-md">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                    <span class="text-emerald-600">♻️</span> @yield('dashboard-title', __('Smart Waste Logistics Node'))
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    🏛️ Ratnapura Municipal Council • රත්නපුර මහා නගර සභාව • Environment & Sanitation Division
                </p>
            </div>
            <div class="shrink-0 self-start sm:self-center">
                <span class="px-3.5 py-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 rounded-full flex items-center gap-1.5 border border-emerald-200/60 shadow-inner">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ __('Live Server Connected') }}
                </span>
            </div>
        </header>

        <!-- Dynamic Success/Error Alerts -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 text-emerald-900 p-4 rounded-r-xl mb-6 shadow-sm flex items-center gap-3 animate-fadeIn">
                <span class="text-lg">🌿</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-900 p-4 rounded-r-xl mb-6 shadow-sm animate-fadeIn">
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-lg">⚠️</span>
                    <span class="text-sm font-bold">Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 ml-8 text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Yield View Content -->
        <div class="flex-1">
            @yield('content')
        </div>
    </main>

</body>
</html>