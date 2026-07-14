@extends('app')

@section('role-title', __('System Administrator'))
@section('dashboard-title', __('Dashboard') . ': ' . __('Route Management') . ' & ' . __('Logistics'))

@section('content')
<div class="space-y-8 pb-12">

    <!-- Overview Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Routes') }}</h3>
                <p class="text-3xl font-black text-slate-800 mt-2">{{ $totalRoutes }}</p>
            </div>
            <span class="text-4xl p-3 bg-emerald-50 rounded-xl text-emerald-600">🗺️</span>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Active Drivers') }}</h3>
                <p class="text-3xl font-black text-slate-800 mt-2">{{ $activeDrivers }}</p>
            </div>
            <span class="text-4xl p-3 bg-amber-50 rounded-xl text-amber-600">🚚</span>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Citizen Reports') }}</h3>
                <p class="text-3xl font-black text-slate-800 mt-2">{{ $totalReports }}</p>
            </div>
            <span class="text-4xl p-3 bg-rose-50 rounded-xl text-rose-600">🚨</span>
        </div>
        <div class="bg-white p-6 rounded-2xl border {{ $pendingRecyclingCount > 0 ? 'border-amber-300 bg-amber-50/40' : 'border-emerald-100' }} shadow-sm hover:shadow-md transition-all flex items-center justify-between relative">
            @if($pendingRecyclingCount > 0)
                <span class="absolute top-3 right-3 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center text-[9px] font-black text-white animate-pulse">!</span>
            @endif
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Pending Eco Claims') }}</h3>
                <p class="text-3xl font-black {{ $pendingRecyclingCount > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-2">{{ $pendingRecyclingCount }}</p>
            </div>
            <span class="text-4xl p-3 bg-amber-50 rounded-xl">🌱</span>
        </div>
    </div>

    <!-- Main Workspace: Route Creator & Interactive Map -->
    <div id="manage-routes" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Route Creation Panel -->
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="text-emerald-600">➕</span> {{ __('Create Collection Route') }}
                </h2>
                
                <form action="{{ route('admin.routes.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Route Name') }}</label>
                        <input type="text" name="route_name" required placeholder="{{ __('e.g. Zone Alpha - Town Center') }}" 
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Waste Category') }}</label>
                            <select name="waste_type" required 
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                                <option value="Organic">{{ __('Organic') }} 🍎</option>
                                <option value="Plastic">{{ __('Plastic') }} 🥤</option>
                                <option value="Paper">{{ __('Paper') }} 📦</option>
                                <option value="Hazardous">{{ __('Hazardous') }} ⚠️</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Scheduled Day') }}</label>
                            <select name="scheduled_day" required 
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                                <option value="Monday">{{ __('Monday') }}</option>
                                <option value="Tuesday">{{ __('Tuesday') }}</option>
                                <option value="Wednesday">{{ __('Wednesday') }}</option>
                                <option value="Thursday">{{ __('Thursday') }}</option>
                                <option value="Friday">{{ __('Friday') }}</option>
                                <option value="Saturday">{{ __('Saturday') }}</option>
                                <option value="Sunday">{{ __('Sunday') }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500">{{ __('Zone Polygon Coordinates') }}</label>
                            <button type="button" onclick="clearDrawing()" class="text-[9px] font-bold text-rose-600 hover:underline">{{ __('Clear Map Points') }}</button>
                        </div>
                        <textarea id="coords-text" name="zone_coordinates" readonly required placeholder="{{ __('[]') }}" rows="4"
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-mono text-slate-500 focus:outline-none"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1 leading-normal">🗺️ <strong>{{ __('How to draw') }}:</strong> {{ __('Click points on the map on the right to define the boundary vertices. A green polygon overlay will represent your active route path.') }}</p>
                    </div>

                    <button type="submit" 
                        class="w-full py-3 bg-emerald-700 hover:bg-emerald-650 text-white font-bold text-sm rounded-xl transition-all shadow-md mt-2 cursor-pointer">
                        💾 {{ __('Save Route Path') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Leaflet Map Container -->
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="text-emerald-600">🗺️</span> {{ __('Route Boundary Editor (Leaflet & OSM)') }}
                </h2>
                <div id="admin-map" class="w-full h-[400px] rounded-xl border border-slate-200 z-10"></div>
            </div>
        </div>

    </div>

    <!-- Lower Section Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Active Routes List -->
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between gap-2 mb-4 border-b border-slate-100 pb-3">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="text-emerald-600">📋</span> Active Collection Routes
                </h2>
                <!-- Search Filter -->
                <form method="GET" class="flex gap-2">
                    <input type="text" name="route_search" placeholder="{{ __('Search routes...') }}" value="{{ request('route_search') }}"
                        class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                        🔍 {{ __('Search') }}
                    </button>
                    @if(request('route_search'))
                        <a href="?{{ http_build_query(request()->except('route_search')) }}" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg">
                            ✕ {{ __('Clear') }}
                        </a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-500 border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                            <th class="py-3.5 px-4">{{ __('Route Name') }}</th>
                            <th class="py-3.5 px-4">{{ __('Waste Category') }}</th>
                            <th class="py-3.5 px-4">{{ __('Scheduled Day') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Vertices') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($routes as $route)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $route->route_name }}</td>
                                <td class="py-3 px-4">
                                    @if($route->waste_type == 'Organic')
                                        <span class="px-2.5 py-1 text-[10px] font-bold bg-amber-50 text-amber-800 rounded-full border border-amber-200/60">🍎 Organic</span>
                                    @elseif($route->waste_type == 'Plastic')
                                        <span class="px-2.5 py-1 text-[10px] font-bold bg-cyan-50 text-cyan-800 rounded-full border border-cyan-200/60">🥤 Plastic</span>
                                    @elseif($route->waste_type == 'Paper')
                                        <span class="px-2.5 py-1 text-[10px] font-bold bg-indigo-50 text-indigo-850 rounded-full border border-indigo-200/60">📦 Paper</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold bg-rose-50 text-rose-800 rounded-full border border-rose-200/60">⚠️ Hazardous</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-semibold">{{ $route->scheduled_day }}</td>
                                <td class="py-3 px-4 text-center font-mono text-xs">{{ count($route->zone_coordinates) }} {{ __('Vertices') }}</td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('admin.routes.destroy', $route->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('{{ __('Are you sure you want to archive this route? (It will be preserved in the database via Soft Deletes)') }}')" 
                                            class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline">
                                            🗑️ Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="text-5xl opacity-50 mb-2">🗺️</div>
                                        <p class="text-sm font-semibold text-slate-500">{{ __('No active routes defined.') }}</p>
                                        <p class="text-xs text-slate-400">{{ __('Use the drawing tool above to add your first collection route.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination Links -->
            <div class="mt-4 flex justify-center">
                {{ $routes->appends(request()->query())->links() }}
            </div>

            <!-- Archived Routes -->
            @if(count($archivedRoutes) > 0)
            <div class="mt-6 border-t border-slate-100 pt-4">
                <details class="group">
                    <summary class="text-xs font-bold text-slate-500 cursor-pointer hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>▶</span> {{ __('View Archived Routes') }} ({{ count($archivedRoutes) }})
                    </summary>
                    <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <table class="w-full text-left text-[11px] text-slate-500">
                            <tbody>
                                @foreach($archivedRoutes as $archivedRoute)
                                    <tr class="border-b border-slate-200/50 last:border-0">
                                        <td class="py-2 px-2 font-bold">{{ $archivedRoute->route_name }}</td>
                                        <td class="py-2 px-2 text-slate-400">Archived on {{ $archivedRoute->deleted_at->format('Y-m-d') }}</td>
                                        <td class="py-2 px-2 text-right">
                                            <form action="{{ route('admin.routes.restore', $archivedRoute->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-emerald-600 hover:underline font-bold">♻️ Restore</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
            @endif
        </div>

        <!-- Driver Assignment Panel -->
        <div id="assign-drivers" class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="text-emerald-600">📅</span> {{ __('Driver Logistics Schedule') }}
                </h2>
                
                <!-- Driver assignment form -->
                <form action="{{ route('admin.routes.assign') }}" method="POST" class="space-y-4 mb-6">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Select Route') }}</label>
                        <select name="route_id" required 
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->route_name }} ({{ $route->scheduled_day }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Select Truck Operator / Driver') }}</label>
                        <select name="driver_id" required 
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Assignment Date') }}</label>
                        <input type="date" name="assigned_date" required min="{{ date('Y-m-d') }}"
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    </div>

                    <button type="submit" 
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-sm rounded-xl transition-all shadow-md cursor-pointer">
                        🗓️ {{ __('Assign Driver Duty') }}
                    </button>
                </form>

                <!-- Current schedules list -->
                <div class="border-t border-slate-100 pt-4">
                        <span class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2">{{ __('Duty Assignments (Active)') }}</span>
                    <div class="space-y-3 max-h-[220px] overflow-y-auto pr-1">
                        @forelse($assignments as $assignment)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-150 flex items-center justify-between text-xs hover:border-emerald-500/35 transition-all">
                                <div>
                                    <h4 class="font-bold text-slate-800">{{ $assignment->route?->route_name ?? __('Archived Route') }}</h4>
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ __('Driver:') }} {{ $assignment->driver?->name ?? __('Archived Driver') }}</p>
                                </div>
                                <span class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[9px] font-black text-slate-600">{{ $assignment->assigned_date }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">{{ __('No driver assignments logged yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- SECTION: Driver Account Management                      -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Add New Driver Form -->
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                <span class="text-emerald-600">➕</span> {{ __('Register New Driver') }}
            </h2>
            <form action="{{ route('admin.drivers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Full Name') }}</label>
                    <input type="text" name="name" required placeholder="e.g. D. Bandara (Driver 03)"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Email Address') }}</label>
                    <input type="email" name="email" required placeholder="driver@wmis.lk"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Contact Phone') }}</label>
                    <input type="text" name="phone" placeholder="+94 7X XXX XXXX"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Temporary Password') }}</label>
                    <input type="password" name="password" required minlength="8" placeholder="Min. 8 characters"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                </div>
                <button type="submit"
                    class="w-full py-3 bg-emerald-700 hover:bg-emerald-650 text-white font-bold text-sm rounded-xl transition-all shadow-md cursor-pointer">
                    🚚 Create Driver Account
                </button>
            </form>
        </div>

        <!-- Driver Roster Table -->
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between gap-2 mb-4 border-b border-slate-100 pb-3">
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="text-emerald-600">👥</span> {{ __('Driver Roster') }}
                </h2>
                <!-- Search Filter -->
                <form method="GET" class="flex gap-2">
                    <input type="text" name="driver_search" placeholder="{{ __('Search drivers...') }}" value="{{ request('driver_search') }}"
                        class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                        🔍 Search
                    </button>
                    @if(request('driver_search'))
                        <a href="?{{ http_build_query(request()->except('driver_search')) }}" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg">
                            ✕ Clear
                        </a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-500 border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                            <th class="py-3.5 px-4">{{ __('Driver Name') }}</th>
                            <th class="py-3.5 px-4">Email</th>
                            <th class="py-3.5 px-4">Phone</th>
                            <th class="py-3.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($drivers as $driver)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-800">🚚 {{ $driver->name }}</td>
                                <td class="py-3 px-4 text-xs font-mono">{{ $driver->email }}</td>
                                <td class="py-3 px-4 text-xs">{{ $driver->phone ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('admin.drivers.destroy', $driver->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('{{ __('Remove driver :name? (Their historical data will be preserved via Soft Deletes)', ['name' => $driver->name]) }}')"
                                            class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline">
                                            🗑️ {{ __('Remove') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="text-5xl opacity-50 mb-2">🧑‍✈️</div>
                                        <p class="text-sm font-semibold text-slate-500">{{ __('No drivers registered.') }}</p>
                                        <p class="text-xs text-slate-400">{{ __('Add a new driver using the registration form to start assigning routes.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination Links -->
            <div class="mt-4 flex justify-center">
                {{ $drivers->appends(request()->query())->links() }}
            </div>

            <!-- Archived Drivers -->
            @if(count($archivedDrivers) > 0)
            <div class="mt-6 border-t border-slate-100 pt-4">
                <details class="group">
                    <summary class="text-xs font-bold text-slate-500 cursor-pointer hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>▶</span> {{ __('View Archived Drivers') }} ({{ count($archivedDrivers) }})
                    </summary>
                    <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <table class="w-full text-left text-[11px] text-slate-500">
                            <tbody>
                                @foreach($archivedDrivers as $archivedDriver)
                                    <tr class="border-b border-slate-200/50 last:border-0">
                                        <td class="py-2 px-2 font-bold">{{ $archivedDriver->name }}</td>
                                        <td class="py-2 px-2 text-slate-400">{{ $archivedDriver->email }}</td>
                                        <td class="py-2 px-2 text-right">
                                            <form action="{{ route('admin.drivers.restore', $archivedDriver->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-emerald-600 hover:underline font-bold">♻️ Restore</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
            @endif
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- SECTION: Recycling Claim Moderation Queue              -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm">
        <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
            <span class="text-emerald-600">🌱</span> Recycling Claim Moderation Queue
            @if($pendingRecyclingCount > 0)
                <span class="px-2 py-0.5 bg-amber-500 text-white text-[10px] font-black rounded-full ml-1">{{ $pendingRecyclingCount }} Pending</span>
            @endif
        </h2>

        <div class="space-y-4">
            @forelse($recyclingSubmissions as $sub)
                <div class="p-4 rounded-2xl border {{ $sub->status === 'Pending' ? 'border-amber-200 bg-amber-50/30' : ($sub->status === 'Approved' ? 'border-emerald-200 bg-emerald-50/20' : 'border-slate-200 bg-slate-50') }} flex flex-col sm:flex-row justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-slate-700">{{ $sub->citizen?->name }}</span>
                            <span class="text-[9px] text-slate-400">Ref: #{{ $sub->id }}</span>
                            <span class="px-2 py-0.5 text-[9px] font-black rounded-full uppercase
                                {{ $sub->status === 'Pending' ? 'bg-amber-100 text-amber-800 border border-amber-300/60' : ($sub->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600') }}">
                                {{ $sub->status === 'Pending' ? '⏳' : ($sub->status === 'Approved' ? '✅' : '❌') }} {{ $sub->status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 leading-normal">{{ $sub->description }}</p>
                        <div class="text-[10px] text-slate-400 flex flex-wrap gap-4">
                            <span>🏅 Claimed: <strong>{{ $sub->claimed_points }} eco-points</strong></span>
                            <span>📅 Submitted: {{ $sub->created_at->diffForHumans() }}</span>
                            @if($sub->reviewed_at)
                                <span>👤 Reviewed by: {{ $sub->reviewer?->name ?? 'System' }} · {{ $sub->reviewed_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if($sub->admin_note)
                            <div class="text-[11px] text-slate-500 bg-white border border-slate-200 rounded-lg px-3 py-2 mt-1">
                                <span class="font-bold">{{ __('Admin Note:') }}</span> {{ $sub->admin_note }}
                            </div>
                        @endif
                    </div>

                    @if($sub->status === 'Pending')
                        <div class="flex flex-col gap-2 shrink-0 w-full sm:w-48">
                            {{-- Approve --}}
                            <form action="{{ route('admin.recycling.approve', $sub->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="admin_note" value="Verified and approved by municipal officer.">
                                <button type="submit"
                                    class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl transition-all cursor-pointer">
                                    ✅ Approve & Award {{ $sub->claimed_points }}pts
                                </button>
                            </form>
                            {{-- Reject with reason --}}
                            <form action="{{ route('admin.recycling.reject', $sub->id) }}" method="POST" class="space-y-2">
                                @csrf
                                <input type="text" name="admin_note" required placeholder="{{ __('Rejection reason (required)') }}"
                                    class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-rose-400">
                                <button type="submit"
                                    onclick="return confirm('Reject this recycling submission?')"
                                    class="w-full py-2 bg-rose-100 hover:bg-rose-200 text-rose-800 text-xs font-extrabold rounded-xl transition-all cursor-pointer border border-rose-200">
                                    ❌ Reject
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-16 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 flex flex-col items-center justify-center mt-4">
                    <div class="text-5xl opacity-50 mb-3">🌱</div>
                    <h3 class="text-base font-bold text-slate-600 mb-1">{{ __('Queue is Empty') }}</h3>
                    <p class="text-xs text-slate-400 max-w-xs">{{ __('There are currently no pending recycling submissions awaiting your review.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- announcements & citizen reports -->
    <div id="broadcaster" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Post Announcement Form -->
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="text-emerald-600">📢</span> Broadcast Eco Announcement
                </h2>
                
                <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('English Title') }}</label>
                        <input type="text" name="title_en" required placeholder="e.g. Garbage Schedule Alteration"
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Sinhala Title') }} (සිංහල මාතෘකාව)</label>
                        <input type="text" name="title_si" required placeholder="e.g. අපද්‍රව්‍ය එකතු කිරීමේ කාලසටහන වෙනස්වීම"
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('English Content') }}</label>
                        <textarea name="content_en" required placeholder="Type English announcement details here..." rows="3"
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">{{ __('Sinhala Content') }} (සිංහල විස්තරය)</label>
                        <textarea name="content_si" required placeholder="සිංහල නිවේදනය මෙතන ලියන්න..." rows="3"
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"></textarea>
                    </div>

                    <button type="submit" 
                        class="w-full py-3 bg-emerald-850 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl transition-all shadow-md cursor-pointer">
                        📢 Broadcast Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Citizen Incident Reports -->
        <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-4 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                            <span class="text-emerald-600">🚨</span> Citizen Incident Inbox
                        </h2>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.export.reports.csv') }}" class="px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-[10px] font-black uppercase tracking-widest rounded-lg flex items-center gap-1 transition-colors">
                                📥 CSV
                            </a>
                            <a href="{{ route('admin.export.reports.print') }}" target="_blank" class="px-3 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-[10px] font-black uppercase tracking-widest rounded-lg flex items-center gap-1 transition-colors">
                                🖨️ PDF / Print
                            </a>
                        </div>
                    </div>
                    <!-- Search and Filter -->
                    <form method="GET" class="flex gap-2">
                        <select name="report_status" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                            <option value="">All Statuses</option>
                            <option value="Open" {{ request('report_status') === 'Open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                            <option value="Investigating" {{ request('report_status') === 'Investigating' ? 'selected' : '' }}>{{ __('Investigating') }}</option>
                            <option value="Resolved" {{ request('report_status') === 'Resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                        </select>
                        <input type="text" name="report_search" placeholder="Search by type..." value="{{ request('report_search') }}"
                            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                            🔍 Search
                        </button>
                        @if(request('report_search') || request('report_status'))
                            <a href="?{{ http_build_query(request()->except(['report_search', 'report_status'])) }}" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg">
                                ✕ Clear
                            </a>
                        @endif
                    </form>
                </div>
                <div class="space-y-4">
                    @forelse($reports as $report)
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-emerald-500/40 transition-all flex flex-col sm:flex-row justify-between gap-4">
                            <div class="space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase text-slate-700 bg-slate-200">{{ __('Incident #') }}{{ $report->id }}</span>
                                    @if($report->issue_type == 'Missed Pickup')
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase text-amber-700 bg-amber-50 border border-amber-200/50">🗑️ Missed Pickup</span>
                                    @elseif($report->issue_type == 'Illegal Dumping')
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase text-rose-700 bg-rose-50 border border-rose-200/50">🚮 Illegal Dumping</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase text-red-800 bg-red-50 border border-red-200/50">☢️ Hazardous Waste</span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-slate-800 text-sm">{{ __('Submitted By:') }} {{ $report->citizen?->name ?? __('Anonymous Citizen') }}</h4>
                                <p class="text-xs text-slate-500 leading-normal">{{ $report->description ?? 'No description details provided.' }}</p>
                                <div class="text-[10px] text-slate-400 flex flex-wrap gap-x-4 gap-y-1">
                                    <span>📍 Coords: {{ $report->location_lat }}, {{ $report->location_lng }}</span>
                                    <span>📅 Submitted: {{ $report->created_at->diffForHumans() }}</span>
                                </div>
                                {{-- Inline Status Update Form --}}
                                <form action="{{ route('admin.reports.status', $report->id) }}" method="POST" class="flex items-center gap-2 mt-2">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()"
                                        class="px-2 py-1 border rounded-lg text-[11px] font-bold focus:outline-none focus:ring-1 focus:ring-emerald-500
                                        {{ $report->status == 'Open' ? 'bg-amber-50 border-amber-300 text-amber-800' : ($report->status == 'Investigating' ? 'bg-indigo-50 border-indigo-300 text-indigo-800' : 'bg-emerald-50 border-emerald-300 text-emerald-800') }}">
                                        <option value="Open" {{ $report->status == 'Open' ? 'selected' : '' }}>⏳ Open</option>
                                        <option value="Investigating" {{ $report->status == 'Investigating' ? 'selected' : '' }}>🔍 Investigating</option>
                                        <option value="Resolved" {{ $report->status == 'Resolved' ? 'selected' : '' }}>✅ Resolved</option>
                                    </select>
                                    <span class="text-[9px] text-slate-400">← change to update</span>
                                </form>
                            </div>
                            <div class="shrink-0 flex flex-col items-end gap-2">
                                @if($report->image_path)
                                    <a href="{{ asset('storage/' . $report->image_path) }}" target="_blank" class="block w-24 h-16 rounded-lg overflow-hidden border border-slate-200 shadow-sm relative group bg-black">
                                        <img src="{{ asset('storage/' . $report->image_path) }}" class="w-full h-full object-cover group-hover:opacity-75 transition-opacity">
                                        <span class="absolute inset-0 flex items-center justify-center text-[9px] text-white bg-black/40 opacity-0 group-hover:opacity-100 font-bold transition-all">View Photo</span>
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">No Photo</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-8">{{ __('Citizen report inbox is currently empty.') }}</p>
                    @endforelse
                </div>
                <!-- Pagination Links -->
                <div class="mt-4 flex justify-center">
                    {{ $reports->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

    </div>

    <!-- Audit Event Log Table -->
    <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm">
        <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
            <span class="text-emerald-600">📋</span> Security & System Event Audit Log (Audit Trail)
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-500 text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Event Action</th>
                        <th class="py-3 px-4">Authorized Agent</th>
                        <th class="py-3 px-4">Logged Record Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150">
                    @forelse($systemLogs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4 text-xs font-semibold">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black {{ str_contains($log->action, 'Create') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs font-semibold text-slate-650">{{ $log->user?->name ?? 'System Process' }}</td>
                            <td class="py-3 px-4 text-xs font-mono">{{ $log->entity_type }} [ID: {{ $log->entity_id }}]</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">No system events logged in audit table.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Leaflet Polygon Drawing Script Logic -->
<script>
    var map;
    var markers = [];
    var polygon;

    // Initialize Map on Ratnapura Center
    document.addEventListener("DOMContentLoaded", function () {
        map = L.map('admin-map').setView([6.6825, 80.3995], 13.5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Click handler to draw polygon
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            
            // Add a marker
            var marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            markers.push(marker);

            // Re-draw polygon and update textarea when dragged or clicked
            marker.on('dragend', updatePolygon);
            
            updatePolygon();
        });

        // Add pre-existing routes as read-only polygon overlays to avoid conflicts visually!
        @foreach($routes as $rt)
            L.polygon([
                @foreach($rt->zone_coordinates as $pt)
                    [{{ $pt['lat'] }}, {{ $pt['lng'] }}],
                @endforeach
            ], {
                color: '#10b981',
                fillColor: '#a7f3d0',
                fillOpacity: 0.3
            }).addTo(map).bindPopup('<strong>Route:</strong> {{ $rt->route_name }} ({{ $rt->waste_type }})');
        @endforeach
    });

    function updatePolygon() {
        var coords = [];
        markers.forEach(function(m) {
            coords.push({lat: m.getLatLng().lat, lng: m.getLatLng().lng});
        });

        // Re-draw polygon
        if (polygon) {
            map.removeLayer(polygon);
        }

        if (coords.length > 0) {
            var leafletCoords = coords.map(c => [c.lat, c.lng]);
            polygon = L.polygon(leafletCoords, {
                color: '#10b981',
                fillColor: '#34d399',
                fillOpacity: 0.45
            }).addTo(map);
        }

        // Update coordinate output box
        document.getElementById('coords-text').value = JSON.stringify(coords);
    }

    function clearDrawing() {
        // Clear all markers from map
        markers.forEach(function(m) {
            map.removeLayer(m);
        });
        markers = [];
        
        // Remove polygon
        if (polygon) {
            map.removeLayer(polygon);
        }
        
        document.getElementById('coords-text').value = '';
    }
</script>
@endsection