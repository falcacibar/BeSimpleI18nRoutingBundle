$(document).ready(function(){
	var rebindEvents = function(){

		if($('.resultados_wrapper').height() > 2000){
			var $sidebar   = $(".sidebar_busqueda"),
			    $window    = $(window),
			    topPadding = 60,
				sideBarOffset     = $sidebar.offset(),
				compensation = ($('.mensaje_exito').length > 0)?69:0;
	    	

		    $window.scroll(function() {
		    	margin = $(document).height() - 1541 - compensation;

		    	if($window.scrollTop() >= sideBarOffset.top && margin + 400 >= $window.scrollTop()){
				    $sidebar.stop().animate({
			            top: $window.scrollTop() - sideBarOffset.top + topPadding
			        });
		        }else if(margin - 200 <= $window.scrollTop()){
				    $sidebar.stop().animate({
			            top: margin
			        });
		        }else{
				    $sidebar.stop().animate({
			            top: 0
			        });
		        }
		    });
	    }
		var getCaracteristicas = getParameterByName('caracteristicas').split(',');

		$.each(getCaracteristicas, function(i){
			$('.qtip_caracteristicas input[value="'+getCaracteristicas[i]+'"]').click();
		});

		$('.resultado-busqueda-stars-raty').each(function(){
			var estrellas = $(this).attr('data-stars');
			$(this).raty({
				width: 160,
			    starOff:  WEBROOT+'../assets/images/extras/estrella_vacia_recomendacion.png',
			    starOn:   WEBROOT+'../assets/images/extras/estrella_llena_recomendacion.png',
			    starHalf:   WEBROOT+'../assets/images/extras/estrella_media_recomendacion.png',
			    half: true,
			    start: estrellas,
			    readOnly: true,
			    space: false
			});
		});

		$('.resultado-busqueda-precio-raty').each(function(){
			var precio = $(this).attr('data-precio');
			$(this).raty({
				width: 140,
		        starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
		        starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
			    half: true,
			    start: precio,
			    readOnly: true,
			    space: false
			});
		});
		 
		$('.filtro_precios_raty').each(function(){
			var precio = $(this).attr('data-precio');
			$(this).raty({
				width: 140,
		        starOff:  WEBROOT+'../assets/images/extras/precio_vacio.png',
		        starOn:   WEBROOT+'../assets/images/extras/precio_lleno.png',
			    half: true,
			    start: precio,
			    readOnly: true,
			    space: false
			});
			$(this).parent().prev('input').show().css('display', 'inline');
		});

		var precio = getTipo('dondeComer');
		var	qTipPrecio = '<div class="qtip_precio">';

		for(i=precio.length -1; i >= 0; i--){
			qTipPrecio += "<p><span class='signo_precio'>";

			for(j=precio.length;i<j;j--){qTipPrecio += "$";}

			qTipPrecio += "</span> = "+precio[i]+"</p>";	
		}
		qTipPrecio += "</div>";

		$('.explicacion_precio').qtip({
		   content: {
		      text: 'Corresponde al valor del consumo promedio apróximado por persona según las características del lugar.<br/><br/>'+qTipPrecio
		   },
		   style: {
		      classes: 'ui-tooltip-precio',
		      tip: {
			   	border: 0,
			   	width: 12,
			   	color: '#f0f'
			   }
		   },
		   position: {
			my: 'top center', 
			at: 'bottom center'
		   }
		});

		$('.filtros_subcategorias .filtros_expandir').qtip({
			content: {
				text: $('.qtip_subcategorias')
			},
			style: {
				classes: 'ui-tooltip-filtros',
				tip: {
					border: 0,
					width: 12,
					color: '#f0f',
					corner: true,
					offset: 378
				}
			},
			position: {
				my: 'top center', 
				at: 'bottom left',
				adjust: {
					x: 50
				}
			},
			show: {
		      event: 'click',
		      solo: true
		    },
		    hide:{
		    	event: 'click'
		    }
		});

		$('.filtros_zonas .filtros_expandir').qtip({
			content: {
				text: $('.qtip_zonas')
			},
			style: {
				classes: 'ui-tooltip-filtros',
				tip: {
					border: 0,
					width: 12,
					color: '#f0f',
					corner: true,
					offset: 19
				}
			},
			position: {
				my: 'top center', 
				at: 'bottom left',
				adjust: {
					x: 50
				}
			},
			show: {
		      event: 'click',
		      solo: true
		    },
		    hide:{
		    	event: 'click'
		    }
		});

		$('.filtros_categorias .filtros_expandir').qtip({
			content: {
				text: $('.qtip_categorias')
			},
			style: {
				classes: 'ui-tooltip-filtros',
				tip: {
					border: 0,
					width: 12,
					color: '#f0f',
					corner: true,
					offset: 709
				}
			},
			position: {
				my: 'top center', 
				at: 'bottom left',
				adjust: {
					x: 50
				}
			},
			show: {
		      event: 'click',
		      solo: true
		    },
		    hide:{
		    	event: 'click'
		    }
		});

		$('.filtros_caracteristicas .filtros_expandir').qtip({
			content: {
				text: $('.qtip_caracteristicas')
			},
			style: {
				classes: 'ui-tooltip-filtros',
				tip: {
					border: 0,
					width: 12,
					color: '#f0f',
					corner: true,
					offset: 704
				}
			},
			position: {
				my: 'top center', 
				at: 'bottom left',
				adjust: {
					x: 50
				}
			},
			show: {
		      event: 'click',
		      solo: true
		    },
		    hide:{
		    	event: 'click'
		    }
		});
	}

	rebindEvents();

	$('[name=precio]').live('change', function(){
		$this = $(this);
		if($this.is(':checked')){
			$this.next('a').click();
		}else{
			$('.reset_precio').click();
		}
	});

	$('.link_filtrar_caracteristicas').live('click', function(e){
		e.preventDefault();

		var $this = $(this),
			caracteristica = '',
			href = $this.data('ohref');

		$('.qtip_caracteristicas input[type=checkbox]:checked').each(function(){
			caracteristica += $(this).val() + ",";
		});
		
		href += "&caracteristicas=" +  caracteristica.substring(0, caracteristica.length-1);
		$this.attr('href', href);
	});

	if(oldBrowser == false){
		$('.filtros a, .qtip_filtros a').click(function(e){e.preventDefault();}).pjax({
			url: $(this).attr('href'),
			container: '.resultados_wrapper',
			fragment: '.resultados_wrapper',
			timeout: 20000,
			beforeSend: function(){
				$('.filtros_expandir').qtip('toggle', false)
				$('.resultados_wrapper').append("<div class='overlay'><div class='loader'>Cargando Lugares</div></div>").fadeIn(300);
		 		$('.resultados_wrapper').append("<img class='loader' src='"+WEBROOT+"../assets/images/extras/loader.gif'>");
			},
			success: function(data){
				$('.overlay').fadeOut(0, function(){
					$(this).remove(); 
				});
				$('.resultados_wrapper').html(data).fadeIn(300, function(){
					$('.qtip').remove();
					rebindEvents();
					$.getScript(WEBROOT+'../assets/js/googleMapBuscar.js');
				});
			}
		});
	}

	$('.close_qtip').live('click',function(){
		var rel = $(this).parent().attr('rel');
		$('.'+rel+' .filtros_expandir').qtip('toggle', false);
	});
});