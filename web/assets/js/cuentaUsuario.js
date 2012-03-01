$(document).ready(function(){
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

    var optGroupCiudades = [],
        optGroupComunas = [],
        selected = {};

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

    $('.comuna, .ciudad, select.pais, #form_fecha_nacimiento_day, #form_fecha_nacimiento_month, #form_fecha_nacimiento_year').chosen();

    actualizarCiudades();
});