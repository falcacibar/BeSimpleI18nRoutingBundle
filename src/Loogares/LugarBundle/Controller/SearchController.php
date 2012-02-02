<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
  private function generarVista($arr){
    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array('lugares' => $arr['lugares'], 'buscando' => $arr['buscando'] ));
  }

  public function buscarAction(){

    function aasort ($array, $key) {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
        return $array;
    }

    $em = $this->getDoctrine()->getEntityManager();
    $fn = $this->get('fn');
    $terminosBuscar = explode(' ', $_GET['buscar']);
    $buscar = $_GET['buscar'];
    $buscarSlug = $fn->generarSlug($buscar);
    $results = array();
    $where = null;
    $st = null;
    $tags = '';
    $subcat = '';
    $arr['lugares'] = array();
    $exclude = '';

    //Hacemos las consultas para ver que datos tenemos.

    //Categorias
    $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Categoria u WHERE u.slug LIKE '%$buscarSlug%'");
    $categoriaResult = $q->getSingleScalarResult();

    //Subcategorias
    $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\SubCategoria u WHERE u.slug LIKE '%$buscarSlug%'");
    $subCategoriaResult = $q->getSingleScalarResult();

    //Lugares por slug
    $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.slug LIKE '%$buscarSlug%'");
    $lugaresPorSlugResult = $q->getSingleScalarResult();

    //Tags
    $q = $em->createQuery("SELECT count(tr.id)
                           FROM Loogares\UsuarioBundle\Entity\TagRecomendacion tr
                           JOIN tr.recomendacion r
                           JOIN r.lugar l
                           JOIN tr.tag t
                           WHERE t.tag LIKE '%$buscarSlug%'");
    $tagsResult = $q->getSingleScalarResult();

    //Calles
    $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.calle LIKE '%$buscar%'");
    $callesResult = $q->getSingleScalarResult();

    $results = array(
        'categorias' => $categoriaResult,
        'subcategorias' => $subCategoriaResult,
        'lugaresPorSlug' => $lugaresPorSlugResult,
        'tags' => $tagsResult,
        'calles' => $callesResult
    );


    /*
    * PRIORIDADES
    * DE
    * BUSQUEDA
    */

    function excluirLugares($array, $buffer = ''){
        foreach($array as $lugar){
            $buffer .= ' AND lugares.id != ' . $lugar['id'];
        }
        return $buffer;
    }


    //Si hay una categoria con ese nombre...
    if($categoriaResult != 0){
      $arr['buscando'] = 'Buscando por Categoria: ' . $buscar;
      $arr['lugares'] = array_merge($arr['lugares'], $em
             ->getConnection()
             ->fetchAll("SELECT lugares.* FROM categoria_lugar

                        JOIN lugares
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        WHERE categorias.slug LIKE '%$buscarSlug%' $exclude
                        ORDER BY lugares.visitas desc"));
       $exclude .= excluirLugares($arr['lugares']);
    }



    //Si hay una categoria con ese nombre...
    if($subCategoriaResult != 0){
      $arr['buscando'] = 'Buscando por Subcategoria: ' . $buscar;
      $arr['lugares'] = array_merge($arr['lugares'], $em
             ->getConnection()
             ->fetchAll("SELECT lugares.* FROM subcategoria_lugar

                        JOIN lugares
                        ON subcategoria_lugar.lugar_id = lugares.id

                        JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id

                        WHERE subcategoria.slug LIKE '%$buscarSlug%' $exclude
                        ORDER BY lugares.visitas DESC"));
       $exclude .= excluirLugares($arr['lugares']);
    }

    /*
    * THE HARD HART ------
    * Si no encontramos una Categoria o Subcategoria que haga match con el termino de busqueda, 
    * pasamos a una busqueda mas particular
    */

    //Busqueda General por termino transformado a slug, muy especifico, hace match solamente cuando el lugar es buscado por el nombre correcto

    if($lugaresPorSlugResult != 0){
        $arr['buscando'] = 'Busqueda por Slug: ' . $buscarSlug;
        $arr['lugares'] = array_merge($arr['lugares'], $em
            ->getConnection()
            ->fetchAll("SELECT lugares.* from lugares where lugares.slug like '%$buscarSlug%' $exclude"));
       $exclude .= excluirLugares($arr['lugares']);
    }

    if($tagsResult != 0){
        $arr['buscando'] = 'Busqueda por Slug: ' . $buscarSlug;
        $arr['lugares'] = array_merge($arr['lugares'], $em
            ->getConnection()
            ->fetchAll("SELECT DISTINCT lugares.* FROM tag_recomendacion
                        JOIN recomendacion
                        ON recomendacion.id = tag_recomendacion.recomendacion_id
                        JOIN lugares
                        ON lugares.id = recomendacion.lugar_id
                        JOIN tag
                        ON tag.id = tag_recomendacion.tag_id
                        WHERE tag.tag LIKE '%$buscarSlug%' $exclude"));
       $exclude .= excluirLugares($arr['lugares']);
    }

    //Si no encontramos nada con el slug, tenemos que adivinar que es lo que el usuario quiere buscar, hacemos una busqueda por termino...
    // $buscarArray = explode(' ', $buscar);
    // $buscarLike = '';
    // foreach($buscarArray as $term){
    //     $buscarLike .= "'%$term%' OR ";
    // }
    // $buscarLike = preg_replace('/OR\s$/', '', $buscarLike);

    // $arr['buscando'] = 'Busqueda por Terminos: ' . $buscarLike;

    // //Ejecutamos una consulta por termino...
    // foreach($buscarArray as $key => $term){
    //     $arr['lugares'][$key] = $em
    //         ->getConnection()
    //         ->fetchAll("SELECT lugares.* 
    //                     FROM lugares 
    //                     JOIN recomendacion
    //                     ON recomendacion.lugar_id = lugares.id
    //                     JOIN tag_recomendacion
    //                     ON tag_recomendacion.recomendacion_id = recomendacion.id
    //                     JOIN tag
    //                     ON tag.id = tag_recomendacion.tag_id
    //                     WHERE lugares.slug LIKE '%$term%'");
    // }

    // //Revisamos que termino devolvio los menores resultados (mayores a 1), asumimos que se buscaba eso
    // sort($arr['lugares']);
    // $arr['lugares'] = $arr['lugares'][0];

    // if(isset($arr['lugares'])){
    //     return $this->generarVista($arr);
    // }

    $tmp = '';

    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array('lugares' => $arr['lugares'], 'buscando' => $arr['buscando'], 'results' => $results));

  }
}
