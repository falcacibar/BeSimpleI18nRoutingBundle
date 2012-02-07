$(document).ready(function(){
    var $sidebar   = $(".gmaps"),
    $window    = $(window),
    offset     = $sidebar.offset(),
    topPadding = 50;

    $window.scroll(function() {
        if ($window.scrollTop() > offset.top && $window.scrollTop() <= $('.calle').offset().top) {
            $sidebar.stop().animate({
                marginTop: $window.scrollTop() - offset.top + topPadding
            });
        } else if($window.scrollTop() >= $('.calle').offset().top - topPadding){
            var margin = ($('.calle').offset().top - topPadding - 15 )+'px';
            $sidebar.stop().animate({
                marginTop: margin
            });
        }else {
            $sidebar.stop().animate({
                marginTop: 0
            });
        }
    });

        $( "#form_calle" ).autocomplete({
            source: "http://localhost"+WEBROOT+"ajax/recomendarCalle",
            minLength: 2
        });

        $('.categoria, .sector, .comuna, select.pais, .ciudad').chosen();

    $('.quitar').click(function(e){
        e.preventDefault();

        var $this = $(this);
            rel = $this.attr('rel') || '',
            count = 0;

        $this.parent().parent().fadeOut();
        $this.parent().parent().prev('li').find('span').fadeIn();

        if(rel.indexOf(['categoria', 'telefono'])){
            count = parseInt($('a.display-more-info[rel="'+rel+'"]').find('span').text());

            if(count == 0){
                $('a.display-more-info[rel="'+rel+'"]').parent().parent().fadeIn().css('display', 'block');
            }

            $('a[rel='+rel+']').find('span').text(count + 1)
        }
    });

    $('.display-next-li').click(function(e){
        e.preventDefault(); 
        $(this).parent().parent().next('li').fadeIn().css('display', 'block');
        $(this).parent().fadeOut();
    });

    $('.display-next-li-cb').click(function(){
        if($(this).is(':checked')){
            $(this).parent().next('li').fadeIn().css('display', 'block');
        }else{
            $(this).parent().next('li').fadeOut()
        }
    });

    $('.display-more-info').click(function(e){
        e.preventDefault();
        
        var $this = $(this),
            rel = $this.attr('rel');
            count = parseInt($this.find('span').text()) - 1;
            if(count == 0){
                $this.parent().parent().fadeOut();
            }

            if(count >= 0){
                if(count == 1){
                $('.'+rel+':first').parent().next('li:hidden').fadeIn().css('display', 'block');
                }else{
                  $('.'+rel+':first').parent().next('li').next('li:hidden').fadeIn().css('display', 'block');  
                }
                $this.find('span').text(count);
            }
    });


    $('.categoria').live('change', function(){
        $this = $(this);
        var categoria = $(this).val().camelCase(),
            anterior = selected[$this.attr('id')],
            subCategorias = categorias[categoria].subCategorias;

        $('.categoria').find('option').removeAttr('disabled');

        //Habilitamos el precio de ser necesario
        if($('.precio-li').is(':not(:visible)')){
            var stars = $('.precio-raty').attr('data-stars');
            if(categoria == 'nightClubs'){
                $('.precio').show();
            }else if($(this).find('option:selected').parent().attr('label').camelCase() == 'dóndeComer'){
                $('.precio-li').show();
                precioAgregar(stars, 'dondeComer')
            }else if($(this).find('option:selected').parent().attr('label').camelCase() == 'dóndeDormir'){
                $('.precio-li').show();
                precioAgregar(stars, 'dondeDormir')
            }
        }

        //Deshabilitar en los demas dropdown
        $('.categoria').not($this).each(function(){
            //Deshabilitamos en el resto de los dropdowns el valor que seleccionamos recien
            $(this).find('option[value="'+$this.val()+'"]').attr('disabled', 'disabled').addClass(categoria);
            
            //Habilitamos el valor anterior
            $(this).find('option[value="'+selected[$this.attr('id')]+'"]').removeAttr('disabled');
            $(this).trigger("liszt:updated");
        });

        $('.caracteristicas').parent().fadeOut();
        $('.caracteristicas ul li').fadeOut();

        $('.categoria').each(function(){
            var thisCat = $(this).val().camelCase(),
                caracteristicas = categorias[thisCat].caracteristicas;

            if(caracteristicas != ''){
                $('.caracteristicas').parent().fadeIn().css('display', 'block');
            }

            $.each(caracteristicas, function(i){
                $('input[value="'+caracteristicas[i]+'"]').parent().parent().fadeIn();
                $('.caracteristicas').parent().fadeIn();
            });

            if( anterior != undefined ){
                //Sacamos los camposo especiales.
                camposEspeciales = categorias[anterior.camelCase()].camposEspeciales
                $.each(camposEspeciales, function(i){
                    $('#form_'+camposEspeciales[i]).parent().fadeOut().css('display', 'block');
                });

                //Subcategorias
            }//end anterior
        });// end each

        $this.parent().find('.subcategorias').fadeOut().find('input:checked').click();
        $this.parent().find('.subcategorias li').fadeOut();

        if(subCategorias != ''){
            $this.parent().find('.subcategorias').stop(true,true).fadeIn().css('display', 'inline-block');
        }

        $.each(subCategorias, function(i){
            $this.parent().find('.subcategorias input[value="'+subCategorias[i]+'"]').parent().parent().stop(true, true).fadeIn().css('display', 'inline-block');
        });

        camposEspeciales = categorias[categoria].camposEspeciales
        $.each(camposEspeciales, function(i){
            $('#form_'+camposEspeciales[i]).parent().fadeIn().css('display', 'block');
        });

        $('.categoria').each(function(){
            var val = $(this).val().camelCase(),
                $this = $(this);
            deshabilitar = categorias[val].deshabilitar;
            $.each(deshabilitar, function(j){
                $('.categoria').not($this).each(function(i){
                    $(this).find('option[value="'+deshabilitar[j]+'"]').attr('disabled', 'disabled').addClass(val);
                }); 
            });
            $(this).trigger("liszt:updated");
        });
        
        //Seteamos el valor previo correspondiente al ID del dropdown que cambiamos
        selected[$this.attr('id')] = $this.val();
        $('.categoria').trigger("liszt:updated");
    });

    $('.jornada-doble-checkbox').click(function(){
        if($(this).is(':checked')){
            $(this).parent().parent().find('.horario-pm').fadeIn();
            $('table').find('th.horario-pm').fadeIn();
        }else{
            $(this).parent().parent().find('.horario-pm').fadeOut(function(){
                if($('td.horario-pm:visible').length == 0){
                   $('table').find('th.horario-pm').fadeOut();
                }
            });

        }
    });

    $('.habilitar-horario').click(function(){
        if($(this).is(':checked')){
            $(this).parent().next('li').fadeIn().css('display', 'block');
        }else{
            $(this).parent().next('li').fadeOut();
        }
    });


    //Validacion Subcategorias
    $('.subcategorias').change(function(e){
        $subcat = $(this).parent().find('.subcategoria');
        if($subcat.find(':checked').length == 3){
            $subcat.find(':not(:checked)').attr('disabled', 'disabled');
        }else if($subcat.find(':checked').length < 3){
            $subcat.find(':not(:checked)').removeAttr('disabled', 'disabled');
        }
    });

    $('.ciudad').change(function(){
        actualizarComunas();
    });

    $('.pais').change(function(){
        actualizarCiudades();
    });

    $('form[name="agregar-lugar"]').submit(function(e){
        errores = '';
        $('.errors').remove();
        $.each($('.required'), function(){
            if(($(this).val() == $(this).attr('placeholder') || $(this).val() == '') || ($(this).val() == 'elige')){
                errores += "<p>"+$(this).attr('title')+"</p>";
                $(this).addClass('input-error');
                if($(this).hasClass('calle')){
                    $('.numero').after('<small class="errors">'+$(this).attr('title')+'</small>');
                }else if($(this).is('select') && $(this).not('.secundaria')){
                    $(this).next('.chzn-container').find('.chzn-single').addClass('chzn-error');
                    $(this).after('<small class="errors">'+$(this).attr('title')+'</small>'); 
                }else{
                   $(this).after('<small class="errors">'+$(this).attr('title')+'</small>'); 
                }
                
            }else{
                $(this).removeClass('input-error');
            }
        });

        if( !($('input[name="form[numero]"]').val().match(/(\d+|s\/n)/g)) ){
            errores += "<p>Debes ingresar sólo números o \"s/n\" en el campo de Nº</p>";
            $('input[name="form[numero]"]').addClass('input-error');
            $('input[name="form[numero]"]').after('<small class="errors">Debes ingresar sólo números o "s/n" en el campo de Nº</small>')
        }

        $.each($('.categoria:not(:hidden)'), function(){
            $this = $(this);
            if($this.parent().find('.subcategorias ul').children(':visible').length > 0){
                if($this.parent().find('.subcategorias').find('ul > li').length > 0 && $this.parent().find('.subcategorias').find('ul > li > label').children(':checked').length == 0){
                    $this.addClass('input-error');
                    $this.parent().find('.subcategorias ul').after('<small class="errors categoria">Debes seleccionar al menos una subcategoría</small>');
                    errores += "<p>Debes seleccionar al menos una subcategoría</p>";
                }
            }
        });

        //Worst Conditional Ever
        if(
            ( $('.mapx').val() == '' || $('.mapy').val() == '' ) 
            && 
            ( $('.calle').val() != $('.calle').attr('placeholder') && $('.numero').val() != $('.numero').attr('placeholder'))
            && 
            $('.comuna').val() != 'elige')
            {
            errores += "<p>¡Espera! Acuérdate de ubicar el lugar en el mapa, ya sea cargando el mapa o arrastrando el icono a su posición.</p>";
            $('.mapa_info').before('<small class="errors">¡Espera! Acuérdate de ubicar el lugar en el mapa, ya sea cargando el mapa o arrastrando el icono a su posición.</small>');
        }

        recomendacion = true;
        if($('[name="habilitar-recomienda"]').is(':checked')){
            recomendacion = validarRecomendacion();
            if(recomendacion == false){
                errores += "<p>Porfavor completa tu recomendacion.</p>";
            }
        }

        if(errores != '' || recomendacion == false){
            $('.errores-container').html(errores);
            $('body').animate({'scrollTop': 0}, 400);
            $('.errores-container').fadeIn();
        }else{
            $('.placeholder').val('');
            return true;
        }
        return false;
    });

    $('.cargar_mapa').click(function(e){
        e.preventDefault();
        if(!$('#form_calle').val().match('Ej') && !$('#form_numero').val().match('Ej')){
            $.ajax({
                url: WEBROOT+'ajax/lugarYaExiste',
                type: 'post', 
                dataType: 'json',
                data: "calle="+$('#form_calle').val()+"&numero="+$('#form_numero').val()+"&id="+$('.id').val(),
                success: function(data){
                    if(data.lugar){
                        $('.lugar-existe').append('<h5>Hay otros lugares con la misma dirección. Asegúrate de no agregar uno que ya esté.</5>')
                        $.each(data.lugar, function(i){
                           $('.lugar-existe').append("<p>"+data.lugar[i]+"</p>"); 
                        })
                        $('.lugar-existe').fadeIn();
                        
                    /*  }else{
                        $('.lugar-existe').append('¡No existen lugares en esta dirección!').fadeIn(); */
                    }
                },
                error: function(data) {console.log(data)}
            });
        }
    onCargarMapaAgregar();
    });

    /* Fncs */



    function actualizarComunas(){
        var ciudadSeleccionada = $('.ciudad option:selected').text();

        $.each(optGroupComunas, function(i){
            if(optGroupComunas[i].match(ciudadSeleccionada)){
                $('.comuna > optgroup').remove('optgroup');
                $('.comuna').append(optGroupComunas[i]);
                $(".comuna").trigger("liszt:updated")
            }
        });

        $.each(optGroupSectores, function(i){
            if(optGroupSectores[i].match(ciudadSeleccionada)){
                $('.sector  optgroup').remove('optgroup');
                $('.sector').append(optGroupSectores[i]);
                $(".sector").trigger("liszt:updated")
            }
        });
    }

    function actualizarCiudades(){
        var paisSeleccionado = $('.pais option:selected').text();

        $.each(optGroupCiudades, function(i){
            if(optGroupCiudades[i].match(paisSeleccionado)){
                $('.ciudad optgroup').remove('optgroup');
                $('.ciudad').append(optGroupCiudades[i]);
                $(".ciudad").trigger("liszt:updated")
            }
        });

        actualizarComunas();
    }

    /* INITIALIZATION */

    var optGroupCiudades = [],
        optGroupComunas = [],
        optGroupSectores = [],
        selected = {},
        caracteristicasASacar = [];


    $('input[type=text], textarea').each(function(){ $(this).placeholder() });

    $('.ciudad optgroup').each(function(i){
        optGroupCiudades[i] = $(this).outerHTML();
        $(this).remove();
    });

    $('.comuna optgroup').each(function(i){
        optGroupComunas[i] = $(this).outerHTML();
        $(this).remove();
    });

    $('.sector optgroup').each(function(i){
        optGroupSectores[i] = $(this).outerHTML();
        $(this).remove();
    });

    actualizarCiudades();
    actualizarComunas();
});

function precioAgregar(precio, tipo){
    tipo = getTipo(tipo);

    $('.precio-raty').raty({
        width: 140,
        starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
        starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
        start: precio,
        space: false,
        hintList: tipo,
        target: '.precio-detalle'
    });
}