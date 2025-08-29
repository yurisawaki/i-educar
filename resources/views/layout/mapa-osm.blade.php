<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>OpenStreetMap com Leaflet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        #map {
            height: 100vh;
            width: 100%;
        }

        .coords {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        input[type="text"] {
            padding: 5px;
            width: 180px;
        }

        button {
            padding: 6px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        #saveButton {
            display: none;
        }

        #updateCoordsButton {
            margin-left: auto;
        }
    </style>
</head>

<body>

    <div class="coords">
        <label for="latitude">Latitude:</label>
        <input type="text" id="latitude" name="latitude">

        <label for="longitude">Longitude:</label>
        <input type="text" id="longitude" name="longitude">

        <button id="updateCoordsButton">Editar Coordenadas</button>
        <button id="saveButton">Salvar</button>
    </div>

    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        let map, marker;

        function getUrlParams() {
            const params = new URLSearchParams(window.location.search);
            return {
                lat: parseFloat(params.get('lat')) || -1.52053,
                lng: parseFloat(params.get('lng')) || -52.5815
            };
        }

        function initMap() {
            const { lat, lng } = getUrlParams();

            map = L.map('map').setView([lat, lng], 15);

            // Camada base OpenStreetMap
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            });

            // Camada satélite Esri
            const esriSat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '&copy; <a href="https://www.esri.com/">Esri</a>, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
            });

            // Adiciona camada padrão (OSM)
            osm.addTo(map);

            // Controle para alternar entre OSM e Satélite
            const baseMaps = {
                "Mapa (OSM)": osm,
                "Satélite (Esri)": esriSat
            };
            L.control.layers(baseMaps).addTo(map);

            // Marcador arrastável
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);

            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);

            marker.on('dragend', function (e) {
                const { lat, lng } = marker.getLatLng();
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
            });

            document.getElementById("updateCoordsButton").addEventListener("click", () => {
                const { lat, lng } = marker.getLatLng();
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
                document.getElementById("saveButton").style.display = "inline-block";
            });

            document.getElementById("saveButton").addEventListener("click", () => {
                const lat = parseFloat(document.getElementById('latitude').value);
                const lng = parseFloat(document.getElementById('longitude').value);

                if (isNaN(lat) || isNaN(lng)) {
                    alert("Por favor, insira coordenadas válidas.");
                    return;
                }

                if (!confirm("Tem certeza que deseja salvar as coordenadas?")) return;

                // Atualiza a página anterior e fecha a janela
                if (window.opener && !window.opener.closed) {
                    window.opener.document.getElementById('latitude').value = lat.toFixed(6);
                    window.opener.document.getElementById('longitude').value = lng.toFixed(6);
                }

                window.close();
            });


        }

        window.onload = initMap;
    </script>

</body>

</html>