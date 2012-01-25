function getParameterByName(name){
  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.href);
  if(results == null)
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

$(document).ready(function(){
    var getResultados = getParameterByName('resultados');

    $('.seleccionar_resultados_por_pagina').find('option[value="'+getResultados+'"]').attr('selected', 'selected')
        .end().change(function(){
            if( getResultados == ''){
                if(window.location.href.match(/\?/)){
                    var location = window.location.href.replace(/pagina=\d+/, 'pagina=1');
                    window.location = location+'&resultados='+$(this).val(); 
                }else{
                    window.location = window.location.href+'?resultados='+$(this).val();  
                }
            }else{
                window.location = window.location.href.replace(/resultados=\d+/, 'resultados='+$(this).val()).replace(/pagina=\d+/, 'pagina=1');
            }
        });

    $('.boton_util').click(function(e){
        e.preventDefault();

        var $this = $(this),
            dataUtil = $(this).attr('data-util').split('-');

        $.ajax({
           url: WEBROOT+'ajax/util',
           type: 'post',
           data: {'recomendacion': dataUtil[0], 'usuario': dataUtil[1]},
           success: function(data){
            console.log(data)
                var util = parseInt($this.parent().find('.conteo_util').text());
                if($this.hasClass('boton_activado')){
                    $this.removeClass('boton_activado').addClass('boton_desactivado');
                    $('.conteo_util').text(util+1)
                }else if($this.hasClass('boton_desactivado')){
                    $this.removeClass('boton_desactivado').addClass('boton_activado');
                    $('.conteo_util').text(util-1)
                }
           }
        });
    });
});

function precioLugar(precio, tipo){
    if(tipo == 'dondeComer'){
        tipo = ['Menos de $3.000', '$3.000 - $7.000', '$7.000 - $12.000', '$12.000 - $18.000', 'Mas de $18.000'];
    }else if(tipo == 'dondeDormir'){
        tipo = ['Minimo', 'Barato', 'Medio', 'Alto', 'Maximo'];
    }

    $('.precio-raty').raty({
        width: 140,
        starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
        starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
        start: precio,
        readOnly: true,
        space: false
    });

    $('.precio-detalle').append(tipo[precio-1])
}

function estrellasPorRecomendacion(id, estrellas){
    $('.recomendacion-'+id+'-raty').raty({
        width: 140,
        starOff:  WEBROOT+'../assets/images/extras/estrella_vacia_recomendacion.png',
        starOn:   WEBROOT+'../assets/images/extras/estrella_llena_recomendacion.png',
        starHalf:   WEBROOT+'../assets/images/extras/estrella_media_recomendacion.png',
        half: true,
        start: estrellas,
        readOnly: true,
        space: false,
        scoreName: 'estrellas'
    });
}

function estrellasDelLugar(estrellas){
    $('.star-raty').raty({
        width: 140,
        half: true,
        start: estrellas,
        readOnly: true,
        space: false
    });
}

function estrellasOtrosLugares(id, estrellas){
    console.log(id)
    $('.star-raty-otrosLugares'+id).raty({
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