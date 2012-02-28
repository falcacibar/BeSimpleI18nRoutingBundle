<?php

namespace Loogares\ExtraBundle\Extension;

class LoogaresExtension extends \Twig_Extension {

    public function getFunctions() {
        return array(
            'ad' => new \Twig_Function_Method($this, 'espacioAd'),
        );
    }

    public function espacioAd($type, $section, $position, $size) {

        // Array que lista todos los espacios (en 4 niveles)
        $espacios = array(
            'static' => array(
                'home' => array(
                    'top' => array(
                        'medium' => 'home_top_medium_300x250'),
                    'bottom' => array(
                        'medium' => 'home_bottom_medium_300x250')),

                'search' => array(
                    'top' => array(
                        'medium' => 'search_top_medium_300x250')),
                
                'ficha' => array(
                    'top' => array(
                        'medium' => 'ficha_top_medium_300x250')),
                
                'galeria' => array(
                    'top' => array(
                        'medium' => 'galeria_top_medium_300x250'))),

            'category' => array(
                1 => array(
                    'top' => array(
                        'medium' => 'arquitectura_medium_rectangule_top_300x250')),             
                85 => array(
                    'top' => array(
                        'medium' => 'electronica_computacion_medium_rectangule_top_300x250'))),
               
        );

        return $espacios[$type][$section][$position][$size];
    }

    public function getName()
    {
        return 'loogares';
    }

}