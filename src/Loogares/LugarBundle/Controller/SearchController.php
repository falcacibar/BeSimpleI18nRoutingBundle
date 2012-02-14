<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
  public function buscarAction(Request $request, $slug){
    $fn = $this->get('fn');
    $em = $this->getDoctrine()->getEntityManager();
    $order = "ranking desc";

    $term = $_GET['q'];
    $termSlug = $fn->generarSlug($term);
    $termArray = preg_split('/\s/', $term);

    $fields = "STRAIGHT_JOIN lugares.id, lugares.nombre as nombre_lugar, lugares.slug, lugares.calle, lugares.numero, lugares.estrellas, lugares.precio, lugares.total_recomendaciones, lugares.fecha_ultima_recomendacion, lugares.utiles, lugares.visitas, (lugares.estrellas*6 + lugares.utiles + lugares.total_recomendaciones*2) as ranking, group_concat(DISTINCT categorias.nombre) as categorias_nombre, group_concat(DISTINCT categorias.slug) as categorias_slug, categorias.slug, categorias.nombre, group_concat(DISTINCT subcategoria.slug) as subcategoria_slug, subcategoria.slug";   
    $noCategorias = false;
    $filterCat = false;
    $filterSubCat = false;
    $filterComuna = false;
    $filterSector = false;
    $countRows = "SQL_CALC_FOUND_ROWS";

    $orderFilters = array(
      'recomendaciones' => 'lugares.total_recomendaciones desc',
      'utiles' => 'lugares.utiles desc',
      'alfabetico' => 'lugares.nombre asc'
    );

    if(isset($_GET['orden'])){
      if(isset($orderFilters[$_GET['orden']])){
        $order = $orderFilters[$_GET['orden']];
      }
    }

    if(isset($_GET['categoria']) || isset($_GET['subcategoria'])){
      $noCategorias = true;
    }

    if(isset($_GET['comuna'])){
      $filterComuna .= ' AND comuna.slug = "' . $_GET['comuna'] . '"';      
    }

    if(isset($_GET['sector'])){
      $filterSector .= ' AND sector.slug = "' . $_GET['sector'] . '"';      
    }
    
    //Vemos si no es igual a una categoria...
    $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Categoria u WHERE u.slug = '$termSlug'");
    $esCategoria = $q->getSingleScalarResult();
 
    if($esCategoria[0] == 1){
      //Mandar a Categoria c/flash
    }

    //Vemos si no es igual a una subcategoria...


    if($noCategorias == false){
      //Buscamos por Categorias
      $unionQuery[] = "(SELECT SQL_CALC_FOUND_ROWS $fields FROM lugares
                        LEFT JOIN comuna
                        ON lugares.comuna_id = comuna.id
                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        WHERE lugares.id != 0 $filterSector $filterComuna
                        GROUP BY lugares.id
                        HAVING categorias_slug LIKE '%$termSlug%'
                        ORDER BY $order
                        LIMIT 2000)";

      //Buscamos por Slug
      $unionQuery[] = "(SELECT  $fields FROM lugares 
                        LEFT JOIN comuna
                        ON lugares.comuna_id = comuna.id
                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        WHERE lugares.slug like '%$termSlug%' $filterSector $filterComuna GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
      
      foreach($termArray as $key => $value){
        $unionQuery[] = "(SELECT $fields 
                          FROM lugares
                          LEFT JOIN comuna
                          ON lugares.comuna_id = comuna.id
                          LEFT JOIN sector
                          ON lugares.sector_id = sector.id
                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id
                          LEFT JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id
                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id
                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id
                          WHERE lugares.slug LIKE '%$value%' $filterSector $filterComuna GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
      }

      //Total de Categorias Generadas por el Slug
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             LEFT JOIN comuna
                             ON lugares.comuna_id = comuna.id
                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id

                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             LEFT JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE lugares.slug LIKE '%$termSlug%' $filterSector $filterComuna)";

      foreach($termArray as $key => $value){
        $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                               FROM lugares

                               LEFT JOIN comuna
                               ON lugares.comuna_id = comuna.id
                               LEFT JOIN sector
                               ON lugares.sector_id = sector.id

                               LEFT JOIN categoria_lugar
                               ON categoria_lugar.lugar_id = lugares.id

                               LEFT JOIN categorias
                               ON categoria_lugar.categoria_id = categorias.id

                               WHERE lugares.slug LIKE '%$value%' $filterSector $filterComuna)";
      }

      //Total de Categorias Generadas por la Categoria
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares
                             LEFT JOIN comuna
                             ON lugares.comuna_id = comuna.id
                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id
                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             LEFT JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE categorias.slug LIKE '%$termSlug%' $filterSector $filterComuna )";
    
      //Total de Categorias por Calle
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares
                             LEFT JOIN comuna
                             ON lugares.comuna_id = comuna.id
                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id
                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             LEFT JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE lugares.calle LIKE '%$term%' $filterSector $filterComuna)";

      /*
      * Totales de Sectores
      */
        $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares

                             JOIN sector
                             ON sector.id = lugares.sector_id

                             WHERE lugares.slug LIKE '%$termSlug%')";

      //Total de Categorias Generadas por la Categoria
      $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares
                             JOIN comuna
                             ON lugares.comuna_id = comuna.id
                             JOIN sector
                             ON lugares.sector_id = sector.id
                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             LEFT JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE categorias.slug LIKE '%$termSlug%' )";

      foreach($termArray as $key => $value){
        $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares

                             JOIN sector
                             ON sector.id = lugares.sector_id

                             WHERE lugares.slug LIKE '%$value%')";
      }

        $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares

                             JOIN sector
                             ON sector.id = lugares.sector_id

                             WHERE lugares.calle LIKE '%$term%')";

      /*
      * Totales de Sectores
      */

      //Total de Categorias Generadas por la Categoria
      $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON lugares.comuna_id = comuna.id
                             JOIN sector
                             ON lugares.sector_id = sector.id
                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id
                             LEFT JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id
                             WHERE categorias.slug LIKE '%$termSlug%' )";

        $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON comuna.id = lugares.comuna_id
                             WHERE lugares.slug LIKE '%$termSlug%')";

      foreach($termArray as $key => $value){
        $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON comuna.id = lugares.comuna_id
                             WHERE lugares.slug LIKE '%$value%')";
      }

        $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON comuna.id = lugares.comuna_id
                             WHERE lugares.calle LIKE '%$term%')";

      //Total de Categorias por Tags
      /*$totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             LEFT JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             LEFT JOIN recomendacion
                             ON recomendacion.lugar_id = lugares.id
                              
                             LEFT JOIN tag_recomendacion
                             ON tag_recomendacion.recomendacion_id = recomendacion.id
                              
                             LEFT JOIN tag
                             ON tag_recomendacion.tag_id = tag.id

                             WHERE tag.tag LIKE '%$term%')";*/
    }else{

      if(isset($_GET['categoria'])){
        $filterCat = ' AND categorias.slug = "' . $_GET['categoria'] . '"';      
      }

      if(isset($_GET['subcategoria'])){
        $filterSubCat = ' AND subcategoria.slug = "' . $_GET['subcategoria'] . '"';      
      }

      //Buscamos por Slug
      $unionQuery[] = "(SELECT SQL_CALC_FOUND_ROWS $fields FROM lugares 
                       JOIN comuna
                       ON lugares.comuna_id = comuna.id
                       JOIN sector
                       ON lugares.sector_id = sector.id
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id   
                        WHERE lugares.slug like '%$termSlug%' $filterComuna $filterSector $filterCat $filterSubCat GROUP BY lugares.id ORDER BY $order LIMIT 2000)";

      foreach($termArray as $key => $value){
          $unionQuery[] = "(SELECT $fields 
                            FROM lugares
                          JOIN comuna
                           ON lugares.comuna_id = comuna.id
                           JOIN sector
                           ON lugares.sector_id = sector.id
                            LEFT JOIN categoria_lugar
                            ON categoria_lugar.lugar_id = lugares.id
                            LEFT JOIN categorias
                            ON categoria_lugar.categoria_id = categorias.id
                            LEFT JOIN subcategoria_lugar
                            ON subcategoria_lugar.lugar_id = lugares.id
                            LEFT JOIN subcategoria
                            ON subcategoria_lugar.subcategoria_id = subcategoria.id
                            WHERE lugares.slug LIKE '%$value%' $filterComuna $filterSector $filterCat $filterSubCat GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
      }

      //Buscamos por Categorias
      /*$unionQuery[] = "(SELECT $fields FROM lugares
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        WHERE lugares.nombre != '1' $filterCat $filterSubCat
                        GROUP BY lugares.id
                        HAVING subcategoria_slug LIKE '%$termSlug%'
                        ORDER BY $order
                        LIMIT 2000)";*/

      //Total de Categorias generadas por el Slug
      $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                FROM lugares

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                LEFT JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                LEFT JOIN categorias
                                ON subcategoria.categoria_id = categorias.id

                                WHERE lugares.slug LIKE '%$termSlug%' $filterCat)";


      foreach($termArray as $key => $value){
        $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                  FROM lugares

                                  LEFT JOIN subcategoria_lugar
                                  ON subcategoria_lugar.lugar_id = lugares.id

                                  LEFT JOIN subcategoria
                                  ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                  LEFT JOIN categorias
                                  ON subcategoria.categoria_id = categorias.id

                                  WHERE lugares.slug LIKE '%$value%' $filterCat)";
      }

      //Total de Categorias generadas por la Subcategoria
      $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                FROM lugares

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                LEFT JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                LEFT JOIN categorias
                                ON subcategoria.categoria_id = categorias.id

                                WHERE subcategoria.slug = '%$termSlug%' $filterCat)";
                
      //Total de Calles en Subcategorias
      $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                        FROM lugares

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id

                        LEFT JOIN categorias
                        ON subcategoria.categoria_id = categorias.id

                        WHERE lugares.calle like '%$termSlug%' $filterCat)";

      //Total de Tags en Subcategorias
      /*$totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                FROM lugares

                                JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                JOIN categorias
                                ON subcategoria.categoria_id = categorias.id
                                
                                LEFT JOIN recomendacion
                                ON recomendacion.lugar_id = lugares.id
                                
                                LEFT JOIN tag_recomendacion
                                ON tag_recomendacion.recomendacion_id = recomendacion.id
                                
                                LEFT JOIN tag
                                ON tag_recomendacion.tag_id = tag.id

                                WHERE tag.tag LIKE '%$term%' $filterCat)";*/


      /*
      * Totales de Sectores
      */
        $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares
                             JOIN sector
                             ON sector.id = lugares.sector_id
                             WHERE lugares.slug LIKE '%$termSlug%')";

      //Total de Categorias Generadas por la Categoria
      $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares
                             JOIN sector
                             ON sector.id = lugares.sector_id
                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id
                             LEFT JOIN categorias
                             ON categorias.id = categoria_lugar.categoria_id
                             WHERE categorias.slug LIKE '%$termSlug%' )";

      foreach($termArray as $key => $value){
        $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares
                             JOIN sector
                             ON sector.id = lugares.sector_id
                             WHERE lugares.slug LIKE '%$value%')";
      }

        $totalSectores[] = "(SELECT lugares.id as lid, sector.id as sid, sector.nombre, sector.slug
                             FROM lugares
                             JOIN sector
                             ON sector.id = lugares.sector_id
                             WHERE lugares.calle LIKE '%$term%')";

      /*
      * Totales de Sectores
      */

      //Total de Categorias Generadas por la Categoria
      $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON lugares.comuna_id = comuna.id
                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id
                             LEFT JOIN categorias
                             ON categorias.id = categoria_lugar.categoria_id
                             WHERE categorias.slug LIKE '%$termSlug%')";

        $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON comuna.id = lugares.comuna_id
                             WHERE lugares.slug LIKE '%$termSlug%')";

      foreach($termArray as $key => $value){
        $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON comuna.id = lugares.comuna_id
                             WHERE lugares.slug LIKE '%$value%')";
      }

        $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                             FROM lugares
                             JOIN comuna
                             ON comuna.id = lugares.comuna_id
                             WHERE lugares.calle LIKE '%$term%')";
    }

    //Query por Calles
    $unionQuery[] = "(SELECT $fields FROM lugares
                      LEFT JOIN sector
                      ON sector.id = lugares.sector_id
                      LEFT JOIN comuna
                      ON comuna.id = lugares.comuna_id 
                      LEFT JOIN categoria_lugar
                      ON categoria_lugar.lugar_id = lugares.id
                      LEFT JOIN categorias
                      ON categoria_lugar.categoria_id = categorias.id
                      LEFT JOIN subcategoria_lugar
                      ON subcategoria_lugar.lugar_id = lugares.id
                      LEFT JOIN subcategoria
                      ON subcategoria_lugar.subcategoria_id = subcategoria.id   
                      WHERE lugares.calle like '%$term%' $filterComuna $filterSector $filterCat $filterSubCat GROUP BY lugares.id ORDER BY $order LIMIT 2000)";

    /*$unionQuery[] = "(SELECT $fields FROM lugares 
                      LEFT JOIN categoria_lugar
                      ON categoria_lugar.lugar_id = lugares.id
                      LEFT JOIN categorias
                      ON categoria_lugar.categoria_id = categorias.id
                      LEFT JOIN subcategoria_lugar
                      ON subcategoria_lugar.lugar_id = lugares.id
                      LEFT JOIN subcategoria
                      ON subcategoria_lugar.subcategoria_id = subcategoria.id   
                      LEFT JOIN recomendacion
                      ON recomendacion.lugar_id = lugares.id
                      LEFT JOIN tag_recomendacion
                      ON tag_recomendacion.recomendacion_id = recomendacion.id
                      LEFT JOIN tag
                      ON tag_recomendacion.tag_id = tag.id
                      WHERE tag.tag like '%$term%' $filterCat $filterSubCat GROUP BY lugares.id ORDER BY $order LIMIT 2000)";*/

  

    

    //Armamos y ejecutamos las queries
    if(is_array($unionQuery)){
      if($noCategorias == false){
       //Generacion y Ejecucion de Query
        $totalCategorias = join(" UNION ", $totalCategorias);
        $totalCategorias =  "select count(lid) as total, lid, cid, nombre, slug from (" . $totalCategorias . ") sq group by cid order by total desc";
        $results['totalPorCategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalCategorias);
      }else{
        //Generacion y Ejecucion de Query
        $totalSubCategorias = join(" UNION ", $totalSubCategorias);
        $totalSubCategorias =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalSubCategorias . ") sq group by sid order by total desc";
        $results['totalPorSubcategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalSubCategorias);
      }

      //Generacion y Ejecucion de Query
      $totalSectores = join(" UNION ", $totalSectores);
      $totalSectores =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalSectores . ") sq group by sid order by total desc";
      $results['totalPorSectores'] = $this->getDoctrine()->getConnection()->fetchAll($totalSectores);

      //Generacion y Ejecucion de Query
      $totalComunas = join(" UNION ", $totalComunas);
      $totalComunas =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalComunas . ") sq group by sid order by total desc";
      $results['totalPorComunas'] = $this->getDoctrine()->getConnection()->fetchAll($totalComunas);


      $unionQuery = join(" UNION ", $unionQuery);
      $unionQuery .= " LIMIT 30 OFFSET 0";  
      $arr['lugares'] = $this->getDoctrine()->getConnection()->fetchAll($unionQuery);
      $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");   
    }

    //Sacamos los otros datos de los 30 resultados que corresponden
    foreach($arr['lugares'] as $key => $lugar){
      $arr['lugares'][$key]['categorias_nombre'] = explode(',', $lugar['categorias_nombre']);
      $arr['lugares'][$key]['categorias_slug'] = explode(',', $lugar['categorias_slug']);

      foreach($arr['lugares'][$key]['categorias_nombre'] as $i => $categoria){
        $catPath = $this->generateUrl('_lugar', array('slug' => $arr['lugares'][$key]['categorias_slug'][$i]));
        $arr['lugares'][$key]['categorias_nombre'][$i] = "<a href='$catPath'>".$categoria."</a>";
      }

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

    $categoria = ((isset($_GET['categoria']))?$_GET['categoria']:NULL);
    $subcategoria = ((isset($_GET['subcategoria']))?$_GET['subcategoria']:NULL);
    $sector = ((isset($_GET['sector']))?$_GET['sector']:NULL);
    $comuna = ((isset($_GET['comuna']))?$_GET['comuna']:NULL);

    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array(
      'term' => $term,
      'slug' => $slug,
      'lugares' => $arr['lugares'],
      'total' => $resultSetSize[0]['rows'],
      'results' => $results,
      'categoria' => $categoria,
      'subcategoria' => $subcategoria,
      'sector' => $sector,
      'comuna' => $comuna
    ));




    /*$em = $this->getDoctrine()->getEntityManager();
    $fn = $this->get('fn');
    if(isset($_GET['term'])){ $term = $_GET['term']; }
    $terminosBuscar = explode(' ', $term);
    $countRows = "SQL_CALC_FOUND_ROWS";
    $totalResults = 0;
    $buscarSlug = $fn->generarSlug($term);
    $arr['lugares'] = array();
    $callesLike = "'%".$term."%'";
    $buscarArray = explode(' ', $term);
    $buscarLike = '';
    $unionQuery = '';
    $filter = null;
    $filterCategoria = '';
    $mostrarSubCategorias = false;

    if($esBusqueda == true){
      $es = 'busqueda';
    }else if($esCategoria == true){
      $es = 'categoria';
      $mostrarSubCategorias = true;
    }else if($esSubCategoria == true){
      $es = 'categoria';
    }else if($esTag == true){
      $es = 'tag';
    }else if($esComuna == true){
      $es = 'comuna';
    }else if($esSector == true){
      $es = 'sector';
    }

    if(isset($_GET['categoria'])){
      $esCategoria = true;
      $mostrarSubCategorias = true;
      $es = 'categoria';
      $filter = ' AND categorias.slug = "' . $_GET['categoria'] . '"';      
    }

    if(isset($_GET['subcategoria'])){
      $filter = ' AND subcategoria.slug = "' . $_GET['subcategoria'] . '"';
    }

    if(isset($_GET['comuna'])){
      $filter .= ' AND comuna.slug = "' . $_GET['comuna'] . '"';      
    }

    if(isset($_GET['sector'])){
      $filter .= ' AND sector.slug = "' . $_GET['sector'] . '"';      
    }



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

    $fields = "lugares.id, lugares.nombre as nombre_lugar, lugares.slug, lugares.calle, lugares.numero, lugares.estrellas, lugares.precio, lugares.total_recomendaciones, lugares.fecha_ultima_recomendacion, lugares.utiles, lugares.visitas, (lugares.estrellas*6 + lugares.utiles + lugares.total_recomendaciones*2) as ranking, group_concat(DISTINCT categorias.nombre) as categorias_nombre, group_concat(DISTINCT categorias.slug) as categorias_slug, categorias.slug, categorias.nombre";    

    foreach($buscarArray as $term){
        $cleanTerm = $fn->generarSlug($term);
        $buscarLike .= "'%$cleanTerm%' OR ";
    }

    $buscarLike = preg_replace('/OR\s$/', '', $buscarLike);
    $callesLike = preg_replace('/(%\w+)\'/', "$1\'", $callesLike);

    //Hacemos las consultas para ver que datos tenemos.

    //Categorias
    if($esBusqueda == true && $esCategoria == false){
      $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Categoria u WHERE u.slug LIKE '%$buscarSlug%'");
      $results['lugaresPorCategoria'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorCategoria'];
    }

    //Subcategorias
    if($esBusqueda == true || $esSubCategoria == true){
      $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\SubCategoria u WHERE u.slug LIKE '%$buscarSlug%'");
      $results['lugaresPorSubcategoria'] = $q->getSingleScalarResult();
      $totalResults = $totalResults + $results['lugaresPorSubcategoria'];
      echo "Buscando por SubCategoria<br/>";
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
      echo "Buscando por Termino<br/>";
    
      if($esCategoria == false && $esSubCategoria == false){
        //Calles
        $calles = $em->getConnection()->fetchAll("SELECT count(id) as ct FROM lugares where lugares.calle LIKE $callesLike");
        $results['lugaresPorCalles'] = $calles[0]['ct'];
        $totalResults = $totalResults + $results['lugaresPorCalles'];
      }
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


    //Si hay una categoria con ese nombre...
    if($esCategoria == false && (isset($results['lugaresPorCategoria']) && $results['lugaresPorCategoria'] != 0)){
      if($filter != ''){
        $filterCategoria = preg_replace('/^\sAND/', 'WHERE', $filter);
        $having = '';
      }else{
        $having = "HAVING categorias_slug LIKE '%$buscarSlug%'";
      }

      $unionQuery[] = "(SELECT $countRows $fields FROM lugares
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        $filterCategoria
                        GROUP BY lugares.id $having ORDER BY $order LIMIT 2000)";
      
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             LEFT JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE categorias.slug LIKE '%$buscarSlug%' $filter)";

      $countRows = null;
    }

    //Si hay una categoria con ese nombre...
    if(isset($results['lugaresPorSubcategoria']) && $results['lugaresPorSubcategoria'] != 0){
      $unionQuery[] = "(SELECT $countRows $fields FROM subcategoria_lugar
                        JOIN lugares
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        WHERE categorias.slug LIKE '%$buscarSlug%' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";

      $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                FROM lugares

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                LEFT JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                WHERE subcategoria.slug LIKE '%".$_GET['subcategoria']."%' AND lugares.slug LIKE '%$buscarSlug%')"; 
      
      $countRows = null;
    }


    //Busqueda General por termino transformado a slug, muy especifico, hace match solamente cuando el lugar es buscado por el nombre correcto

    if(isset($results['lugaresPorSlug']) && $results['lugaresPorSlug'] != 0){
      $unionQuery[] = "(SELECT $countRows $fields FROM lugares 
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id   
                        WHERE lugares.slug like '%$buscarSlug%' $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";
    }

    if(isset($results['lugaresPorTags']) && $results['lugaresPorTags'] != 0){
        if($esBusqueda == true){ $buscarSlug = "%".$buscarSlug."%"; }
        $unionQuery[] = "(SELECT DISTINCT $countRows $fields FROM tag_recomendacion
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
        
      $countRows = null;
    }

    //Si no encontramos nada con el slug, tenemos que adivinar que es lo que el usuario quiere buscar, hacemos una busqueda por termino...
    if(isset($results['lugaresPorTermino']) && $results['lugaresPorTermino'] != 0){
        //Ejecutamos una consulta por termino...

        foreach($buscarArray as $key => $term){
          $unionQuery[] = "(SELECT $countRows $fields 
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

          $countRows = null;

          if($esCategoria == true){
            $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                      FROM lugares

                                      LEFT JOIN subcategoria_lugar
                                      ON subcategoria_lugar.lugar_id = lugares.id

                                      LEFT JOIN subcategoria
                                      ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                      WHERE lugares.slug LIKE '%$term%')";    
          }else{
            $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                                   FROM lugares

                                   LEFT JOIN categoria_lugar
                                   ON categoria_lugar.lugar_id = lugares.id

                                   LEFT JOIN categorias
                                   ON categoria_lugar.categoria_id = categorias.id

                                   WHERE lugares.slug LIKE '%$term%')";
          }
            
        }
      
        if($esCategoria == true){
          $sqlSubCategorias = preg_replace('/UNION\s$/', '', $sqlSubCategorias);
          $sqlSubCategorias .= ") sq group by sq.nombre order by total desc)";
          $totalSubCategorias[] = $sqlSubCategorias;          
        }else{
          $sqlCategorias = preg_replace('/UNION\s$/', '', $sqlCategorias);
          $sqlCategorias .= ") sq group by sq.nombre order by total desc)";
          $totalCategorias[] = $sqlCategorias;
        }

    }

    //Y POR ULTIMOOOOOOO, buscamos por calles
    if(isset($results['lugaresPorCalles']) && $results['lugaresPorCalles'] != 0){
      $unionQuery[] = "(SELECT $countRows $fields FROM lugares
                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id
                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id
                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
                        WHERE lugares.calle LIKE $callesLike $filter GROUP BY lugares.id ORDER BY $order LIMIT 2000)";

      $countRows = null;
    }

    if(isset($results['lugaresPorComuna']) && $results['lugaresPorComuna'] != 0){
      $unionQuery[] = "(SELECT $countRows $fields FROM lugares
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

      $countRows = null;
    }

    if(isset($results['lugaresPorSector']) && $results['lugaresPorSector'] != 0){
      $unionQuery[] = "(SELECT $countRows $fields FROM lugares
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
      $countRows = null;
    }

    if(is_array($unionQuery)){
        $unionQuery = join(" UNION ", $unionQuery);
        $unionQuery .= " LIMIT $resultadosPorPagina OFFSET $offset";
        $arr['lugares'] = $this->getDoctrine()->getConnection()->fetchAll($unionQuery);
        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");  
             
        if($esCategoria == true){
          $totalSubCategorias = join(" UNION ", $totalSubCategorias);
          $totalSubCategorias =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalSubCategorias . ") sq group by sid order by total desc";
          $results['totalPorSubcategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalSubCategorias);
        }else if($esBusqueda == true){
          $totalCategorias = join(" UNION ", $totalCategorias);
          $totalCategorias =  "select count(lid) as total, lid, cid, nombre, slug from (" . $totalCategorias . ") sq group by cid order by total desc";
          $results['totalPorCategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalCategorias);
        }
    }   

    $params = array(  
        'slug' => $slug
    );

    $paginacion = $fn->paginacion( $resultSetSize[0]['rows'], $resultadosPorPagina, '_buscar', $params, $this->get('router') );


    foreach($arr['lugares'] as $key => $lugar){
      $arr['lugares'][$key]['categorias_nombre'] = explode(',', $lugar['categorias_nombre']);
      $arr['lugares'][$key]['categorias_slug'] = explode(',', $lugar['categorias_slug']);

      foreach($arr['lugares'][$key]['categorias_nombre'] as $i => $categoria){
        $catPath = $this->generateUrl('_lugar', array('slug' => $arr['lugares'][$key]['categorias_slug'][$i]));
        $arr['lugares'][$key]['categorias_nombre'][$i] = "<a href='$catPath'>".$categoria."</a>";
      }

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


    $categoria = ((isset($_GET['categoria']))?$_GET['categoria']:NULL);
    $subcategoria = ((isset($_GET['subcategoria']))?$_GET['subcategoria']:NULL);

    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array(
        'lugares' => $arr['lugares'],
        'buscar' => $term,
        'paginacion' => $paginacion,
        'query' => $_GET,
        'slug' => $slug,
        'results' => $results,
        'es' => $es,
        'categoria' => $categoria,
        'subcategoria' => $subcategoria,
        'total' => $resultSetSize[0]['rows']
    ));*/

  }

  public function pocAction(){
    return $this->render('LoogaresLugarBundle:Search:poc.html.twig', array('i' => $_GET['i']));
  }
}
