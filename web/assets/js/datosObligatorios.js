$(document).ready(function(){
    var optGroupCiudades = [],
    optGroupComunas = [],
    selected = {};


	$('.siguiente_paso').click(function(e){
		e.preventDefault();
		$('.datos_obligatorios_lista').animate({
			marginLeft: -600
		});
	});

	$('.anterior_paso').click(function(e){
		e.preventDefault();
		$('.datos_obligatorios_lista').animate({
			marginLeft: 0
		});
	});

	$('.ciudad optgroup').each(function(i){
	    optGroupCiudades[i] = $(this).outerHTML();
	    $(this).remove();
	});

	$('.comuna optgroup').each(function(i){
	    optGroupComunas[i] = $(this).outerHTML();
	    $(this).remove();
	});

	$('.ciudad').change(function(){
		actualizarComunas();
	});

	$('.pais').change(function(){
		actualizarCiudades();
	});

	$('.comuna, .ciudad, select.pais').chosen();

	$('[name="forzar_datos_form"]').on('submit', function(e){
		e.preventDefault();

		var errorNombre = 0, errorComuna = 0;

		$('.errors').remove();
		if($('.nombre').length > 0){
			if($('.nombre').val() == ''){
				$('.nombre').parent().append('<small class="errors">error</small>');
				errorNombre = 1;
			}

			if($('.apellido').val() == ''){
				$('.apellido').parent().append('<small class="errors">error</small>');
				errorNombre = 1;
			}
		}

		if($('select.pais').length > 0){
			if($('select.pais').val() == 'elige'){
				$('select.pais').parent().append('<small class="errors">error</small>');
				errorComuna = 1;
			}

			if($('.ciudad').val() == 'elige' && $('.container_ciudad').is(':visible')){
				$('.ciudad').parent().append('<small class="errors">error</small>');
				errorComuna = 1;
			}

			if($('.comuna').val() == 'elige'  && $('.container_comuna').is(':visible')){
				$('.comuna').parent().append('<small class="errors">error</small>');
				errorComuna = 1;
			}
		}

		if(errorNombre){
			$('.datos_obligatorios_lista').animate({
				marginLeft: 0
			});
		}else if(errorComuna){
			$('.datos_obligatorios_lista').animate({
				marginLeft: $('.datos_obligatorios_lista li').width()+600
			});
		}

		if(errorNombre == 0 && errorComuna == 0){
			$.ajax({
				type: 'POST',
				data: $('[name="forzar_datos_form"]').serialize(),
				url: WEBROOT+"usuario/forzar_datos",
				success: function(data){
					if(data == 'gud gud'){
						window.location = window.location.href;
					}
				}
			});
		}
	});

	function actualizarComunas(){
	    var ciudadSeleccionada = $('.ciudad option:selected').text(),
	    	found = 0;

	    $.each(optGroupComunas, function(i){
	        if(optGroupComunas[i].match(ciudadSeleccionada)){
	            $('.comuna > optgroup').remove('optgroup');
	            $('.comuna').append(optGroupComunas[i]);
	            $(".comuna").trigger("liszt:updated");
	            $('.container_comuna').fadeIn();
	            found = 1;
	        }
	    });

			if(found == 0){
	    	$('.comuna optgroup').remove('optgroup');
	    	$(".comuna").trigger("liszt:updated");
	    	$('.container_comuna').fadeOut();
	    }
	}

	function actualizarCiudades(){
	    var paisSeleccionado = $('.pais option:selected').text(),
	    	found = 0;

	    $.each(optGroupCiudades, function(i){
	        if(optGroupCiudades[i].match(paisSeleccionado)){
	            $('.ciudad optgroup').remove('optgroup');
	            $('.ciudad').append(optGroupCiudades[i]);
	            $(".ciudad").trigger("liszt:updated");
	            $('.container_ciudad').fadeIn();
	            found = 1;
	        }
	    });

	    if(found == 0){
	    	$('.ciudad optgroup').remove('optgroup');
	    	$(".ciudad").trigger("liszt:updated");
	    	$('.container_ciudad').fadeOut();
	    }

	    actualizarComunas();
	}
});