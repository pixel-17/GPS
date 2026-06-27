<!DOCTYPE html>
<html>
<head>
    <title>Prueba GPS</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        #map{
            width:100%;
            height:600px;
        }
    </style>
</head>
<body>

<h2>Prueba de ubicación del camión</h2>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>

const map = L.map('map').setView([-13.5257,-71.9831],15);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{
    maxZoom:19
}).addTo(map);

let camion=null;

async function actualizar(){

    try{

        const res=await fetch('/api/rutas/1/ultima-posicion');

        const data=await res.json();

        console.log(data);

        const lat=parseFloat(data.latitude);
        const lng=parseFloat(data.longitude);

        if(!camion){

            camion=L.marker([lat,lng]).addTo(map);

            map.setView([lat,lng],17);

        }else{

            camion.setLatLng([lat,lng]);

        }

    }catch(e){

        console.error(e);

    }

}

actualizar();

setInterval(actualizar,2000);

</script>

</body>
</html>