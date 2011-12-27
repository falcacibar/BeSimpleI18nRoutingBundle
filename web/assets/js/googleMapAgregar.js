//Metodo que contiene todo sobre cargar el mapa
function onCargarMapaAgregar(pos){
	
  	if(GBrowserIsCompatible){
  		
		var address = null;

        /*quita los mensajes de error que hubiera */
        var errormapa = $('#coordenadas').next()
        if(errormapa.is('label.error'))
            errormapa.remove()
		
		if(pos == 'default'){
			address = 'Baquedano, Providencia, Santiago de Chile';
		}else{
			var calle = $('.calle').val();
			var numero = $('.numero').val();
			var comuna = $('.comuna > option:selected').text();
			var ciudad = $('.ciudad > option:selected').text();
			
			address = ( calle + ' ' + parseInt(numero) + ', ' + comuna + ', ' + ciudad);
		}
		
		if(address){
			var map = new GMap2(document.getElementById("mapa"));
			if(pos == 'default'){
				map.setCenter(new GLatLng(-33.43692082916139, -70.63445091247559),10);
			}
			//map.setUIToDefault();
			map.addControl(new GSmallZoomControl());
	
			var baseIcon = new GIcon(G_DEFAULT_ICON);
			baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
			baseIcon.iconSize = new GSize(20,34);
			baseIcon.shadowSize = new GSize(37,34);
			baseIcon.iconAnchor = new GPoint(9,34);
			baseIcon.infoWindowAnchor = new GPoint(1,1);
			baseIcon.infoWindowSize = new GSize(20,34);
	
			var geocoder = new GClientGeocoder();
			
			//Con esto creamos el marker que esta basado en baseIcon:
			function createMarker(point){
				//Si queremos darle alguna letra o numero especial al icono del lugar:
				//var letter = String.fromCharCode("A".charCodeAt(0) + index);
				var letteredIcon = new GIcon(baseIcon);
				letteredIcon.image = "/symf/web/assets/images/gmaps/puntodestacado.png";
				markerOptions = {icon: letteredIcon, draggable: true};
				var marker = new GMarker(point, markerOptions);
				if(pos == 'default'){
					$(".mapx").val('');
					$(".mapy").val('');
					defaultpos = null;
				}else{
					$(".mapx").val(point.lat());
					$(".mapy").val(point.lng());
				}
				 //if they drag the marker
				GEvent.addListener(marker, 'dragend',
					function(p) {
						map.panTo(p);
						$(".mapx").val(point.lat());
						$(".mapy").val(point.lng());
					}
				);
				return marker;
			}
	
			//Con este metodo hacemos el geocoding y llamamos al metodo createMarker:
			function addToMap(response){
				if(!response || response.Status.code != 200) {
					$(".mapx").val('');
					$(".mapy").val('');
					alert("La direcci\u00F3n ingresada no fue encontrada en el mapa.");
					onCargarMapaAgregar('default');
				} else {
					// Nos da el objeto:
					place = response.Placemark[0];
					// Nos da la latitud y longitud:
					point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
					// Center the map on this point
					map.setCenter(point, 16);
					map.addOverlay(createMarker(point));
				}
			}
			geocoder.getLocations(address, addToMap);
		} else{
			alert('Debes indicar Comuna, Calle y N\u00b0 para poder cargar el mapa.')
			$(".mapx").val('');
			$(".mapy").val('');
		}
  	}
}
