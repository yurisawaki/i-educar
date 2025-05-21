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
        const pontos = @json($pontos);

        function haversine(lat1, lon1, lat2, lon2) {
            const toRad = angle => angle * Math.PI / 180;
            const R = 6371;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a =
                Math.sin(dLat / 2) ** 2 +
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
                        pontos[i].latitude,
                        pontos[i].longitude,
                        pontos[j].latitude,
                        pontos[j].longitude
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

        function initMap() {
            if (pontos.length === 0) {
                alert('Nenhum ponto encontrado.');
                return;
            }

            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: {
                    lat: parseFloat(pontos[0].latitude),
                    lng: parseFloat(pontos[0].longitude)
                }
            });

            const matriz = construirMatriz(pontos);
            const ordemOtima = tspNearestNeighbor(matriz);
            const pontosOtimizados = ordemOtima.map(i => pontos[i]);

            // Adiciona marcadores no mapa
            pontosOtimizados.forEach(p => {
                new google.maps.Marker({
                    position: {
                        lat: parseFloat(p.latitude),
                        lng: parseFloat(p.longitude)
                    },
                    map: map,
                    title: p.descricao
                });
            });

            // Desenha a linha da rota fluvial
            const rota = pontosOtimizados.map(p => ({
                lat: parseFloat(p.latitude),
                lng: parseFloat(p.longitude)
            }));

            const linhaRota = new google.maps.Polyline({
                path: rota,
                geodesic: true,
                strokeColor: '#0066FF',
                strokeOpacity: 0.8,
                strokeWeight: 4
            });

            linhaRota.setMap(map);
        }
    </script>

    <!-- API Google Maps com chave -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&callback=initMap">
    </script>
</body>

</html>