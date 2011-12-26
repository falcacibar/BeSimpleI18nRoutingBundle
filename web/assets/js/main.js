/* Función para el menú desplegable */

$(document).ready(function() {

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

});