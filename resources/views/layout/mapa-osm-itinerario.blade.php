<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Itinerário OSM</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />

    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        #map {
            height: 100vh;
            width: 100%;
        }

        #toggleTipoRota {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            padding: 10px 15px;
            background-color: #0066FF;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
            margin-right: 50px;
        }
    </style>
</head>

<body>
    <button id="toggleTipoRota">Modo: Rural</button>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>

    <script>

        const pontos = @json($pontos);
        let tipoRota = 'Rural';
        let map, routingControl;

        function haversine(lat1, lon1, lat2, lon2) {
            const toRad = deg => deg * Math.PI / 180;
            const R = 6371;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a = Math.sin(dLat / 2) ** 2 +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function construirMatriz(pontos) {
            const matriz = [];
            for (let i = 0; i < pontos.length; i++) {
                matriz[i] = [];
                for (let j = 0; j < pontos.length; j++) {
                    matriz[i][j] = i === j ? 0 : haversine(
                        pontos[i].latitude, pontos[i].longitude,
                        pontos[j].latitude, pontos[j].longitude
                    );
                }
            }
            return matriz;
        }

        function tspNearestNeighbor(matriz) {
            const n = matriz.length;
            const visitado = Array(n).fill(false);
            const rota = [0];
            visitado[0] = true;

            for (let i = 1; i < n; i++) {
                const ultimo = rota[rota.length - 1];
                let menor = Infinity;
                let proximo = -1;

                for (let j = 0; j < n; j++) {
                    if (!visitado[j] && matriz[ultimo][j] < menor) {
                        menor = matriz[ultimo][j];
                        proximo = j;
                    }
                }

                if (proximo !== -1) {
                    rota.push(proximo);
                    visitado[proximo] = true;
                }
            }

            return rota;
        }

        function renderMapa(tipo) {
            if (map) map.remove(); // destruir mapa anterior
            map = L.map('map').setView([pontos[0].latitude, pontos[0].longitude], 13);

            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            });

            // Camada satélite Esri
            const esriSat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 17,
                attribution: '&copy; <a href="https://www.esri.com/">Esri</a>, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
            });


            osm.addTo(map);


            const baseMaps = {
                "Mapa (OSM)": osm,
                "Satélite (Esri)": esriSat
            };
            L.control.layers(baseMaps).addTo(map);
            if (routingControl) {
                map.removeControl(routingControl);
            }

            if (tipo === 'Urbana') {
                routingControl = L.Routing.control({
                    waypoints: pontos.map(p => L.latLng(p.latitude, p.longitude)),
                    routeWhileDragging: false,
                    draggableWaypoints: false,
                    addWaypoints: false,
                    show: false,
                }).addTo(map);
            } else {
                const matriz = construirMatriz(pontos);
                const ordemOtima = tspNearestNeighbor(matriz);
                const pontosOtimizados = ordemOtima.map(i => pontos[i]);

                pontosOtimizados.forEach(p => {
                    L.marker([p.latitude, p.longitude])
                        .addTo(map)
                        .bindPopup(p.descricao || 'Ponto')
                        .openPopup();
                });

                const latlngs = pontosOtimizados.map(p => [p.latitude, p.longitude]);
                L.polyline(latlngs, {
                    color: '#0066FF',
                    weight: 4,
                    opacity: 0.8,
                    smoothFactor: 1
                }).addTo(map);
            }
        }

        function initApp() {
            renderMapa(tipoRota);

            document.getElementById('toggleTipoRota').addEventListener('click', () => {
                tipoRota = tipoRota === 'Urbana' ? 'Rural' : 'Urbana';
                document.getElementById('toggleTipoRota').innerText = 'Modo: ' + tipoRota;
                renderMapa(tipoRota);
            });
        }

        window.onload = initApp;
    </script>
</body>

</html>