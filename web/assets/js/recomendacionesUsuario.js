$(document).ready(function(){
    $('.editar_lugar_recomendacion').click(function(e){
        e.preventDefault();
        $recomendacionBloque = $(this).closest('.caja_recomendaciones_lugar_usuario').find('.textos_recomendacion_lugar_usuario');
        $.ajax({
            type:'post',
            data: {'slug': $(this).data('lugar')},
            url: WEBROOT+'../ajax/recomendacion',
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
});