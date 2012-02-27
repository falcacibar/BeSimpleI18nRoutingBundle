$(function(){
    $('.estrellas_recDia, .estrellas_actividad').each(function(i){
		var estrellas = $(this).attr('data-stars');
		estrellasRecomendacion(estrellas, $(this));
	});

    $('.actividad_extendida form select').change(function(){
        $(this).parent().submit();
    });
});	

function estrellasRecomendacion(estrellas, $this){
    $this.raty({
        width: 140,
        starOff:  WEBROOT+'../assets/images/extras/estrella_vacia_recomendacion.png',
        starOn:   WEBROOT+'../assets/images/extras/estrella_llena_recomendacion.png',
        starHalf:   WEBROOT+'../assets/images/extras/estrella_media_recomendacion.png',
        half: true,
        start: estrellas,
        readOnly: true,
        space: false
    });
}