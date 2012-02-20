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
    $order = null;
    $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
    $resultadosPorPagina = (!isset($_GET['resultados']))?30:$_GET['resultados'];
    $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*$resultadosPorPagina);

    $orderFilters = array(
      'recomendaciones' => 'lugares.total_recomendaciones desc',
      'utiles' => 'lugares.utiles desc',
      'alfabetico' => 'lugares.nombre asc',
      'recomendaciones' => 'ranking desc'
    );

    if(isset($_GET['orden'])){
      if(isset($orderFilters[$_GET['orden']])){
        $order = "ORDER BY " . $orderFilters[$_GET['orden']];
      }
    }

    $term = $_GET['q'];
    $termSlug = $fn->generarSlug($term);
    $termArray = preg_split('/\s/', $term);

    $fields = "STRAIGHT_JOIN lugares.id, lugares.nombre as nombre_lugar, lugares.slug, lugares.calle, lugares.numero, lugares.estrellas, lugares.precio, lugares.total_recomendaciones, lugares.fecha_ultima_recomendacion, lugares.utiles, lugares.visitas, (lugares.estrellas*6 + lugares.utiles + lugares.total_recomendaciones*2) as ranking, group_concat(DISTINCT categorias.nombre) as categorias_nombre, group_concat(DISTINCT categorias.slug) as categorias_slug, categorias.slug, categorias.nombre, group_concat(DISTINCT subcategoria.slug) as subcategoria_slug, subcategoria.slug";   

    $noCategorias = false;
    $filterCat = false;
    $filterSubCat = false;
    $filterComuna = false;
    $filterSector = false;
    $filterPrecio = false;

    if(!isset($_GET['o']) || $_GET['o'] != 'no'){
      $mappedCategorias = array(
        'bar' => 'bares-pubs'
      );

      $mappedSubCategorias = array(
        'comida mexicana' => 'comida-mexicana'
      );
    }

    $categoria = ((isset($_GET['categoria']))?$_GET['categoria']:NULL);
    $subcategoria = ((isset($_GET['subcategoria']))?$_GET['subcategoria']:NULL);
    $sector = ((isset($_GET['sector']))?$_GET['sector']:NULL);
    $comuna = ((isset($_GET['comuna']))?$_GET['comuna']:NULL);
    $override = ((isset($_GET['o']))?$_GET['o']:NULL);

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
    if(isset($mappedCategorias[$term])){
      $termSlug = $mappedCategorias[$term];
      $categoria = $mappedCategorias[$term];
    }
    $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Categoria u WHERE u.slug = '$termSlug'");
    $esCategoria = $q->getSingleScalarResult();
 
    if($esCategoria[0] == 1){
        $term = 'somethingneverfound';
        $termArray = array();
        $noCategorias = true;
        $_GET['categoria'] = $termSlug;
        $categoria = $termSlug;
        $subcategoria = 'none';

      $this->get('session')->setFlash('buscar_flash','Creemos que estas buscando una Categoria!, asi que te enviamos a esta!.<br/>Si quieres intentar tu busqueda y que no adivinemos, haz click aqui: <a href="?o=no">AAAAA</a>');
    }else{
      if(isset($mappedSubCategorias[$term])){
        $termSlug = $mappedSubCategorias[$term];
        $categoria = $mappedSubCategorias[$term];
      }

      //Vemos si no es igual a una subcategoria...
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\SubCategoria u WHERE u.slug = '$termSlug'");
      $esSubCategoria = $q->getOneOrNullResult();

      if($esSubCategoria != null){
        $term = 'somethingneverfound';
        $termArray = array();
        $noCategorias = true;

        //Subcategoria pasa a ser condicion
        $_GET['subcategoria'] = $termSlug;
        $subcategoria = $termSlug;

        //Categoria pasa a ser el termino buscado
        $termSlug = $esSubCategoria->getCategoria()->getSlug();
        $categoria = $termSlug;
        $_GET['categoria'] = $termSlug;

        $this->get('session')->setFlash('buscar_flash','Creemos que estas buscando una SubCategoria!, asi que te enviamos a esta!.<br/>Si quieres intentar tu busqueda y que no adivinemos, haz click aqui: <a href="?o=no">AAAAA</a>');
      }
    }

    if($noCategorias == false){
      //Buscamos por Categorias
      $unionQuery[] = "(SELECT SQL_CALC_FOUND_ROWS $fields 
                        FROM lugares

                        JOIN comuna
                        ON lugares.comuna_id = comuna.id

                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id

                        JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id

                        WHERE categorias.slug LIKE '%$termSlug%' $filterSector $filterComuna
                        
                        GROUP BY lugares.id
                        $order
                        LIMIT 3000)";

      //Buscamos por Slug
      $unionQuery[] = "(SELECT $fields 
                        FROM lugares 

                        JOIN comuna
                        ON lugares.comuna_id = comuna.id

                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id

                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        LEFT JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id

                        WHERE lugares.slug like '%$termSlug%' $filterSector $filterComuna GROUP BY lugares.id $order LIMIT 3000)";
      
      foreach($termArray as $key => $value){
        $unionQuery[] = "(SELECT $fields 
                          FROM lugares

                          JOIN comuna
                          ON lugares.comuna_id = comuna.id

                          LEFT JOIN sector
                          ON lugares.sector_id = sector.id

                          JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id

                          JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id

                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id

                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id

                          WHERE lugares.slug LIKE '%$value%' $filterSector $filterComuna GROUP BY lugares.id $order LIMIT 3000)";
      }

      //Total de Categorias Generadas por el Slug
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             JOIN comuna
                             ON lugares.comuna_id = comuna.id

                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id

                             JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE lugares.slug LIKE '%$termSlug%' $filterSector $filterComuna)";

      foreach($termArray as $key => $value){
        $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                               FROM lugares

                               JOIN comuna
                               ON lugares.comuna_id = comuna.id

                               LEFT JOIN sector
                               ON lugares.sector_id = sector.id

                               JOIN categoria_lugar
                               ON categoria_lugar.lugar_id = lugares.id

                               JOIN categorias
                               ON categoria_lugar.categoria_id = categorias.id

                               WHERE lugares.slug LIKE '%$value%' $filterSector $filterComuna)";
      }

      //Total de Categorias Generadas por la Categoria
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             JOIN comuna
                             ON lugares.comuna_id = comuna.id

                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id

                             JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE categorias.slug LIKE '%$termSlug%' $filterSector $filterComuna)";
                             
 
      //Total de Categorias por Calle
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             JOIN comuna
                             ON lugares.comuna_id = comuna.id

                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id

                             JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE lugares.calle LIKE '%$term%' $filterSector $filterComuna)";

    }else{

      if(isset($_GET['categoria'])){
        $filterCat = ' AND categorias.slug = "' . $categoria . '"';      
      }

      if(isset($_GET['subcategoria'])){
        $filterSubCat = ' AND subcategoria.slug = "' . $subcategoria . '"';      
      }

      if(isset($_GET['precio'])){
        $filterPrecio = ' AND lugares.precio <= "' . $_GET['precio'] . '"';      
      }

      //Buscamos por Categorias
      $unionQuery[] = "(SELECT SQL_CALC_FOUND_ROWS $fields 
                        FROM lugares

                        JOIN comuna
                        ON lugares.comuna_id = comuna.id

                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id

                        JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id

                        LEFT JOIN caracteristica_lugar
                        ON caracteristica_lugar.lugar_id = lugares.id

                        LEFT JOIN caracteristica
                        ON caracteristica.id = caracteristica_lugar.caracteristica_id
  
                        WHERE categorias.slug LIKE '%$termSlug%'
                        AND categorias.slug = '$categoria'
                        $filterSector $filterComuna $filterSubCat $filterPrecio
                        
                        GROUP BY lugares.id
                        $order
                        LIMIT 3000)";


      //Buscamos por Slug
      $unionQuery[] = "(SELECT $fields 
                        FROM lugares

                        JOIN comuna
                        ON lugares.comuna_id = comuna.id

                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id

                        JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id  

                        LEFT JOIN caracteristica_lugar
                        ON caracteristica_lugar.lugar_id = lugares.id

                        LEFT JOIN caracteristica
                        ON caracteristica.id = caracteristica_lugar.caracteristica_id
                         
                        WHERE lugares.slug like '%$termSlug%' 
                        $filterComuna $filterSector $filterCat $filterSubCat $filterPrecio
                        GROUP BY lugares.id $order LIMIT 3000)";

      foreach($termArray as $key => $value){
          $unionQuery[] = "(SELECT $fields 
                            FROM lugares

                            JOIN comuna
                            ON lugares.comuna_id = comuna.id

                            LEFT JOIN sector
                            ON lugares.sector_id = sector.id

                            JOIN categoria_lugar
                            ON categoria_lugar.lugar_id = lugares.id

                            JOIN categorias
                            ON categoria_lugar.categoria_id = categorias.id

                            LEFT JOIN subcategoria_lugar
                            ON subcategoria_lugar.lugar_id = lugares.id

                            LEFT JOIN subcategoria
                            ON subcategoria_lugar.subcategoria_id = subcategoria.id

                            LEFT JOIN caracteristica_lugar
                            ON caracteristica_lugar.lugar_id = lugares.id

                            LEFT JOIN caracteristica
                            ON caracteristica.id = caracteristica_lugar.caracteristica_id

                            WHERE lugares.slug LIKE '%$value%' 
                            $filterComuna $filterSector $filterCat $filterSubCat  $filterPrecio 
                            GROUP BY lugares.id $order LIMIT 3000)";
      }

      //Total de Categorias generadas por el Slug
      $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                FROM lugares

                                JOIN comuna
                                ON lugares.comuna_id = comuna.id

                                LEFT JOIN sector
                                ON lugares.sector_id = sector.id

                                LEFT JOIN categoria_lugar
                                ON categoria_lugar.lugar_id = lugares.id

                                LEFT JOIN categorias
                                ON categoria_lugar.categoria_id = categorias.id

                                JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                WHERE lugares.slug LIKE '%$termSlug%' 
                                $filterCat $filterComuna $filterSector)";


      foreach($termArray as $key => $value){
        $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                  FROM lugares

                                  JOIN comuna
                                  ON lugares.comuna_id = comuna.id

                                  LEFT JOIN sector
                                  ON lugares.sector_id = sector.id

                                  LEFT JOIN categoria_lugar
                                  ON categoria_lugar.lugar_id = lugares.id

                                  LEFT JOIN categorias
                                  ON categoria_lugar.categoria_id = categorias.id

                                  JOIN subcategoria_lugar
                                  ON subcategoria_lugar.lugar_id = lugares.id

                                  JOIN subcategoria
                                  ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                  WHERE lugares.slug LIKE '%$value%' 
                                  $filterCat $filterSector $filterComuna)";
      }

      //Total de Categorias generadas por la Subcategoria
      $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                FROM lugares

                                JOIN comuna
                                ON comuna.id = lugares.comuna_id 
                          
                                LEFT JOIN sector
                                ON sector.id = lugares.sector_id

                                JOIN categoria_lugar
                                ON categoria_lugar.lugar_id = lugares.id

                                JOIN categorias
                                ON categoria_lugar.categoria_id = categorias.id

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                LEFT JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id  

                                WHERE categorias.slug LIKE '%$termSlug%'
                                $filterCat $filterSector $filterComuna)";
                
      //Total de Calles en Subcategorias
      $totalSubCategorias[] = "(SELECT lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug
                                FROM lugares

                                JOIN comuna
                                ON comuna.id = lugares.comuna_id 
                          
                                LEFT JOIN sector
                                ON sector.id = lugares.sector_id

                                JOIN categoria_lugar
                                ON categoria_lugar.lugar_id = lugares.id

                                JOIN categorias
                                ON categoria_lugar.categoria_id = categorias.id

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                LEFT JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id  

                                WHERE lugares.calle like '%$termSlug%' 
                                $filterCat $filterSector $filterComuna)";
      /*
      * Totales de Sectores
      */
      $totalCaracteristicas[] = "(SELECT lugares.id as lid, caracteristica.id as sid, caracteristica.nombre, caracteristica.slug
                                  FROM lugares

                                  LEFT JOIN caracteristica_lugar
                                  ON caracteristica_lugar.lugar_id = lugares.id

                                  JOIN caracteristica
                                  ON caracteristica.id = caracteristica_lugar.caracteristica_id

                                  WHERE lugares.slug LIKE '%$termSlug%')";

      //Total de Categorias Generadas por la Categoria
      $totalCaracteristicas[] = "(SELECT lugares.id as lid, caracteristica.id as sid, caracteristica.nombre, caracteristica.slug
                           FROM lugares
                
                           LEFT JOIN caracteristica_lugar
                           ON caracteristica_lugar.lugar_id = lugares.id

                           JOIN caracteristica
                           ON caracteristica.id = caracteristica_lugar.caracteristica_id

                           JOIN categoria_lugar
                           ON categoria_lugar.lugar_id = lugares.id

                           JOIN categorias
                           ON categoria_lugar.categoria_id = categorias.id

                           WHERE categorias.slug LIKE '%$termSlug%' )";

      foreach($termArray as $key => $value){
        $totalCaracteristicas[] = "(SELECT lugares.id as lid, caracteristica.id as sid, caracteristica.nombre, caracteristica.slug
                             FROM lugares

                             LEFT JOIN caracteristica_lugar
                             ON caracteristica_lugar.lugar_id = lugares.id

                             JOIN caracteristica
                             ON caracteristica.id = caracteristica_lugar.caracteristica_id

                             WHERE lugares.slug LIKE '%$value%')";
      }

        $totalCaracteristicas[] = "(SELECT lugares.id as lid, caracteristica.id as sid, caracteristica.nombre, caracteristica.slug
                             FROM lugares

                             LEFT JOIN caracteristica_lugar
                             ON caracteristica_lugar.lugar_id = lugares.id

                             JOIN caracteristica
                             ON caracteristica.id = caracteristica_lugar.caracteristica_id

                             JOIN sector
                             ON sector.id = lugares.sector_id

                             WHERE lugares.calle LIKE '%$term%')";
    } 

    //Query por Calles
    $unionQuery[] = "(SELECT $fields
                      FROM lugares

                      JOIN comuna
                      ON comuna.id = lugares.comuna_id 
                
                      LEFT JOIN sector
                      ON sector.id = lugares.sector_id

                      JOIN categoria_lugar
                      ON categoria_lugar.lugar_id = lugares.id

                      JOIN categorias
                      ON categoria_lugar.categoria_id = categorias.id

                      LEFT JOIN subcategoria_lugar
                      ON subcategoria_lugar.lugar_id = lugares.id

                      LEFT JOIN subcategoria
                      ON subcategoria_lugar.subcategoria_id = subcategoria.id   
          
                      JOIN caracteristica_lugar
                      ON caracteristica_lugar.lugar_id = lugares.id

                      LEFT JOIN caracteristica
                      ON caracteristica.id = caracteristica_lugar.caracteristica_id

                      WHERE lugares.calle like '%$term%' 
                      $filterComuna $filterSector $filterCat $filterSubCat GROUP BY lugares.id $order LIMIT 3000)";

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

                           JOIN categoria_lugar
                           ON categoria_lugar.lugar_id = lugares.id

                           JOIN categorias
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
      * Totales de Comunas
      */

      //Total de Categorias Generadas por la Categoria
      $totalComunas[] = "(SELECT lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug
                          FROM lugares

                          JOIN comuna
                          ON comuna.id = lugares.comuna_id 

                          JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id

                          JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id

                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id

                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id  

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

        //Generacion y Ejecucion de Query
        $totalCaracteristicas = join(" UNION ", $totalCaracteristicas);
        $totalCaracteristicas =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalCaracteristicas . ") sq group by sid order by total desc";
        $results['totalPorCaracteristica'] = $this->getDoctrine()->getConnection()->fetchAll($totalCaracteristicas);
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
      $unionQuery .= " LIMIT $resultadosPorPagina OFFSET $offset";  
      $arr['lugares'] = $this->getDoctrine()->getConnection()->fetchAll($unionQuery);
      $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");   
    }


    //Sacamos los otros datos de los 30 resultados que corresponden
    foreach($arr['lugares'] as $key => $lugar){
      $arr['lugares'][$key]['categorias_nombre'] = explode(',', $lugar['categorias_nombre']);
      $arr['lugares'][$key]['categorias_slug'] = explode(',', $lugar['categorias_slug']);

      foreach($arr['lugares'][$key]['categorias_nombre'] as $i => $value){
        $catPath = $this->generateUrl('_lugar', array('slug' => $arr['lugares'][$key]['categorias_slug'][$i]));
        $arr['lugares'][$key]['categorias_nombre'][$i] = "<a href='$catPath'>".$value."</a>";
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

    //Re-seteamos q
    $term = $_GET['q'];

    $params = array(
      'q' => $term,
      'slug' => $slug,
      'categoria' => $categoria,
      'subcategoria' => $subcategoria,
      'sector' => $sector,
      'comuna' => $comuna,
      'resultados' => $resultadosPorPagina,
      'o' => $override
    );

    $paginacion = $fn->paginacion( $resultSetSize[0]['rows'], $resultadosPorPagina, '_buscar', $params, $this->get('router') );



    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array(
      'term' => $term,
      'slug' => $slug,
      'lugares' => $arr['lugares'],
      'total' => $resultSetSize[0]['rows'],
      'results' => $results,
      'categoria' => $categoria,
      'subcategoria' => $subcategoria,
      'sector' => $sector,
      'comuna' => $comuna,
      'query' => $_GET,
      'paginacion' => $paginacion,
      'override' => $override,
      'total' => $resultSetSize[0]['rows'],
      'resultados' => ($resultadosPorPagina == 30)?null:$resultadosPorPagina
    ));
  }
}
