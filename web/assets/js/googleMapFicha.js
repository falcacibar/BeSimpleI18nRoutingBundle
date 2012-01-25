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

            //Ahora creamos el eBubble:
            //bubble = new EBubble(map, url + "/images/gmaps/cuadro-informativo.png", new GSize(283, 140), new GSize(263, 110), new GPoint(10,0),new GPoint(258,125),true);

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
                GEvent.addListener(mainMarker, "mouseover", function(){
                    /*if(bubble) {
                        bubble.show();
                        text = document.getElementById('infoFicha').innerHTML
                        bubble.openOnMarker(mainMarker, text);}
                    });*/

                    var vineta = $('#infoFicha').html()
                    if(vineta){
                        $('#vminfowindow').remove()
                        var markerOffset = map.fromLatLngToContainerPixel(mainMarker.getPoint());
                        $('#mapaOtros').prepend('<div id="vminfowindow"></div>');
                        $('#vminfowindow').css({'visibility':'hidden','position':'absolute'}).addClass('fs-09').html( vineta );
                        var h = $('#vminfowindow .vineta').height();
                        var w = $('#vminfowindow .vineta').width();
                        var t = markerOffset.y - h - 33;
                        var l = markerOffset.x - w + 25;
                        $('#vminfowindow').css({top:t,left:l,height:h,width:w,'visibility':'visible','z-index':9999});
                    }
                })




                GEvent.addListener(mainMarker, "mouseout", function() {
                    /*if(bubble)
                        bubble.hide()*/
                    $('#vminfowindow').hide()
                });
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
        if(document.getElementById('cantidadLugares').value >0){
            for(var i = 0 ; i <= document.getElementById('cantidadLugares').value ; i++){
                coordVal = document.getElementById('coordenadas'+i).value;
                if(coordVal!="" && coordVal!=", " && coordVal!=",0" && coordVal!=", 0" && coordVal!="0," && coordVal!="0, "){
                    LatLong = coordVal.split(',');
                    LatLongDestacado = coordenadasDestacadas.split(',');
                    if(LatLong[0]!=LatLongDestacado[0] && LatLong[1]!=LatLongDestacado[1]){
                        point = new GLatLng(LatLong[0], LatLong[1]);
                        var cat = document.getElementById('num'+i).value;
                        info = document.getElementById('info'+i).innerHTML;
                        markersArray[markersArray.length] = createMarker(point, cat, info, i);
                        map.addOverlay(markersArray[markersArray.length-1]);
                    }
                }
                if(i == document.getElementById('cantidadLugares').value){
                    $.getScript(WEBROOT+'../assets/js/otrosLugares.js');
                }
            }

        }
    }
}

function createMarker(point, cat, informacion, num){
    //Si queremos darle alguna letra o numero especial al icono del lugar:
    var baseIcon2 = new GIcon(G_DEFAULT_ICON);
    baseIcon2.shadow = "http://www.google.com/mapfiles/shadow50.png";
    baseIcon2.iconSize = new GSize(20,34);
    baseIcon2.shadowSize = new GSize(37,34);
    baseIcon2.iconAnchor = new GPoint(9,34);
    baseIcon2.infoWindowAnchor = new GPoint(1,1);
    baseIcon2.infoWindowSize = new GSize(20,34);

    var letteredIconMarker = new GIcon(baseIcon2);

    var url2 = document.getElementById("baseurl").value;
    letteredIconMarker.image = url + "../assets/images/gmaps/categoria"+cat+".png";
    var url3 = document.getElementById("baseurl").value;
    markerOptions2 = {icon: letteredIconMarker, draggable: false};
    var marker = new GMarker(point, markerOptions2);
    //bubble2 = new EBubble(map, url3 + "/images/gmaps/cuadro-informativo.png", new GSize(283, 140), new GSize(263, 110), new GPoint(10,0),new GPoint(258,125),true);
    //var bubble2 = new EBubble(map, url3 + "/images/gmaps/cuadro-informativo.png", new GSize(283, 400), new GSize(283, 400), new GPoint(10,0),new GPoint(40,200));
    GEvent.addListener(marker, "mouseover", function() {
        /*if(bubble2) {
            bubble2.openOnMarker(marker,informacion);
            bubble2.show();
        }*/
        $('#vminfowindow').remove()
        var markerOffset = map.fromLatLngToContainerPixel(marker.getPoint());
        $('#mapaOtros').prepend('<div id="vminfowindow"></div>');
        $('#vminfowindow').css({'visibility':'hidden','position':'absolute'}).addClass('fs-09').html( informacion );
        var h = $('#vminfowindow .vineta').height();
        var w = $('#vminfowindow .vineta').width();
        var t = markerOffset.y - h - 33;
        var l = markerOffset.x - w + 25;
        $('#vminfowindow').css({top:t,left:l,height:h,width:w,'visibility':'visible','z-index':9999});

    });
    GEvent.addListener(marker, "mouseout", function() {
        /*if(bubble2)
            bubble2.hide();*/
        $('#vminfowindow').hide()
    });
    GEvent.addListener(marker, "click", function() {
        if(document.getElementById('linkLugar'+num) != null) {
            location.href=document.getElementById('linkLugar'+num).value;
        }
    });
    return marker;
}
