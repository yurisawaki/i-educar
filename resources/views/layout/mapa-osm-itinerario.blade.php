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

        .legend {
            background: white;
            padding: 10px;
            line-height: 18px;
            color: #333;
            border-radius: 4px;
        }

        .legend i {
            width: 18px;
            height: 18px;
            float: left;
            margin-right: 8px;
            opacity: 0.7;
        }

        .subtle-marker {
            background-color: rgba(255, 0, 0, 0.5);
            border-radius: 50%;
            /* deixa redondo */
            width: 15px;
            height: 15px;
            box-shadow: 0 0 2px rgba(255, 0, 0, 0.5);
            /* sombra bem leve */
        }
    </style>
</head>

<body>
    <button id="toggleTipoRota">Modo: Rural</button>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>

        const pontosItinerario = @json($pontosItinerario);
        const pontosTrajeto = @json($pontosTrajeto);
        let tipoRota = 'Rural';
        let map;

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
            if (map) map.remove();
            map = L.map('map').setView([pontosItinerario[0]?.latitude || pontosTrajeto[0]?.latitude,
            pontosItinerario[0]?.longitude || pontosTrajeto[0]?.longitude], 13);

            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            });
            osm.addTo(map);

            const esriSat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 17,
                attribution: '&copy; <a href="https://www.esri.com/">Esri</a>',
            });

            const baseMaps = {
                "Mapa (OSM)": osm,
                "Satélite (Esri)": esriSat
            };
            L.control.layers(baseMaps).addTo(map);

            // Itinerário
            if (pontosItinerario.length) {
                const matrizIti = construirMatriz(pontosItinerario);
                const ordemIti = tspNearestNeighbor(matrizIti);
                const pontosItiOtimizados = ordemIti.map(i => pontosItinerario[i]);

                pontosItiOtimizados.forEach(p => {
                    L.marker([p.latitude, p.longitude])
                        .addTo(map)
                        .bindPopup(p.descricao || 'Ponto Itinerário');
                });

                L.polyline(pontosItiOtimizados.map(p => [p.latitude, p.longitude]), {
                    color: '#0066FF',
                    weight: 4
                }).addTo(map);
            }


            if (pontosTrajeto.length) {
                const matrizTrajeto = construirMatriz(pontosTrajeto);
                const ordemTrajeto = tspNearestNeighbor(matrizTrajeto);
                const pontosTrajetoOtimizados = ordemTrajeto.map(i => pontosTrajeto[i]);

                pontosTrajetoOtimizados.forEach((p, i) => {
                    if (i === 0 || i === pontosTrajetoOtimizados.length - 1) {
                        // Marker normal para primeiro e último
                        L.marker([p.latitude, p.longitude])
                            .addTo(map)
                            .bindPopup(p.no_ponto)
                            .openPopup();
                    } else {
                        // Marker sutil com tooltip para pontos intermediários
                        // Marker sutil com tooltip para pontos intermediários
                        var subtleIcon = L.divIcon({
                            className: 'subtle-marker',
                            iconSize: [6, 6]
                        });

                        L.marker([p.latitude, p.longitude], { icon: subtleIcon })
                            .addTo(map)
                            .bindTooltip(p.no_ponto || p.descricao, {
                                permanent: false, // só aparece no hover
                                direction: 'top',
                                offset: [0, -5]
                            });
                    }
                });

                L.polyline(pontosTrajetoOtimizados.map(p => [p.latitude, p.longitude]), {
                    color: '#1E90FF',
                    weight: 4,
                    opacity: 0.7,
                    dashArray: '6, 6',
                }).addTo(map);

            }




            // Legenda
            const legend = L.control({ position: "bottomright" });
            legend.onAdd = function () {
                const div = L.DomUtil.create("div", "legend");
                div.innerHTML += "<i style='background:#0066FF'></i> Itinerário<br>";
                div.innerHTML += "<i style='background:#FF0000'></i> Trajeto";
                return div;
            };
            legend.addTo(map);
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