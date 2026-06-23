document.addEventListener("DOMContentLoaded", function () {
    // 1. Initialize Map
    const mapContainer = document.getElementById('roadtrip-map');
    if (!mapContainer) return;

    // Default center (Malaysia)
    const map = L.map('roadtrip-map').setView([4.2105, 101.9758], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // 2. Load Waypoints from Backend
    const waypointsData = window.SESSION_WAYPOINTS || [];
    
    // Filter out waypoints that don't have valid lat/lng
    const validWaypoints = waypointsData.filter(wp => wp.lat && wp.lng);
    
    if (validWaypoints.length > 0) {
        const routePoints = validWaypoints.map(wp => L.latLng(parseFloat(wp.lat), parseFloat(wp.lng)));
        
        // Add markers manually so we can customize them
        validWaypoints.forEach((wp, index) => {
            let color = 'blue';
            if (wp.type === 'start') color = 'green';
            if (wp.type === 'destination') color = 'red';
            if (wp.type === 'toll') color = 'orange';

            const markerHtml = `<div style="background-color: ${color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.5);"></div>`;
            const icon = L.divIcon({ html: markerHtml, className: 'custom-div-icon', iconSize: [14, 14], iconAnchor: [7, 7] });
            
            L.marker([parseFloat(wp.lat), parseFloat(wp.lng)], { icon: icon })
             .addTo(map)
             .bindPopup(`<b>${wp.name}</b><br/>${wp.type.toUpperCase()}`);
        });

        // Use Leaflet Routing Machine to draw the roads (if more than 1 point)
        if (routePoints.length > 1) {
            const routingControl = L.Routing.control({
                waypoints: routePoints,
                routeWhileDragging: false,
                addWaypoints: false,
                show: false, // Hide the turn-by-turn text box
                createMarker: function() { return null; }, // We drew our own markers above
                lineOptions: {
                    styles: [{color: '#3b82f6', opacity: 0.8, weight: 5}]
                }
            }).addTo(map);

            // Hide the default routing machine instruction container which looks ugly
            routingControl.on('routesfound', function(e) {
                const container = document.querySelector('.leaflet-routing-container');
                if (container) container.style.display = 'none';
            });
        } else {
            // Just center on the single point
            map.setView(routePoints[0], 12);
        }
    }

    // 3. Simple Geocoding for the Add Waypoint Form
    const searchInput = document.getElementById('waypoint-search-input');
    const latInput = document.getElementById('waypoint-lat');
    const lngInput = document.getElementById('waypoint-lng');

    if (searchInput) {
        let typingTimer;
        
        // Create dropdown for results
        const resultsDiv = document.createElement('div');
        resultsDiv.className = 'absolute z-50 w-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-y-auto';
        searchInput.parentNode.style.position = 'relative';
        searchInput.parentNode.appendChild(resultsDiv);

        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            if (this.value.length < 3) {
                resultsDiv.classList.add('hidden');
                return;
            }
            
            typingTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.value)}&countrycodes=my,sg,th`)
                    .then(response => response.json())
                    .then(data => {
                        resultsDiv.innerHTML = '';
                        if (data.length > 0) {
                            resultsDiv.classList.remove('hidden');
                            data.forEach(place => {
                                const item = document.createElement('div');
                                item.className = 'px-4 py-2 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-600 text-sm text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-600 last:border-0';
                                item.innerText = place.display_name;
                                item.onclick = () => {
                                    searchInput.value = place.name || place.display_name.split(',')[0];
                                    latInput.value = place.lat;
                                    lngInput.value = place.lon;
                                    resultsDiv.classList.add('hidden');
                                };
                                resultsDiv.appendChild(item);
                            });
                        } else {
                            resultsDiv.classList.add('hidden');
                        }
                    });
            }, 500);
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== searchInput) {
                resultsDiv.classList.add('hidden');
            }
        });
    }
});
