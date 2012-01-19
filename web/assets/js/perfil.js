$(function(){
	$('#lista-fotos-usuario ul li').hover(function(){
		$('.opciones-fotos-usuario',$(this)).toggle();
	});	

	$('.borrar-foto-lugar').click(function(){
		if(!confirm('¿Estás seguro de querer borrar tu foto?'))
			return false;
	});
});	