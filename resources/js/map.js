import L from "leaflet";
import "leaflet/dist/leaflet.css";

window.L = L;

function getColor(d) {
    return d >= 20 ? '#B30000' :
           d >= 10 ? '#E53935' :
           d >= 1  ? '#FB8C00' :
                     '#E0E0E0';
}

function getFillOpacity(d) {
    return d >= 20 ? 0.8 :
           d >= 10 ? 0.6 :
           d >= 1  ? 0.4 :
                     0.2;
}

function crimeIcon(color) {
    return L.divIcon({
        className: "",
        html: `<span style="display:block;width:22px;height:22px;border-radius:999px;background:${color};border:4px solid rgba(255,255,255,.85);box-shadow:0 1px 4px rgba(0,0,0,.35);"></span>`,
        iconSize: [22, 22],
        iconAnchor: [11, 11],
    });
}

let crimeData = [];
let currentView = 'heatmap';
let map;
let geojsonLayer;
let markerLayer;

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    map = L.map("map", {
        zoomControl: true,
        attributionControl: true,
    }).setView([-8.1322, 113.2245], 12);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap",
    }).addTo(map);

    fetchMapData();
    createLegend();
    initSwitchToggle();
});

async function fetchMapData() {
    try {
        const response = await fetch('/api/map/statistics');
        if (!response.ok) throw new Error('Network response was not ok');
        crimeData = await response.json();
        updateMap();
    } catch (error) {
        console.error("Error fetching map data:", error);
    }
}

function updateMap() {
    if (geojsonLayer) map.removeLayer(geojsonLayer);
    if (markerLayer) map.removeLayer(markerLayer);

    if (currentView === 'heatmap') {
        renderHeatmap();
    } else {
        renderMarkers();
    }
}

function renderHeatmap() {
    const features = crimeData.map(item => {
        const counts = {};
        item.reports.forEach(r => {
            const catName = r.kategori?.nama_kategori || 'Lainnya';
            counts[catName] = (counts[catName] || 0) + 1;
        });
        const breakdownStr = Object.entries(counts)
            .map(([cat, count]) => `• ${cat}: ${count}`)
            .join('<br>');

        return {
            "type": "Feature",
            "properties": {
                "name": item.nama_lokasi,
                "total": item.total_laporan,
                "breakdown": breakdownStr
            },
            "geometry": item.polygon_geojson
        };
    }).filter(f => f.geometry);

    if (features.length === 0) {
        return;
    }

    geojsonLayer = L.geoJSON({ "type": "FeatureCollection", "features": features }, {
        style: (feature) => ({
            fillColor: getColor(feature.properties.total),
            weight: 1.5,
            opacity: 1,
            color: 'white',
            fillOpacity: getFillOpacity(feature.properties.total)
        }),
        onEachFeature: (feature, layer) => {
            const breakdown = feature.properties.breakdown ? `<br><strong>Detail Laporan:</strong><br>${feature.properties.breakdown}` : '';
            layer.bindTooltip(`<strong>Kecamatan: ${feature.properties.name}</strong><br>Total Laporan: ${feature.properties.total}${breakdown}`);
            layer.on({
                mouseover: (e) => {
                    const l = e.target;
                    l.setStyle({ weight: 3, color: '#333', fillOpacity: 0.9 });
                    l.bringToFront();
                },
                mouseout: (e) => {
                    geojsonLayer.resetStyle(e.target);
                },
                click: (e) => {
                    map.fitBounds(e.target.getBounds());
                }
            });
        }
    }).addTo(map);
}

window.focusLocation = function(name) {
    if (!geojsonLayer || !map) return;

    if (currentView !== 'heatmap') {
        currentView = 'heatmap';
        const switchEl = document.getElementById("map-view-switch");
        if (switchEl) switchEl.checked = false;
        updateMap();
    }

    geojsonLayer.eachLayer(layer => {
        geojsonLayer.resetStyle(layer);
        layer.closeTooltip();
    });

    let targetLayer = null;
    geojsonLayer.eachLayer(layer => {
        if (layer.feature && layer.feature.properties && layer.feature.properties.name.toLowerCase() === name.toLowerCase()) {
            targetLayer = layer;
        }
    });

    if (targetLayer) {
        map.fitBounds(targetLayer.getBounds(), { padding: [50, 50] });
        targetLayer.setStyle({ weight: 4, color: '#111', fillOpacity: 0.95 });
        targetLayer.openTooltip(targetLayer.getBounds().getCenter());

        setTimeout(() => {
            if (geojsonLayer && targetLayer) {
                geojsonLayer.resetStyle(targetLayer);
            }
        }, 6000);
    }
};

function renderMarkers() {
    markerLayer = L.layerGroup();
    crimeData.forEach(item => {
        item.reports.forEach(report => {
            if (report.latitude && report.longitude) {
                const color = report.kategori ? report.kategori.warna_marker : '#2952e3';
                L.marker([report.latitude, report.longitude], {
                    icon: crimeIcon(color)
                })
                .bindPopup(`<strong>${report.judul_laporan}</strong><br>Status: ${report.status}`)
                .addTo(markerLayer);
            }
        });
    });
    markerLayer.addTo(map);
}

function createLegend() {
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend');
        div.style.background = 'white';
        div.style.padding = '10px';
        div.style.borderRadius = '4px';
        div.style.boxShadow = '0 0 15px rgba(0,0,0,0.2)';

        const grades = [0, 1, 10, 20];
        const labels = ['Aman', 'Rawan (1-9)', 'Sangat Rawan (10-19)', 'Kritis (>= 20)'];

        div.innerHTML += '<strong>Tingkat Kriminalitas</strong><br>';
        for (let i = 0; i < grades.length; i++) {
            div.innerHTML += `<i style="display:inline-block;width:15px;height:15px;margin-right:5px;background:${getColor(grades[i] + (i===0?0:1))}"></i> ${labels[i]}<br>`;
        }
        return div;
    };
    legend.addTo(map);
}

function initSwitchToggle() {
    const switchEl = document.getElementById("map-view-switch");
    if (switchEl) {
        switchEl.checked = currentView === 'markers';
        switchEl.addEventListener("change", (e) => {
            currentView = e.target.checked ? 'markers' : 'heatmap';
            updateMap();
        });
    }
}
