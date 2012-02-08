var hintListEstrellas = ['¡Argh, no me gustó nada!', 'Mmm, más o menos nomás.', 'Está bien, cumple.', 'Me gusta, me gusta.', '¡Me encanta, es el mejor de todos!'];

/* Función para el menú desplegable */

$(document).ready(function() {
    var getResultados = getParameterByName('resultados');
    
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

   // Menú header
   $('.menu ul li:has(ul)').hover(
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

  $('.logo_ciudad_menu').css('position', 'absolute').css('display', 'none')
  $('.menu_categorias').css('padding-left', '131px')
    var msie6 = $.browser == 'msie' && $.browser.version < 7;

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


});

function getTipo(tipo){
    if(tipo == 'dondeComer'){
        return ['Menos de $3.000', '$3.000 - $7.000', '$7.000 - $12.000', '$12.000 - $18.000', 'Mas de $18.000'];
    }else if(tipo == 'dondeDormir'){
        return ['Minimo', 'Barato', 'Medio', 'Alto', 'Maximo'];
    }
}