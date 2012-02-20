oldBrowser = false;

var IE;
//@cc_on IE = navigator.appVersion;
if(/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)){var ffx=new Number(RegExp.$1);}
if(IE || ffx < 3.7){
    oldBrowser = true;
}

$(document).ready(function(){
    var compartirTimeout,
    fechasRecomendaciones = [];
        
    $('.fecha_recomendacion').each(function(){ 
        var date = $(this).text().split('/'),
            dateObj = Date.parse(date[2] + "-" + date[1] + "-" + date[0]);
        fechasRecomendaciones.push( dateObj );
    });

    fechasRecomendaciones.sort().reverse();


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
        $estaRecomendacion = $(this).closest('.recomendacion');

        //Si es browser moderno, evitamos el link, hacemos pushstate, y escondemos el itennnnn
        if(oldBrowser == false){
            e.preventDefault();
            window.history.pushState({}, "", $(this).attr('href'));
            $estaRecomendacion.hide();
        }
        
        nombre = $estaRecomendacion.find('.nombre_recomendacion > strong > a').text();

        if($('.recomendacion_pedida').length > 0){
            if(oldBrowser == false){
                $pedida = $('.recomendacion_pedida').hide();
            }
            
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
            }else if(oldBrowser == false){
                $pedida.remove();
            }
        }
        
        $('body').animate({'scrollTop': $('.editar_lugar').offset().top}, 200);

        $estaRecomendacion.addClass('recomendacion_pedida');
        $('.recomendacion_pedida_container').append($estaRecomendacion.fadeIn(800));
        $('.recomendacion_pedida_container > h1').text('Recomendacion De '+nombre);
    });

    var send_util_mail = function(){
        $.ajax({
            url: WEBROOT+'ajax/util_mail',
            type: 'post',
            data: dataObj
        });
    }

    var ejecutar_accion = function(dataObj, $this){
        var idLugar = $('#lugar-ficha').attr('data-id');
        $.ajax({
            url: WEBROOT+'ajax/accion',
            type: 'post',
            data: dataObj,
            dataType: 'json',
            success: function(data){
                var conteo = parseInt($this.prev('.conteo').text());

                if($this.hasClass('boton_util')){
                    if($this.hasClass('boton_activado')){
                        $this.prev('.conteo').text(conteo+1);
                        $this.removeClass('boton_activado').addClass('boton_clickeado'); 

                        // Request para enviar mail a usuario de recomendación solo si es un util
                        send_util_mail();
                    }
                    else if($this.hasClass('boton_clickeado')){
                        $this.prev('.conteo').text(conteo-1);
                        $this.removeClass('boton_clickeado').addClass('boton_activado');
                    }
                }
                else {
                    if($this.hasClass('boton_activado')) {
                        $this.removeClass('boton_activado').addClass('boton_clickeado');                    
                    }
                    else if($this.hasClass('boton_clickeado')) {
                        $this.removeClass('boton_clickeado').addClass('boton_activado');
                    }

                    $('.quiero_ir_valor').text(data.totalAcciones[0].total)                       
                    $('.quiero_volver_valor').text(data.totalAcciones[1].total)
                    $('.estuve_alla_valor').text(data.totalAcciones[2].total)
                    $('.favoritos_valor').text(data.totalAcciones[3].total)

                    $('.quiero_ir_lugar').attr('data-hecho',data.accionesUsuario[0].hecho)
                    $('.quiero_volver_lugar').attr('data-hecho',data.accionesUsuario[1].hecho)
                    $('.estuve_alla_lugar').attr('data-hecho',data.accionesUsuario[2].hecho)
                    $('.favoritos_lugar_icono').attr('data-hecho',data.accionesUsuario[3].hecho)
                    $('.recomendar_despues_lugar').attr('data-hecho',data.accionesUsuario[4].hecho)

                    // Pop-up para recomendar
                    if($this.attr('data-hecho') == "1" && ($this.hasClass('estuve_alla_lugar') || $this.hasClass('quiero_volver_lugar')) && $('.recomendar_despues_lugar').attr('data-hecho') == 0) {
                        $(".fancybox").click();   
                    }

                    // Se desactivan botones pertinentes
                    if(data.accionesUsuario[0].puede == 0) {
                        $('.quiero_ir_lugar').replaceWith("<p class='quiero_ir_lugar boton_desactivado' data-hecho=''></p>");
                    }
                    if(data.accionesUsuario[2].puede == 0) {
                        $('.estuve_alla_lugar').replaceWith("<p class='estuve_alla_lugar boton_desactivado' data-hecho=''></p>");                        
                    }
                    if(data.accionesUsuario[4].puede == 0) {
                        $('.recomendar_despues_lugar').replaceWith("<p class='recomendar_despues_lugar boton_desactivado' data-hecho=''></p>");                        
                    }

                    // Se actualiza el menú del usuario
                    $.ajax({
                      type: "GET",
                      url: WEBROOT+'usuario/acciones_pendientes/5',
                    }).done(function( data ) {
                      $('.por_recomendar').html(data);
                    });

                    $.ajax({
                      type: "GET",
                      url: WEBROOT+'usuario/acciones_pendientes/1',
                    }).done(function( data ) {
                      $('.para_visitar').html(data);
                    });
                }
            }
        });
    }
    $('.boton_accion').click(function(e){
        e.preventDefault();

        var $this = $(this),
            idLugar = $('#lugar-ficha').attr('data-id');
        //UTIL: Recomendacion ID y Usuario(OBSOLETE)
        if($this.hasClass('boton_util')){
            dataObj = {'recomendacion': $this.closest('.recomendacion').attr('data-id'),'accion': 'util'};
        }else if($this.hasClass('quiero_ir_lugar')){
            dataObj = {'lugar': idLugar,'accion': 'quiero_ir'};
        }else if($this.hasClass('quiero_volver_lugar')){
            dataObj = {'lugar': idLugar,'accion': 'quiero_volver'};
        }else if($this.hasClass('estuve_alla_lugar')){
            dataObj = {'lugar': idLugar,'accion': 'estuve_alla'};    
        }else if($this.hasClass('favoritos_lugar_icono')){
            dataObj = {'lugar': idLugar,'accion': 'favoritos'};
        }else if($this.hasClass('recomendar_despues_lugar')){
            dataObj = {'lugar': idLugar,'accion': 'recomendar_despues'};
        }             

        ejecutar_accion(dataObj, $this);
    });

    $(".fancybox").fancybox({        
        hideOnContentClick : false,
        padding: 0,
        showCloseButton: false         
    });

    $('.pedido_fancybox').fancybox({
        hideOnContentClick : false,
        padding: 0,
        type: 'ajax',
        showCloseButton: false,
        onStart: function(){
            $.fancybox.showActivity;
        },
        onComplete: function(){
            $.fancybox.hideActivity;
        }
    });
    
    $('.recomendar_ahora').click(function(e){
        e.preventDefault();
        if($('.recomienda_lugar_caja h3').offset().top != null)
            $('body').animate({'scrollTop': $('.recomienda_lugar_caja h3').offset().top - 20}, 200);
        $.fancybox.close()
    });
    
    $('.recomendar_despues').click(function(e){
        e.preventDefault();
        var boton = $('.recomendar_despues_lugar');
        if(boton.attr('data-hecho') == 0) {
            boton.click();
        }
        $.fancybox.close()        
    });
    
    $('.recomienda_lugar').click(function(e){
        e.preventDefault();
        if($('.recomienda_lugar_caja h3').offset() != null)
            $('body').animate({'scrollTop': $('.recomienda_lugar_caja h3').offset().top - 20}, 200);
    }); 
    $('.cerrar-popup').click(function(e){
        e.preventDefault();
        $.fancybox.close()
    });
    
    $('.not_logged').hover(function() {
        var popup = $('.not_logged_acciones_popup')
        popup.appendTo($(this));
        popup.toggle();
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
    $('.recomendacion[data-slug="'+slug+'"]').find('.permalink_recomendacion').click();
}