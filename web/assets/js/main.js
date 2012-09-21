oldBrowser = false, IE = '';

//@cc_on IE = navigator.appVersion;
if(/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)){var ffx=new Number(RegExp.$1);}
if(IE || ffx < 3.7){
    oldBrowser = true;

    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = WEBROOT+"../assets/js/legacy.js";
    document.getElementsByTagName('head')[0].appendChild(script);
}

var hintListEstrellas = ['¡Argh, no me gustó nada!', 'Mmm, más o menos nomás.', 'Está bien, cumple.', 'Me gusta, me gusta.', '¡Me encanta, es el mejor de todos!'];

var loogares = {
    'parametros' : {
      'ratyEstrellas' : {
          width: 160,
            starOff:  WEBROOT+'../assets/images/extras/estrella_vacia_recomendacion.png',
            starOn:   WEBROOT+'../assets/images/extras/estrella_llena_recomendacion.png',
            starHalf:   WEBROOT+'../assets/images/extras/estrella_media_recomendacion.png',
            half: true,
            readOnly: true,
            space: false
      }
    }
};



/* Función para el menú desplegable */

$(document).ready(function() {

    var getResultados = getParameterByName('resultados');

    $('.seleccionar_resultados_por_pagina').find('option[value="'+getResultados+'"]').attr('selected', 'selected')
        .end().live('change', function(){
          console.log('test');
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

   // Menú header
   $('.menu ul li:has(.cajas_menu)').hover(
      function(e)
      {
         $(this).find('.cajas_menu').css({display: "block"});
         $(this).find('a:first').addClass('li-activo');
      },

      function(e)
      {
         $(this).find('.cajas_menu').css({display: "none"});
         $(this).find('a:first').removeClass('li-activo')
      }
   );

   // Menú ciudad
   $('.menu_ciudad ul li:has(ul)').hover(
      function(e)
      {
         $(this).find('ul').css({display: "block"});
         $(this).find('a:first').addClass('li-activo');
      },

      function(e)
      {
         $(this).find('ul').css({display: "none"});
         $(this).find('a:first').removeClass('li-activo')
      }
   );

   // Menú usuario
   $('.login ul li:has(ul)').hover(
      function(e)
      {
         $(this).find('ul').css({display: "block"});
         $(this).find('a:first').addClass('li-activo');
      },

      function(e)
      {
         $(this).find('ul').css({display: "none"});
         $(this).find('a:first').removeClass('li-activo')
      }
   );

    var msie6 = $.browser == 'msie' && $.browser.version < 7;

    if($('.menu').length > 0) {
      if (!msie6) {
        var weAreDown = false;
        var top = $('.menu').offset().top - parseFloat($('.menu').css('marginTop').replace(/auto/, 0));
        $(window).scroll(function (event) {
          // what the y position of the scroll is
          var y = $(this).scrollTop();

            if(y >= top && weAreDown == false){
              $('.menu').addClass('fixed');
              weAreDown = true;
              $('.logo_ciudad').animate({'margin-top': '-80px'}, 300, function(){
                  $('.logo_ciudad_menu').fadeIn()
              });
            }else if(weAreDown == true && top >= y){
              $('.menu').removeClass('fixed');
              weAreDown = false;
              $('.logo_ciudad').animate({'margin-top': '0px'}, 500, function(){
                    $('.logo_ciudad_menu').fadeOut()
              });
            }

        });
      }
    }
});

function getTipo(tipo){
    if($('body').hasClass('chile')){
      if(tipo == 'dondeComer'){
          return ['Menos de $3.000', '$3.000 - $7.000', '$7.000 - $12.000', '$12.000 - $18.000', 'Más de $18.000'];
      }else if(tipo == 'dondeDormir'){
          return ['Mínimo', 'Barato', 'Medio', 'Alto', 'Máximo'];
      }else if(tipo == 'nightclub'){
        return ['Menos de $10.000', '$10.000 - $20.000', '$20.000 - $30.000', '$30.000 - $50.000', 'Más de $50.000'];
      }
    }else if($('body').hasClass('argentina')){
      if(tipo == 'dondeComer'){
          return ['Menos de $30', '$30 - $70', '$70 - $100', '$100 - $160', 'Más de $160'];
      }else if(tipo == 'dondeDormir'){
          return ['Mínimo', 'Barato', 'Medio', 'Alto', 'Máximo'];
      }else if(tipo == 'nightclub'){
          return ['Menos de $100', '$100 - $200', '$200 - $300', '$300 - $500', 'Más de $500'];
      }
    }else if($('body').hasClass('brasil')){
      if(tipo == 'dondeComer'){
          return ['Menos de R$26', 'R$26 - R$70', 'R$70 - R$100', 'R$100 - R$160', 'Más de R$160'];
      }else if(tipo == 'dondeDormir'){
          return ['Mínimo', 'Barato', 'Medio', 'Alto', 'Máximo'];
      }else if(tipo == 'nightclub'){
          return ['Menos de R$100', 'R$100 - R$200', 'R$200 - R$300', 'R$300 - R$500', 'Más de R$500'];
      }
    }
}