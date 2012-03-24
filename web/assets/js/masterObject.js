var categorias = {
    elige: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: []
    },
// Qué Visitar
    arquitectura: {
       camposEspeciales: ['profesional', 'agno_construccion', 'materiales'],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: ['arte-urbano-graffiti', 'atractivos-turisticos', 'monumentos-esculturas', 'parques', 'plazas']
    },
    arteUrbanoGraffiti: {
       camposEspeciales: ['profesional', 'agno_construccion', 'materiales'],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: ['arquitectura', 'atractivos-turisticos', 'monumentos-esculturas', 'parques', 'plazas']
    },
    ascensores: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: []
    },
    atractivosTuristicos: {
       camposEspeciales: ['descripcion'],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: []
    },
    bibliotecas: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Wifi'],
       deshabilitar: []
    },
    caletasDePescadores: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: []
    },
    centrosCulturales: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Wifi'],
       deshabilitar: []
    },
    galeriasDeArte: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Wifi'],
       deshabilitar: []
    },
    monumentosEsculturas: {
       camposEspeciales: ['profesional', 'angno_construccion', 'materiales'],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: ['arquitectura', 'arte-urbano-graffiti', 'atractivos-turisticos', 'parques', 'plazas']
    },
    museos: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Wifi'],
       deshabilitar: []
    },
    parques: {
       camposEspeciales: ['profesional', 'agno_construccion'],
       subCategorias: [],
       caracteristicas: ['Estacionamiento Autos', 'Estacionamiento Bicicletas'],
       deshabilitar: ['arquitectura', 'arte-urbano-graffiti', 'atractivos-turisticos', 'monumentos-esculturas', 'plazas']
    },
    paseosMiradores: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: []
    },
    playas: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Estacionamiento Bicicletas'],
       deshabilitar: []
    },
    plazas: {
       camposEspeciales: ['profesional', 'agno_construccion'],
       subCategorias: [],
       caracteristicas: ['Estacionamiento Bicicletas'],
       deshabilitar: ['arquitectura', 'arte-urbano-graffiti', 'atractivos-turisticos', 'monumentos-esculturas', 'parques']
    },
// Dónde Comer
	baresPubs: {
	   camposEspeciales: [],
	   subCategorias: ['Bares / Pubs', 'Bares con Transmisión Deportiva', 'Karaoke', 'Cervecerías', 'Pubs Irlandeses / Ingleses', 'Wine Bar', 'Hookah Bar'],
     caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'Happy Hour', 'No Fumadores', 'Pago con Cheque de Restaurante', 'Redcompra', 'Tarjeta de Crédito', 'Terraza', 'Wifi'],
	   deshabilitar: ['apart-hotel', 'bed-breakfast', 'hostales', 'hoteles', 'hoteles-boutique', 'moteles', 'residenciales']
	},
	cafesTeterias: {
	   camposEspeciales: [],
	   subCategorias: ['Cafés', 'Cafés con Piernas', 'Jugos / Smoothies', 'Teterías'],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'Happy Hour', 'No Fumadores', 'Pago con Cheque de Restaurante', 'Redcompra', 'Tarjeta de Crédito', 'Terraza', 'Wifi'],
	   deshabilitar: ['apart-hotel', 'bed-breakfast', 'hostales', 'hoteles', 'hoteles-boutique', 'moteles', 'residenciales']
	},
  delivery: {
     camposEspeciales: [],
     subCategorias: ['Bistró', 'Brunch', 'Carnes y Parrilladas', 'Cocina de Autor', 'Comida Árabe', 'Comida Argentina', 'Comida Casera', 'Comida Colombiana', 'Comida Coreana', 'Comida Cubana', 'Comida Chilena', 'Comida China', 'Comida Ecuatoriana', 'Comida Española', 'Comida Étnica', 'Comida Francesa', 'Comida Fusión', 'Comida Griega', 'Comida India', 'Comida Internacional', 'Comida Italiana', 'Comida Japonesa', 'Comida Latinoamericana', 'Comida Mexicana', 'Comida Naturista', 'Comida Nikkei', 'Comida Norteamericana', 'Comida Peruana', 'Comida Rápida', 'Comida Tailandesa', 'Comida Uruguaya', 'Comida Vasca', 'Comida Vegetariana', 'Comida Vietnamita', 'Completos y Ases', 'Empanadas', 'Ensaladas', 'Fuentes de Soda', 'Hamburguesas', 'Kebabs y Falafel', 'Otros (especial)', 'Pastas', 'Pescados y Mariscos', 'Picadas', 'Pitas y Wraps', 'Pizzerías', 'Pollos y Pavos', 'Sándwiches', 'Sushi'],
       caracteristicas: ['Happy Hour', 'Pago con Cheque de Restaurante', 'Redcompra', 'Tarjeta de Crédito'],
     deshabilitar: ['apart-hotel', 'bed-breakfast', 'hostales', 'hoteles', 'hoteles-boutique', 'moteles', 'residenciales']
  },
	heladerias: {
	   camposEspeciales: [],
	   subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'Happy Hour', 'No Fumadores', 'Pago con Cheque de Restaurante', 'Redcompra', 'Tarjeta de Crédito', 'Para Llevar', 'Terraza', 'Wifi'],
	   deshabilitar: ['apart-hotel', 'bed-breakfast', 'hostales', 'hoteles', 'hoteles-boutique', 'moteles', 'residenciales']
	},
	restaurantes: {
	   camposEspeciales: [],
	   subCategorias: ['Bistró', 'Brunch', 'Buffet', 'Carnes y Parrilladas', 'Cocina de Autor', 'Comida Alemana', 'Comida Árabe', 'Comida Argentina', 'Comida Armenia', 'Comida Asiática', 'Comida Austriaca', 'Comida Autóctona', 'Comida Belga', 'Comida Boliviana', 'Comida Brasilera', 'Comida Casera', 'Comida Catalana', 'Comida Chilena', 'Comida China', 'Comida Colombiana', 'Comida Coreana', 'Comida Croata', 'Comida Cubana', 'Comida Ecuatoriana', 'Comida Española', 'Comida Étnica', 'Comida Francesa', 'Comida Fusión', 'Comida Griega', 'Comida India', 'Comida Internacional', 'Comida Irlandesa', 'Comida Italiana', 'Comida Japonesa', 'Comida Judía', 'Comida Latinoamericana', 'Comida Mediterránea', 'Comida Mexicana', 'Comida Molecular', 'Comida Naturista', 'Comida Neocelandesa', 'Comida Nikkei', 'Comida Nórdica', 'Comida Norteamericana', 'Comida Patagónica', 'Comida Peruana', 'Comida Polaca', 'Comida Polinésica', 'Comida Porteña', 'Comida Rápida', 'Comida Rusa', 'Comida Suiza', 'Comida Tailandesa', 'Comida Tradicional', 'Comida Uruguaya', 'Comida Vasca', 'Comida Vegetariana', 'Comida Vietnamita', 'Completos y Asses', 'Creperías', 'Deli', 'Empanadas', 'Ensaladas', 'Fuentes de Soda', 'Hamburguesas', 'Kebabs y Falafel', 'Milanesas', 'Otros (especial)', 'Pastas', 'Pescados y Mariscos', 'Picadas', 'Pitas y Wraps', 'Pizzerías', 'Pollos y Pavos', 'Sándwiches', 'Sushi', 'Tapas'],
     caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Delivery', 'Delivery Online', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'Happy Hour', 'No Fumadores', 'Pago con Cheque de Restaurante', 'Redcompra', 'Tarjeta de Crédito', 'Para Llevar', 'Reserva', 'Reserva Online', 'Terraza', 'Wifi'],
	   deshabilitar: ['apart-hotel', 'bed-breakfast', 'hostales', 'hoteles', 'hoteles-boutique', 'moteles', 'residenciales']
	},
// Qué Comprar
	adultosSexShops: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	artesaniaJoyas: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	comidaBebida: {
   	   camposEspeciales: [],
       subCategorias: ['Botillerías', 'Cafés / Té', 'Dulces / Chocolates', 'Pastelerías / Panaderías', 'Productos Orientales', 'Tiendas Gourmet / Orgánicos', 'Vinos'],
       caracteristicas: ['Aire Acondicionado', 'Delivery', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Pago con Cheque de Restaurante', 'Redcompra', 'Tarjeta de Crédito', 'Para Llevar'],
       deshabilitar: []
	},
	deportes: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	electronicaComputacion: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	ferias: {
   	   camposEspeciales: [],
       subCategorias: ['Ferías Libres', 'Ferías de Antigüedades'],
       caracteristicas: ['Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	fotografia: {
   	   camposEspeciales: [],
       subCategorias: ['Cámaras / Accesorios', 'Impresión / Revelados'],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	hogarDecoracion: {
   	   camposEspeciales: [],
       subCategorias: ['Antigüedades', 'Decoración', 'Florerías', 'Hogar / Construcción', 'Tiendas de Diseño'],
       caracteristicas: ['Aire Acondicionado', 'Delivery', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	juegosJuguetes: {
   	   camposEspeciales: [],
       subCategorias: ['Juegos de Mesa', 'Juguetes', 'Videojuegos'],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	lentesOpticas: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	librerias: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	librosRevistas: {
   	   camposEspeciales: [],
       subCategorias: ['Libros', 'Revistas / Comics'],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	mallCentrosComerciales: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Terraza', 'Wifi'],
       deshabilitar: []
	},
	mascotas: {
   	   camposEspeciales: [],
       subCategorias: ['Accesorios para Mascotas', 'Mascotas'],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	multitiendas: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Terraza', 'Wifi'],
       deshabilitar: []
	},
	musicaInstrumentos: {
   	   camposEspeciales: [],
       subCategorias: ['CD / DVD', 'Instrumentos Musicales', 'Vinilos'],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	peliculas: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	perfumesCosmetica: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	ropaAccesorios: {
   	   camposEspeciales: [],
       subCategorias: ['Bolsos / Accesorios', 'Diseño Independiente', 'Disfraces', 'Otros (especial)', 'Relojes', 'Ropa de Dormir / Pijamas', 'Ropa de Embarazadas', 'Ropa de Fiestas', 'Ropa de Hombres', 'Ropa de Mujer', 'Ropa de Niño / Guagua', 'Ropa Deportiva', 'Ropa Formal', 'Ropa Interior', 'Ropa Usada / Outlets', 'Sombreros', 'Trajes de Baño', 'Vestidos de Novia', 'Zapatos'],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	supermercadosMinimarkets: {
   	   camposEspeciales: [],
       subCategorias: ['Minimarkets', 'Supermercados'],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	tabaquerias: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
// Cómo Entretenerse
	canchas: {
   	   camposEspeciales: [],
       subCategorias: ['Campos de Golf', 'Canchas de Fútbol', 'Canchas de Tenis'],
       caracteristicas: ['Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	casino: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'Happy Hour', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Terraza', 'Wifi'],
       deshabilitar: []
	},
	centrosDeEventos: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Terraza', 'Wifi'],
       deshabilitar: []
	},
	centrosDeSki: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	ciclovias: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: [],
       deshabilitar: []
	},
	cines: {
   	   camposEspeciales: [],
       subCategorias: ['Cine Arte', 'Cine para Adultos', 'Multicines'],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	discotecasSalonesDeBaile: {
   	   camposEspeciales: [],
       subCategorias: ['Cuequerías', 'Discotecas', 'Salsotecas', 'Tanguerías'],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	estadios: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	juegosElectronicosBowlings: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
  karting: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
  },
	musicaEnVivo: {
   	   camposEspeciales: [],
       subCategorias: ['Acústica', 'Blues', 'Cueca', 'Electrónica / Djs', 'Folclor', 'Funk', 'Hip-Hop', 'Indie', 'Jazz', 'Metal', 'Música Chilena', 'Música Clásica', 'Música Internacional', 'Música Latinoamericana', 'Ópera / Baller', 'Otros (especial)', 'Pop', 'Reggae', 'Reggaeton', 'Rock', 'Salsa', 'Sonoras / Cumbia', 'Varios'],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	nightClubs: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	parquesDeDiversionesAventura: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	piscinas: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	planetario: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	salonesDePool: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	skateparksBikeparks: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Estacionamiento Autos', 'Estacionamiento Bicicletas'],
       deshabilitar: []
	},
	teatros: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	telefericoFunicular: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Estacionamiento Autos', 'Estacionamiento Bicicletas'],
       deshabilitar: []
	},
	zoologico: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
// Cómo Cuidarse
	centrosDeBelleza: {
   	   camposEspeciales: [],
       subCategorias: ['Depilación / Bronceado', 'Maquillaje / Manicure / Pedicure', 'Masaje / Spa'],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	centrosDeEstetica: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	escuelasAcademias: {
   	   camposEspeciales: [],
       subCategorias: ['Artes Marciales', 'Natación', 'Yoga / Pilates / Baile'],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	gimnasios: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	peluquerias: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	tatuajesPiercings: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Gay Friendly', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
// Dónde Dormir
	apartHotel: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: ['bares-pubs', 'cafes-teterias', 'heladerias', 'restaurantes']
	},
	bedBreakfast: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: ['bares-pubs', 'cafes-teterias', 'heladerias', 'restaurantes']
	},
	hostales: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: ['bares-pubs', 'cafes-teterias', 'heladerias', 'restaurantes']
	},
	hoteles: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: ['bares-pubs', 'cafes-teterias', 'heladerias', 'restaurantes']
	},
	hotelesBoutique: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: ['bares-pubs', 'cafes-teterias', 'heladerias', 'restaurantes']
	},
	moteles: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: ['bares-pubs', 'cafes-teterias', 'heladerias', 'restaurantes']
	},
	residenciales: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Apto para Niños', 'Discapacitados', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Fumadores', 'Gay Friendly', 'No Fumadores', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: ['bares-pubs', 'cafes-teterias', 'heladerias', 'restaurantes']
	},
// Servicios
	aeropuerto: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	arriendoDeAutos: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	arriendoDeBicicletas: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	cambioDeMoneda: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	estacionamientos: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
	informacionTuristica: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
	},
  tallerDeAutos: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
  },
  tallerDeBicicletas: {
       camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito'],
       deshabilitar: []
  },
	telefoniaInternet: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	},
	terminalesDeBuses: {
   	   camposEspeciales: [],
       subCategorias: [],
       caracteristicas: ['Aire Acondicionado', 'Estacionamiento Autos', 'Estacionamiento Bicicletas', 'Redcompra', 'Tarjeta de Crédito', 'Wifi'],
       deshabilitar: []
	}
}
