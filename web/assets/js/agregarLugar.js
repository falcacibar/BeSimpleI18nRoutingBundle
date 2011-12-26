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

$('.display-more-info').click(function(e){
    e.preventDefault();
    
    var $this = $(this),
        rel = $this.attr('rel');
        count = parseInt($this.find('span').text()) - 1;
        if(count == 0){
            $this.parent().parent().fadeOut();
        }

        if(count >= 0){
            $('.'+rel+':not(:hidden):last').parent().next('li').fadeIn().css('display', 'block');
            $this.find('span').text(count);
        }
});


$('.categoria').live('change', function(){
    $this = $(this);
    var categoria = $(this).val().camelCase(),
        anterior = selected[$this.attr('id')];

    //Deshabilitar en los demas dropdown
    $('.categoria').not($this).each(function(){
        //Deshabilitamos en el resto de los dropdowns el valor que seleccionamos recien
        $(this).find('option[value="'+$this.val()+'"]').attr('disabled', 'disabled').addClass(categoria);
        
        //Habilitamos el valor anterior
        $(this).find('option[value="'+selected[$this.attr('id')]+'"]').removeAttr('disabled');
    });

    $('.caracteristicas').parent().fadeOut();

    $('.categoria').not(':hidden').each(function(){
        var thisCat = $(this).val().camelCase(),
            caracteristicas = categorias[thisCat].caracteristicas;
            subCategorias = categorias[thisCat].subCategorias;

        if(caracteristicas != ''){
            $('.caracteristicas').parent().fadeIn().css('display', 'block');
        }

        if(subCategorias != ''){
            $this.parent().find('.subcategorias').stop(true,true).fadeIn().css('display', 'inline-block');
        }

        $.each(caracteristicas, function(i){
            $('input[value="'+caracteristicas[i]+'"]').parent().parent().fadeIn();
            $('.caracteristicas').parent().fadeIn();
        });

        if( anterior != undefined ){
            //Por cada categoria que deshabilitamos
            habilitar = categorias[anterior.camelCase()].deshabilitar;
            $.each(habilitar, function(i){
                $('.categoria').not($this).each(function(){
                    $aHabilitar = $(this).find('option[value="'+habilitar[i]+'"]');
                    //Por cada categoria, reviamos si otra Categoria la deshabilito, comprobamos si la clase que la deshabilito
                    //Es la ultima, si es, la habilitamos, si no, permanece deshabilitado
                    
                    if($aHabilitar.checkDefaultClass(anterior)){
                        //Enable it
                       $aHabilitar.removeAttr('disabled');
                    }
                });
            }); //End Habilitar

            //Sacamos los camposo especiales.
            camposEspeciales = categorias[anterior.camelCase()].camposEspeciales
            $.each(camposEspeciales, function(i){
                $('#form_'+camposEspeciales[i]).parent().fadeOut().css('display', 'block');
            });

            //Subcategorias
        }//end anterior
    });// end each

    $this.parent().find('.subcategorias ul li').fadeOut().find('input:checked').click();

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
    });
    
    //Seteamos el valor previo correspondiente al ID del dropdown que cambiamos
    selected[$this.attr('id')] = $this.val();
});

$('.jornada-doble').click(function(){
    if($(this).is(':checked')){
        $(this).parent().parent().parent().find('.horario-pm').fadeIn();
        $('table').find('th.horario-pm').fadeIn();
    }else{
        $(this).parent().parent().parent().find('.horario-pm').fadeOut(function(){
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

$('form').submit(function(e){
    errores = '';
    $('.errors').remove();
    $.each($('.required:not(:hidden)'), function(){
        if(($(this).val() == $(this).attr('placeholder') || $(this).val() == '') || ($(this).val() == 'elige')){
            errores += "<p>"+$(this).attr('title')+"</p>";
            $(this).addClass('input-error');
            if($(this).hasClass('calle')){
                $(this).next('input').after('<small class="errors">'+$(this).attr('title')+'</small>');
            }else{
               $(this).after('<small class="errors">'+$(this).attr('title')+'</small>'); 
            }
            
        }else{
            $(this).removeClass('input-error');
        }
    });

    if( !($('input[name="form[numero]"]').val().match(/(\d+|s\/n)/g)) ){
        errores += "<p>Ingrese solo numeros o s/n en el numero del Lugar</p>";
        $('input[name="form[numero]"]').addClass('input-error');
        $('input[name="form[numero]"]').after('<small class="errors">Ingrese solo numeros o s/n en el numero del Lugar</small>')
    }

    $.each($('.categoria:not(:hidden)'), function(){
        $this = $(this);
        if($this.parent().find('.subcategorias ul').children().length > 0){
            if($this.parent().find('.subcategorias').find('ul > li').length > 0 && $this.parent().find('.subcategorias').find('ul > li > label').children(':checked').length == 0){
                $this.addClass('input-error');
                $this.parent().find('.subcategorias ul').after('<small class="errors categoria">Seleccione al menos una Subcategoria</small>');
                errores += "<p>Seleccione al menos una Subcategoria por Categoria</p>";
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
        errores += "<p>Cargue la direccion en el mapa, o arrastre el cursor donde esta el local</p>";
        $('.mapa_info').before('<small class="errors">Carga una direccion o arrastra el icono en el mapa.</small>');
    }

    $('.errores-container').html(errores);

    if(errores != ''){
        $('body').animate({'scrollTop': 0}, 400);
        $('.errores-container').fadeIn();
    }else{
        $('.placeholder').val('');
        return true;
    }
    return false;
});

$('.cargar-mapa').click(function(e){e.preventDefault();onCargarMapaAgregar();});

/* Fncs */

String.prototype.camelCase = function() {
    str = this;
    return str
        .replace(/[\s\-](.)/g, function($1) { return $1.toUpperCase(); })
        .replace(/[\s\-]/g, '')
        .replace(/^(.)/, function($1) { return $1.toLowerCase(); });
}

function actualizarComunas(){
    var ciudadSeleccionada = $('.ciudad > option:selected').text();
    $.each(optGroupComunas, function(i){
        if(optGroupComunas[i].match(ciudadSeleccionada)){
            $('.comuna > optgroup').remove('optgroup');
            $('.comuna').append(optGroupComunas[i]);
        }
    });

    $.each(optGroupSectores, function(i){
        if(optGroupSectores[i].match(ciudadSeleccionada)){
            $('.sector > optgroup').remove('optgroup');
            $('.sector').append(optGroupSectores[i]);
        }
    });
}

/* INITIALIZATION */

var optGroupComunas = [],
    optGroupSectores = [],
    selected = {},
    caracteristicasASacar = [];

    $(function(){

$('input[type=text], textarea').each(function(){ $(this).placeholder() });

$('.comuna > optgroup').each(function(i){
    optGroupComunas[i] = $(this).outerHTML();
    $(this).remove();
});

$('.sector > optgroup').each(function(i){
    optGroupSectores[i] = $(this).outerHTML();
    $(this).remove();
});

actualizarComunas();
})