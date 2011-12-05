<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class AjaxController extends Controller
{
    public function otrosLugaresEnElAreaAction(){
        list($mapxDesde, $mapyDesde) = explode(',',$_GET['southWest']);
        list($mapxHasta, $mapyHasta) = explode(',',$_GET['northEast']);
        $idLugar = $_GET['idLugar'];

        $otrosLugaresResult = $this->getDoctrine()->getConnection()->fetchAll("SELECT lugares.*, group_concat(DISTINCT categorias.nombre) as categorias, imagen_full, count(recomendacion.id) as recomendaciones, tipo_categoria.id as tipo
                                                                               FROM lugares
                                                                               LEFT JOIN categoria_lugar
                                                                               ON categoria_lugar.lugar_id = lugares.id
                                                                               LEFT JOIN categorias
                                                                               ON categorias.id = categoria_lugar.categoria_id
                                                                               LEFT JOIN imagenes_lugar
                                                                               ON imagenes_lugar.lugar_id = lugares.id
                                                                               LEFT JOIN tipo_categoria
                                                                               ON categorias.tipo_categoria_id = tipo_categoria.id
                                                                               LEFT JOIN recomendacion
                                                                               ON recomendacion.lugar_id = lugares.id
                                                                               WHERE lugares.id != $idLugar
                                                                               AND mapx BETWEEN $mapxDesde AND $mapxHasta
                                                                               AND mapy BETWEEN $mapyDesde AND $mapyHasta
                                                                               GROUP BY lugares.id
                                                                               ORDER BY RAND()
                                                                               LIMIT 20");
        
        for($i = 0; $i < sizeOf($otrosLugaresResult); $i++){
            $otrosLugaresResult[$i]['categorias'] = explode(',',$otrosLugaresResult[$i]['categorias']);
        }

        return $this->render('LoogaresLugarBundle:Lugares:otrosLugares.html.twig', array('lugares' => $otrosLugaresResult));
    }

    public function filtroDeLugaresAction(){
        return new Response('<h1>Filtro de Lugares</h1>');    
    }

    public function sugerirUnLugarAction(){
        return new Response('<h1>Sugerir un Lugar</h1>');
    }
}
