$(function(){
    $('.estrellas_recDia, .estrellas_actividad').each(function(i){
		var estrellas = $(this).attr('data-stars');
		estrellasRecomendacion(estrellas, $(this));
	});

    $('.actividad_extendida form select').change(function(){
        $(this).parent().submit();
    });

    $('.boton_fb').parent().click(function(e){
        e.preventDefault();
        $('.fb_hidden_button').find('.fb_button').click();
    });

    $('.tips_icon').qtip({
       content: {
          text: $('.qtip_recomendacion_del_dia')
       },
        style: {
            classes: 'ui-tooltip-precio',
            tip: {
                border: 0,
                width: 12,
                color: '#f0f',
                corner: true,
                offset: 85
            }
        },
        position: {
            my: 'top center', 
            at: 'bottom left',
            adjust: {
                x: 6,
                y: 6
            }
        }
    });

    $('.slider li').css('visibility', 'visible')

    var slider = $('.slider').bxSlider({
        auto: true,
        controls: false,
        onBeforeSlide: function(currentSlideNumber, totalSlideQty, currentSlideHtmlObject){
            $('.active').removeClass('active');
            $('.slide-change').eq(currentSlideNumber).addClass('active');
        }
    });

    $('.slide-change').click(function(e){
        e.preventDefault();
        $this = $(this);
        $('.active').removeClass('active');
        slider.goToSlide($this.data('slide'));
        $this.addClass('active');
    });

    $('.concursos li').css('visibility', 'visible');



    var concursoSlider = $('.concursos').bxSlider({
        displaySlideQty: $('.concursos li').length,
        moveSlideQty: 1,
        controls: false,
        auto: true
    });


    $('.prev_concurso').click(function(){
        concursoSlider.goToPreviousSlide();
        return false;
    });

    $('.next_concurso').click(function(){
        concursoSlider.goToNextSlide();
        return false;
    });
});	

function estrellasRecomendacion(estrellas, $this){
    $this.raty({
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