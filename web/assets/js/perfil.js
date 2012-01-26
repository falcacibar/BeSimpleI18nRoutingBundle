$(function(){
	$('#lista-fotos-usuario ul li').hover(function(){
		$(this).find('.opciones-fotos-usuario').toggle();
	});	

	$('.borrar-foto-lugar').click(function(){
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
});	