$(function(){
	/* Detalle foto */

	$('.borrar_foto').live("click", function(){
		if(!confirm('¿Estás seguro de querer borrar tu foto?'))
			return false;
	});

	$('a .siguiente').live("click", function(e){
		e.preventDefault();
		cambiarFoto($(this).parent().attr('href'));
		return false;
		
	});

	$('a .anterior').live("click", function(e){
		e.preventDefault();	
		cambiarFoto($(this).parent().attr('href'));
		return false;		
		
	});

	$('.foto_galeria').live("click", function(e){
		$('.siguiente a').click();
		return false;
	});	

	$('.editar_foto').live('click', function(e){		
        e.preventDefault();
        var $this = $(this);
        var $descripcion = $('.descripcion_foto');
        $.ajax({
            type:'get',
            url: WEBROOT+'lugar/'+$descripcion.attr('data-slug')+'/galeria/'+$descripcion.attr('data-id')+'/editar',
            success: function(data){
            	// Descripción reemplazada por form
                $descripcion.find('.container').fadeOut(100, function(){
                    $descripcion.append(data)
                });
                // Inhabilitamos el botón de editar
                $this.fadeOut(100);
            }
        }); 
    });

     $('.cancelar_edicion').live('click', function(e){
     	e.preventDefault();
        $('.descripcion_foto form').fadeOut(function(){
        	// Desaparece form, aparece descripción, y habilitamos botón de editar
            $(this).parent().find('.container').show();
            $(this).remove();
            $('.editar_foto').show();
        })
    });

    $('.editar_submit').live('click', function(e){
    	e.preventDefault();
    	var $this = $(this);
        var $descripcion = $('.descripcion_foto');
        var $form = $('.descripcion_foto form');
        $.ajax({
            type:'post',
            data: $form.serializeArray(),
            url: WEBROOT+'lugar/'+$descripcion.attr('data-slug')+'/galeria/'+$descripcion.attr('data-id')+'/editar',
            success: function(data){
            	$form.fadeOut(function(){
		        	// Desaparece form, aparece descripción, y habilitamos botón de editar
		            $(this).parent().find('.container').html(data).show();
		            $(this).remove();
		            $('.editar_foto').show();
		        })
            }
        }); 

    });

    $(document).keydown(function(e){
		// Tecla izquierda
	    if (e.which == 37 && e.target.nodeName == 'BODY') {
	    	$('a .anterior').click();
	    }
	    // Tecla derecha
	    else if(e.which == 39 && e.target.nodeName == 'BODY') {
	    	$('a .siguiente').click();
	    }
	});

	/* Paginación fotos de lugar */

	$('.imagen').live("click", function(e){
		e.preventDefault();	
		cambiarFoto($("a", this).attr('href'));
		return false;
	});

	$('.orden li a').live("click", function(e){
		e.preventDefault();	
		cambiarPaginaFoto($(this).attr('href'));
		return false;		
	});
});


function cambiarFoto(fotoUrl) {
	$.ajax({
	  type: "GET",
	  url: fotoUrl,
	}).done(function( data ) {
	  $('.contenido_galeria').html($(data).fadeIn('fast'));
	  window.history.pushState("", "", fotoUrl);
	}).fail(function( data ) {
	  console.log(data);
	});
	return false;			
}

function cambiarPaginaFoto(paginaUrl) {
	$.ajax({
	  type: "GET",
	  url: paginaUrl,
	}).done(function( data ) {
	  $('#pagina').html($(data).fadeIn('fast'));
	}).fail(function( data ) {
	  console.log(data);
	});
	return false;
}