<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{ 
    public function indexAction(Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");



        return $this->render('LoogaresAdminBundle:Admin:index.html.twig', array(
            'totalLugares' => $lr->getTotalLugares()
        ));
    }   

    public function lugaresAction(Request $request){
        $order = false;
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $filters = array();
        $listadoFilters = array(
            'id' => 'lugares.id', 
            'nombre' => 'lugares.nombre');

        foreach($_GET as $column => $filter){
            if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order = "ORDER BY ".$listadoFilters[$column]." $filter";
                }else{
                    $order .= ", $column $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        foreach($listadoFilters as $key => $value){
            if(!isset($_GET[$key])){
                $_GET[$key] = '';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = floor($paginaActual*30);

        $ih8doctrine = $this->getDoctrine()->getConnection()
        ->fetchAll("SELECT lugares.*, 
                    usuarios.mail as usuarioMail,
                    comuna.nombre as comunaNombre,
                    sector.nombre as sectorNombre,
                    pais.nombre as paisNombre,
                    ciudad.nombre as ciudadNombre,
                    cast(AVG(recomendacion.estrellas) as signed) as estrellas,
                    count(distinct util.id) as utiles,
                    group_concat(distinct categorias.nombre) as categorias,
                    group_concat(distinct subcategoria.nombre) as subcategorias,
                    group_concat(distinct caracteristica.nombre) as caracteristicas

                    FROM lugares

                    left join usuarios
                    on usuarios.id = lugares.usuario_id

                    left join comuna
                    on comuna.id = lugares.comuna_id

                    left join sector
                    on sector.id = lugares.sector_id

                    left join ciudad
                    on ciudad.id = comuna.ciudad_id

                    left join pais
                    on pais.id = ciudad.pais_id

                    left join recomendacion
                    on recomendacion.lugar_id = lugares.id

                    left join util
                    on util.recomendacion_id = recomendacion.id

                    left join categoria_lugar
                    on categoria_lugar.lugar_id = lugares.id

                    left join categorias
                    on categorias.id = categoria_lugar.categoria_id

                    left join subcategoria_lugar
                    on subcategoria_lugar.lugar_id = lugares.id

                    left join subcategoria
                    on subcategoria.categoria_id = categorias.id and subcategoria.id = subcategoria_lugar.subcategoria_id

                    left join caracteristica_lugar
                    on caracteristica_lugar.lugar_id = lugares.id

                    left join caracteristica
                    on caracteristica.id = caracteristica_lugar.caracteristica_id

                    GROUP BY lugares.id
                    $order
                    LIMIT 30
                    OFFSET $offset");

        for($i = 0; $i < sizeOf($ih8doctrine); $i++){
            //$ih8doctrine[$i]['tags'] = explode(',', $ih8doctrine[$i]['tags']);
            $ih8doctrine[$i]['caracteristicas'] = explode(',', $ih8doctrine[$i]['caracteristicas']);
            $ih8doctrine[$i]['categorias'] = explode(',', $ih8doctrine[$i]['categorias']);
            $ih8doctrine[$i]['subcategorias'] = explode(',', $ih8doctrine[$i]['subcategorias']);
        }

       
        $lugares = $ih8doctrine;
        $paginas = floor(($lr->getTotalLugares() / 30));
        $mostrarDesde = $paginaActual-5;
        $mostrarHasta = $paginaActual+5;

        return $this->render('LoogaresAdminBundle:Admin:lugares.html.twig', array(
            'lugares' => $ih8doctrine, 
            'filters' => $filters,
            'query' => $_GET,
            'totalPaginas' => $paginas,
            'paginaActual' => $paginaActual,
            'mostrarDesde' => $mostrarDesde,
            'mostrarHasta' => $mostrarHasta
        ));
    }
}
