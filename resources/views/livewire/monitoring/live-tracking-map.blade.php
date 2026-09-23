@assets
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

<div>

    <!-- Page Header -->
    <div class="page-header" style="margin-bottom:20px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <h1 class="page-title">Monitoring Armada Live GPS</h1>
                <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:20px;font-size:12px;font-weight:700;color:#065f46;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#10b981;animation:pulse 1.5s infinite;"></span>
                    LIVE STREAMING
                </span>
            </div>
            <p class="page-subtitle">Pantau posisi koordinat, kecepatan, dan rute armada secara real-time dari sinyal GPS aplikasi sopir.</p>
        </div>

        <div style="display:flex;align-items:center;gap:12px;">
            <a href="/sopir" target="_blank" class="btn btn-secondary" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Buka App Driver (Sopir)
            </a>
            <button onclick="fetchLocations()" class="btn btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh Peta
            </button>
        </div>
    </div>

    <!-- Main Grid: Map & Vehicle List Panel -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

        <!-- Left Sidebar: Active Fleet List (1 col) -->
        <div class="flex flex-col gap-4">
            <div class="card" style="padding:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <h3 style="font-size:14px;font-weight:700;color:#2a3042;text-transform:uppercase;letter-spacing:0.5px;margin:0;">
                        Armada On-Trip (<span id="fleet-count">0</span>)
                    </h3>
                    <span style="font-size:11px;color:#64748b;" id="last-ping-time">Menghubungkan...</span>
                </div>

                <div id="fleet-list-container" style="display:flex;flex-direction:column;gap:10px;max-height:600px;overflow-y:auto;padding-right:4px;">
                    <div style="text-align:center;padding:30px 10px;color:#94a3b8;font-size:13px;">
                        Memuat data armada bergerak...
                    </div>
                </div>
            </div>

            <!-- Gudang Utama Info Box -->
            <div class="card" style="padding:16px;background:#f8fafc;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#e0e7ff;color:#4338ca;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1e293b;">{{ $warehouse->name }}</div>
                        <div style="font-size:11.5px;color:#64748b;">{{ $warehouse->address }} &bull; Titik Awal Armada</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Interactive Leaflet Map (3 cols) -->
        <div class="xl:col-span-3">
            <div class="card" style="overflow:hidden;position:relative;height:680px;border:1px solid #cbd5e1;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                <!-- Map Container -->
                <div id="fleet-map" style="width:100%;height:100%;z-index:1;"></div>

                <!-- Floating Map Overlay Legend -->
                <div style="position:absolute;bottom:20px;left:20px;z-index:999;background:rgba(255,255,255,0.95);backdrop-filter:blur(4px);padding:10px 14px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);border:1px solid #e2e8f0;font-size:12px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <div style="width:12px;height:12px;border-radius:50%;background:#0d6efd;"></div>
                        <span style="font-weight:600;color:#334155;">Armada Bergerak</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <div style="width:12px;height:12px;border-radius:50%;background:#10b981;"></div>
                        <span style="font-weight:600;color:#334155;">Halal Dedicated</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:13px;">🏢</span>
                        <span style="font-weight:600;color:#334155;">Gudang Asal</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:13px;">🏁</span>
                        <span style="font-weight:600;color:#334155;">Titik Tujuan</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-weight:800;color:#6366f1;font-size:14px;">━</span>
                        <span style="font-weight:600;color:#334155;">Jejak GPS</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-weight:800;color:#0d6efd;font-size:14px;">╍</span>
                        <span style="font-weight:600;color:#334155;">Rencana Rute</span>
                    </div>
                </div>

                <!-- Floating Driver Location Detail Panel (hidden by default) -->
                <div id="driver-location-panel" style="display:none;position:absolute;top:16px;right:16px;z-index:999;background:rgba(255,255,255,0.97);backdrop-filter:blur(8px);padding:14px 16px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.13);border:1px solid #e2e8f0;min-width:260px;max-width:300px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="font-size:12px;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:0.5px;">📍 Lokasi Sopir</div>
                        <button onclick="closeLocationPanel()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:16px;line-height:1;padding:0;">&times;</button>
                    </div>
                    <div style="margin-bottom:8px;">
                        <span id="panel-plate" style="font-weight:800;font-family:monospace;font-size:14px;color:#1e293b;background:#f1f5f9;padding:2px 8px;border-radius:4px;"></span>
                        <span id="panel-driver" style="font-size:12px;color:#64748b;margin-left:8px;"></span>
                    </div>
                    <!-- Lokasi Sekarang -->
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;margin-bottom:8px;">
                        <div style="font-size:11px;font-weight:700;color:#1d4ed8;margin-bottom:4px;">LOKASI SEKARANG</div>
                        <div id="panel-location-now" style="font-size:12.5px;color:#1e293b;font-weight:600;line-height:1.4;">
                            <span style="color:#94a3b8;font-style:italic;">Memuat lokasi...</span>
                        </div>
                        <div id="panel-coords" style="font-size:10.5px;color:#64748b;margin-top:4px;font-family:monospace;"></div>
                    </div>
                    <!-- Terakhir Update -->
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#64748b;">
                        <div>⚡ <span id="panel-speed"></span></div>
                        <div>🕐 <span id="panel-updated"></span></div>
                    </div>
                    <!-- Tujuan -->
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;">
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:2px;">Tujuan:</div>
                        <div id="panel-destination" style="font-size:12px;color:#0ea5e9;font-weight:600;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .vehicle-marker-pulse {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(13, 110, 253, 0.4);
            border: 2px solid #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        .vehicle-marker-inner {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #0d6efd;
        }
        .halal-marker .vehicle-marker-pulse {
            background: rgba(16, 185, 129, 0.4);
            border-color: #10b981;
        }
        .halal-marker .vehicle-marker-inner {
            background: #10b981;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        #driver-location-panel {
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
    </style>

@script
<script>
    let map;
    let markers = {};
    let destMarkers = {};
    let routePolylines = {};
    let trailPolylines = {};
    let fleetDataMap = {};
    // Cache reverse geocode results to avoid repeated requests for same coordinates
    const geocodeCache = {};
    const WAREHOUSE_COORDS = [{{ (float) $warehouse->latitude }}, {{ (float) $warehouse->longitude }}];

    window.initMap = function() {
        const mapContainer = document.getElementById('fleet-map');
        if (!mapContainer) return;
        if (map) return;

        map = L.map('fleet-map', {
            center: WAREHOUSE_COORDS,
            zoom: 11,
            zoomControl: true
        });

        // OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Warehouse marker
        const warehouseIcon = L.divIcon({
            className: 'warehouse-marker',
            html: '<div style="width:28px;height:28px;border-radius:8px;background:#f59e0b;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;box-shadow:0 3px 8px rgba(245,158,11,0.5);border:2px solid #fff;">🏢</div>',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
        L.marker(WAREHOUSE_COORDS, { icon: warehouseIcon })
            .addTo(map)
            .bindPopup('<b>{{ addslashes($warehouse->name) }}</b><br>{{ addslashes($warehouse->address) }}');

        fetchLocations();
        setInterval(fetchLocations, 4000);
    };

    window.fetchLocations = async function() {
        try {
            const res = await fetch('/api/fleet/live-locations');
            const data = await res.json();
            if (data.success) {
                updateFleetUI(data.fleets);
            }
        } catch (err) {
            console.error('Error fetching fleet coordinates:', err);
        }
    };

    /**
     * Reverse geocode lat/lng to a human-readable address using Nominatim.
     * Results are cached per rounded coordinate pair (4 decimal places ≈ 11m precision).
     */
    async function reverseGeocode(lat, lng) {
        const key = `${parseFloat(lat).toFixed(4)},${parseFloat(lng).toFixed(4)}`;
        if (geocodeCache[key]) return geocodeCache[key];

        try {
            const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=id`;
            const res = await fetch(url, { headers: { 'Accept-Language': 'id' } });
            const data = await res.json();

            if (data && data.address) {
                const a = data.address;
                // Build a short, readable description (street + suburb/village/city)
                const parts = [
                    a.road || a.pedestrian || a.footway,
                    a.suburb || a.village || a.town || a.city_district,
                    a.city || a.county || a.state,
                ].filter(Boolean);
                const label = parts.length > 0 ? parts.join(', ') : (data.display_name || 'Tidak diketahui');
                geocodeCache[key] = { label, full: data.display_name || label };
                return geocodeCache[key];
            }
        } catch (e) {
            // Silently fail; caller will show fallback
        }
        const fallback = { label: `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`, full: null };
        geocodeCache[key] = fallback;
        return fallback;
    }

    function updateFleetUI(fleets) {
        const countEl = document.getElementById('fleet-count');
        const pingEl = document.getElementById('last-ping-time');
        const container = document.getElementById('fleet-list-container');

        if (countEl) countEl.innerText = fleets.length;
        if (pingEl) pingEl.innerText = 'Sinkron: ' + new Date().toLocaleTimeString();

        if (!container) return;

        if (fleets.length === 0) {
            container.innerHTML = `
                <div style="text-align:center;padding:30px 10px;color:#94a3b8;font-size:13px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:36px;height:36px;margin:0 auto 8px;display:block;opacity:0.6;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Tidak ada armada yang sedang di perjalanan.<br>
                    <span style="font-size:11.5px;color:#cbd5e1;">Semua unit berada di gudang atau belum dimulai oleh sopir.</span>
                </div>
            `;
            return;
        }

        let listHtml = '';
        fleetDataMap = {};

        fleets.forEach(fleet => {
            const lat = fleet.latitude;
            const lng = fleet.longitude;
            fleetDataMap[fleet.assignment_id] = fleet;

            // 1. Update/Add Leaflet Vehicle Marker
            const markerClass = fleet.is_halal ? 'halal-marker' : '';
            const customIcon = L.divIcon({
                className: markerClass,
                html: `
                    <div class="vehicle-marker-pulse">
                        <div class="vehicle-marker-inner"></div>
                    </div>
                `,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            const popupId = `popup-loc-${fleet.assignment_id}`;
            const popupHtml = `
                <div style="min-width:230px;font-family:'Plus Jakarta Sans',sans-serif;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <span style="background:#1e293b;color:#fff;padding:2px 8px;border-radius:4px;font-weight:700;font-size:12px;letter-spacing:1px;font-family:monospace;">
                            ${fleet.license_plate}
                        </span>
                        ${fleet.is_halal ? '<span style="color:#10b981;font-weight:700;font-size:11px;">✓ Halal</span>' : ''}
                    </div>
                    <div style="font-size:13px;font-weight:700;color:#1e293b;">${fleet.brand_model}</div>
                    <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Sopir: <strong>${fleet.driver_name}</strong></div>
                    <div style="padding:6px 8px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:11.5px;margin-bottom:6px;">
                        <div style="font-weight:700;color:#1d4ed8;font-size:10.5px;margin-bottom:3px;">📍 LOKASI SEKARANG</div>
                        <div id="${popupId}" style="color:#1e293b;font-weight:600;line-height:1.4;">
                            <span style="color:#94a3b8;font-style:italic;">Memuat...</span>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;margin-top:2px;font-family:monospace;">${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}</div>
                    </div>
                    <div style="padding:6px 8px;background:#f1f5f9;border-radius:6px;font-size:11.5px;margin-bottom:6px;">
                        <div>🏁 <strong>Tujuan:</strong> ${fleet.destination}</div>
                        <div style="margin-top:2px;">⚡ <strong>Kecepatan:</strong> ${fleet.speed_kmh} KM/Jam</div>
                        <div style="font-size:10.5px;color:#94a3b8;margin-top:2px;">Update: ${fleet.last_updated}</div>
                    </div>
                    <a href="/kendaraan/${fleet.vehicle_id}" style="display:block;text-align:center;padding:6px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:6px;font-size:12px;font-weight:600;">
                        Lihat Detail Armada
                    </a>
                </div>
            `;

            if (markers[fleet.assignment_id]) {
                markers[fleet.assignment_id].setLatLng([lat, lng]);
                markers[fleet.assignment_id].getPopup().setContent(popupHtml);
            } else if (map) {
                const marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
                marker.bindPopup(popupHtml);
                marker.on('popupopen', () => fillGeocodedLocation(popupId, lat, lng));
                markers[fleet.assignment_id] = marker;
            }

            // 2. Add/Update Historical Trail Polyline (Jejak GPS riil)
            if (fleet.trail && fleet.trail.length > 0 && map) {
                const trailCoords = [WAREHOUSE_COORDS, ...fleet.trail, [lat, lng]];
                if (trailPolylines[fleet.assignment_id]) {
                    trailPolylines[fleet.assignment_id].setLatLngs(trailCoords);
                } else {
                    trailPolylines[fleet.assignment_id] = L.polyline(trailCoords, {
                        color: '#6366f1',
                        weight: 4,
                        opacity: 0.85
                    }).addTo(map);
                }
            }

            // 3. Add/Update Destination Marker 🏁 and Planned Route Polyline
            if (fleet.destination_latitude && fleet.destination_longitude && map) {
                const destLat = parseFloat(fleet.destination_latitude);
                const destLng = parseFloat(fleet.destination_longitude);

                const destIcon = L.divIcon({
                    className: 'dest-marker-div',
                    html: '<div style="width:28px;height:28px;border-radius:8px;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;box-shadow:0 3px 8px rgba(16,185,129,0.5);border:2px solid #fff;">🏁</div>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                const destPopup = `
                    <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;">
                        <strong style="color:#065f46;">🏁 Titik Tujuan (${fleet.license_plate}):</strong><br>
                        ${fleet.destination}
                    </div>
                `;

                if (destMarkers[fleet.assignment_id]) {
                    destMarkers[fleet.assignment_id].setLatLng([destLat, destLng]);
                    destMarkers[fleet.assignment_id].getPopup().setContent(destPopup);
                } else {
                    const dMarker = L.marker([destLat, destLng], { icon: destIcon }).addTo(map);
                    dMarker.bindPopup(destPopup);
                    destMarkers[fleet.assignment_id] = dMarker;
                }

                // Planned route polyline (Dashed line from current position to destination)
                const plannedLine = [[lat, lng], [destLat, destLng]];
                if (routePolylines[fleet.assignment_id]) {
                    routePolylines[fleet.assignment_id].setLatLngs(plannedLine);
                } else {
                    routePolylines[fleet.assignment_id] = L.polyline(plannedLine, {
                        color: '#0d6efd',
                        weight: 3,
                        dashArray: '6, 8',
                        opacity: 0.75
                    }).addTo(map);
                }
            }

            // 4. Generate sidebar card item
            const isSilent = fleet.is_silent;
            const cardBg = isSilent ? 'background:#fff1f2;border:1.5px solid #ef4444;' : 'background:#f8fafc;border:1px solid #e2e8f0;';
            const statusBadge = isSilent 
                ? `<span style="font-size:10.5px;font-weight:800;color:#dc2626;background:#fee2e2;padding:2px 6px;border-radius:4px;border:1px solid #fca5a5;">🚨 Sinyal Putus (${fleet.silence_minutes}m)</span>`
                : `<span style="font-size:11px;font-weight:700;color:${fleet.speed_kmh > 0 ? '#10b981' : '#f59e0b'};">${fleet.speed_kmh} km/h</span>`;

            const cardLocId = `card-loc-${fleet.assignment_id}`;

            listHtml += `
                <div onclick="focusVehicle(${lat}, ${lng}, ${fleet.assignment_id})" style="padding:12px;${cardBg}border-radius:8px;cursor:pointer;transition:all 0.15s ease;" onmouseover="this.style.filter='brightness(0.96)'" onmouseout="this.style.filter='none'">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-weight:700;color:#1e293b;font-family:monospace;font-size:13px;">${fleet.license_plate}</span>
                        ${statusBadge}
                    </div>
                    <div style="font-size:12px;color:#475569;margin-top:2px;">${fleet.brand_model}</div>
                    <div style="font-size:11.5px;color:#64748b;margin-top:4px;">
                        Sopir: <strong>${fleet.driver_name}</strong>
                    </div>
                    <div style="margin-top:5px;padding:5px 8px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:11px;color:#1d4ed8;">
                        <span style="font-weight:700;">📍 Lokasi:</span>
                        <span id="${cardLocId}" style="color:#1e293b;font-weight:600;">
                            <span style="color:#94a3b8;font-style:italic;">Memuat...</span>
                        </span>
                    </div>
                    <div style="font-size:11px;color:#0ea5e9;margin-top:4px;display:flex;align-items:center;gap:4px;">
                        <span>🏁</span>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${fleet.destination}</span>
                    </div>
                    ${isSilent ? `<div style="font-size:10.5px;color:#dc2626;font-weight:700;margin-top:4px;">⚠️ Terakhir Update: ${fleet.last_updated}</div>` : ''}
                    ${fleet.destination_latitude ? '<div style="font-size:10px;color:#10b981;font-weight:700;margin-top:2px;">🏁 Ada titik koordinat tujuan</div>' : ''}
                </div>
            `;

            fillGeocodedLocation(cardLocId, lat, lng);
        });

        container.innerHTML = listHtml;

        fleets.forEach(fleet => {
            fillGeocodedLocation(`card-loc-${fleet.assignment_id}`, fleet.latitude, fleet.longitude);
        });
    }

    async function fillGeocodedLocation(elementId, lat, lng) {
        const geo = await reverseGeocode(lat, lng);
        const el = document.getElementById(elementId);
        if (el) {
            el.innerHTML = geo.label;
            el.title = geo.full || geo.label;
        }
    }

    window.focusVehicle = function(lat, lng, assignmentId) {
        if (!map) return;
        const fleet = fleetDataMap[assignmentId];

        if (fleet && fleet.destination_latitude && fleet.destination_longitude) {
            const destLat = parseFloat(fleet.destination_latitude);
            const destLng = parseFloat(fleet.destination_longitude);
            // Fit bounds to show both vehicle position and destination clearly!
            map.fitBounds([[lat, lng], [destLat, destLng]], { padding: [60, 60] });
        } else {
            map.setView([lat, lng], 15);
        }

        if (markers[assignmentId]) {
            markers[assignmentId].openPopup();
        }

        showLocationPanel(fleet, lat, lng);
    };

    async function showLocationPanel(fleet, lat, lng) {
        const panel = document.getElementById('driver-location-panel');
        if (!panel || !fleet) return;

        document.getElementById('panel-plate').textContent    = fleet.license_plate;
        document.getElementById('panel-driver').textContent   = fleet.driver_name;
        document.getElementById('panel-speed').textContent    = `${fleet.speed_kmh} km/jam`;
        document.getElementById('panel-updated').textContent  = fleet.last_updated;
        document.getElementById('panel-destination').textContent = fleet.destination;
        document.getElementById('panel-coords').textContent   = `${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}`;
        document.getElementById('panel-location-now').innerHTML = '<span style="color:#94a3b8;font-style:italic;">Memuat lokasi...</span>';

        panel.style.display = 'block';

        const geo = await reverseGeocode(lat, lng);
        const locEl = document.getElementById('panel-location-now');
        if (locEl) locEl.innerHTML = geo.label;
    }

    window.closeLocationPanel = function() {
        const panel = document.getElementById('driver-location-panel');
        if (panel) panel.style.display = 'none';
    };

    // Inisialisasi saat Leaflet sudah dimuat
    if (typeof L !== 'undefined') {
        initMap();
    } else {
        window.addEventListener('load', initMap);
    }
    document.addEventListener('livewire:navigated', initMap);
</script>
@endscript
</div>
