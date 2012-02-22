var maxx = [];
var maxy = [];
var markersArray = new Array();
var url = WEBROOT;

$(document).ready(function(){
        if(GBrowserIsCompatible){
            var centroLat = 0, centroLogn = 0,
                map = new GMap2(document.getElementById("mapa_buscar")),
                $lugar = $('.lugar_data');

            map.setCenter(new GLatLng(-33.43692082916139, -70.63445091247559),10);
            map.addControl(new GSmallZoomControl());
            bounds = map.getBounds();

            $.each($lugar, function(i){
                markersArray[markersArray.length] = createMarker($(this));
                map.addOverlay(markersArray[markersArray.length-1]);
            });

            //Sacamos el nivel de zoom en base a los bounds (limites)
            map.setZoom(map.getBoundsZoomLevel(bounds))

            maxx.sort();
            maxy.sort();

            centroLat = (parseFloat(maxx[0]) + parseFloat(maxx[maxx.length -1])) / 2;
            centroLong = (parseFloat(maxy[0]) + parseFloat(maxy[maxy.length -1])) / 2;

            centro = new GLatLng(centroLat, centroLong);
            map.panTo(centro);
        }
});

function createMarker($container){
    var baseIcon = new GIcon(G_DEFAULT_ICON);

    baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png",
    baseIcon.iconSize = new GSize(20,34),
    baseIcon.shadowSize = new GSize(37,34),
    baseIcon.iconAnchor = new GPoint(9,34),
    baseIcon.infoWindowAnchor = new GPoint(1,1),
    baseIcon.infoWindowSize = new GSize(20,34);

    var letteredIconMarker = new GIcon(baseIcon);
    letteredIconMarker.image = url + "../assets/images/gmaps/gmap"+(markersArray.length + 1)+".png";

    var markerOptions = {icon: letteredIconMarker, draggable: false},
    coords = $container.data('coords').split(','),
    point = new GLatLng(coords[0], coords[1]),
    marker = new GMarker(point, markerOptions);

    //Find out max x and y values and add points to the limits
    bounds.extend(point)

    maxx.push(coords[0]);
    maxy.push(coords[1]);

    GEvent.addListener(marker, "mouseover", function() {
        var markerOffset = map.fromLatLngToContainerPixel(marker.getPoint());
        var t = markerOffset.y - 510;
        var l = markerOffset.x - 211;

        $container.show().css('top', t).css('left', l)
    });

    GEvent.addListener(marker, "mouseout", function() {
        $container.hide();
    });
    GEvent.addListener(marker, "click", function() {
        location.href = $container.data('url')
    });

    return marker;
}