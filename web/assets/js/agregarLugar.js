$(document).ready(function(){
    var precioFlag = null, label = '';
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
        source: WEBROOT+"../ajax/recomendar-calle",
        minLength: 2,
        delay: 0
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

            //Si el precio es Null (osea, step 1, iniciamos el ciclo), entonces es igual a False
            if(precioFlag == null) precioFlag = false;

            if( $(this).find('option:selected').parent().attr('label') != undefined ){
                label = $(this).find('option:selected').parent().attr('label').camelCase();

                //Si la categoria o label necesita precio, seteamos true
                if(categoria == 'nightClubs'){
                    precioFlag = true;
                    label = 'nightClubs';
                }else if(label == 'dóndeComer'){
                    precioFlag = true;
                }else if(label == 'dóndeDormir'){
                    precioFlag = true;
                }
            }

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

        //Mostramos el precio si lo necesitamos
        if(precioFlag == true){
            var stars = $('.precio-raty').attr('data-stars');
            if(label == 'dóndeComer'){
                $('.precio-li').show();
                $('.recomienda-precio-li').show();
                precioAgregar(stars, 'dondeComer')
            }else if(label == 'dóndeDormir'){
                $('.precio-li').show();
                $('.recomienda-precio-li').show();
                precioAgregar(stars, 'dondeDormir');
            }else if(label == 'nightClubs'){
                $('.precio-li').show();
                $('.recomienda-precio-li').show();
                precioAgregar(stars, 'nightClubs');
            }
        }else{
            $('.precio-li').hide();
            $('.precio-raty').html('');
            $('[name=precio]').val('');

            $('.recomendacion-precio-raty').html('');
            $('[name=recomienda-precio]').val('');
            $('.recomienda-precio-li').hide();
        }

        //Volvemos a setear precio como null
        precioFlag = null;

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

    $('form[name="agregar-lugar"]').on('submit', function(e){
        errores = '';
        $('.errors').remove();
        $required = $('.required');
        $.each($required, function(){
            $this = $(this);
            if(($this.val() == $this.attr('placeholder') || $this.val() == '') || ($this.val() == 'elige')){
                errores += "<p>"+$this.attr('title')+"</p>";
                $this.addClass('input-error');
                if($this.hasClass('calle')){
                    $('.numero').after('<small class="errors">'+$this.attr('title')+'</small>');
                }else if($this.is('select') && $this.not('.secundaria')){
                    $this.next('.chzn-container').find('.chzn-single').addClass('chzn-error');
                    $this.after('<small class="errors">'+$this.attr('title')+'</small>');
                }else{
                   $this.after('<small class="errors">'+$this.attr('title')+'</small>');
                }

            }else{
                $this.removeClass('input-error');
            }
        });

        if( !($('input[name="form[numero]"]').val().match(/(\d+|s\/n)/g)) ){
            errores += "<p>Debes ingresar sólo números o \"s/n\" en el campo de Nº</p>";
            $('input[name="form[numero]"]').addClass('input-error');
            $('input[name="form[numero]"]').after('<small class="errors">Debes ingresar sólo números o "s/n" en el campo de Nº</small>')
        }

        $categoriasNoHidden = $('.categoria:visible');
        $.each($categoriasNoHidden, function(){
            $this = $(this);
            if($this.parent().find('.subcategorias ul').children(':visible').length > 0){
                if($this.parent().find('.subcategorias').find('ul > li').length > 0 && $this.parent().find('.subcategorias').find('ul > li > label').children(':checked').length == 0){
                    $this.addClass('input-error');
                    $this.parent().find('.subcategorias ul').after('<small class="errors categoria">Debes seleccionar al menos una subcategoría</small>');
                    errores += "<p>Debes seleccionar al menos una subcategoría</p>";
                }
            }
        });

        if($('input[name="precio"]').val() == '' && $('.precio').is(':visible')){
            errores += "<p>¡Debes agregar el precio!</p>";
            $('input[name="precio"]').after('<small class="errors">Seleccione un precio.</small>');
        }

        if(validMap == false){
            if($('body').hasClass('argentina')){
                errores += "<p>¡Espera! Acordate de ubicar el lugar en el mapa, ya sea cargando el mapa o arrastrando el icono a su posición.</p>";
                $('.mapa_info').before('<small class="errors">¡Espera! Acordate de ubicar el lugar en el mapa, ya sea cargando el mapa o arrastrando el icono a su posición.</small>');
            }else{
                errores += "<p>¡Espera! Acuérdate de ubicar el lugar en el mapa, ya sea cargando el mapa o arrastrando el icono a su posición.</p>";
                $('.mapa_info').before('<small class="errors">¡Espera! Acuérdate de ubicar el lugar en el mapa, ya sea cargando el mapa o arrastrando el icono a su posición.</small>');
            }
        }

        recomendacion = true;
        if($('[name="habilitar-recomienda"]').is(':checked')){
            recomendacion = validarRecomendacion();
            if(recomendacion == false){
                errores += "<p>Porfavor completa tu recomendacion.</p>";
            }
        }

        if(errores != '' || recomendacion == false){
            $('.txt_error').html(errores);
            $('body').animate({'scrollTop': 0}, 400);
            $('.mensaje_error').fadeIn();
        }else{
            $('.guardar-datos').click(function(){return false;});
            $('.placeholder').val('');
            return true;
        }
        return false;
    });

    $('.cargar_mapa').click(function(e){
        e.preventDefault();
        if(!$('#form_calle').val().match('Ej') && !$('#form_numero').val().match('Ej')){
            $.ajax({
                url: WEBROOT+'../ajax/lugarYaExiste',
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
                    }
                },
                error: function(data) {console.log(data)}
            });
        }
        geocodeAddress();
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
            if(paisSeleccionado == 'Argentina'){
                $('.codigo_area').text('+54');
            }else if(paisSeleccionado == 'Brasil'){
                $('.codigo_area').text('+55');
            }else{
                $('.codigo_area').text('+56');
            }
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

    $('.precio-raty').html('').raty({
        width: 100,
        starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
        starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
        start: precio,
        space: false,
        hintList: tipo,
        target: '.precio-detalle',
        scoreName: 'precio'
    });

    $('.recomendacion-precio-raty').html('').raty({
        width: 100,
        starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
        starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
        start: 0,
        space: false,
        hintList: tipo,
        target: '#precio-recomienda',
        scoreName: 'recomienda-precio'
    });
}