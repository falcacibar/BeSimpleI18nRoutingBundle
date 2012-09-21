<?php

namespace Loogares\ExtraBundle\Extension;

class LoogaresExtension extends \Twig_Extension {

    public function getFunctions() {
        return array(
            'ad' => new \Twig_Function_Method($this, 'espacioAd'),
        );
    }

    public function getFilters() {
        return array(
            'xmlsitemap'  => new \Twig_Filter_Method($this, 'sitemap'),
            'explode' => new \Twig_Filter_Method($this, 'explode'),
            'token' => new \Twig_Filter_Method($this, 'strtok')
        );
    }

    public function strtok($string, $delimiters=null) {
        return strtok($string, $delimiters);
    }

    public function sitemap($url, $priority='1.00', $freq=2) {
        $f = array('always','hourly','daily','weekly','monthly','yearly','never');
        $freq = $f[$freq];
        return "
            <url>
              <loc>$url</loc>
              <changefreq>$freq</changefreq>
              <priority>$priority</priority>
            </url>";
    }

    public function explode($string, $delimiter) {
        return explode($delimiter, $string);
    }

    public function espacioAd($type, $section, $position, $size) {

        // Array que lista todos los espacios (en 4 niveles)
        $espacios = array(
            'static' => array(
                'blog' => array(
                    'top' => array(
                        'medium' => 'home_top_medium_300x250')),

                'home' => array(
                    'top' => array(
                        'medium' => 'home_top_medium_300x250'),
                    'bottom' => array(
                        'medium' => 'home_bottom_medium_300x250')),

                'search' => array(
                    'top' => array(
                        'medium' => 'search_top_medium_300x250'),
                    'patrocinado' => array(
                        'largo' => 'busquedas_top_largo_602x184'),
                    'concursos' => array(
                        'medium' => 'concursos_activos_search_185x132')),

                'ficha' => array(
                    'top' => array(
                        'medium' => 'ficha_top_medium_300x250'),
                    'bottom' => array(
                        'medium' => 'ficha_bottom_medium_300x250'),
                    'patrocinado' => array(
                        'largo' => 'fichaslugar_top_largo_640x184'),
                    'concursos' => array(
                        'medium' => 'concursos_activos_ficha_198x136')),

                'galeria' => array(
                    'top' => array(
                        'medium' => 'galeria_top_medium_300x250'))),

            'categoria' => array(
                1 => array(
                    'top' => array(
                        'medium' => 'arquitectura_top_medium_300x250')),
                2 => array(
                    'top' => array(
                        'medium' => 'arte-urbano-graffiti_top_medium_300x250')),
                3 => array(
                    'top' => array(
                        'medium' => 'bibliotecas_top_medium_300x250')),
                4 => array(
                    'top' => array(
                        'medium' => 'centros-culturales_top_medium_300x250')),
                5 => array(
                    'top' => array(
                        'medium' => 'galerias-de-arte_top_medium_300x250')),
                6 => array(
                    'top' => array(
                        'medium' => 'monumentos-esculturas_top_medium_300x250')),
                7 => array(
                    'top' => array(
                        'medium' => 'museos_top_medium_300x250')),
                8 => array(
                    'top' => array(
                        'medium' => 'parques_top_medium_300x250')),
                9 => array(
                    'top' => array(
                        'medium' => 'plazas_top_medium_300x250')),
                10 => array(
                    'top' => array(
                        'medium' => 'atractivos-turisticos_top_medium_300x250')),
                11 => array(
                    'top' => array(
                        'medium' => 'bares-pubs_top_medium_300x250')),
                12 => array(
                    'top' => array(
                        'medium' => 'cafes-teterias_top_medium_300x250')),
                13 => array(
                    'top' => array(
                        'medium' => 'heladerias_top_medium_300x250')),
                14 => array(
                    'top' => array(
                        'medium' => 'restaurantes_top_medium_300x250')),
                15 => array(
                    'top' => array(
                        'medium' => 'artesania-joyas_top_medium_300x250')),
                16 => array(
                    'top' => array(
                        'medium' => 'mall-centros-comerciales_top_medium_300x250')),
                17 => array(
                    'top' => array(
                        'medium' => 'hogar-decoracion_top_medium_300x250')),
                18 => array(
                    'top' => array(
                        'medium' => 'libros-revistas_top_medium_300x250')),
                19 => array(
                    'top' => array(
                        'medium' => 'comida-bebida_top_medium_300x250')),
                20 => array(
                    'top' => array(
                        'medium' => 'musica-instrumentos_top_medium_300x250')),
                21 => array(
                    'top' => array(
                        'medium' => 'peliculas_top_medium_300x250')),
                23 => array(
                    'top' => array(
                        'medium' => 'ropa-accesorios_top_medium_300x250')),
                24 => array(
                    'top' => array(
                        'medium' => 'adultos-sex-shops_top_medium_300x250')),
                27 => array(
                    'top' => array(
                        'medium' => 'cines_top_medium_300x250')),
                28 => array(
                    'top' => array(
                        'medium' => 'discotecas-salones-de-baile_top_medium_300x250')),
                29 => array(
                    'top' => array(
                        'medium' => 'juegos-electronicos-bowlings_top_medium_300x250')),
                30 => array(
                    'top' => array(
                        'medium' => 'musica-en-vivo_top_medium_300x250')),
                31 => array(
                    'top' => array(
                        'medium' => 'night-clubs_top_medium_300x250')),
                32 => array(
                    'top' => array(
                        'medium' => 'parques-de-diversiones-aventura_top_medium_300x250')),
                33 => array(
                    'top' => array(
                        'medium' => 'piscinas_top_medium_300x250')),
                34 => array(
                    'top' => array(
                        'medium' => 'planetario_top_medium_300x250')),
                35 => array(
                    'top' => array(
                        'medium' => 'salones-de-pool_top_medium_300x250')),
                36 => array(
                    'top' => array(
                        'medium' => 'teatros_top_medium_300x250')),
                37 => array(
                    'top' => array(
                        'medium' => 'teleferico-funicular_top_medium_300x250')),
                38 => array(
                    'top' => array(
                        'medium' => 'zoologico_top_medium_300x250')),
                39 => array(
                    'top' => array(
                        'medium' => 'apart-hotel_top_medium_300x250')),
                40 => array(
                    'top' => array(
                        'medium' => 'hostales_top_medium_300x250')),
                41 => array(
                    'top' => array(
                        'medium' => 'hoteles_top_medium_300x250')),
                42 => array(
                    'top' => array(
                        'medium' => 'moteles_top_medium_300x250')),
                43 => array(
                    'top' => array(
                        'medium' => 'residenciales_top_medium_300x250')),
                44 => array(
                    'top' => array(
                        'medium' => 'cambio-de-monedas_top_medium_300x250')),
                45 => array(
                    'top' => array(
                        'medium' => 'estacionamientos_top_medium_300x250')),
                46 => array(
                    'top' => array(
                        'medium' => 'informacion-turistica_top_medium_300x250')),
                47 => array(
                    'top' => array(
                        'medium' => 'supermercados-minimarkets_top_medium_300x250')),
                49 => array(
                    'top' => array(
                        'medium' => 'telefonia-internet_top_medium_300x250')),
                50 => array(
                    'top' => array(
                        'medium' => 'multitiendas_top_medium_300x250')),
                51 => array(
                    'top' => array(
                        'medium' => 'ferias_top_medium_300x250')),
                52 => array(
                    'top' => array(
                        'medium' => 'juegos-juguetes_top_medium_300x250')),
                53 => array(
                    'top' => array(
                        'medium' => 'librerias_top_medium_300x250')),
                54 => array(
                    'top' => array(
                        'medium' => 'tabaquerias_top_medium_300x250')),
                55 => array(
                    'top' => array(
                        'medium' => 'perfumes-cosmetica_top_medium_300x250')),
                56 => array(
                    'top' => array(
                        'medium' => 'fotografia_top_medium_300x250')),
                57 => array(
                    'top' => array(
                        'medium' => 'deportes_top_medium_300x250')),
                58 => array(
                    'top' => array(
                        'medium' => 'mascotas_top_medium_300x250')),
                60 => array(
                    'top' => array(
                        'medium' => 'centros-de-ski_top_medium_300x250')),
                61 => array(
                    'top' => array(
                        'medium' => 'estadios_top_medium_300x250')),
                62 => array(
                    'top' => array(
                        'medium' => 'canchas_top_medium_300x250')),
                63 => array(
                    'top' => array(
                        'medium' => 'arriendo-de-bicicletas_top_medium_300x250')),
                64 => array(
                    'top' => array(
                        'medium' => 'skateparks-bikeparks_top_medium_300x250')),
                65 => array(
                    'top' => array(
                        'medium' => 'hoteles-boutique_top_medium_300x250')),
                66 => array(
                    'top' => array(
                        'medium' => 'bed-breakfast_top_medium_300x250')),
                67 => array(
                    'top' => array(
                        'medium' => 'arriendo-de-autos_top_medium_300x250')),
                68 => array(
                    'top' => array(
                        'medium' => 'terminales-de-buses_top_medium_300x250')),
                69 => array(
                    'top' => array(
                        'medium' => 'aeropuerto_top_medium_300x250')),
                70 => array(
                    'top' => array(
                        'medium' => 'peluquerias_top_medium_300x250')),
                71 => array(
                    'top' => array(
                        'medium' => 'centros-de-belleza_top_medium_300x250')),
                72 => array(
                    'top' => array(
                        'medium' => 'gimnasios_top_medium_300x250')),
                73 => array(
                    'top' => array(
                        'medium' => 'tatuajes-piercings_top_medium_300x250')),
                74 => array(
                    'top' => array(
                        'medium' => 'escuelas-academias_top_medium_300x250')),
                76 => array(
                    'top' => array(
                        'medium' => 'lentes-opticas_top_medium_300x250')),
                77 => array(
                    'top' => array(
                        'medium' => 'paseos-miradores_top_medium_300x250')),
                78 => array(
                    'top' => array(
                        'medium' => 'ascensores_top_medium_300x250')),
                79 => array(
                    'top' => array(
                        'medium' => 'playas_top_medium_300x250')),
                80 => array(
                    'top' => array(
                        'medium' => 'casino_top_medium_300x250')),
                82 => array(
                    'top' => array(
                        'medium' => 'centros-de-eventos_top_medium_300x250')),
                83 => array(
                    'top' => array(
                        'medium' => 'centros-de-estetica_top_medium_300x250')),
                84 => array(
                    'top' => array(
                        'medium' => 'caletas-de-pescadores_top_medium_300x250')),
                85 => array(
                    'top' => array(
                        'medium' => 'electronica-computacion_top_medium_300x250')),
                87 => array(
                    'top' => array(
                        'medium' => 'ciclovias_top_medium_300x250')),
                90 => array(
                    'top' => array(
                        'medium' => 'taller-de-autos_top_medium_300x250')),
                91 => array(
                    'top' => array(
                        'medium' => 'taller-de-bicicletas_top_medium_300x250')),
                93 => array(
                    'top' => array(
                        'medium' => 'delivery_top_medium_300x250')),
                94 => array(
                    'top' => array(
                        'medium' => 'karting_top_medium_300x250')),
                96 => array(
                    'top' => array(
                        'medium' => 'cabanas_top_medium_300x250'))),
        );

        return $espacios[$type][$section][$position][$size];
    }

    public function getName()
    {
        return 'loogares';
    }

}