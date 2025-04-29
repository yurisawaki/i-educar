<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Itinerário no Mapa</title>
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <script>
        function initMap() {
            const pontos = @json($pontos);

            if (pontos.length === 0) {
                alert('Nenhum ponto encontrado para esta rota.');
                return;
            }

            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: {
                    lat: parseFloat(pontos[0].latitude),
                    lng: parseFloat(pontos[0].longitude)
                },
            });

            // Marcadores com descrição
            pontos.forEach(ponto => {
                new google.maps.Marker({
                    position: {
                        lat: parseFloat(ponto.latitude),
                        lng: parseFloat(ponto.longitude),
                    },
                    map: map,
                    title: ponto.descricao,
                });
            });

            // Traçando a linha da rota
            if (pontos.length >= 2) {
                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({ map: map });

                const origem = {
                    lat: parseFloat(pontos[0].latitude),
                    lng: parseFloat(pontos[0].longitude),
                };
                const destino = {
                    lat: parseFloat(pontos[pontos.length - 1].latitude),
                    lng: parseFloat(pontos[pontos.length - 1].longitude),
                };

                const waypoints = pontos.slice(1, -1).map(p => ({
                    location: {
                        lat: parseFloat(p.latitude),
                        lng: parseFloat(p.longitude),
                    },
                    stopover: true,
                }));

                const request = {
                    origin: origem,
                    destination: destino,
                    waypoints: waypoints,
                    travelMode: google.maps.TravelMode.DRIVING,
                };

                directionsService.route(request, (result, status) => {
                    if (status === 'OK') {
                        directionsRenderer.setDirections(result);
                    } else {
                        console.error('Falha na rota:', status);
                    }
                });
            }
        }
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&callback=initMap">
    </script>
</body>

</html>