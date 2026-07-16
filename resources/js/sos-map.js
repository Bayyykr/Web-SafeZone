import L from "leaflet";
import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import "leaflet.markercluster";

window.L = L;

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById("sos-map");
    if (!mapElement) return;

    const map = L.map("sos-map", {
        zoomControl: true,
        attributionControl: true,
    }).setView([-8.1322, 113.2245], 12);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap",
    }).addTo(map);

    const markerCluster = L.markerClusterGroup();
    map.addLayer(markerCluster);

    const sosData = JSON.parse(document.getElementById("sos-map-data").textContent);
    console.log("SOS Data:", sosData);
    renderMarkers(sosData, markerCluster);

    if (sosData.length > 0) {
        map.fitBounds(markerCluster.getBounds());
    }

    // Add controls
    // L.control.fullscreen().addTo(map);
    // L.control.locate().addTo(map);
});

function renderMarkers(data, cluster) {
    cluster.clearLayers();
    data.forEach(item => {
        if (!item.latitude || !item.longitude) return;

        const isBlinking = item.status === 'aktif' ? 'sos-marker-active' : '';
        
        const icon = L.divIcon({
            className: `sos-marker ${isBlinking}`,
            html: `<div style="background:${item.status === 'aktif' ? '#dc2626' : item.status === 'dalam_penanganan' ? '#eab308' : '#22c55e'}; width:20px; height:20px; border-radius:50%; border:2px solid white;"></div>`,
            iconSize: [20, 20]
        });

        const marker = L.marker([item.latitude, item.longitude], { icon });
        marker.bindPopup(`
            <strong>${item.kode_darurat}</strong><br>
            Pelapor: ${item.user?.name || '-'}<br>
            Status: ${item.status}<br>
            <a href="/admin/laporan/darurat" class="text-blue-500">Lihat Detail</a>
        `);
        cluster.addLayer(marker);
    });
}
