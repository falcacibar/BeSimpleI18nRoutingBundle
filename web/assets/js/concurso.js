$(function(){
    $('.ver_todos').fancybox({
        hideOnContentClick : false,
        padding: 0,
        type: 'ajax',
    });

    $('.boton_registro').fancybox({
        hideOnContentClick : false,
        padding: 0,
        type: 'ajax',
        closeBtn: false
    });

    $('.boton_participar').fancybox({
        hideOnContentClick : false,
        padding: 0,
        type: 'ajax',
        modal: true,
        closeBtn: false
    });

    $('.boton_participar').click(function(e){
        var $this = $(this);
        var dataObj = {'concurso' :  $('.concurso_boton').attr('data-id') }
        $.ajax({
            url: WEBROOT+'../ajax/participar',
            type: 'post',
            data: dataObj,
            dataType: 'json',
            success: function(data){
                if(data.status == 'ok') {
                    if($this.hasClass('boton_participar_click')) {
                        $this.replaceWith("<div class='boton_participando boton_participando_click'></div>");
                    }
                    else if($this.hasClass('boton_participar_recomendar')) {
                        $this.replaceWith("<div class='boton_participando boton_esperando_recomendar'></div>");
                    }
                    // Actualizamos los participantes
                    $.ajax({
                        url: WEBROOT+'../ajax/actualizar_participantes',
                        type: 'post',
                        data: dataObj,
                        success: function(data){
                            $('.participantes_container').html(data);
                            actualizarQtip();
                        }
                    });
                }
                else {
                    e.preventDefault();
                }
            }
        });
    });

    $('.concursos li').css('visibility', 'visible')

    var concursoSlider = $('.concursos').bxSlider({
        displaySlideQty: $('.concursos li').length,
        moveSlideQty: 1,
        controls: false,
        auto: moveConcursos
    });

    $('.prev_concurso').click(function(){
        concursoSlider.goToPreviousSlide();
        return false;
    });

    $('.next_concurso').click(function(){
        concursoSlider.goToNextSlide();
        return false;
    });

    actualizarQtip();
})

function actualizarQtip() {
    $('.participante_detalle').each(function(){
        $(this).qtip({
            content: {
              text: $(this).find('.nombre_usuario')
            },
            style: {
                classes: 'ui-tooltip-precio',
                tip: {
                    border: 0,
                    width: 12,
                    color: '#f0f',
                    corner: true,
                    offset: 0
                }
            },
            position: {
                my: 'bottom center',
                at: 'top center',
                adjust: {
                    x: 0,
                    y: -2
                }
            }
        });
    });
}

function checkEstadoConcurso(actual, termino) {
    var cerrado = $('.boton_cerrado'),
        participar = $('.boton_participar'),
        registro = $('.boton_registro'),
        participando = $('.boton_participando');
    if(cerrado.length == 0 && actual > termino) {
        if(participar.length > 0) {
            participar.replaceWith("<div class='boton_cerrado'></div>");
        }
        else if(registro.length > 0) {
            registro.replaceWith("<div class='boton_cerrado'></div>");
        }
        else {
            participando.replaceWith("<div class='boton_cerrado'></div>");
        }
    }
}