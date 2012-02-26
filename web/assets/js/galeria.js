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

	$(document).keydown(function(e){
		e.preventDefault();
		// Tecla izquierda
	    if (e.which == 37) {
	    	$('a .anterior').click();
	    }
	    // Tecla derecha
	    else if(e.which == 39) {
	    	$('a .siguiente').click();
	    }
	    return false;
	});

	$('.editar_foto').click(function(e){
        e.preventDefault(); 
        $descripcion = $('.descripcion_foto');
        console.log($descripcion)
        $.ajax({
            type:'post',
            url: WEBROOT+'lugar/'+$descripcion.attr('data-slug')+'/galeria/'+$descripcion.attr('data-id')+'/editar',
            success: function(data){
                $descripcion.find('.container').fadeOut(200, function(){
                    $descripcion.append(data)
                })
            }
        }); 
    });

     $('.cancelar_edicion').live('click', function(e){
     	e.preventDefault();
        $('.descripcion_foto form').fadeOut(function(){
            $(this).parent().find('.container').show();
            $(this).remove();
        })
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