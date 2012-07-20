// Presets y otros datos
var mapa = {
    'capas'         : {} ,
    'controles'     : {} ,
    'olMmapa'       : null ,
    'features'      : {} ,
    'lonlat'        : {} ,
    'util'          : {} ,
    'conf'          : {
        'compartida'    : {}
    } ,
    'parametros'    : {
        'imagenLoogarPorDefecto'    : WEBROOT + '../assets/media/cache/small_lugar/assets/images/lugares/default.gif',
        'imagenLoogarThumbDir'      : WEBROOT + '../assets/media/cache/small_lugar/assets/images/lugares/',
        'zoomInicial'               : 6
    } ,
    'estilos'       : {
        'marcadorBase'          : {
            'pointRadius'       : 16 ,
            'backgroundGraphic' : "http://www.google.com/mapfiles/shadow50.png",
            'backgroundXOffset' : -7 ,
            'backgroundYOffset' : -15,
            'graphicZIndex'     : 11 ,//MARKER_Z_INDEX,
            'backgroundGraphicZIndex' : 10 // SHADOW_Z_INDEX
        }
    } ,
    'proyecciones' : {
        'shpericalMercator' : new OpenLayers.Projection('EPSG:900913') ,
        'geographicWGS84'   : new OpenLayers.Projection('EPSG:4326')
    }
};

// Alias de proyeccioens
mapa.proyecciones.cliente   = mapa.proyecciones.shpericalMercator;
mapa.proyecciones.servidor  = mapa.proyecciones.geographicWGS84;

// Functiones Popup
mapa.util.popupLonLat = function(feature) {
    var lonlat = feature.origLonLat.clone();
    lonlat.lon += mapa.olMapa.getResolution() * 15;
    return lonlat;
}

mapa.util.loogarInfoParser = function(feature, selector) {
    var HTMLCategorias = '';
    var categoria;

    for(var i=0;i<feature.attributes.categorias.length; i++) {
        categoria       = feature.attributes.categorias[i];
        HTMLCategorias  += '<a href="' + categoria.url + '">' + categoria.nombre + '</a>';

        if((i + 1 ) < feature.attributes.categorias.length) HTMLCategorias  += ' , ';
    }

    if(feature.attributes.imagen)  {
        var imagen = mapa.parametros.imagenLoogarThumbDir + feature.attributes.imagen;
    } else {
        var imagen = mapa.parametros.imagenLoogarPorDefecto;
    }


    return $(selector)
        .text()
            .replace(/URLNS/, feature.attributes.slug)
            .replace(/\(\(% imagen %\)\)/g, imagen)
            .replace(/\(\(% categorias %\)\)/g, HTMLCategorias)
            .replace(/\(\(%(.*?)%\)\)/g, function(tag, variable) {
                var temprop = feature.attributes;
                var props   = $.trim(variable).split(/\./gm);

                while(prop = props.shift()) {
                    temprop = temprop[prop];
                }

                return temprop;
                // return eval('feature.properties.'+$.trim(variable)+';');
        })
}

$(function() {
    // Conf compartida Google Maps
    mapa.conf.compartida.olCapaGMap = {
            "mumZoomLevel" : 22 ,
            "minZoomLevel" : 11 ,
            "sphericalMercator" : true
    };

    // Mapa
    mapa.olMapa = new OpenLayers.Map({
            "div"       : "mapa" ,
            "units"     : 'm' ,
            "projection"    : mapa.proyecciones.cliente
    });

    // instancias mapas
/**
    mapa.capas.OSM              = new OpenLayers.Layer.OSM(
        "OpenStreetMap", null, $.extend(true, {
                "isBaseLayer" : true
            }, mapa.olCapaConfCompartida)
    );
/**/

    // Instancias mapas Google Mapse
    mapa.capas.GMapStreets      = new OpenLayers.Layer.Google(
        "Google Streets",
        $.extend(true, {},
            mapa.conf.compartida.olCapaGMap ,
            { "isBaseLayer"       : true }
        )
    );
/**/
    mapa.capas.GMapHybrid      = new OpenLayers.Layer.Google(
        "Google Hybrid",
        $.extend(true, {} ,
            mapa.conf.compartida.olCapaGMap , {
                "isBaseLayer"       : true ,
                "type" : google.maps.MapTypeId.HYBRID ,
        })
    );
/**/
    mapa.capas.Loogares           = new OpenLayers.Layer.Vector(
        'Loogares.com', {
            'strategies'    : [ new OpenLayers.Strategy.BBOX({
                        'resFactor' : 1,
                        'ratio'     : 1,
                        'update'    : function() {
                            var $listaOtrosLugares          = $('#lista-otros-lugares').empty();
                            var $cargandoOtrosLugares       = $('#cargando-otros-lugares').show();

                            $listaOtrosLugares.append($cargandoOtrosLugares);

                            delete $listaOtrosLugares, $cargandoOtrosLugares;

                            return OpenLayers.Strategy.BBOX.prototype.update.call(this, $.makeArray(arguments));
                        }
                    })
            ],
            'protocol'      : new OpenLayers.Protocol.HTTP({
                'params'        : {
                    'id'            : mapa.features.lugarActual.attributes.num ,
                    'scale'         :{'toString':new Function("return mapa.olMapa.getScale();")}
                } ,
                'url'           : WEBROOT+'../ajax/geo/lugares',
                'format'        : new OpenLayers.Format.GeoJSON({
                    'read'                 : function() {
                        var results     = OpenLayers.Format.GeoJSON.prototype.read.apply(
                                            this
                                            , $.makeArray(arguments)
                        );

                        var lugarActual         = mapa.features.lugarActual.clone();
                        lugarActual.geometry    = mapa.features.lugarActual.geometry.clone();

                        var $listaOtrosLugares        = $('#lista-otros-lugares');

                        $.each(results, function() {
                            var self = this;
                            var $entrada = $(mapa.util.loogarInfoParser(self, '#plant-lista-otros-lugares'));

                            $entrada
                                .data('feature', this)
                                .hover(function() {
                                    mapa.controles.seleccion.highlight($(this).data('feature'));
                                }, function() {
                                    mapa.controles.seleccion.unhighlight($(this).data('feature'));
                                });

                            $listaOtrosLugares.append($entrada);

                            delete $entrada;
                        })

                        var $raty = $listaOtrosLugares.find('.resultado-busqueda-stars-raty').each(function() {
                            var $this = $(this);
                            $this.raty($.extend(
                                        true, {} ,
                                        loogares.parametros.ratyEstrellas ,
                                        { 'start' : $this.attr('data-stars') }
                            ));

                            delete $this;
                        });

                        var $cargandoOtrosLugares       = $('#cargando-otros-lugares').detach().hide();
                        var $otrosLugares               = $('#otros-lugares').append($cargandoOtrosLugares);

                        results.push(lugarActual);

                        delete $raty, $listaOtrosLugares, $cargandoOtrosLugares;

                        return results;
                    },
                    'internalProjection'   : OpenLayers.Projection(mapa.proyecciones.cliente.getCode()),
                    'externalProjection'   : OpenLayers.Projection(mapa.proyecciones.servidor.getCode())
                })
            }) ,
            'styleMap'      : new OpenLayers.StyleMap( {
                    'default' : $.extend( true, {}, mapa.estilos.marcadorBase),
                    'select'  : {
                        'externalGraphic'   : WEBROOT+'../assets/images/gmaps/puntoseleccionado.png' ,
                        'graphicZIndex'     : 12
                    }
            }) ,
            'rendererOptions'   : {'yOrdering':true, 'zOrdering': true} ,
            'renderers'         : 'SVG,VML'.split(/,/) ,
            'projection'        : mapa.proyecciones.servidor
        }
    );

    //Agregar capas base
    mapa.olMapa.addLayers([
        mapa.capas.GMapStreets ,
        mapa.capas.GMapHybrid ,
        /** mapa.capas.OSM ,  /**/
    ]);

    // Zoom y posicion inicial.
    mapa.olMapa.zoomTo(mapa.parametros.zoomInicial);
    mapa.olMapa.panTo(mapa.lonlat.lugarActual);

    // Agregar capas vectores
    mapa.olMapa.addLayers([
            mapa.capas.Loogares
    ]);

    // Regla estilo lugar actual
    mapa.capas.Loogares.styleMap.addUniqueValueRules("default", "actual", {
        "t" : {'externalGraphic' : WEBROOT+'../assets/images/gmaps/puntodestacado.png'},
        "f" : {'externalGraphic' : WEBROOT+'../assets/images/gmaps/categoria${tipo}.png'}
    });

    // Control Popup
    mapa.olMapa.addControl(
        mapa.controles.seleccion = new OpenLayers.Control.SelectFeature(
            mapa.capas.Loogares, {
                'clickout'  : true,
                'multiple'  : false,
                'hover'     : true,
                'box'       : false,
                'onBeforeSelect'    : function(feature) {
                    if(typeof(feature.origLonLat) === 'undefined') {
                        feature.origLonLat  = feature.geometry.getBounds().getCenterLonLat();
                    }

                    if(typeof(feature.popupHTML) === 'undefined') {
                        feature.popupHTML   = mapa.util.loogarInfoParser(feature, '#plant-popup-mapa');
                    }

                    if(feature.popup === null) {
                        feature.popup = new OpenLayers.Popup.Anchored(
                                "LoogarInfoMapa" ,
                                mapa.util.popupLonLat(feature) ,
                                new OpenLayers.Size(310, 100),
                                feature.popupHTML
                        );
                    } else {
                        feature.popup.lonlat = mapa.util.popupLonLat(feature);
                    }

                    setTimeout(function()  {
                        feature.popup.div.className = 'olPopup lugar_data';
                        var $div = $(feature.popup.div).css({
                                'display'   : 'block',
                                'width'     : '',
                                'height'    : ''
                        });

                        var $raty = $div.find('.resultado-busqueda-stars-raty');
                        $raty.raty($.extend(
                            true, {} ,
                            loogares.parametros.ratyEstrellas ,
                            { 'start' : $raty.attr('data-stars') }
                        ));

                        var rels = $div.find('> div').add($div.find('> div > div'));
                        rels.css({
                                    'overflow'  : 'visible',
                                    'height'    : ''
                        });
                    }, 1);

                    $('.olMapViewport').css('overflow', 'visible');
                    mapa.olMapa.addPopup(feature.popup);

                    return false;
                },
                'onUnselect'    : function(feature) {
                    var popups = mapa.olMapa.popups;

                    $('.olMapViewport').css('overflow', 'hidden');
                    for(i=0;i<popups.length;i++) {
                        mapa.olMapa.removePopup(popups[i]);
                    }
                }
            }
        )
    );

    mapa.controles.seleccion.activate();


    mapa.controles.panel = new OpenLayers.Control.Panel(null, {'autoActivate'  : true});

    if(typeof(mapa.lonlat.lugarActual) != 'undefined') {
        var $botonLugarActual   = $('#mapa-botones .boton_mapa_ubicarlugar').clone().click(function(evt) {
                mapa.olMapa.panTo(mapa.lonlat.lugarActual);
                evt.stopInmediatePropagation();
                return false;
        });

        var $viewport           = $('#mapa div.olMapViewport').append($botonLugarActual);

        delete $viewport, $botonLugarActual;
    }


    mapa.olMapa.addControl(mapa.controles.panel);

    // Controles Adicionales
    mapa.olMapa.addControls([
                // new OpenLayers.Control.LayerSwitcher(),
                // new OpenLayers.Control.Permalink,
                new OpenLayers.Control.ScaleLine({geodesic: true}),
    ])
});