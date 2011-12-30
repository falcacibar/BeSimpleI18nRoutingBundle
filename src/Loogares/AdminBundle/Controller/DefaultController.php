<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{ 
    public function indexAction(Request $request){
        $order = false;
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $filters = array();
        $listadoFilters = array('id', 'nombre');

        foreach($_GET as $column => $filter){
            if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order = "ORDER BY u.$column $filter";
                }else{
                    $order .= ", u.$column $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        foreach($listadoFilters as $key){
            if(!isset($_GET[$key])){
                $_GET[$key] = '';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $lugares = $lr->getLugares(null, 30, ($paginaActual - 1) * 30, $order);
        $totalLugares = $lr->getTotalLugares();
        $paginas = floor($totalLugares / 30) + 1;
        $mostrarDesde = $paginaActual-5;
        $mostrarHasta = $paginaActual+5;

        return $this->render('LoogaresAdminBundle:Admin:lugares.html.twig', array(
            'lugares' => $lugares, 
            'filters' => $filters,
            'query' => $_GET,
            'totalPaginas' => $paginas,
            'paginaActual' => $paginaActual,
            'mostrarDesde' => $mostrarDesde,
            'mostrarHasta' => $mostrarHasta
        ));
    }
}
