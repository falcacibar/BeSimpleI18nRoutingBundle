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
    var getResultados = getParameterByName('resultados'),
        compartirTimeout;

    fechasRecomendaciones = [];
        
    $('.fecha_recomendacion').each(function(){ 
        var date = $(this).text().split('/'),
            dateObj = Date.parse(date[2] + "-" + date[1] + "-" + date[0]);
        fechasRecomendaciones.push( dateObj );
    });

    fechasRecomendaciones.sort().reverse();

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


    if(window.location.href.match(/\/recomendacion\//)){
        $('body').animate({'scrollTop': $('.editar_lugar').offset().top}, 200);
    }

    $('.recomendacion').hover(function(){
        $(this).find('.opciones_recomendacion').toggle();
    });
    
    $('.compartir').click(function(e){
        e.preventDefault();
        var rel = $(this).attr('rel'),
            $compartir = $('.'+rel);
            $compartir = $(this).parent().parent().parent().find('.'+rel)

        if($compartir.is(':hidden')){
            $compartir.fadeIn('fast');
            compartirTimeout = setTimeout(function(){$compartir.fadeOut('fast')}, 5000);
        }else{
            $compartir.fadeOut('fast');
            clearTimeout(compartirTimeout);  
        }
    });

    $('.compartir_lugar, .compartir_recomendacion').mouseover(function(){
        clearTimeout(compartirTimeout);
    }).mouseout(function(){
        $this = $(this);
        compartirTimeout = setTimeout(function(){$this.fadeOut('fast')}, 2500);
    });

    $('.estrellas_recomendacion').each(function(i){
        var stars = $(this).attr('data-stars');
        estrellasPorRecomendacion(i, stars);
        if(i == 0 && $('.recomendacion-pedida-raty').length > 0){
            estrellasPorRecomendacion('pedida', stars);
        }
    });
        
    $('.cancelar-recomienda').live('click', function(){
        $('[name="recomienda"]').fadeOut(function(){
            $(this).parent().parent().find('.recomendacion-bloque').children().show();
            $(this).remove();
        })
    });

    $('.permalink_recomendacion').click(function(e){
        e.preventDefault();

        $estaRecomendacion = $(this).closest('.recomendacion').hide();
        nombre = $estaRecomendacion.find('.nombre_recomendacion > strong > a').text();

        if($('.recomendacion_pedida').length > 0){
            $pedida = $('.recomendacion_pedida').hide();
            fechaPedida = $pedida.find('.fecha_recomendacion').text().split('/');
            fechaPedidaObj = Date.parse(fechaPedida[2] + "-" + fechaPedida[1] + "-" + fechaPedida[0]);

            eq = fechasRecomendaciones.indexOf(fechaPedidaObj);

            $pedida.removeClass('recomendacion_pedida');

            if(eq != -1){
                if(eq == 0){
                    $('.recomendacion').eq(eq+1).before($pedida.show());
                }else{
                    $('.recomendacion').eq(eq).after($pedida.show());
                }
            }else{
                $pedida.remove();
            }
        }
        
        $('body').animate({'scrollTop': $('.editar_lugar').offset().top}, 200);
        window.history.pushState({}, "", $(this).attr('href'));
        $estaRecomendacion.addClass('recomendacion_pedida');
        $('.recomendacion_pedida_container').append($estaRecomendacion.fadeIn(800));
        $('.recomendacion_pedida_container > h1').text('Recomendacion De '+nombre);
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
                var util = parseInt($this.parent().find('.conteo_util').text());
                if($this.hasClass('boton_activado')){
                    $this.removeClass('boton_activado').addClass('boton_desactivado');
                    $('.conteo_util').text(util+1)
                    // Request para enviar mail a usuario de recomendación
                    $.ajax({
                       url: WEBROOT+'ajax/util_mail',
                       type: 'post',
                       data: {'recomendacion': dataUtil[0], 'usuario': dataUtil[1]}
                    });
                }else if($this.hasClass('boton_desactivado')){
                    $this.removeClass('boton_desactivado').addClass('boton_activado');
                    $('.conteo_util').text(util-1)
                }
           }
        });
    });

});

function precioLugar(precio, tipo){
    tipo = getTipo(tipo);

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

function editarRecomendacion(lugar){
    $('.edita_recomendacion').click(function(e){
        e.preventDefault(); 
        $recomendacionBloque = $(this).parent().parent().find('.recomendacion-bloque');
        $.ajax({
            type:'post',
            data: {'slug': lugar},
            url: WEBROOT+'ajax/recomendacion',
            success: function(data){
                $recomendacionBloque.children().each(function(i){
                    $(this).fadeOut(200, function(){
                        if(i==0){
                            $recomendacionBloque
                                .append(data)
                            $('[name="recomienda"]')
                                .append('<input type="hidden" name="editando" value="1"/>')
                                .append("<input type='button' value='cancelar' class='cancelar-recomienda'/>");
                        }
                    })
                });  
            }
        }); 
    });
}

function recomendacionPedida(slug){
    $('.recomendacion[data-slug="christopher-uribe-espina"]').find('.permalink_recomendacion').click();
}