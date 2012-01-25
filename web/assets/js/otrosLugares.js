$(document).ready(function(){
	$('.estrellas_lugar').find('div').each(function(i){
		var estrellas = $(this).attr('data-stars');
		estrellasOtrosLugares((i+1), estrellas);
	});
});