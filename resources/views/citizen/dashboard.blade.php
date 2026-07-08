@extends('app')

@section('role-title', __('Citizen'))
@section('dashboard-title', __('Leaderboard') . ' & Live Tracking Map')

@section('content')
<div class="space-y-8 pb-12">

    <!-- Top Grid Layout: Map (70% on Desktops) vs Report/Leaderboard Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-10 gap-8">
        
        <!-- Live Map Segment (Occupies 70% of width on Desktops = 7/10 cols) -->
        <div class="lg:col-span-7 bg-white p-5 rounded-3xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="text-emerald-600">🌍</span> {{ __('Waste Collection Routes') }} Map
                    </h2>
                    <span class="text-[10px] bg-emerald-50 text-emerald-800 px-3 py-1 rounded-full font-bold border border-emerald-200">
                        🔄 Auto-updates: 30s
                    </span>
                </div>
                <!-- Leaflet container -->
                <div id="live-map" class="w-full h-[450px] rounded-2xl border border-slate-200 z-10 shadow-inner"></div>
                <p class="text-[10px] text-slate-400 mt-2 leading-relaxed">
                    ℹ️ <strong>Map Details:</strong> Green regions highlight planned municipal collections. Moving truck markers <span class="font-bold">🚚</span> represent live GPS positions of trucks. Double-click or click on the map to pin coords for the Incident Report form below.
                </p>
            </div>
        </div>

        <!-- Right Hand Column: Stats, Leaderboard & Quick Recycle (3/10 cols) -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Gamified Leaderboard Segment -->
            <div class="bg-white p-5 rounded-3xl border border-emerald-100 shadow-sm">
                <h3 class="text-sm font-extrabold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">🏆 {{ __('Leaderboard') }}</span>
                    <span class="text-[10px] text-emerald-600 font-bold uppercase">Monthly Competition</span>
                </h3>

                <div class="space-y-2.5 max-h-[220px] overflow-y-auto pr-1">
                    @forelse($leaderboard as $index => $row)
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 {{ Auth::id() == $row->citizen_id ? 'bg-amber-50 border-amber-300' : 'bg-slate-50' }}">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center font-black {{ $index == 0 ? 'bg-yellow-400 text-yellow-950' : ($index == 1 ? 'bg-slate-350 text-slate-900' : ($index == 2 ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-600')) }} text-[10px]">
                                    {{ $index + 1 }}
                                </span>
                                <span class="font-bold text-slate-700 truncate max-w-[120px]">{{ $row->citizen->name }}</span>
                            </div>
                            <span class="text-xs font-black text-slate-850 whitespace-nowrap">{{ $row->points }} Eco-pts</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">No leaderboard logs recorded this month.</p>
                    @endforelse
                </div>

                <!-- Recycling Claim Submission -->
                @auth
                    @if(Auth::user()->role === 'Citizen')
                        <div class="border-t border-slate-100 pt-4 mt-4 space-y-3">
                            <div class="text-xs text-slate-500 flex justify-between items-center">
                                <span>Your points: <strong class="text-emerald-700 text-sm">{{ $myPoints }} pts</strong></span>
                                @if($hasSubmittedToday)
                                    <span class="px-2 py-0.5 text-[9px] font-black rounded-full bg-amber-100 text-amber-800 border border-amber-200">⏳ Limit reached today</span>
                                @endif
                            </div>

                            @if(!$hasSubmittedToday)
                                <form action="{{ route('citizen.recycle.claim') }}" method="POST" class="space-y-2">
                                    @csrf
                                    <textarea name="description" required minlength="20" maxlength="1000"
                                        placeholder="Describe what you recycled (e.g. 2kg plastic bottles, 3 cardboard boxes...)&#10;Min. 20 characters required."
                                        rows="3"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500/30 focus:border-emerald-500 resize-none"></textarea>
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-bold text-slate-500 shrink-0">Claim Pts:</label>
                                        <select name="claimed_points" class="flex-1 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-1 focus:ring-emerald-500/30">
                                            <option value="5">5 pts — Small batch (e.g. 1–2 items)</option>
                                            <option value="10">10 pts — Medium bag</option>
                                            <option value="20" selected>20 pts — Full bin or multiple types</option>
                                            <option value="35">35 pts — Large scale / community effort</option>
                                            <option value="50">50 pts — Exceptional initiative</option>
                                        </select>
                                    </div>
                                    <button type="submit"
                                        class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-650 text-white text-xs font-extrabold rounded-xl transition-all shadow-md cursor-pointer">
                                        🌱 Submit Recycling Claim for Review
                                    </button>
                                </form>
                                <p class="text-[9px] text-slate-400 text-center leading-normal">Claims are reviewed by a municipal officer before points are awarded. One submission per day.</p>
                            @else
                                <div class="text-center py-3 bg-amber-50/50 border border-amber-100 rounded-xl">
                                    <p class="text-xs font-bold text-amber-700">⏳ Today's claim submitted!</p>
                                    <p class="text-[10px] text-amber-600 mt-1">Check your submission status below. Come back tomorrow to submit another.</p>
                                </div>
                            @endif

                            <!-- Citizen's own submission history -->
                            @if(count($mySubmissions) > 0)
                                <div class="border-t border-slate-100 pt-3 space-y-2">
                                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400">Your Recent Claims</span>
                                    @foreach($mySubmissions as $sub)
                                        <div class="flex items-center justify-between text-[10px] py-1.5 border-b border-slate-100/60">
                                            <span class="text-slate-600 truncate max-w-[120px]">{{ Str::limit($sub->description, 30) }}</span>
                                            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                                <span class="font-bold text-slate-700">{{ $sub->claimed_points }}pts</span>
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black {{ $sub->status === 'Pending' ? 'bg-amber-100 text-amber-800' : ($sub->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600') }}">
                                                    {{ $sub->status }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <p class="text-[10px] text-slate-400 text-center mt-3 border-t border-slate-100 pt-3">
                        🔐 <a href="{{ route('login') }}" class="text-emerald-600 font-bold hover:underline">Log in as citizen</a> to submit recycling claims.
                    </p>
                @endauth
            </div>

            <!-- Bulletins Feed Panel -->
            <div class="bg-white p-5 rounded-3xl border border-emerald-100 shadow-sm">
                <h3 class="text-sm font-extrabold text-slate-800 border-b border-slate-100 pb-3 mb-4">
                    📢 {{ __('Announcements') }} Feed
                </h3>
                <div class="space-y-4.5 max-h-[220px] overflow-y-auto pr-1">
                    @forelse($announcements as $ann)
                        <div class="p-3 bg-emerald-50/50 border border-emerald-100/60 rounded-2xl">
                            <h4 class="font-bold text-slate-800 text-xs">
                                {{ app()->getLocale() == 'si' ? $ann->title_si : $ann->title_en }}
                            </h4>
                            <p class="text-[11px] text-slate-600 mt-1 leading-normal">
                                {{ app()->getLocale() == 'si' ? $ann->content_si : $ann->content_en }}
                            </p>
                            <span class="block text-[9px] text-slate-400 text-right mt-1.5">{{ $ann->published_at }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">No broadcasts posted by the municipality.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- Bottom Incident Submission & Personal Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Incident reporting form (FR-CTZ-02) -->
        <div class="bg-white p-6 rounded-3xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="text-emerald-600">🚨</span> {{ __('Submit Report') }}
                </h2>

                <form action="{{ route('citizen.report.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">Incident Category</label>
                        <select name="issue_type" required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                            <option value="Missed Pickup">{{ __('Missed Pickup') }}</option>
                            <option value="Illegal Dumping">{{ __('Illegal Dumping') }}</option>
                            <option value="Hazardous Waste">{{ __('Hazardous Waste') }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">Latitude Coords</label>
                            <input type="text" id="report_lat" name="location_lat" required readonly placeholder="Double-click map"
                                class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono text-slate-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">Longitude Coords</label>
                            <input type="text" id="report_lng" name="location_lng" required readonly placeholder="Double-click map"
                                class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono text-slate-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">Upload Evidence Photo (.jpg / .png only, Max 5MB)</label>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[11px] file:font-black file:uppercase file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100 file:cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-1.5">Describe Incident</label>
                        <textarea name="description" rows="3" placeholder="Provide street coordinates, landmarks, or details..."
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"></textarea>
                    </div>

                    <button type="submit" 
                        class="w-full py-3.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl transition-all shadow-md cursor-pointer">
                        🚨 File Incident Report
                    </button>
                </form>
            </div>
        </div>

        <!-- Personal Reports Inbox -->
        <div class="bg-white p-6 rounded-3xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="text-emerald-600">📋</span> Your Reported Incidents
                </h2>
                
                @auth
                    <div class="space-y-4 max-h-[350px] overflow-y-auto pr-1">
                        @forelse($myReports as $report)
                            <div class="p-4 bg-slate-50 border border-slate-150 rounded-2xl flex justify-between items-start text-xs hover:border-emerald-500/35 transition-all">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-700">Ref: INC-{{ $report->id }}</span>
                                        <span class="px-2 py-0.5 rounded text-[8px] font-bold bg-slate-200 text-slate-700 uppercase">{{ $report->issue_type }}</span>
                                    </div>
                                    <p class="text-slate-500 leading-normal">{{ $report->description ?? 'No details provided.' }}</p>
                                    <span class="block text-[9px] text-slate-400">Filed: {{ $report->created_at->diffForHumans() }}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase {{ $report->status == 'Open' ? 'bg-amber-100 text-amber-800 border border-amber-300/40' : ($report->status == 'Investigating' ? 'bg-indigo-150 text-indigo-850' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $report->status }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-10">You have not filed any reports yet.</p>
                        @endforelse
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
                        <span class="text-4xl mb-3">🔒</span>
                        <h4 class="font-bold text-slate-700 text-sm">Account Required</h4>
                        <p class="text-xs text-slate-400 max-w-xs mt-1 leading-normal">Please sign in to view your filed incidents and track their verification progress in real-time.</p>
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs font-bold mt-4 border border-emerald-100/60 transition-colors">🔐 Sign In Now</a>
                    </div>
                @endauth
            </div>
        </div>

    </div>

</div>

<!-- Leaflet Map Integration & AJAX Polling Script -->
<script>
    var map;
    var truckMarkers = {}; // Keep track of active truck markers
    var routeOverlays = {}; // Pre-drawn route overlays

    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Map
        map = L.map('live-map').setView([6.6825, 80.3995], 13.5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Click map handler: Autofill incident latitude and longitude coordinate inputs!
        map.on('dblclick', function(e) {
            document.getElementById('report_lat').value = e.latlng.lat.toFixed(6);
            document.getElementById('report_lng').value = e.latlng.lng.toFixed(6);
        });

        // Also allow single click to drop coordinates
        map.on('click', function(e) {
            document.getElementById('report_lat').value = e.latlng.lat.toFixed(6);
            document.getElementById('report_lng').value = e.latlng.lng.toFixed(6);
        });

        // Draw static routes
        @foreach($activeRoutes as $rt)
            var coords = [
                @foreach($rt->zone_coordinates as $pt)
                    [{{ $pt['lat'] }}, {{ $pt['lng'] }}],
                @endforeach
            ];

            routeOverlays[{{ $rt->id }}] = L.polygon(coords, {
                color: '#10b981',
                fillColor: '#6ee7b7',
                fillOpacity: 0.25
            }).addTo(map).bindPopup('<strong>Route:</strong> {{ $rt->route_name }}<br>Category: {{ $rt->waste_type }}');
        @endforeach

        // Initial fetch of live truck markers
        fetchActiveTrucks();

        // FR-CTZ-01.1: Poll truck locations every 30 seconds
        setInterval(fetchActiveTrucks, 30000);
    });

    function fetchActiveTrucks() {
        fetch("{{ route('api.active-trucks') }}")
        .then(response => response.json())
        .then(data => {
            // Keep track of active trip IDs in this polling cycle
            var currentTripIds = [];

            data.forEach(function(truck) {
                currentTripIds.push(truck.trip_id);

                var pos = [truck.current_lat, truck.current_lng];

                // Create or move truck marker
                if (truckMarkers[truck.trip_id]) {
                    truckMarkers[truck.trip_id].setLatLng(pos);
                } else {
                    // Create beautiful custom emoji marker icon representing waste trucks!
                    var truckIcon = L.divIcon({
                        html: '<div style="font-size: 26px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25)); transition: all 0.3s ease;">🚚</div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });

                    truckMarkers[truck.trip_id] = L.marker(pos, {icon: truckIcon}).addTo(map);
                }

                // Bind a detailed real-time logistics popup
                truckMarkers[truck.trip_id].bindPopup(
                    '<strong>🚚 Truck Active Duty</strong><br>' +
                    'Driver: ' + truck.driver_name + '<br>' +
                    'Route: ' + truck.route_name + '<br>' +
                    'Waste: <span style="font-weight: bold; color: #b45309">' + truck.waste_type + '</span><br>' +
                    'Cleared Nodes: ' + truck.cleared_nodes.length + '/' + truck.route_coordinates.length
                );
            });

            // Remove markers for trips that are no longer active
            for (var tripId in truckMarkers) {
                if (!currentTripIds.includes(parseInt(tripId))) {
                    map.removeLayer(truckMarkers[tripId]);
                    delete truckMarkers[tripId];
                }
            }
        })
        .catch(err => console.error("Error polling live truck updates:", err));
    }
</script>
@endsection