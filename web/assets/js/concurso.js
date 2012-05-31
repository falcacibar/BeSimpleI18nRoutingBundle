$(function(){
    $('.boton_registro').fancybox({
        hideOnContentClick : false,
        padding: 0,
        type: 'ajax',
        showCloseButton: false
    });

    $('.boton_participar').fancybox({
        hideOnContentClick : false,
        padding: 0,
        type: 'ajax',
        showCloseButton: false
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
                    $('.boton_participar').replaceWith("<p>¡Participando!</p>");
                    // Actualizamos los participantes
                    $.ajax({
                        url: WEBROOT+'../ajax/actualizar_participantes',
                        type: 'post',
                        data: dataObj,
                        success: function(data){
                            $('.usuarios').html($(data).fadeIn('fast'));
                        }
                    });
                }
                else {
                    e.preventDefault();
                }               
            }
        });
    });
})