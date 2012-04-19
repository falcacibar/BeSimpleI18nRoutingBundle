$(document).ready(function(){
	var estrellas = $('.star-raty').attr('data-stars'),
		precio = $('.precio-raty').attr('data-stars');

	$('.precio-raty').raty({
                width: 140,
                starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
                starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
                start: precio,
                space: false,
                scoreName: 'precio'
	});

        $('.star-raty').raty({
                width: 140,
                starOff:  WEBROOT+'../assets/images/extras/estrella_vacia_recomendacion.png',
                starOn:   WEBROOT+'../assets/images/extras/estrella_llena_recomendacion.png',
                starHalf:   WEBROOT+'../assets/images/extras/estrella_media_recomendacion.png',
                half: true,
                start: estrellas,
                space: false,
                scoreName: 'estrellas'
        });
});