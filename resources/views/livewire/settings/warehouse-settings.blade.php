@assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

<div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom:24px;">
        <div>
            <h1 class="page-title">Pengaturan Gudang / Pusat Distribusi</h1>
            <p class="page-subtitle">Atur nama, alamat, dan koordinat lokasi gudang utama. Klik di peta untuk memilih lokasi secara akurat.</p>
        </div>
    </div>

    <!-- Success Banner -->
    @if($saved)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
        style="display:flex;align-items:center;gap:10px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;margin-bottom:20px;font-size:13.5px;font-weight:600;color:#065f46;">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Data gudang berhasil disimpan! Peta monitoring sudah diperbarui.
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:24px;align-items:start;">

        <!-- LEFT: Form -->
        <div class="card" style="padding:24px;">
            <h3 style="font-size:14px;font-weight:700;color:#2a3042;margin:0 0 20px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #eff2f7;padding-bottom:14px;">
                Detail Gudang
            </h3>

            <div style="display:flex;flex-direction:column;gap:18px;">

                <!-- Nama -->
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#495057;margin-bottom:6px;">
                        Nama Gudang <span style="color:#ef4444;">*</span>
                    </label>
                    <input wire:model="name" type="text"
                        placeholder="contoh: Gudang Pulogadung"
                        style="width:100%;padding:9px 12px;border:1.5px solid #dee2e6;border-radius:8px;font-size:13.5px;color:#343a40;outline:none;transition:border .2s;"
                        onfocus="this.style.borderColor='#556ee6'" onblur="this.style.borderColor='#dee2e6'">
                    @error('name') <span style="font-size:11.5px;color:#ef4444;margin-top:4px;display:block;">{{ $message }}</span> @enderror
                </div>

                <!-- Alamat -->
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#495057;margin-bottom:6px;">
                        Alamat Lengkap <span style="color:#ef4444;">*</span>
                    </label>
                    <textarea wire:model="address" rows="3"
                        placeholder="Jl. Raya Logistik No. 1, Jakarta Timur"
                        style="width:100%;padding:9px 12px;border:1.5px solid #dee2e6;border-radius:8px;font-size:13.5px;color:#343a40;outline:none;resize:vertical;transition:border .2s;"
                        onfocus="this.style.borderColor='#556ee6'" onblur="this.style.borderColor='#dee2e6'"></textarea>
                    @error('address') <span style="font-size:11.5px;color:#ef4444;margin-top:4px;display:block;">{{ $message }}</span> @enderror
                </div>

                <!-- Koordinat Display -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;text-transform:uppercase;letter-spacing:.4px;">
                        📍 Koordinat (klik peta untuk ubah)
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:4px;">Latitude</label>
                            <input id="input-lat" wire:model="lat" type="number" step="0.0000001"
                                oninput="updateMarkerFromInputs()"
                                style="width:100%;padding:7px 10px;border:1.5px solid #dee2e6;border-radius:6px;font-size:12.5px;font-family:monospace;color:#343a40;outline:none;"
                                onfocus="this.style.borderColor='#556ee6'" onblur="this.style.borderColor='#dee2e6'">
                            @error('lat') <span style="font-size:11px;color:#ef4444;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:4px;">Longitude</label>
                            <input id="input-lng" wire:model="lng" type="number" step="0.0000001"
                                oninput="updateMarkerFromInputs()"
                                style="width:100%;padding:7px 10px;border:1.5px solid #dee2e6;border-radius:6px;font-size:12.5px;font-family:monospace;color:#343a40;outline:none;"
                                onfocus="this.style.borderColor='#556ee6'" onblur="this.style.borderColor='#dee2e6'">
                            @error('lng') <span style="font-size:11px;color:#ef4444;">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Search Alamat di Map -->
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#495057;margin-bottom:6px;">
                        🔍 Cari Lokasi di Peta
                    </label>
                    <div style="display:flex;gap:8px;">
                        <input id="search-input" type="text"
                            placeholder="Ketik nama jalan/daerah atau koordinat Google Maps..."
                            style="flex:1;padding:9px 12px;border:1.5px solid #dee2e6;border-radius:8px;font-size:13px;color:#343a40;outline:none;"
                            onfocus="this.style.borderColor='#556ee6'" onblur="this.style.borderColor='#dee2e6'"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();searchLocation();}">
                        <button type="button" onclick="searchLocation()"
                            style="padding:9px 16px;background:#556ee6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
                            Cari
                        </button>
                    </div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:5px;line-height:1.4;">
                        Bisa ketik nama jalan (misal: <code>Bukit Duri Tanjakan</code>) atau paste koordinat dari Google Maps (misal: <code>-6.2202, 106.8579</code>).
                    </div>
                    <div id="search-status" style="font-size:12px;margin-top:8px;"></div>
                </div>

                <!-- Simpan -->
                <button wire:click="save" wire:loading.attr="disabled"
                    style="width:100%;padding:11px;background:linear-gradient(135deg,#556ee6,#6f42c1);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;letter-spacing:.3px;transition:opacity .2s;"
                    wire:loading.class="opacity-60">
                    <span wire:loading.remove>💾 Simpan Pengaturan Gudang</span>
                    <span wire:loading>Menyimpan...</span>
                </button>

            </div>
        </div>

        <!-- RIGHT: Interactive Map -->
        <div class="card" wire:ignore style="overflow:hidden;padding:0;">
            <div style="padding:14px 18px;border-bottom:1px solid #eff2f7;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:13.5px;font-weight:700;color:#2a3042;">Pilih Lokasi di Peta</span>
                        <span id="warehouse-map-badge" style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;">Google Maps</span>
                    </div>
                    <div style="font-size:11.5px;color:#64748b;margin-top:2px;">Klik di peta atau geser pin untuk menetapkan koordinat gudang</div>
                </div>
                <button type="button" onclick="resetMapView()"
                    style="padding:6px 12px;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;font-weight:600;color:#475569;cursor:pointer;">
                    Reset Tampilan
                </button>
            </div>
            <div id="warehouse-map" wire:ignore style="width:100%;height:480px;z-index:1;"></div>
            <div style="padding:10px 16px;background:#f8fafc;border-top:1px solid #eff2f7;font-size:11.5px;color:#64748b;">
                💡 <strong>Tip:</strong> Klik di peta atau geser pin &bull; Alamat & koordinat terisi otomatis dengan Google Places.
            </div>
        </div>
    </div>

@script
<script>
    let warehouseGoogleMap = null;
    let warehouseGoogleMarker = null;
    let warehouseLeafletMap = null;
    let warehouseLeafletMarker = null;

    function isGoogleReady() {
        return typeof window.google !== 'undefined' && 
               typeof window.google.maps !== 'undefined' && 
               typeof window.google.maps.Map === 'function';
    }

    const initWarehouseMap = () => {
        const container = document.getElementById('warehouse-map');
        if (!container) return;

        const badgeEl = document.getElementById('warehouse-map-badge');
        const initLat = {{ (float) $lat }};
        const initLng = {{ (float) $lng }};

        if (isGoogleReady()) {
            if (badgeEl) {
                badgeEl.innerText = 'Google Maps';
                badgeEl.style.background = '#e0f2fe';
                badgeEl.style.color = '#0369a1';
                badgeEl.style.borderColor = '#bae6fd';
            }

            if (warehouseLeafletMap) {
                try { warehouseLeafletMap.remove(); } catch(e) {}
                warehouseLeafletMap = null;
            }

            if (!warehouseGoogleMap || warehouseGoogleMap.getDiv() !== container) {
                container.innerHTML = '';
                warehouseGoogleMap = new google.maps.Map(container, {
                    center: { lat: initLat, lng: initLng },
                    zoom: 15,
                    mapTypeControl: true,
                    streetViewControl: true,
                    fullscreenControl: true,
                    gestureHandling: 'greedy'
                });

                warehouseGoogleMarker = new google.maps.Marker({
                    position: { lat: initLat, lng: initLng },
                    map: warehouseGoogleMap,
                    draggable: true,
                    title: '📦 Lokasi Gudang'
                });

                warehouseGoogleMap.addListener('click', function(e) {
                    const lat = e.latLng.lat();
                    const lng = e.latLng.lng();
                    moveMarker(lat, lng);
                });

                warehouseGoogleMarker.addListener('dragend', function(e) {
                    const lat = e.latLng.lat();
                    const lng = e.latLng.lng();
                    moveMarker(lat, lng);
                });
            } else {
                warehouseGoogleMarker.setPosition({ lat: initLat, lng: initLng });
                warehouseGoogleMap.panTo({ lat: initLat, lng: initLng });
            }
            return;
        }

        // Leaflet fallback
        if (badgeEl) {
            badgeEl.innerText = 'OpenStreetMap';
            badgeEl.style.background = '#fef3c7';
            badgeEl.style.color = '#92400e';
            badgeEl.style.borderColor = '#fde68a';
        }

        if (typeof L === 'undefined') return;

        if (warehouseLeafletMap) {
            warehouseLeafletMap.invalidateSize();
            return;
        }

        container.innerHTML = '';
        warehouseLeafletMap = L.map('warehouse-map', {
            center: [initLat, initLng],
            zoom: 14,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(warehouseLeafletMap);

        warehouseLeafletMarker = L.marker([initLat, initLng], { draggable: true })
            .addTo(warehouseLeafletMap)
            .bindPopup('📦 Lokasi Gudang')
            .openPopup();

        warehouseLeafletMap.on('click', function(e) {
            const { lat, lng } = e.latlng;
            moveMarker(lat, lng);
        });

        warehouseLeafletMarker.on('dragend', function(e) {
            const { lat, lng } = e.target.getLatLng();
            moveMarker(lat, lng);
        });
    };

    window.moveMarker = function(lat, lng) {
        if (warehouseGoogleMap && warehouseGoogleMarker) {
            warehouseGoogleMarker.setPosition({ lat: parseFloat(lat), lng: parseFloat(lng) });
            warehouseGoogleMap.panTo({ lat: parseFloat(lat), lng: parseFloat(lng) });
        } else if (warehouseLeafletMap && warehouseLeafletMarker) {
            warehouseLeafletMarker.setLatLng([lat, lng]);
            warehouseLeafletMap.panTo([lat, lng]);
        }

        $wire.setCoords(lat, lng);
        reverseGeocode(lat, lng);
    };

    window.reverseGeocode = function(lat, lng) {
        if (isGoogleReady()) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, function(results, status) {
                if (status === 'OK' && results && results[0]) {
                    $wire.setAddress(results[0].formatted_address);
                }
            });
            return;
        }

        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=id`)
            .then(r => r.json())
            .then(data => {
                if (data && data.display_name) {
                    $wire.setAddress(data.display_name);
                }
            })
            .catch(() => {});
    };

    window.updateMarkerFromInputs = function() {
        const latInput = document.getElementById('input-lat');
        const lngInput = document.getElementById('input-lng');
        if (!latInput || !lngInput) return;

        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            if (warehouseGoogleMap && warehouseGoogleMarker) {
                warehouseGoogleMarker.setPosition({ lat, lng });
                warehouseGoogleMap.panTo({ lat, lng });
            } else if (warehouseLeafletMap && warehouseLeafletMarker) {
                warehouseLeafletMarker.setLatLng([lat, lng]);
                warehouseLeafletMap.panTo([lat, lng]);
            }
        }
    };

    window.searchLocation = function() {
        const q = document.getElementById('search-input').value.trim();
        const statusEl = document.getElementById('search-status');
        if (!q) return;

        const coordMatch = q.match(/(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)/);
        if (coordMatch) {
            const latF = parseFloat(coordMatch[1]);
            const lngF = parseFloat(coordMatch[2]);

            if (latF >= -90 && latF <= 90 && lngF >= -180 && lngF <= 180) {
                if (warehouseGoogleMap && warehouseGoogleMarker) {
                    warehouseGoogleMarker.setPosition({ lat: latF, lng: lngF });
                    warehouseGoogleMap.setCenter({ lat: latF, lng: lngF });
                    warehouseGoogleMap.setZoom(16);
                } else if (warehouseLeafletMap && warehouseLeafletMarker) {
                    warehouseLeafletMap.setView([latF, lngF], 17);
                    warehouseLeafletMarker.setLatLng([latF, lngF]);
                }
                $wire.setCoords(latF, lngF);
                reverseGeocode(latF, lngF);
                statusEl.innerHTML = '<span style="color:#059669;font-weight:600;">✅ Koordinat terdeteksi & disetel ke peta!</span>';
                setTimeout(() => { statusEl.innerHTML = ''; }, 4000);
                return;
            }
        }

        if (isGoogleReady()) {
            statusEl.innerHTML = '<span style="color:#2563eb;">Mencari lokasi di Google Maps...</span>';
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: q, componentRestrictions: { country: 'id' } }, function(results, status) {
                if (status === 'OK' && results && results[0]) {
                    const loc = results[0].geometry.location;
                    const latF = loc.lat();
                    const lngF = loc.lng();
                    const address = results[0].formatted_address;

                    if (warehouseGoogleMap && warehouseGoogleMarker) {
                        warehouseGoogleMarker.setPosition({ lat: latF, lng: lngF });
                        warehouseGoogleMap.setCenter({ lat: latF, lng: lngF });
                        warehouseGoogleMap.setZoom(16);
                    } else if (warehouseLeafletMap && warehouseLeafletMarker) {
                        warehouseLeafletMap.setView([latF, lngF], 16);
                        warehouseLeafletMarker.setLatLng([latF, lngF]);
                        warehouseLeafletMap.invalidateSize();
                    }
                    $wire.setCoords(latF, lngF);
                    $wire.setAddress(address);
                    statusEl.innerHTML = '<span style="color:#059669;font-weight:600;">✅ Lokasi ditemukan: ' + address + '</span>';
                    setTimeout(() => { statusEl.innerHTML = ''; }, 5000);
                } else {
                    statusEl.innerHTML = '<span style="color:#ef4444;">❌ Lokasi tidak ditemukan di Google Maps. Coba kata kunci lain atau pilih dari rekomendasi.</span>';
                }
            });
            return;
        }

        statusEl.innerHTML = '<span style="color:#556ee6;">Mencari lokasi di OpenStreetMap...</span>';
        fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1&accept-language=id`)
            .then(r => r.json())
            .then(data => {
                if (data && data.length > 0) {
                    const { lat, lon, display_name } = data[0];
                    const latF = parseFloat(lat);
                    const lngF = parseFloat(lon);

                    if (warehouseLeafletMap && warehouseLeafletMarker) {
                        warehouseLeafletMap.setView([latF, lngF], 16);
                        warehouseLeafletMarker.setLatLng([latF, lngF]);
                        warehouseLeafletMap.invalidateSize();
                    }
                    $wire.setCoords(latF, lngF);
                    $wire.setAddress(display_name);
                    statusEl.innerHTML = '<span style="color:#059669;font-weight:600;">✅ Lokasi ditemukan: ' + display_name + '</span>';
                    setTimeout(() => { statusEl.innerHTML = ''; }, 5000);
                } else {
                    statusEl.innerHTML = '<span style="color:#ef4444;">❌ Lokasi tidak ditemukan di OpenStreetMap.</span>';
                }
            })
            .catch(() => {
                statusEl.innerHTML = '<span style="color:#ef4444;">❌ Gagal mencari jaringan, coba lagi.</span>';
            });
    };

    window.resetMapView = function() {
        const initLat = {{ (float) $lat }};
        const initLng = {{ (float) $lng }};
        if (warehouseGoogleMap && warehouseGoogleMarker) {
            warehouseGoogleMap.setCenter({ lat: initLat, lng: initLng });
            warehouseGoogleMap.setZoom(15);
        } else if (warehouseLeafletMap && warehouseLeafletMarker) {
            warehouseLeafletMap.setView([initLat, initLng], 14);
            warehouseLeafletMap.invalidateSize();
        }
    };

    function setupWarehouseGoogleAutocomplete() {
        const searchInput = document.getElementById('search-input');
        if (searchInput && isGoogleReady() && google.maps.places) {
            if (!searchInput.dataset.autocompleteBound) {
                searchInput.dataset.autocompleteBound = 'true';
                const autocomplete = new google.maps.places.Autocomplete(searchInput, {
                    componentRestrictions: { country: 'id' },
                    fields: ['geometry', 'name', 'formatted_address']
                });

                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) {
                        window.searchLocation();
                        return;
                    }

                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();
                    const name = place.name || '';
                    const addr = place.formatted_address || '';
                    const full = (name && !addr.startsWith(name)) ? `${name}, ${addr}` : (addr || name);

                    if (warehouseGoogleMap && warehouseGoogleMarker) {
                        warehouseGoogleMarker.setPosition({ lat, lng });
                        warehouseGoogleMap.setCenter({ lat, lng });
                        warehouseGoogleMap.setZoom(16);
                    } else if (warehouseLeafletMap && warehouseLeafletMarker) {
                        warehouseLeafletMap.setView([lat, lng], 16);
                        warehouseLeafletMarker.setLatLng([lat, lng]);
                        warehouseLeafletMap.invalidateSize();
                    }
                    $wire.setCoords(lat, lng);
                    $wire.setAddress(full);

                    const statusEl = document.getElementById('search-status');
                    if (statusEl) {
                        statusEl.innerHTML = '<span style="color:#059669;font-weight:600;">✅ Tempat dipilih dari Google: ' + (place.name || full) + '</span>';
                        setTimeout(() => { statusEl.innerHTML = ''; }, 4000);
                    }
                });
            }
        }
    }

    function bootstrapWarehouseMap(retryCount = 0) {
        const hasGoogleKey = Boolean(window.GOOGLE_MAPS_KEY || "{{ config('services.google.maps_api_key') }}");

        if (isGoogleReady()) {
            initWarehouseMap();
            setupWarehouseGoogleAutocomplete();
            return;
        }

        if (hasGoogleKey && retryCount < 30) {
            setTimeout(() => bootstrapWarehouseMap(retryCount + 1), 100);
            return;
        }

        initWarehouseMap();
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        bootstrapWarehouseMap();
    } else {
        window.addEventListener('DOMContentLoaded', () => bootstrapWarehouseMap());
    }

    document.addEventListener('livewire:navigated', () => {
        bootstrapWarehouseMap();
    });
</script>
@endscript
</div>
