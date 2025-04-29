<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Google Map</title>
    <style>
        #map {
            height: 100vh;
            width: 100%;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
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

    <script type="text/javascript">
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
            const myLatLng = { lat, lng };

            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: myLatLng,
                streetViewControl: false,
            });

            marker = new google.maps.Marker({
                position: myLatLng,
                map: map,
                title: "Localização",
                draggable: true
            });

            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);

            // Atualizar as coordenadas quando o marcador for movido
            marker.addListener('dragend', function (event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
            });

            // Quando o botão "Editar Coordenadas" é clicado
            document.getElementById("updateCoordsButton").addEventListener("click", function () {
                const lat = marker.getPosition().lat();
                const lng = marker.getPosition().lng();

                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);

                document.getElementById("saveButton").style.display = "inline-block";
            });

            // Quando o botão "Salvar" é clicado
            document.getElementById("saveButton").addEventListener("click", function () {
                const lat = parseFloat(document.getElementById('latitude').value);
                const lng = parseFloat(document.getElementById('longitude').value);

                if (isNaN(lat) || isNaN(lng)) {
                    alert("Por favor, insira coordenadas válidas.");
                    return;
                }

                const confirmacao = confirm("Tem certeza que deseja salvar as coordenadas?");
                if (!confirmacao) return;

                // Enviar as coordenadas para o backend via fetch
                fetch('/coordenadas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);  // Exibe a resposta do backend
                        console.log("Coordenadas salvas:", data);

                        // Passar as coordenadas para a página de origem
                        if (window.opener && !window.opener.closed) {
                            window.opener.document.getElementById('latitude').value = lat.toFixed(6);
                            window.opener.document.getElementById('longitude').value = lng.toFixed(6);
                        }

                        // Fechar a janela
                        window.close();
                    })
                    .catch(error => {
                        console.error('Erro ao salvar coordenadas:', error);
                        alert('Erro ao salvar as coordenadas. Tente novamente.');
                    });
            });
        }
    </script>

    <!-- Carrega a API do Google Maps -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&callback=initMap&libraries=places">
        </script>

</body>

</html>