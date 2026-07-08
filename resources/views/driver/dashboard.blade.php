@extends('app')

@section('role-title', 'Collection Vehicle Driver')
@section('dashboard-title', 'Distribution Log & Route Portal')

@section('content')
<div class="space-y-6 pb-12 max-w-4xl mx-auto">

    <!-- Active Route Run Section -->
    @if($activeTrip)
        <div class="bg-white rounded-3xl border border-emerald-100 shadow-xl overflow-hidden animate-fadeIn">
            <!-- Active Header -->
            <div class="bg-gradient-to-r from-emerald-700 to-teal-800 text-white p-6">
                <div class="flex items-center justify-between">
                    <span class="px-3.5 py-1 text-[10px] font-black uppercase tracking-wider bg-emerald-500 rounded-full border border-emerald-400">
                        ⚡ Active Trip Running
                    </span>
                    <span class="text-xs font-mono text-emerald-200">Started: {{ $activeTrip->start_time }}</span>
                </div>
                <h3 class="text-2xl font-black tracking-tight mt-3">{{ $activeTrip->route->route_name }}</h3>
                <p class="text-xs text-emerald-150 mt-1 flex items-center gap-1.5">
                    ♻️ Waste category: <strong class="text-amber-400 font-extrabold uppercase">{{ $activeTrip->route->waste_type }}</strong>
                </p>
            </div>

            <div class="p-6 space-y-6">
                <!-- Zone checklist (One touch cleared targets: FR-DRV-02) -->
                <div>
                    <h4 class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-3.5">Zone Collection Checkpoints</h4>
                    <div class="space-y-3">
                        @php
                            $coords = $activeTrip->route->zone_coordinates ?? [];
                            $cleared = $activeTrip->cleared_nodes ?? [];
                        @endphp
                        
                        @foreach($coords as $index => $coord)
                            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-emerald-500/35 transition-all">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" id="node-{{ $index }}" 
                                        onclick="toggleNode({{ $activeTrip->id }}, {{ $index }})"
                                        {{ in_array($index, $cleared) ? 'checked' : '' }}
                                        class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500/20 border-slate-350 cursor-pointer">
                                    <label for="node-{{ $index }}" class="text-sm font-bold text-slate-700 cursor-pointer">
                                        Checkpoint Node #{{ $index + 1 }}
                                        <span class="text-[10px] text-slate-400 font-normal block">📍 Coordinates: {{ round($coord['lat'], 4) }}, {{ round($coord['lng'], 4) }}</span>
                                    </label>
                                </div>
                                <span id="status-badge-{{ $index }}" class="px-2.5 py-1 rounded-full text-[9px] font-black tracking-wider uppercase {{ in_array($index, $cleared) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500' }}">
                                    {{ in_array($index, $cleared) ? 'Cleared' : 'Pending' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Complete trip actions -->
                <div class="border-t border-slate-100 pt-6">
                    <form id="complete-trip-form" action="{{ route('driver.trip.complete') }}" method="POST" onsubmit="return captureGPS(this, 'end_lat', 'end_lng')">
                        @csrf
                        <input type="hidden" name="pickup_log_id" value="{{ $activeTrip->id }}">
                        <input type="hidden" id="end_lat" name="end_lat">
                        <input type="hidden" id="end_lng" name="end_lng">

                        <button type="submit" 
                            class="w-full py-4 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-rose-600/10 hover:shadow-rose-600/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            🛑 End Route & Mark Completed
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <!-- No Active Trip: Start Panel -->
        <div class="bg-white p-6 rounded-3xl border border-emerald-100 shadow-sm animate-fadeIn">
            <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                <span class="text-emerald-600">🚚</span> Start Collection Run
            </h2>

            <form id="start-trip-form" action="{{ route('driver.trip.start') }}" method="POST" onsubmit="return captureGPS(this, 'start_lat', 'start_lng')">
                @csrf
                <input type="hidden" id="start_lat" name="start_lat">
                <input type="hidden" id="start_lng" name="start_lng">

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2">Assigned Routes (Select to Dispatch)</label>
                        <select name="route_id" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                            @forelse($assignments as $assignment)
                                <option value="{{ $assignment->route->id }}">{{ $assignment->route->route_name }} ({{ $assignment->route->waste_type }} - Scheduled: {{ $assignment->route->scheduled_day }})</option>
                            @empty
                                <option disabled>No route assignments for you. Contact Town Hall.</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100/50 flex items-start gap-3">
                        <span class="text-xl mt-0.5">🌐</span>
                        <p class="text-xs text-emerald-800 leading-normal">
                            This system requires <strong>HTML5 Geolocation access</strong> to track truck locations. When starting the route, your browser will prompt for GPS coordinates to visualize on the citizen live map.
                        </p>
                    </div>

                    <button type="submit" @if(count($assignments) == 0) disabled @endif
                        class="w-full py-4 bg-emerald-700 hover:bg-emerald-650 disabled:bg-slate-200 disabled:text-slate-400 text-white font-extrabold text-sm rounded-xl shadow-md cursor-pointer transition-all flex items-center justify-center gap-2">
                        🚀 Start Active Route Run
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Trip Logs History -->
    <div class="bg-white p-6 rounded-3xl border border-emerald-100 shadow-sm mt-8">
        <h3 class="text-lg font-extrabold text-slate-800 mb-4 border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="text-emerald-600">🕰️</span> Your Collection History
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-500 border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                        <th class="py-3.5 px-4">Route Name</th>
                        <th class="py-3.5 px-4">Start Time</th>
                        <th class="py-3.5 px-4">End Time</th>
                        <th class="py-3.5 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tripHistory as $history)
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $history->route->route_name }}</td>
                            <td class="py-3.5 px-4 text-xs font-semibold text-slate-550">{{ $history->start_time }}</td>
                            <td class="py-3.5 px-4 text-xs font-semibold text-slate-550">{{ $history->end_time }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="px-2.5 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-800 rounded-full border border-emerald-200/60">
                                    Completed
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">No completed trip history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Geolocation API Script & AJAX Checklist Toggle (FR-DRV-01.1 & FR-DRV-02) -->
<script>
    // Prompt Geolocation API permission immediately upon landing (FR-DRV-01.1)
    document.addEventListener("DOMContentLoaded", function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) { console.log("GPS initialized successfully: " + pos.coords.latitude); },
                function(err) { alert("Please enable GPS localization to run driver routes successfully."); }
            );
        } else {
            alert("Your browser does not support Geolocation APIs.");
        }
    });

    // Capture GPS before submitting forms
    function captureGPS(form, latId, lngId) {
        if (!navigator.geolocation) {
            alert("GPS API is not supported on this device. Submission cancelled.");
            return false;
        }

        // Display spinner on button
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = "🌀 Retrieving GPS coordinates...";

        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById(latId).value = position.coords.latitude;
                document.getElementById(lngId).value = position.coords.longitude;
                form.submit();
            },
            function(error) {
                alert("Could not capture GPS location details. Please ensure location is enabled on your smartphone.");
                btn.disabled = false;
                btn.innerHTML = "Retry Form Action";
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );

        return false; // Wait for position return async
    }

    // Toggle cleared checkpoints via AJAX (FR-DRV-02)
    function toggleNode(pickupLogId, nodeIndex) {
        var statusBadge = document.getElementById('status-badge-' + nodeIndex);
        statusBadge.innerHTML = 'Updating...';

        fetch("{{ route('driver.trip.toggle-node') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                pickup_log_id: pickupLogId,
                node_index: nodeIndex
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var cleared = data.cleared_nodes;
                var indexInt = parseInt(nodeIndex);
                if (cleared.includes(indexInt)) {
                    statusBadge.innerHTML = 'Cleared';
                    statusBadge.className = 'px-2.5 py-1 rounded-full text-[9px] font-black tracking-wider uppercase bg-emerald-100 text-emerald-800';
                } else {
                    statusBadge.innerHTML = 'Pending';
                    statusBadge.className = 'px-2.5 py-1 rounded-full text-[9px] font-black tracking-wider uppercase bg-slate-200 text-slate-500';
                }
            } else {
                alert('Could not update checkpoint node. Check connection.');
                statusBadge.innerHTML = 'Error';
            }
        })
        .catch(err => {
            alert('A connection error occurred.');
            statusBadge.innerHTML = 'Error';
        });
    }
</script>
@endsection