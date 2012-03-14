$(function(){
    $('.caja_recomendaciones_lugar_usuario').hover(function(){
        $(this).find('.opciones_lugar_recomendacion').toggle();
    });    

	$('.listado_foto_lugar_usuario li').hover(function(){
		$(this).find('.opciones_lugar_imagen').toggle();
	});	

	$('.listado_lugares_guardados_usuario li').live('hover', function(){
		$(this).find('.opciones_lugar_guardado').toggle();
	});	

	$('.borrar_lugar_imagen').click(function(){
		if(!confirm('¿Estás seguro de querer borrar tu foto?'))
			return false;
	});

	$('.submit-borrar input[type=submit]').click(function(){
		if(confirm('¿Estás seguro de querer borrar tu cuenta de Loogares.com?'))
			$('.form-edicion').parent().submit();
		return false;
	});

	$('.form-borrar-foto input[type=submit]').click(function(){
		if(confirm('¿Estás seguro de querer borrar tu foto?'))
			$('.form-borrar-foto').parent().submit();
		return false;
	});

	$('a.accion_borrar_usuario').live('click', function(e){
		e.preventDefault();
		var $this = $(this),
			idLugar = $this.parent().parent().parent().attr('data-id'),
			accion = $this.parent().parent().parent().attr('data-accion');

		if(accion == 1){
            var dataObj = {'lugar': idLugar,'accion': 'quiero_ir'};
        }else if(accion == 2){
            var dataObj = {'lugar': idLugar,'accion': 'quiero_volver'};
        }else if(accion == 3){
            var dataObj = {'lugar': idLugar,'accion': 'estuve_alla'};    
        }else if(accion == 4){
            var dataObj = {'lugar': idLugar,'accion': 'favoritos'};
        }else if(accion == 5){
            var dataObj = {'lugar': idLugar,'accion': 'recomendar_despues'};
        }
		$.ajax({
            url: WEBROOT+'ajax/accion',
            type: 'post',
            data: dataObj,
            dataType: 'json',
            success: function(data){
            	// Se actualizan datos del usuario
	            $.ajax({
	              type: "GET",
	              url: WEBROOT+'../usuario/acciones_pendientes/5'
	            }).done(function( data ) {
	              $('.por_recomendar').html(data);
	              $('.lugares_por_recomendar_usuario span').html(data);
	            });

	            $.ajax({
	              type: "GET",
	              url: WEBROOT+'../usuario/acciones_pendientes/1'
	            }).done(function( data ) {
	              $('.para_visitar').html(data);
	              $('.lugares_por_visitar_usuario span').html(data);
	            });

	            $.ajax({
	              type: "GET",
	              url: WEBROOT+'../usuario/acciones_pendientes/4'
	            }).done(function( data ) {
	              $('.lugares_favoritos_usuario span').html(data);
	            });
            	
                $('a.reload_link').click();
            }
        });
	});

	$('a.reload_link').live('click', function(){
		$.ajax({
            url: $(this).attr('href'),
            type: 'get',
            success: function(data){
            	$('.caja_contenido').html($(data).fadeIn('fast'));
            }
        });
	});

	// Esto es hasta que encuentre una forma de desplegar nombres en vez de números
	$('select.month option').each(function(){
		if($(this).val() == '1')
			$(this).html('Enero');
		else if($(this).val() == '2')
			$(this).html('Febrero');
		else if($(this).val() == '3')
			$(this).html('Marzo');
		else if($(this).val() == '4')
			$(this).html('Abril');
		else if($(this).val() == '5')
			$(this).html('Mayo');
		else if($(this).val() == '6')
			$(this).html('Junio');
		else if($(this).val() == '7')
			$(this).html('Julio');
		else if($(this).val() == '8')
			$(this).html('Agosto');
		else if($(this).val() == '9')
			$(this).html('Septiembre');
		else if($(this).val() == '10')
			$(this).html('Octubre');
		else if($(this).val() == '11')
			$(this).html('Noviembre');
		else if($(this).val() == '12')
			$(this).html('Diciembre');
	});

	$('.estrella_lugar_recomendacion, .estrellas_actividad').each(function(i){
		var estrellas = $(this).attr('data-stars');
		estrellasLugares(estrellas, $(this));
	});

    $('.caja_actividad_usuario form select').change(function(){
        $(this).parent().submit();
    });

    $('.facebook_connect').click(function(e){
        e.preventDefault();
        $('.fb_hidden_button').find('.fb_button').click();
    });
});	

function estrellasLugares(estrellas, $this){
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