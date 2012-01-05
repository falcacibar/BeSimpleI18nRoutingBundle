function getParameterByName(name)
{
  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.href);
  if(results == null)
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

$(document).ready(function(){
    var getResultados = getParameterByName('resultados');
    $('.seleccionar_resultados_por_pagina').find('option[value="'+getResultados+'"]').attr('selected', 'selected')
        .end().change(function(){
            if( getResultados == ''){
                if(window.location.href.match(/\?/)){
                    window.location = window.location.href+'&resultados='+$(this).val(); 
                }else{
                    window.location = window.location.href+'?resultados='+$(this).val();  
                }
            }else{
                window.location = window.location.href.replace(/resultados=\d+/, 'resultados='+$(this).val());
            }
        });

    $('.star-raty').raty({
        width: 140,
        half: true,
        readOnly: true,
        starOff:  WEBROOT+'../assets/images/extras/estrella_vacia.png',
        starOn:   WEBROOT+'../assets/images/extras/estrella_llena.png',
        starHalf:   WEBROOT+'../assets/images/extras/estrella_media.png',
        scoreName: 'form[precio]'
    });
});