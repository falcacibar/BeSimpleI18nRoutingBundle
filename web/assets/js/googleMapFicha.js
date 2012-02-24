//Our XmlHttpRequest object to get the auto suggest
var ajaxReq = getXmlHttpRequestObject();
var pagina = '';
var map;
var markersArray = new Array();
var mainMarker;
var bubble;
var bubble2;
var coordenadasDestacadas;
var idLugar;
var url = WEBROOT;

$(document).ready(
    function(){
        if(GBrowserIsCompatible){
            map = new GMap2(document.getElementById("mapaOtros"));
            map.setCenter(new GLatLng(-33.43692082916139, -70.63445091247559),10);
            map.addControl(new GSmallZoomControl());
            var bounds = map.getBounds();
            var southWest = bounds.getSouthWest();
            var northEast = bounds.getNorthEast();
            idLugar = document.getElementById('idLugar').value;
            xCord = escape(document.getElementById('coordenadasX').value);
            yCord = escape(document.getElementById('coordenadasY').value);
            GEvent.addListener(map, "moveend", function() {
                bounds = map.getBounds();
                southWest = bounds.getSouthWest();
                northEast = bounds.getNorthEast();
                bubble = null;
                bubble2 = null;
                ajaxGmapFiltro(idLugar, southWest.lat()+", "+southWest.lng(), northEast.lat()+", "+northEast.lng(), xCord, yCord, url);
            });
            GEvent.addListener(map, "dragend", function() {
                bounds = map.getBounds();
                southWest = bounds.getSouthWest();
                northEast = bounds.getNorthEast();
                bubble = null;
                bubble2 = null;
                ajaxGmapFiltro(idLugar, southWest.lat()+", "+southWest.lng(), northEast.lat()+", "+northEast.lng(), xCord, yCord, url);
            });
            GEvent.addListener(map, "zoomend", function(){
                bounds = map.getBounds();
                southWest = bounds.getSouthWest();
                northEast = bounds.getNorthEast();
                bubble = null;
                bubble2 = null;
                ajaxGmapFiltro(idLugar, southWest.lat()+", "+southWest.lng(), northEast.lat()+", "+northEast.lng(), xCord, yCord, url);
            });
            var baseIcon = new GIcon(G_DEFAULT_ICON);
            baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
            baseIcon.iconSize = new GSize(20,34);
            baseIcon.shadowSize = new GSize(37,34);
            baseIcon.iconAnchor = new GPoint(9,34);
            baseIcon.infoWindowAnchor = new GPoint(1,1);
            baseIcon.infoWindowSize = new GSize(20,34);

            //Lugar Principal:
            coordVal = document.getElementById("coordenadas0").value;
            coordenadasDestacadas = coordVal;
            num = document.getElementById("puntodestacado").value;

            if(coordVal!="" && coordVal!=", " && coordVal!=", 0"){
                LatLong = coordVal.split(',');
                point = new GLatLng(LatLong[0], LatLong[1]);
                //Center the map on this point (si es el luger predilecto)
                //-----------------------------
                var letteredIcon = new GIcon(baseIcon);
                letteredIcon.image = url + "../assets/images/gmaps/puntodestacado.png";
                markerOptions = {icon: letteredIcon, draggable: false};
                var mainMarker = new GMarker(point, markerOptions);

                map.addOverlay(mainMarker);
                //------------------------------
                map.setCenter(point, 16);
                bounds = map.getBounds();
                southWest = bounds.getSouthWest();
                northEast = bounds.getNorthEast();
            }
            ajaxGmapFiltro(idLugar, southWest.lat()+", "+southWest.lng(), northEast.lat()+", "+northEast.lng(), url);
        }
    }
);
//Gets the browser specific XmlHttpRequest Object
function getXmlHttpRequestObject() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if(window.ActiveXObject) {
        return new ActiveXObject("Microsoft.XMLHTTP");
    } else {
        alert("Lo sentimos, tu explorador no soporta AJAX. Por favor utiliza alg\u00fan explorador de nueva generaci\u00f3n.");
    }
}

//Called from keyup on the search textbox.
//Starts the AJAX request.
function ajaxGmapFiltro(idLugar, southWest, northEast, latitude, longitude, baseurl) {
    if (ajaxReq.readyState == 4 || ajaxReq.readyState == 0) {
        // Sacar variables
        //var info = escape(document.getElementById('blah').value);
        // Enviar via get
        ajaxReq.open("GET", '../ajax/otrosLugaresEnElArea?idLugar='+idLugar+'&southWest='+southWest + '&northEast='+northEast+'&latitude='+latitude+'&longitude='+longitude, true);
        ajaxReq.onreadystatechange = handleMapa;
        ajaxReq.send(null);
    }
}

//Called when the AJAX response is returned.
function handleMapa() {
    if(ajaxReq.readyState == 4) {
        // Limpiar Info Mapa
        var div = document.getElementById('otros-lugares')
        div.innerHTML = '';
        // Respuesta
        var response = ajaxReq.responseText;
        div.innerHTML += response;
        // Remover Markers
        for(i = 0 ; i < markersArray.length ; i++) {
            map.removeOverlay(markersArray[i]);
        }
        markersArray = new Array();
        // Agrego los lugares
        $lugar = $('.lugar_data');

        $.each($lugar, function(i){
            var coordVal = $(this).data('coords');

            if(coordVal!="" && coordVal!=", " && coordVal!=",0" && coordVal!=", 0" && coordVal!="0," && coordVal!="0, "){
                LatLong = coordVal.split(',');
                LatLongDestacado = coordenadasDestacadas.split(',');
                if(LatLong[0]!=LatLongDestacado[0] && LatLong[1]!=LatLongDestacado[1]){
                    var point = new GLatLng(LatLong[0], LatLong[1]);

                    markersArray[markersArray.length] = createMarker($(this), point);
                    map.addOverlay(markersArray[markersArray.length-1]);
                }                
            }
        });

        $.getScript(WEBROOT+'../assets/js/otrosLugares.js');
    }
}

function createMarker($container, point){
    var baseIcon2 = new GIcon(G_DEFAULT_ICON);
    baseIcon2.shadow = "http://www.google.com/mapfiles/shadow50.png";
    baseIcon2.iconSize = new GSize(20,34);
    baseIcon2.shadowSize = new GSize(37,34);
    baseIcon2.iconAnchor = new GPoint(9,34);
    baseIcon2.infoWindowAnchor = new GPoint(1,1);
    baseIcon2.infoWindowSize = new GSize(20,34);

    cat = $container.data('categoria'),
    info = $container.html();

    var letteredIconMarker = new GIcon(baseIcon2);

    var url2 = document.getElementById("baseurl").value;
    letteredIconMarker.image = url + "../assets/images/gmaps/categoria"+cat+".png";
    var url3 = document.getElementById("baseurl").value;
    markerOptions2 = {icon: letteredIconMarker, draggable: false};
    var marker = new GMarker(point, markerOptions2);

    GEvent.addListener(marker, "mouseover", function() {
        var markerOffset = map.fromLatLngToContainerPixel(marker.getPoint()),
            mapaOffset = $('.mapa_sidebar').offset(),
            t = markerOffset.y + (mapaOffset.top) - 195,
            l = markerOffset.x + (mapaOffset.left / 2) - 40;

            $container.show().css('top', t).css('left', l)
    });
    GEvent.addListener(marker, "mouseout", function() {
        $container.hide()
    });
    GEvent.addListener(marker, "click", function() {
        if(document.getElementById('linkLugar'+num) != null) {
            location.href=document.getElementById('linkLugar'+num).value;
        }
    });
    return marker;
}
