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
});

function precioLugar(precio, tipo){
    if(tipo == 'dondeComer'){
        tipo = ['Menos de $3.000', '$3.000 - $7.000', '$7.000 - $12.000', '$12.000 - $18.000', 'Mas de $18.000']
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
        space: false
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