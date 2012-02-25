var markerTimeout;

$(document).ready(function(){
        $('.lugar_data').mouseover(function(){
            clearTimeout(markerTimeout);
        }).mouseleave(function(){
            $(this).hide();
        });

        if(GBrowserIsCompatible){
            var centroLat = 0, centroLogn = 0,
                maxx = [], maxy = [];
                $lugar = $('.lugar_data'),
                zoom = 16,
                map = new GMap2(document.getElementById("mapa_buscar"));

            map.addControl(new GSmallZoomControl());
            bounds = map.getBounds();

            $.each($lugar, function(i){
                var $this = $(this),
                    coords = $this.data('coords').split(','),
                    point = new GLatLng(coords[0], coords[1]);

                maxx.push(coords[0]);
                maxy.push(coords[1]);

                map.addOverlay(createMarker($this, point, i));
                bounds.extend(point)

                if(i == 0){
                    map.setCenter(point, zoom);
                    bounds = map.getBounds();
                }
            });

            map.setZoom(map.getBoundsZoomLevel(bounds))

            maxx.sort();
            maxy.sort();

            centroLat = (parseFloat(maxx[0]) + parseFloat(maxx[maxx.length -1])) / 2;
            centroLong = (parseFloat(maxy[0]) + parseFloat(maxy[maxy.length -1])) / 2;

            centro = new GLatLng(centroLat, centroLong);
            map.panTo(centro);
        }
});

function createMarker($container, point, i){
    var baseIcon = new GIcon(G_DEFAULT_ICON),
        letteredIconMarker = new GIcon(baseIcon),
        markerOptions = {icon: letteredIconMarker, draggable: false},
        marker = new GMarker(point, markerOptions);

    baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png",
    baseIcon.iconSize = new GSize(20,34),
    baseIcon.shadowSize = new GSize(37,34),
    baseIcon.iconAnchor = new GPoint(9,34),
    baseIcon.infoWindowAnchor = new GPoint(1,1),
    baseIcon.infoWindowSize = new GSize(20,34);

    letteredIconMarker.image = WEBROOT + "../assets/images/gmaps/gmap"+(i + 1)+".png";

    GEvent.addListener(marker, "mouseover", function() {
        $('.lugar_data').hide();
        var markerOffset = map.fromLatLngToContainerPixel(marker.getPoint()),
            mapaOffset = $('#mapa_buscar').offset(),
            t = markerOffset.y + (mapaOffset.top) - 195,
            l = markerOffset.x + (mapaOffset.left / 2) - 40;

        $container.show().css('top', t).css('left', l)
    });

    GEvent.addListener(marker, "mouseout", function() {
        markerTimeout = setTimeout(function(){ $container.hide(); }, 100)
    });

    GEvent.addListener(marker, "click", function() {
        location.href = $container.data('url')
    });

    return marker;
}