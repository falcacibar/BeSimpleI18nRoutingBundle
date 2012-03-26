var map, geocoder, marker;

function initializeMap(position){
 	if(position == 'default'){
    	var latlng = new google.maps.LatLng(-33.43692082916139, -70.63445091247559);
    }else{
    	var lat = $(".mapx").val();
		var lng = $(".mapy").val();
    	var latlng = new google.maps.LatLng(lat, lng);
    }

    var myOptions = {
		zoom: 16,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("mapa"),
        myOptions);
    geocoder = new google.maps.Geocoder();

    var image = WEBROOT+"../assets/images/gmaps/puntodestacado.png";

    marker = new google.maps.Marker({
	    position: latlng,
	    icon: image,
	    draggable: true
	});
	
	google.maps.event.addListener(marker, "dragend", function(event) {
        var point = marker.getPosition();
        map.panTo(point);
		$(".mapx").val(point.lat());
		$(".mapy").val(point.lng());
    });

	// To add the marker to the map, call setMap();
	marker.setMap(map);
	if(position == 'default'){
		point = marker.getPosition();
		$(".mapx").val(point.lat());
		$(".mapy").val(point.lng());
	}
}

function geocodeAddress(){
	var calle = $('.calle').val()
		, numero = $('.numero').val()
		, comuna = $('.comuna option:selected').text()
		, ciudad = $('.ciudad option:selected').text()
		, pais = $('.pais option:selected').text(),
		address = '';
		

	if(numero == '' || comuna == 'Elige una comuna' || pais == 'Elige un pais' || calle == '' || ciudad == 'Elige una comuna'){
		alert("Debes indicar Comuna, Calle y N\u00b0 para poder cargar el mapa.");
		return false;
	}

	if(ciudad == "Valparaíso - Viña del Mar"){
		address = ( calle + ' ' + parseInt(numero) + ', ' + comuna + ', ' + pais);
	}else{
		address = ( calle + ' ' + parseInt(numero) + ', ' + comuna + ', ' + ciudad + ', ' + pais);
	}

    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        map.setCenter(results[0].geometry.location);
    	marker.setPosition(results[0].geometry.location)
    	$(".mapx").val(results[0].geometry.location.lat());
		$(".mapy").val(results[0].geometry.location.lng());
      } else {
        alert("Debes indicar Comuna, Calle y N\u00b0 para poder cargar el mapa.");
      }
    });
}