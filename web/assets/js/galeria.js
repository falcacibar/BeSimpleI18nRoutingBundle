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