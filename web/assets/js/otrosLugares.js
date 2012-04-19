$(document).ready(function(){
	$('.estrellas_lugar').find('div').each(function(i){
		var estrellas = $(this).attr('data-stars');
		estrellasOtrosLugares((i+1), estrellas);
	});

	$('.resultado-busqueda-stars-raty').each(function(){
		var estrellas = $(this).attr('data-stars');
		$(this).raty({
			width: 160,
		    starOff:  WEBROOT+'../assets/images/extras/estrella_vacia_recomendacion.png',
		    starOn:   WEBROOT+'../assets/images/extras/estrella_llena_recomendacion.png',
		    starHalf:   WEBROOT+'../assets/images/extras/estrella_media_recomendacion.png',
		    half: true,
		    start: estrellas,
		    readOnly: true,
		    space: false
		});
	});
});