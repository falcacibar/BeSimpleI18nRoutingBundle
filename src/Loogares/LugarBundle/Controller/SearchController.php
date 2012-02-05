<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{

  public function buscarAction($slug){
    $em = $this->getDoctrine()->getEntityManager();
    $fn = $this->get('fn');
    $terminosBuscar = explode(' ', $_GET['buscar']);
    $buscar = $_GET['buscar'];
    $buscarSlug = $fn->generarSlug($buscar);
    $arr['lugares'] = array();
    $callesLike = "'%".$buscar."%'";
    $buscarArray = explode(' ', $buscar);
    $buscarLike = '';
    $unionQuery = '';

    $_GET['pagina'] = (!isset($_GET['pagina']))?1:$_GET['pagina'];
    $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
    $resultadosPorPagina = (!isset($_GET['resultados']))?10:$_GET['resultados'];
    $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*$resultadosPorPagina);

    $fields = "lugares.nombre, lugares.calle, lugares.numero, lugares.estrellas, lugares.precio, lugares.total_recomendaciones, lugares.fecha_ultima_recomendacion, lugares.utiles, lugares.visitas, (lugares.estrellas*6 + lugares.utiles + lugares.total_recomendaciones*2) as ranking, group_concat(DISTINCT categorias.nombre) as categorias_nombre, group_concat(DISTINCT categorias.slug) as categorias_slug, sector.nombre as sector_nombre, sector.slug as sector_slug, LEFT(recomendacion.texto, 140) as ultima_recomendacion, usuarios.nombre as usuario_nombre, usuarios.apellido as usuario_apellido, usuarios.slug as usuario_slug, comuna.slug as comuna_slug, comuna.nombre as comuna_nombre";    

    foreach($buscarArray as $term){
        $cleanTerm = $fn->generarSlug($term);
        $buscarLike .= "'%$cleanTerm%' OR ";
    }

    $buscarLike = preg_replace('/OR\s$/', '', $buscarLike);
    $callesLike = preg_replace('/(%\w+)\'/', "$1\'", $callesLike);

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

    //Lugares por termino
    $lugaresPorTermino = $em->getConnection()->fetchAll("SELECT count(id) as ct FROM lugares where lugares.slug LIKE $buscarLike");
    $lugaresPorTerminoResult = $lugaresPorTermino[0]['ct'];

    //Tags
    $q = $em->createQuery("SELECT count(tr.id)
                           FROM Loogares\UsuarioBundle\Entity\TagRecomendacion tr
                           JOIN tr.recomendacion r
                           JOIN tr.tag t
                           WHERE t.tag LIKE '%$buscarSlug%'");
    $tagsResult = $q->getSingleScalarResult();

    //Calles
    $calles = $em->getConnection()->fetchAll("SELECT count(id) as ct FROM lugares where lugares.calle LIKE $callesLike");
    $callesResult = $calles[0]['ct'];

    $results = array(
        'categorias' => $categoriaResult,
        'subcategorias' => $subCategoriaResult,
        'lugaresPorSlug' => $lugaresPorSlugResult,
        'lugaresPorTermino' => $lugaresPorTerminoResult,
        'tags' => $tagsResult,
        'calles' => $callesResult
    );

    $totalResults = $categoriaResult+$subCategoriaResult+$lugaresPorSlugResult+$lugaresPorTerminoResult+$tagsResult+$callesResult;

    /*
    * PRIORIDADES
    * DE
    * BUSQUEDA
    */

    //Si hay una categoria con ese nombre...
    if($categoriaResult != 0){
      $unionQuery[] = "(SELECT SQL_CALC_FOUND_ROWS $fields FROM categoria_lugar
                        JOIN lugares
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id
                        LEFT JOIN recomendacion
                        ON recomendacion.lugar_id = lugares.id
                        AND recomendacion.id in (select max(recomendacion.id))
                        LEFT JOIN usuarios
                        ON recomendacion.usuario_id = usuarios.id
                        LEFT JOIN comuna
                        ON comuna.id = lugares.comuna_id
                        WHERE categorias.slug LIKE '%$buscarSlug%' GROUP BY lugares.id ORDER BY ranking desc LIMIT 2000)";
    }


    //Si hay una categoria con ese nombre...
    if($subCategoriaResult != 0){
      $unionQuery[] = "(SELECT $fields FROM subcategoria_lugar
                        JOIN lugares
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id
                        LEFT JOIN recomendacion
                        ON recomendacion.lugar_id = lugares.id
                        AND recomendacion.id in (select max(recomendacion.id))
                        LEFT JOIN usuarios
                        ON recomendacion.usuario_id = usuarios.id
                        LEFT JOIN comuna
                        ON comuna.id = lugares.comuna_id
                        WHERE subcategoria.slug LIKE '%$buscarSlug%' GROUP BY lugares.id ORDER BY ranking desc LIMIT 2000)";
    }

    /*
    * THE HARD HART ------
    * Si no encontramos una Categoria o Subcategoria que haga match con el termino de busqueda, 
    * pasamos a una busqueda mas particular
    */

    //Busqueda General por termino transformado a slug, muy especifico, hace match solamente cuando el lugar es buscado por el nombre correcto

    if($lugaresPorSlugResult != 0){
        $unionQuery[] = "(SELECT $fields FROM lugares 
                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id
                          LEFT JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id
                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id
                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id   
                          LEFT JOIN sector
                          ON lugares.sector_id = sector.id
                          LEFT JOIN recomendacion
                          ON recomendacion.lugar_id = lugares.id   
                          AND recomendacion.id in (select max(recomendacion.id))     
                          LEFT JOIN usuarios
                          ON recomendacion.usuario_id = usuarios.id
                          LEFT JOIN comuna
                          ON comuna.id = lugares.comuna_id
                          WHERE lugares.slug like '%$buscarSlug%' GROUP BY lugares.id ORDER BY ranking desc LIMIT 2000)";
    }

    if($tagsResult != 0){
        $unionQuery[] = "(SELECT DISTINCT $fields FROM tag_recomendacion
                          JOIN recomendacion
                          ON recomendacion.id = tag_recomendacion.recomendacion_id
                          JOIN lugares
                          ON lugares.id = recomendacion.lugar_id
                          JOIN tag
                          ON tag.id = tag_recomendacion.tag_id
                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id
                          LEFT JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id
                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id
                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id
                          LEFT JOIN sector
                          ON lugares.sector_id = sector.id
                          LEFT JOIN usuarios
                          ON recomendacion.usuario_id = usuarios.id
                          LEFT JOIN comuna
                          ON comuna.id = lugares.comuna_id
                          WHERE tag.tag LIKE '%$buscarSlug%' GROUP BY lugares.id ORDER BY ranking desc LIMIT 2000)";
    }

    //Si no encontramos nada con el slug, tenemos que adivinar que es lo que el usuario quiere buscar, hacemos una busqueda por termino...
    if($lugaresPorTerminoResult != 0){
        //Ejecutamos una consulta por termino...
        foreach($buscarArray as $key => $term){
            $unionQuery[] = "(SELECT $fields 
                              FROM lugares
                              LEFT JOIN categoria_lugar
                              ON categoria_lugar.lugar_id = lugares.id
                              LEFT JOIN categorias
                              ON categoria_lugar.categoria_id = categorias.id
                              LEFT JOIN subcategoria_lugar
                              ON subcategoria_lugar.lugar_id = lugares.id
                              LEFT JOIN subcategoria
                              ON subcategoria_lugar.subcategoria_id = subcategoria.id
                              LEFT JOIN sector
                              ON lugares.sector_id = sector.id
                              LEFT JOIN recomendacion
                              ON recomendacion.lugar_id = lugares.id
                              AND recomendacion.id in (select max(recomendacion.id))
                              LEFT JOIN usuarios
                              ON recomendacion.usuario_id = usuarios.id
                              LEFT JOIN comuna
                              ON comuna.id = lugares.comuna_id
                              WHERE lugares.slug LIKE $buscarLike GROUP BY lugares.id ORDER BY ranking desc LIMIT 2000)";
        }

            //Revisamos que termino devolvio los menores resultados (mayores a 1), asumimos que se buscaba eso
    }

    //Y POR ULTIMOOOOOOO, buscamos por calles
    if($callesResult != 0){
        $unionQuery[] = "(SELECT $fields FROM lugares
                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id
                          LEFT JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id
                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id
                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id
                          JOIN sector
                          ON lugares.sector_id = sector.id
                          LEFT JOIN recomendacion
                          ON recomendacion.lugar_id = lugares.id
                          AND recomendacion.id in (select max(recomendacion.id))
                          LEFT JOIN usuarios
                          ON recomendacion.usuario_id = usuarios.id
                          LEFT JOIN comuna
                          ON comuna.id = lugares.comuna_id
                          WHERE lugares.calle LIKE $callesLike GROUP BY lugares.id ORDER BY ranking desc LIMIT 2000)";
    }


    if(is_array($unionQuery)){
        $unionQuery = join(" UNION ", $unionQuery);
        $unionQuery .= " LIMIT $resultadosPorPagina OFFSET $offset";
        $arr['lugares'] = $this->getDoctrine()->getConnection()->fetchAll($unionQuery);
    }

    $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");
    foreach($arr['lugares'] as $key => $lugar){
        $arr['lugares'][$key]['categorias_nombre'] = explode(',', $lugar['categorias_nombre']);
        $arr['lugares'][$key]['categorias_slug'] = explode(',', $lugar['categorias_slug']);

        foreach($arr['lugares'][$key]['categorias_nombre'] as $i => $value){
            $catPath = $this->generateUrl('_lugar', array('slug' => $arr['lugares'][$key]['categorias_slug'][$i]));
            $arr['lugares'][$key]['categorias_nombre'][$i] = "<a href='$catPath'>".$value."</a>";
        }

        if($arr['lugares'][$key]['sector_nombre'] != false){
          $sectorPath = $this->generateUrl('_lugar', array('slug' => $arr['lugares'][$key]['sector_slug']));
          $arr['lugares'][$key]['sector_nombre'] = "<a href='$sectorPath'>".$arr['lugares'][$key]['sector_nombre']."</a>";
        }
    }

    $params = array(
        'slug' => $slug
    );

    $paginacion = $fn->paginacion( $resultSetSize[0]['rows'], $resultadosPorPagina, '_buscar', $params, $this->get('router') );

    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array(
        'lugares' => $arr['lugares'],
        'buscar' => $buscar,
        'paginacion' => $paginacion,
        'query' => $_GET
    ));

  }
}
