$(document).ready(function(){
	var tipo = $('.recomendacion-precio-raty').data('tipo');

	$('.recomendacion-star-raty').raty({
        width: 140,
        starOff:  WEBROOT+'../assets/images/extras/estrella_vacia.png',
        starOn:   WEBROOT+'../assets/images/extras/estrella_llena.png',
        starHalf:   WEBROOT+'../assets/images/extras/estrella_media.png',
        half: true,
        start: 0,
        space: false,
       	hintList: hintListEstrellas,
       	target: '#estrellas-recomienda',
        scoreName: 'recomienda-estrellas'
    });

    $('.recomendacion-precio-raty').raty({
        width: 140,
        starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
        starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
        start: 0,
        space: false,
        hintList: getTipo(tipo),
        target: '#precio-recomienda',
        scoreName: 'recomienda-precio'
    });

	$('.texto').keyup(function(){
	    $('.max').text(3000 - parseInt($(this).val().length));
	    $('.llevas').text(parseInt($(this).val().length));
	});

	$('.texto').keyup()

    $('[name="recomienda"]').submit(function(){
    	if(validarRecomendacion()){
    		return true
    	}else{
    		return false
    	}
    });
});