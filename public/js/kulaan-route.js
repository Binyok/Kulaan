/**
 * KulaanRoute — garis rute jalan di dalam peta Kulaan (Leaflet).
 *
 * Mengambil geometri rute dari OSRM (data OpenStreetMap) untuk titik-titik
 * [lat, lng] yang diberikan, lalu menggambarnya sebagai garis biru
 * berbingkai putih — pola yang sama dengan aplikasi ojek online.
 * Bila layanan rute tidak terjangkau, gambar garis putus-putus lurus antar
 * titik sebagai cadangan agar peta tidak pernah kosong/rusak.
 *
 * Dipakai oleh: peta live GPS UMKM, peta per-pengiriman UMKM, dan
 * mini-peta rute pada kartu tugas kurir (data-route-map).
 */
(function () {
    'use strict';

    var cache = new Map();

    function keyOf(points) {
        return points.map(function (p) { return p[0].toFixed(4) + ',' + p[1].toFixed(4); }).join(';');
    }

    function haversineMeters(a, b) {
        var rad = Math.PI / 180;
        var dLat = (b[0] - a[0]) * rad;
        var dLng = (b[1] - a[1]) * rad;
        var s = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(a[0] * rad) * Math.cos(b[0] * rad) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return 6371000 * 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
    }

    function fetchRoute(points) {
        if (!points || points.length < 2) {
            return Promise.resolve(null);
        }
        var key = keyOf(points);
        if (cache.has(key)) {
            return Promise.resolve(cache.get(key));
        }
        var coords = points.map(function (p) { return p[1] + ',' + p[0]; }).join(';');
        var url = 'https://router.project-osrm.org/route/v1/driving/' + coords + '?overview=full&geometries=geojson';
        return fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (json) {
                var route = json && json.routes && json.routes[0];
                if (!route || !route.geometry) {
                    return null;
                }
                var result = {
                    coords: route.geometry.coordinates.map(function (c) { return [c[1], c[0]]; }),
                    km: route.distance / 1000,
                    min: Math.max(1, Math.round(route.duration / 60))
                };
                cache.set(key, result);
                return result;
            })
            .catch(function () { return null; });
    }

    function formatInfo(info) {
        if (!info) {
            return '';
        }
        var km = info.km >= 10 ? info.km.toFixed(0) : info.km.toFixed(1);
        return '±' + String(km).replace('.', ',') + ' km • ±' + info.min + ' menit';
    }

    /**
     * Gambar (atau gambar ulang) rute pada peta. `state` adalah objek milik
     * pemanggil yang dipertahankan antar panggilan: {layer, key, pendingKey,
     * info}. Penggambaran yang sama tidak diulang; onInfo(info|null)
     * dipanggil setiap selesai menggambar.
     */
    function drawRoute(map, state, points, onInfo) {
        if (!window.L || !map || !points || points.length < 2) {
            return;
        }
        var key = keyOf(points);
        if (state.key === key && state.layer) {
            if (onInfo) { onInfo(state.info || null); }
            return;
        }
        state.pendingKey = key;
        fetchRoute(points).then(function (route) {
            if (state.pendingKey !== key) {
                return; // sudah ada permintaan yang lebih baru
            }
            if (state.layer) {
                map.removeLayer(state.layer);
                state.layer = null;
            }
            var group = window.L.layerGroup();
            var latlngs = route ? route.coords : points;
            window.L.polyline(latlngs, { color: '#ffffff', weight: 9, opacity: 0.85 }).addTo(group);
            window.L.polyline(latlngs, {
                color: '#1a73e8',
                weight: 5,
                opacity: 1,
                dashArray: route ? null : '1 7',
                lineCap: 'round'
            }).addTo(group);
            group.addTo(map);
            state.layer = group;
            state.key = key;
            state.info = route ? { km: route.km, min: route.min } : null;
            if (onInfo) { onInfo(state.info); }
        });
    }

    function pinIcon(color, label) {
        return window.L.divIcon({
            className: 'kulaan-route-pin',
            html: '<div style="width:26px;height:26px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:' + color +
                ';border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center">' +
                '<span style="transform:rotate(45deg);color:#fff;font-size:11px;font-weight:800">' + label + '</span></div>',
            iconSize: [26, 26],
            iconAnchor: [13, 24]
        });
    }

    /**
     * Mini-peta rute pada kartu kurir: elemen [data-route-map] berisi
     * data-origin="lat,lng", data-stops='[{"label":..,"lat":..,"lng":..}]',
     * data-courier="lat,lng" (opsional). Pasangannya tombol
     * [data-route-toggle="<id elemen pembungkus>"]. Peta baru dibuat saat
     * pertama kali dibuka (lazy) supaya halaman tetap ringan.
     */
    function initCourierMaps() {
        var toggles = document.querySelectorAll('[data-route-toggle]');
        if (!toggles.length) {
            return;
        }
        toggles.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var wrap = document.getElementById(btn.getAttribute('data-route-toggle'));
                if (!wrap) {
                    return;
                }
                var opening = wrap.classList.contains('hidden');
                wrap.classList.toggle('hidden');
                btn.textContent = opening ? '🗺️ Tutup Peta Rute' : '🗺️ Lihat Peta Rute';
                if (opening) {
                    var el = wrap.querySelector('[data-route-map]');
                    if (el && !el.dataset.inited) {
                        el.dataset.inited = '1';
                        waitForLeaflet(function () { buildCourierMap(el, wrap); });
                    } else if (el && el._kulaanMap) {
                        setTimeout(function () {
                            el._kulaanMap.invalidateSize();
                            if (el._kulaanBounds) { el._kulaanMap.fitBounds(el._kulaanBounds, { padding: [24, 24] }); }
                        }, 60);
                    }
                }
            });
        });
    }

    function waitForLeaflet(cb, tries) {
        tries = tries || 0;
        if (window.L) {
            cb();
        } else if (tries < 40) {
            setTimeout(function () { waitForLeaflet(cb, tries + 1); }, 250);
        }
    }

    function parsePair(text) {
        if (!text) {
            return null;
        }
        var parts = text.split(',').map(Number);
        return parts.length === 2 && isFinite(parts[0]) && isFinite(parts[1]) ? parts : null;
    }

    function buildCourierMap(el, wrap) {
        var origin = parsePair(el.getAttribute('data-origin'));
        var courier = parsePair(el.getAttribute('data-courier'));
        var stops = [];
        try {
            stops = JSON.parse(el.getAttribute('data-stops') || '[]')
                .filter(function (s) { return isFinite(s.lat) && isFinite(s.lng); });
        } catch (e) {
            stops = [];
        }
        var caption = wrap.querySelector('[data-route-caption]');

        var firstPoint = origin || (stops.length ? [stops[0].lat, stops[0].lng] : null) || courier;
        if (!firstPoint) {
            if (caption) { caption.textContent = 'Koordinat tujuan belum tersedia untuk pengiriman ini.'; }
            return;
        }

        var map = window.L.map(el, { attributionControl: false, zoomAnimation: false }).setView(firstPoint, 13);
        el._kulaanMap = map;
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        var boundsPoints = [];
        if (origin) {
            window.L.marker(origin, { icon: pinIcon('#9A7B2D', 'G') }).addTo(map)
                .bindPopup('<strong>Gudang distributor</strong><br>Titik ambil barang');
            boundsPoints.push(origin);
        }
        stops.forEach(function (stop, i) {
            window.L.marker([stop.lat, stop.lng], { icon: pinIcon('#2D6A4F', String(i + 1)) }).addTo(map)
                .bindPopup('<strong>Tujuan ' + (i + 1) + ':</strong><br>' + stop.label);
            boundsPoints.push([stop.lat, stop.lng]);
        });
        if (courier) {
            window.L.circleMarker(courier, {
                radius: 8, color: '#ffffff', weight: 2.5, fillColor: '#1a73e8', fillOpacity: 1
            }).addTo(map).bindPopup('<strong>Posisi kurir terakhir</strong>');
            boundsPoints.push(courier);
        }

        var routePoints = [];
        if (origin) { routePoints.push(origin); }
        stops.forEach(function (s) { routePoints.push([s.lat, s.lng]); });

        var state = {};
        drawRoute(map, state, routePoints, function (info) {
            if (caption) {
                caption.textContent = info
                    ? 'Total rute jalan: ' + formatInfo(info) + ' dari gudang ke semua tujuan.'
                    : 'Garis jalan tidak tersedia — garis putus-putus menunjukkan arah lurus antar titik.';
            }
        });

        if (boundsPoints.length > 1) {
            var bounds = window.L.latLngBounds(boundsPoints).pad(0.18);
            el._kulaanBounds = bounds;
            map.fitBounds(bounds, { padding: [24, 24] });
        }
        setTimeout(function () { map.invalidateSize(); }, 80);
    }

    window.KulaanRoute = {
        fetchRoute: fetchRoute,
        drawRoute: drawRoute,
        formatInfo: formatInfo,
        haversineMeters: haversineMeters
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCourierMaps);
    } else {
        initCourierMaps();
    }
})();
