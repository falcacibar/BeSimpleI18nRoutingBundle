<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
  public function buscarAction(Request $request, $slug, $term, $esBusqueda = false, $esCategoria = false, $esSubCategoria = false, $esTag = false, $esComuna = false, $esSector = false){
    $em = $this->getDoctrine()->getEntityManager();
    $fn = $this->get('fn');
    if(isset($_GET['term'])){ $term = $_GET['term']; }
    $terminosBuscar = explode(' ', $term);
    $totalResults = 0;
    $buscarSlug = $fn->generarSlug($term);
    $arr['lugares'] = array();
    $callesLike = "'%".$term."%'";
    $buscarArray = explode(' ', $term);
    $buscarLike = '';
    $unionQuery = '';
    $filter = null;
    $filterCategoria = '';

    if($esBusqueda == true){
      $es = 'busqueda';
    }else if($esCategoria == true){
      $es = 'categoria';
    }else if($esSubCategoria == true){
      $es = 'subcategoria';
    }else if($esTag == true){
      $es = 'tag';
    }else if($esComuna == true){
      $es = 'comuna';
    }else if($esSector == true){
      $es = 'sector';
    }

    if(isset($_POST['categoria'])){
      $filter = 'AND categorias.slug = "' . $_POST['categoria'] . '"';      
    }

    $results = array(
      'lugaresPorCategoria' => 0,
      'lugaresPorSubcategoria' => 0,
      'lugaresPorSlug' => 0,
      'lugaresPorTermino' => 0,
      'lugaresPorTags' => 0,
      'lugaresPorCalles' => 0,
      'totalPorCategoria' => '',
      'totalPorSubcategoria' => '',
      'lugaresPorComuna' => 0,
      'lugaresPorSector' => 0
    );

    $orderFilters = array(
      'recomendaciones' => 'lugares.total_recomendaciones desc',
      'utiles' => 'lugares.utiles desc',
      'alfabetico' => 'lugares.nombre asc'
    );

    if(isset($_GET['orden'])){
      if(isset($orderFilters[$_GET['orden']])){
        $order = $orderFilters[$_GET['orden']];
      }else{
        $order = "ranking desc";
      }
    }else{
      $order = "ranking desc";
    }

    $_GET['pagina'] = (!isset($_GET['pagina']))?1:$_GET['pagina'];
    $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
    $resultadosPorPagina = (!isset($_GET['resultados']))?30:$_GET['resultados'];
    $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*$resultadosPorPagina);

    $fields = "lugares.id, lugares.nombre, lugares.slug, lugares.calle, lugares.numero, lugares.estrellas, lugares.precio, lugares.total_recomendaciones, lugares.fecha_ultima_recomendacion, lugares.utiles, lugares.visitas, (lugares.estrellas*6 + lugares.utiles + lugares.total_recomendaciones*2) as ranking, group_concat(DISTINCT categorias.nombre) as categorias_nombre, group_concat(DISTINCT categorias.slug) as categorias_slug";    

    foreach($buscarArray as $term){
        $cleanTerm = $fn->generarSlug($term);
        $buscarLike .= "'%$cleanTerm%' OR ";
    }

    $buscarLike = preg_replace('/OR\s$/', '', $buscarLike);
    $callesLike = preg_replace('/(%\w+)\'/', "$1\'", $callesLike);

    //Hacemos las consultas para ver que datos tenemos.

    //Categorias
    if($esBusqueda == true || $esCategoria == true){
      $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Categoria u WHERE u.slug LIKE '%$buscarSlug%'");
      $results['lugaresPorCategoria'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorCategoria'];
    }

    //Subcategorias
    if($esBusqueda == true || $esSubCategoria == true){
      $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\SubCategoria u WHERE u.slug LIKE '%$buscarSlug%'");
      $results['lugaresPorSubcategoria'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorSubcategoria'];
    }

    
    if($esBusqueda == true){
      //Lugares por slug
      $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.slug LIKE '%$buscarSlug%'");
      $results['lugaresPorSlug'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorSlug'];


      //Lugares por termino
      $lugaresPorTermino = $em->getConnection()->fetchAll("SELECT count(id) as ct FROM lugares where lugares.slug LIKE $buscarLike");
      $results['lugaresPorTermino'] = $lugaresPorTermino[0]['ct'];
      $totalResults = $totalResults + $results['lugaresPorTermino'];

      //Calles
      $calles = $em->getConnection()->fetchAll("SELECT count(id) as ct FROM lugares where lugares.calle LIKE $callesLike");
      $results['lugaresPorCalle'] = $calles[0]['ct'];
      $totalResults = $totalResults + $results['lugaresPorCalle'];
    }

    //Tags
    if($esBusqueda == true || $esTag == true){
      $q = $em->createQuery("SELECT count(tr.id)
                             FROM Loogares\UsuarioBundle\Entity\TagRecomendacion tr
                             JOIN tr.recomendacion r
                             JOIN tr.tag t
                             WHERE t.tag LIKE '%$buscarSlug%'");
      $results['lugaresPorTags'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorTags'];
    }

    if($esBusqueda == false && $esComuna == true){
      $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Lugar u LEFT JOIN u.comuna c WHERE c.slug = '$term'");
      $results['lugaresPorComuna'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorComuna'];      
    }

    if($esBusqueda == false && $esSector == true){
      $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Lugar u LEFT JOIN u.sector s WHERE s.slug = '$term'");
      $results['lugaresPorSector'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorSector'];      
    }

    /*
    * PRIORIDADES
    * DE
    * BUSQUEDA
    */

    //Si hay una categoria con ese nombre...
    if($results['lugaresPorCategoria'] != 0){
      if($filter != ''){
        $filterCategoria = "WHERE categorias.slug = '".$_POST['categoria']."'";
      }
      $unionQuery[] = "(SELECT SQL_CALC_FOUND_ROWS $fields FROM categoria_lugar
                        JOIN lugares
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        $filterCategoria
                        GROUP BY lugares.id HAVING categorias_nombre LIKE '%$buscarSlug%' ORDER BY ranking desc LIMIT 2000)";

      $totalCategorias = $this->getDoctrine()->getConnection()->fetchAll("SELECT count(categorias.id) as total, categorias.nombre, categorias.slug
                         FROM categorias

                         LEFT JOIN categoria_lugar
                         ON categoria_lugar.categoria_id = categorias.id

                         LEFT JOIN lugares
                         ON categoria_lugar.lugar_id = lugares.id

                         WHERE categorias.slug LIKE '%$buscarSlug%'
                         GROUP BY categorias.nombre");
      $results['totalPorCategoria'] = $totalCategorias;
    }

    //Si hay una categoria con ese nombre...
    if($results['lugaresPorSubcategoria'] != 0){
      $unionQuery[] = "(SELECT $fields FROM subcategoria_lugar
                        JOIN lugares
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        WHERE subcategoria.slug LIKE '%$buscarSlug%' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
      $totalSubCategorias = $this->getDoctrine()->getConnection()->fetchAll("SELECT count(subcategoria.id) as total, subcategoria.nombre, subcategoria.slug from subcategoria

                             left join subcategoria_lugar
                             on subcategoria_lugar.subcategoria_id = subcategoria.id

                             left join lugares
                             on subcategoria_lugar.lugar_id = lugares.id

                             where subcategoria.nombre like '%$buscarSlug%'
                             group by subcategoria.nombre");
      $results['totalPorSubcategoria'] = $totalSubCategorias;
    }

    /*
    * THE HARD HART ------
    * Si no encontramos una Categoria o Subcategoria que haga match con el termino de busqueda, 
    * pasamos a una busqueda mas particular
    */

    //Busqueda General por termino transformado a slug, muy especifico, hace match solamente cuando el lugar es buscado por el nombre correcto

    if($results['lugaresPorSlug'] != 0){
        $unionQuery[] = "(SELECT $fields FROM lugares 
                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id
                          LEFT JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id
                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id
                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id   
                          WHERE lugares.slug like '%$buscarSlug%' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
      $totalCategorias = $this->getDoctrine()->getConnection()->fetchAll("SELECT count(categorias.id) as total, categorias.nombre, categorias.slug
                         FROM categorias

                         LEFT JOIN categoria_lugar
                         ON categoria_lugar.categoria_id = categorias.id

                         LEFT JOIN lugares
                         ON categoria_lugar.lugar_id = lugares.id

                         WHERE lugares.slug LIKE '%$buscarSlug%'
                         GROUP BY categorias.nombre");
    }

    if($results['lugaresPorTags'] != 0){
        if($esBusqueda == true){ $buscarSlug = "%".$buscarSlug."%"; }
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
                          WHERE tag.tag LIKE '$buscarSlug' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";

    }

    //Si no encontramos nada con el slug, tenemos que adivinar que es lo que el usuario quiere buscar, hacemos una busqueda por termino...
    if($results['lugaresPorTermino'] != 0){
        //Ejecutamos una consulta por termino...
        $totalLugaresPorTermino = 0;
        $sql = "SELECT categoria_nombre as nombre, slug, sum(total) as total FROM(";
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
                            WHERE lugares.slug LIKE '%$term%' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
          $sql .= "(SELECT count(categorias.id) as total, categorias.nombre as categoria_nombre, categorias.slug FROM categorias LEFT JOIN categoria_lugar ON categoria_lugar.categoria_id = categorias.id LEFT JOIN lugares ON categoria_lugar.lugar_id = lugares.id WHERE lugares.slug LIKE '%$term%' group by categorias.nombre) UNION ";
            
        }
        $sql = preg_replace('/UNION\s$/', '', $sql);
        $sql .= ") sq group by categoria_nombre";

        $totalCategorias = $this->getDoctrine()->getConnection()->fetchAll($sql);
        $results['totalPorCategoria'] = $totalCategorias;

    }


    //Y POR ULTIMOOOOOOO, buscamos por calles
    if($results['lugaresPorCalles'] != 0){
        $unionQuery[] = "(SELECT $fields FROM lugares
                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id
                          LEFT JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id
                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id
                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id
                          WHERE lugares.calle LIKE $callesLike $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
    }

    if($results['lugaresPorComuna'] != 0){
        $unionQuery[] = "(SELECT $fields FROM lugares
                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id
                          LEFT JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id
                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id
                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id
                          LEFT JOIN comuna
                          ON comuna.id = lugares.comuna_id
                          WHERE comuna.slug = '$term' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
    }

    if($results['lugaresPorSector'] != 0){
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
                          ON sector.id = lugares.sector_id
                          WHERE sector.slug = '$term' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
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

      foreach($arr['lugares'][$key]['categorias_nombre'] as $i => $categoria){
        $catPath = $this->generateUrl('_lugar', array('slug' => $arr['lugares'][$key]['categorias_slug'][$i]));
        $arr['lugares'][$key]['categorias_nombre'][$i] = "<a href='$catPath'>".$categoria."</a>";
      }
    }

    $params = array(  
        'slug' => $slug
    );

    $paginacion = $fn->paginacion( $resultSetSize[0]['rows'], $resultadosPorPagina, '_buscar', $params, $this->get('router') );


    foreach($arr['lugares'] as $key => $lugar){
      $buffer = $this->getDoctrine()->getConnection()
      ->fetchAll("SELECT imagenes_lugar.imagen_full as imagen_lugar, comuna.nombre as comuna_nombre, comuna.slug as comuna_slug, sector.nombre as sector_nombre, sector.slug as sector_slug, LEFT(recomendacion.texto, 140) as ultima_recomendacion, usuarios.slug as usuario_slug, usuarios.nombre as usuario_nombre, usuarios.apellido as usuario_apellido, usuarios.imagen_full as usuario_imagen
        from lugares 
        LEFT JOIN comuna
        ON comuna.id = lugares.comuna_id
        LEFT JOIN sector
        ON sector.id = lugares.sector_id
        LEFT JOIN imagenes_lugar 
        ON imagenes_lugar.lugar_id = lugares.id 
        AND imagenes_lugar.id in (select max(imagenes_lugar.id))
        LEFT JOIN recomendacion
        ON recomendacion.lugar_id = lugares.id
        AND recomendacion.id in (select max(recomendacion.id))
        LEFT JOIN usuarios 
        ON usuarios.id = recomendacion.usuario_id
        WHERE lugares.id = ".$lugar['id']." group by lugares.id");
      $arr['lugares'][$key] = array_merge($arr['lugares'][$key], $buffer[0]);
    }

    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array(
        'lugares' => $arr['lugares'],
        'buscar' => $term,
        'paginacion' => $paginacion,
        'query' => $_GET,
        'slug' => $slug,
        'results' => $results,
        'es' => $es
    ));

  }
}
