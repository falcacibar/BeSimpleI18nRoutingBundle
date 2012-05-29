<?php

namespace Loogares\PhoneBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function listadoCategoriasAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $tlr = $em->getRepository("LoogaresLugarBundle:TipoCategoria");    
        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\TipoCategoria u ORDER BY u.prioridad_web asc");
        $tipoCategoria = $q->getResult();
        $ciudad = $this->get('session')->get('ciudad');
        $idCiudad = $ciudad['id'];
        
        foreach($tipoCategoria as $key => $value){
            $id = $value->getId();
            $buff = $this->getDoctrine()
            ->getConnection()->fetchAll("SELECT count(categorias.id) as total, categorias.nombre as categoria_nombre, categorias.slug as categoria_slug, tipo_categoria.nombre
                                         FROM lugares

                                         JOIN comuna
                                         ON comuna.id = lugares.comuna_id

                                         LEFT JOIN categoria_lugar
                                         ON categoria_lugar.lugar_id = lugares.id

                                         JOIN categorias
                                         ON categorias.id = categoria_lugar.categoria_id

                                         LEFT JOIN tipo_categoria
                                         ON tipo_categoria.id = categorias.tipo_categoria_id

                                         WHERE tipo_categoria.id = $id AND comuna.ciudad_id = $idCiudad AND lugares.estado_id != 3

                                         GROUP BY categorias.id
                                         ORDER BY tipo_categoria.id, categorias.nombre asc");
            $data[]['tipo'] = $value->getNombre();
            $data[sizeOf($data)-1]['categorias'] = $buff;
        }

        $json = json_encode($data);

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
    }

    public function lugaresPorCategoriaAction($categoria = 'todas', $offset = 1, $latitude = null, $longitude = null){
        if($categoria == 'todas'){ $categoria = null; }

        $em = $this->getDoctrine()->getEntityManager();
        $offset--; $offset = $offset*20;
        $cr = $em->getRepository("LoogaresLugarBundle:Categoria");
        $data = array();

        if($latitude != null && $longitude != null){
            $geoloc = ",( 6371 * acos( cos( radians($latitude) ) * cos( radians( l.mapx ) ) * cos( radians( l.mapy ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( l.mapx ) ) ) ) AS distance";
            $geolocCondition = "HAVING distance < 1";
            $orderBy = "ORDER BY distance ASC";
        }else{
            $geoloc = null;
            $geolocCondition = null;
            $orderBy = "ORDER BY ranking ASC";
        }
        
        if($categoria == null){
            $lugares = $this->getDoctrine()->getConnection()
        ->fetchAll(
            "SELECT SQL_CALC_FOUND_ROWS l.nombre, l.slug, l.estrellas, l.calle, l.mapx, l.mapy, l.numero,
             (l.estrellas*6 + l.utiles + l.total_recomendaciones*2) AS ranking, il.imagen_full as imagen_full,
             cat.tipo_categoria_id as tipo_categoria, cat.nombre as categoria,
             (SELECT count(r.id) FROM recomendacion AS r WHERE r.lugar_id = l.id AND r.estado_id != 3) as total_recomendaciones
             $geoloc
             FROM  categoria_lugar cl

             JOIN lugares l
             ON l.id = cl.lugar_id

	         LEFT JOIN imagenes_lugar il
             ON il.lugar_id = l.id
             AND il.id = (SELECT max(il.id) FROM imagenes_lugar AS il WHERE il.lugar_id = l.id AND il.estado_id = 2)

             LEFT JOIN categorias cat
             ON cat.id = cl.categoria_id

             JOIN comuna c
             ON c.id = l.comuna_id 

             WHERE l.estado_id != 3 AND c.ciudad_id = 1
             GROUP BY l.id
             $geolocCondition
             $orderBy
             LIMIT 20 OFFSET $offset");
        }else{
            $categoria = $cr->findBySlug($categoria);
            $categoriaId = $categoria[0]->getId();
            $lugares = $this->getDoctrine()->getConnection()->fetchAll(
            "SELECT SQL_CALC_FOUND_ROWS l.nombre, l.slug, l.estrellas, l.calle, l.mapx, l.mapy, l.numero,
             (l.estrellas*6 + l.utiles + l.total_recomendaciones*2) AS ranking, il.imagen_full as imagen_full,
             cat.tipo_categoria_id as tipo_categoria, cat.nombre as categoria,
             (SELECT count(r.id) FROM recomendacion AS r WHERE r.lugar_id = l.id AND r.estado_id != 3) as total_recomendaciones
             $geoloc
             FROM  categoria_lugar cl

             JOIN lugares l
             ON l.id = cl.lugar_id

             LEFT JOIN imagenes_lugar il
             ON il.lugar_id = l.id
             AND il.id = (SELECT max(il.id) FROM imagenes_lugar AS il WHERE il.lugar_id = l.id AND il.estado_id = 2) 

             LEFT JOIN categorias cat
             ON cat.id = cl.categoria_id
 
             JOIN comuna c
             ON c.id = l.comuna_id 

             WHERE l.estado_id != 3 AND c.ciudad_id = 1
             AND cl.categoria_id = $categoriaId
             
             GROUP BY l.id
             $geolocCondition
             $orderBy
             LIMIT 20 OFFSET $offset");
        }

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        for($i=sizeOf($lugares)-1;$i>=0;$i--){
            $data[]['nombre'] = $lugares[$i]['nombre'];
            $data[sizeOf($data)-1]['slug'] = $lugares[$i]['slug'];
            $data[sizeOf($data)-1]['estrellas'] = $lugares[$i]['estrellas'];
            $data[sizeOf($data)-1]['calle'] = $lugares[$i]['calle'];
            $data[sizeOf($data)-1]['mapx'] = $lugares[$i]['mapx'];
            $data[sizeOf($data)-1]['mapy'] = $lugares[$i]['mapy'];
            $data[sizeOf($data)-1]['numero'] = $lugares[$i]['numero'];
            
            if($lugares[$i]['distance'] < 1){
                $data[sizeOf($data)-1]['distance'] = round($lugares[$i]['distance'] * 1000);
            }else{
                $data[sizeOf($data)-1]['distance'] = round($lugares[$i]['distance']);
            }

            $data[sizeOf($data)-1]['categoria'] = $lugares[$i]['categoria'];

            $data[sizeOf($data)-1]['ranking'] = $lugares[$i]['ranking'];
            $data[sizeOf($data)-1]['tipoCategoria'] = $lugares[$i]['tipo_categoria'];

            $imagenes = $lugares[$i]['imagen_full'];

            if($lugares[$i]['imagen_full'] != '' && file_exists('assets/images/lugares/'.$lugares[$i]['imagen_full'])){
                if(!file_exists('assets/media/cache/medium_lugar/assets/images/lugares/'.$lugares[$i]['imagen_full'])){
                    $this->get('imagine.controller')->filter('assets/images/lugares/'.$lugares[$i]['imagen_full'], "medium_lugar");
                }
                $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/medium_lugar/assets/images/lugares/'.$lugares[$i]['imagen_full'];
            }else{
                if(!file_exists('assets/media/cache/medium_lugar/assets/images/lugares/default.gif')){
                    $this->get('imagine.controller')->filter('assets/images/lugares/default.gif', "medium_lugar");
                }
                $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/medium_lugar/assets/images/lugares/default.gif';
            }

            $data[sizeOf($data)-1]['totalRecomendaciones'] = $lugares[$i]['total_recomendaciones'];
        }

        $data = array_reverse($data);

        $json = json_encode(array_reverse(array('lugares'=>$data, 'total' => $resultSetSize[0]['rows'])));

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));  
    }

    public function lugarAction($slug){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);
        ($lugar->getComuna()->getCiudad()->getPais()->getCodigoArea() != '')?$codigo = $lugar->getComuna()->getCiudad()->getPais()->getCodigoArea().' ':null;
        $recomendaciones = $lugar->getRecomendacionesActivas();

        for($i=0;$i<sizeOf($recomendaciones);$i++){
            $usuario = $recomendaciones[$i]->getUsuario();
            $nombre = $usuario->getNombre() . " " . $usuario->getApellido();
            if($nombre == ' '){
                $data['recomendaciones'][$i]['usuario'] = $usuario->getSlug();
            }else{
                $data['recomendaciones'][$i]['usuario'] = $nombre;
            }
            $data['recomendaciones'][$i]['estrellas'] = $recomendaciones[$i]->getEstrellas();
            $data['recomendaciones'][$i]['fechaCreacion'] = $recomendaciones[$i]->getFechaCreacion()->format('y-m-d');
            $data['recomendaciones'][$i]['texto'] = $recomendaciones[$i]->getTexto();
        }

        $data['nombre'] = $lugar->getNombre();
        $data['slug'] = $lugar->getSlug();
        $data['mapx'] = $lugar->getMapx();
        $data['mapy'] = $lugar->getMapy();
        $data['direccion'] = $lugar->getCalle() . " - " . $lugar->getNumero();
        $data['telefonos'] = array();
        if($lugar->getTelefono1() != '') $data['telefonos'][] = $codigo . $lugar->getTelefono1();
        if($lugar->getTelefono2() != '') $data['telefonos'][] = $codigo . $lugar->getTelefono2();
        if($lugar->getTelefono3() != '') $data['telefonos'][] = $codigo . $lugar->getTelefono3();
        $data['estrellas'] = $lugar->getEstrellas();
        $data['categoriaPrincipal'] = $lugar->getCategoriaPrincipal()->getNombre();
        $data['totalRecomendaciones'] = sizeOf($recomendaciones);

        $imagenes = $lugar->getImagenesActivasLugar();
        if(sizeOf($imagenes) != 0 && file_exists('assets/images/lugares/'.$imagenes[sizeOf($imagenes)-1]->getImagenFull())){
            if(!file_exists('assets/media/cache/medium_lugar/assets/images/lugares/'.$imagenes[sizeOf($imagenes)-1]->getImagenFull())){
                $this->get('imagine.controller')->filter('assets/images/lugares/'.$imagenes[sizeOf($imagenes)-1]->getImagenFull(), "medium_lugar");
            }
            $data['imagen130'] = 'assets/media/cache/medium_lugar/assets/images/lugares/'.$imagenes[sizeOf($imagenes)-1]->getImagenFull();
        }else{
            if(!file_exists('assets/media/cache/medium_lugar/assets/images/lugares/default.gif')){
                $this->get('imagine.controller')->filter('assets/images/lugares/default.gif', "medium_lugar");
            }
            $data['imagen130'] = 'assets/media/cache/medium_lugar/assets/images/lugares/default.gif';
        }

        $json = json_encode($data);

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
    }

    public function quickSearchAction($term){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $data = array();

        $q = $em->createQuery("SELECT l, (l.estrellas*6 + l.utiles + l.total_recomendaciones*2) as ranking FROM Loogares\LugarBundle\Entity\Lugar l WHERE l.nombre LIKE ?1 ORDER BY ranking DESC");
        $q->setParameter(1, "%$term%");
        $q->setMaxResults(5);
        $results = $q->getResult(); 

        for($i=0;$i<sizeOf($results);$i++){
            $data[]['title'] = $results[$i][0]->getNombre();
            $data[sizeOf($data)-1]['slug'] = $results[$i][0]->getSlug();
        }

        $json = json_encode($data);

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));       
    }

public function searchAction(Request $request, $slug, $subcategoria = null, $categoria = null, $sector = null, $comuna = null){
    $fn = $this->get('fn');
    $em = $this->getDoctrine()->getEntityManager();
    $mostrarPrecio = null;

    $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
    $ciudad = $cr->findOneBySlug($slug);
    $ciudadArray = array();
    $ciudadArray['id'] = $ciudad->getId();
    $ciudadArray['nombre'] = $ciudad->getNombre();
    $ciudadArray['slug'] = $ciudad->getSlug();
    $ciudadArray['pais']['id'] = $ciudad->getPais()->getId();
    $ciudadArray['pais']['nombre'] = $ciudad->getPais()->getNombre();
    $ciudadArray['pais']['slug'] = $ciudad->getPais()->getSlug();
    
    $this->get('session')->setLocale($ciudad->getPais()->getLocale());
    $this->get('session')->set('ciudad',$ciudadArray);

    $idCiudad = $ciudad->getId();
    $lr = $em->getRepository('LoogaresLugarBundle:Lugar');

    foreach($_GET as $key => $value){
      // Estandarizamos caracteres de $string  
      $string = trim($value);
   
      $string = str_replace(
          array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
          array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
          $string
      );
   
      $string = str_replace(
          array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
          array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
          $string
      );
   
      $string = str_replace(
          array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
          array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
          $string
      );
   
      $string = str_replace(
          array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
          array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
          $string
      );
   
      $string = str_replace(
          array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
          array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
          $string
      );
   
      $string = str_replace(
          array('ñ', 'Ñ', 'ç', 'Ç'),
          array('n', 'N', 'c', 'C',),
          $string
      );
   
      // Esta parte se encarga de eliminar cualquier caracter extraño
      $string = str_replace(
          array("\\", "¨", "º", "~",".",
               "#", "@", "|", "!", "\"",
               "·", "$", "%", "&", "/",
               "(", ")", "?", "'", "¡",
               "¿", "[", "^", "`", "]",
               "+", "}", "{", "¨", "´",
               ">", "<", ";", ":"),
          '',
          $string
      );
       $_GET[$key] = htmlspecialchars($string, ENT_QUOTES);
    }
    
    $ciudad_repo = $em->getRepository('LoogaresExtraBundle:Ciudad');
    $sector_repo = $em->getRepository('LoogaresExtraBundle:Sector');
    $comuna_repo = $em->getRepository('LoogaresExtraBundle:Comuna');

    $categoria_repo = $em->getRepository('LoogaresLugarBundle:Categoria');
    $subcat_repo = $em->getRepository('LoogaresLugarBundle:SubCategoria');

    $categoriaResult = ((isset($_GET['categoria']))?$categoria_repo->findOneBySlug($_GET['categoria']):NULL);
    $subcategoriaResult = ((isset($_GET['subcategoria']))?$subcat_repo->findOneBySlug($_GET['subcategoria']):NULL);
    $sectorResult = ((isset($_GET['sector']))?$sector_repo->findOneBySlug($_GET['sector']):NULL);
    $comunaResult = ((isset($_GET['comuna']))?$comuna_repo->findOneBySlug($_GET['comuna']):NULL);
    $ciudadResult = $ciudad_repo->findOneBySlug($slug);
    $override = ((isset($_GET['o']))?$_GET['o']:NULL);

    $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
    $resultadosPorPagina = (!isset($_GET['resultados']))?20:$_GET['resultados'];
    $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*$resultadosPorPagina);

    $term = $_GET['q'];
    $termSlug = $fn->generarSlug($term);
    $termArray = preg_split('/\s/', $term);

    $fields = "STRAIGHT_JOIN lugares.mapx, lugares.mapy, lugares.id, lugares.nombre as nombre_lugar, lugares.slug as lugar_slug, lugares.calle, lugares.numero, lugares.estrellas, lugares.precio, lugares.total_recomendaciones, lugares.fecha_ultima_recomendacion, lugares.utiles, lugares.visitas, (lugares.estrellas*6 + lugares.utiles + lugares.total_recomendaciones*2+lugares.visitas*0) as ranking, categorias.slug, categorias.nombre, ( select max(recomendacion.fecha_creacion) from recomendacion where recomendacion.lugar_id = lugares.id and recomendacion.estado_id != 3 ) as ultima_recomendacion";   

    $noCategorias = false;
    $filterCat = false;
    $filterSubCat = false;
    $filterComuna = false;
    $filterSector = false;
    $filterPrecio = false;
    $filterCaracteristica = false;
    $filterCiudad = " AND comuna.ciudad_id = $idCiudad";
    $filterCiudadSector = " AND sector.ciudad_id = $idCiudad";

    if(!isset($_GET['o']) || $_GET['o'] != 'no'){
      $mappedCategorias = array(
        'bar' => 'bares-pubs'
      );

      $mappedSubCategorias = array(
        'comida mexicana' => 'comida-mexicana'
      );
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
    if(isset($mappedCategorias[$term])){
      $termSlug = $mappedCategorias[$term];
      $categoria = $mappedCategorias[$term];
    }

    $q = $em->createQuery("SELECT count(u.id) FROM Loogares\LugarBundle\Entity\Categoria u WHERE u.slug = '$termSlug'");
    $esCategoria = $q->getSingleScalarResult();
 
    if($esCategoria[0] == 1 && (!isset($_GET['o']) || $_GET['o'] != 'no')){
        $_GET['orden'] = (isset($_GET['orden']))?$_GET['orden']:'recomendaciones';
        $term = 'somethingneverfound';
        $termArray = array();
        $noCategorias = true;
        $_GET['categoria'] = $termSlug;
        $categoriaResult = ((isset($_GET['categoria']))?$categoria_repo->findOneBySlug($_GET['categoria']):NULL);
        $categoria = $categoriaResult->getSlug();

    }else if((!isset($_GET['o']) || $_GET['o'] != 'no')){
      if(isset($mappedSubCategorias[$term])){
        $termSlug = $mappedSubCategorias[$term];
        $categoria = $mappedSubCategorias[$term];
      }

      //Vemos si no es igual a una subcategoria...
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\SubCategoria u WHERE u.slug = '$termSlug'");
      $esSubCategoria = $q->getOneOrNullResult();

      if($esSubCategoria != null){
        $_GET['orden'] = (isset($_GET['orden']))?$_GET['orden']:'recomendaciones';
        $term = 'somethingneverfound';
        $termArray = array();
        $noCategorias = true;


        //Subcategoria pasa a ser condicion
        $_GET['subcategoria'] = $termSlug;
        $subcategoriaResult = ((isset($_GET['subcategoria']))?$subcat_repo->findOneBySlug($_GET['subcategoria']):NULL);
        $categoriaResult = $subcategoriaResult->getCategoria();

        //Categoria pasa a ser el termino buscado
        $termSlug = $esSubCategoria->getCategoria()->getSlug();
        $_GET['categoria'] = $termSlug;
      }
    }

    $orderFilters = array(
      'recomendaciones' => 'lugares.total_recomendaciones desc',
      'alfabetico' => 'lugares.nombre asc',
      'recomendaciones' => 'ranking desc',
      'ultimas_recomendaciones' => 'ultima_recomendacion desc',
      'mas_recomendados' => 'lugares.total_recomendaciones desc'
    );

    if(isset($_GET['orden'])){
      if(isset($orderFilters[$_GET['orden']])){
        $order = "ORDER BY " . $orderFilters[$_GET['orden']];
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

                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id

                        WHERE categorias.slug LIKE '%$termSlug%' 
                        $filterSector $filterComuna $filterCiudad
                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)

                        GROUP BY lugares.id $order LIMIT 3000)";

      //Buscamos por Slug
      $unionQuery[] = "(SELECT $fields 
                        FROM lugares 

                        JOIN comuna
                        ON lugares.comuna_id = comuna.id

                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id

                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id

                        WHERE lugares.slug like '%$termSlug%' 
                        $filterSector $filterComuna  $filterCiudad
                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)

                        GROUP BY lugares.id $order LIMIT 3000)";
      
      foreach($termArray as $key => $value){
        $unionQuery[] = "(SELECT $fields 
                          FROM lugares

                          JOIN comuna
                          ON lugares.comuna_id = comuna.id

                          LEFT JOIN sector
                          ON lugares.sector_id = sector.id

                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id

                          JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id

                          LEFT JOIN subcategoria_lugar
                          ON subcategoria_lugar.lugar_id = lugares.id

                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id

                          WHERE lugares.slug LIKE '%$value%' 
                          $filterSector $filterComuna  $filterCiudad
                          AND (lugares.estado_id = 1 or lugares.estado_id = 2)

                          GROUP BY lugares.id $order LIMIT 3000)";
      }

      //Total de Categorias Generadas por el Slug
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             JOIN comuna
                             ON lugares.comuna_id = comuna.id

                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id

                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE lugares.slug LIKE '%$termSlug%' 
                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                             $filterSector $filterComuna $filterCiudad)";

      foreach($termArray as $key => $value){
        $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                               FROM lugares

                               JOIN comuna
                               ON lugares.comuna_id = comuna.id

                               LEFT JOIN sector
                               ON lugares.sector_id = sector.id

                               LEFT JOIN categoria_lugar
                               ON categoria_lugar.lugar_id = lugares.id

                               JOIN categorias
                               ON categoria_lugar.categoria_id = categorias.id

                               WHERE lugares.slug LIKE '%$value%' 
                               AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                               $filterSector $filterComuna $filterCiudad)";
      }

      //Total de Categorias Generadas por la Categoria
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             JOIN comuna
                             ON lugares.comuna_id = comuna.id

                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id

                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE categorias.slug LIKE '%$termSlug%' 
                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                             $filterSector $filterComuna $filterCiudad)";
                             
 
      //Total de Categorias por Calle
      $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
                             FROM lugares

                             JOIN comuna
                             ON lugares.comuna_id = comuna.id

                             LEFT JOIN sector
                             ON lugares.sector_id = sector.id

                             LEFT JOIN categoria_lugar
                             ON categoria_lugar.lugar_id = lugares.id

                             JOIN categorias
                             ON categoria_lugar.categoria_id = categorias.id

                             WHERE lugares.calle LIKE '%$term%' 
                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                             $filterSector $filterComuna $filterCiudad)";


    }else{

      $fields .= ", (
                      select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
                      left join caracteristica
                      on caracteristica_lugar.caracteristica_id = caracteristica.id
                      where caracteristica_lugar.lugar_id = lugares.id
                    ) as caracteristica_slug";

      if(isset($_GET['categoria'])){
        $filterCat = ' AND categorias.slug = "' . $_GET['categoria'] . '"';      
      }

      if(isset($_GET['subcategoria'])){
        $filterSubCat = ' AND subcategoria.slug = "' . $_GET['subcategoria'] . '"';      
      }

      if(isset($_GET['precio'])){
        $filterPrecio = ' AND lugares.precio = "' . $_GET['precio'] . '"';      
      }

      if(isset($_GET['caracteristicas'])){
        $caracteristicas = explode(',', $_GET['caracteristicas']);
        sort($caracteristicas);

        $filterCaracteristica = "HAVING caracteristica_slug LIKE '";
        foreach($caracteristicas as $caracteristica){
          $filterCaracteristica .= "%$caracteristica%";
        }
        $filterCaracteristica .= "'";
      }

      //Buscamos por Categorias
      $unionQuery[] = "(SELECT SQL_CALC_FOUND_ROWS $fields 
                        FROM lugares

                        JOIN comuna
                        ON lugares.comuna_id = comuna.id

                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id

                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id
  
                        WHERE categorias.slug LIKE '%$termSlug%'
                        AND categorias.slug = '".$_GET['categoria']."'
                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                        $filterSector $filterComuna $filterSubCat $filterPrecio $filterCiudad
                        
                        GROUP BY lugares.id
                        $filterCaracteristica
                        $order LIMIT 3000)";

      //Buscamos por Slug
      $unionQuery[] = "(SELECT $fields 
                        FROM lugares

                        JOIN comuna
                        ON lugares.comuna_id = comuna.id

                        LEFT JOIN sector
                        ON lugares.sector_id = sector.id

                        LEFT JOIN categoria_lugar
                        ON categoria_lugar.lugar_id = lugares.id

                        JOIN categorias
                        ON categoria_lugar.categoria_id = categorias.id

                        LEFT JOIN subcategoria_lugar
                        ON subcategoria_lugar.lugar_id = lugares.id

                        LEFT JOIN subcategoria
                        ON subcategoria_lugar.subcategoria_id = subcategoria.id  
                         
                        WHERE lugares.slug like '%$termSlug%' 
                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                        $filterComuna $filterSector $filterCat $filterSubCat $filterPrecio  $filterCiudad

                        GROUP BY lugares.id  
                        $filterCaracteristica
                        $order LIMIT 3000)";

      foreach($termArray as $key => $value){
          $unionQuery[] = "(SELECT $fields 
                            FROM lugares

                            JOIN comuna
                            ON lugares.comuna_id = comuna.id

                            LEFT JOIN sector
                            ON lugares.sector_id = sector.id

                            LEFT JOIN categoria_lugar
                            ON categoria_lugar.lugar_id = lugares.id

                            JOIN categorias
                            ON categoria_lugar.categoria_id = categorias.id

                            LEFT JOIN subcategoria_lugar
                            ON subcategoria_lugar.lugar_id = lugares.id

                            LEFT JOIN subcategoria
                            ON subcategoria_lugar.subcategoria_id = subcategoria.id

                            WHERE lugares.slug LIKE '%$value%' 
                            AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                            $filterComuna $filterSector $filterCat $filterSubCat  $filterPrecio  $filterCiudad

                            GROUP BY lugares.id 
                            $filterCaracteristica
                            $order LIMIT 3000)";
      }

      $subCategoriasFields = "distinct lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug,
                                (
                                  select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
                                  left join caracteristica
                                  on caracteristica_lugar.caracteristica_id = caracteristica.id
                                  where caracteristica_lugar.lugar_id = lid
                                ) as caracteristica_slug";

      //Total de Categorias generadas por el Slug
      $totalSubCategorias[] = "(SELECT $subCategoriasFields

                                FROM lugares

                                JOIN comuna
                                ON lugares.comuna_id = comuna.id

                                LEFT JOIN sector
                                ON lugares.sector_id = sector.id

                                LEFT JOIN categoria_lugar
                                ON categoria_lugar.lugar_id = lugares.id

                                JOIN categorias
                                ON categoria_lugar.categoria_id = categorias.id

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                WHERE lugares.slug LIKE '%$termSlug%' 
                                AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                $filterCat $filterComuna $filterSector  $filterCiudad
                                $filterCaracteristica)";


      foreach($termArray as $key => $value){
        $totalSubCategorias[] = "(SELECT $subCategoriasFields

                                  FROM lugares

                                  JOIN comuna
                                  ON lugares.comuna_id = comuna.id

                                  LEFT JOIN sector
                                  ON lugares.sector_id = sector.id

                                  LEFT JOIN categoria_lugar
                                  ON categoria_lugar.lugar_id = lugares.id

                                  JOIN categorias
                                  ON categoria_lugar.categoria_id = categorias.id

                                  LEFT JOIN subcategoria_lugar
                                  ON subcategoria_lugar.lugar_id = lugares.id

                                  JOIN subcategoria
                                  ON subcategoria_lugar.subcategoria_id = subcategoria.id

                                  WHERE lugares.slug LIKE '%$value%' 
                                  AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                  $filterCat $filterSector $filterComuna $filterPrecio  $filterCiudad
                                  $filterCaracteristica)";
      }

      //Total de Categorias generadas por la Subcategoria
      $totalSubCategorias[] = "(SELECT $subCategoriasFields

                                FROM lugares

                                JOIN comuna
                                ON comuna.id = lugares.comuna_id 
                          
                                LEFT JOIN sector
                                ON sector.id = lugares.sector_id

                                LEFT JOIN categoria_lugar
                                ON categoria_lugar.lugar_id = lugares.id

                                JOIN categorias
                                ON categoria_lugar.categoria_id = categorias.id

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id  

                                WHERE categorias.slug LIKE '%$termSlug%'
                                AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                $filterCat $filterSector $filterComuna $filterPrecio  $filterCiudad
                                $filterCaracteristica)";
                
      //Total de Calles en Subcategorias
      $totalSubCategorias[] = "(SELECT $subCategoriasFields

                                FROM lugares

                                JOIN comuna
                                ON comuna.id = lugares.comuna_id 
                          
                                LEFT JOIN sector
                                ON sector.id = lugares.sector_id

                                LEFT JOIN categoria_lugar
                                ON categoria_lugar.lugar_id = lugares.id

                                JOIN categorias
                                ON categoria_lugar.categoria_id = categorias.id

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id  

                                WHERE lugares.calle like '%$termSlug%' 
                                AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                $filterCat $filterSector $filterComuna $filterPrecio $filterCiudad
                                $filterCaracteristica)";
      /*
      * Totales de Caracteristicas
      */

      $caracteristicasFields = "distinct lugares.id as lid, caracteristica.id as sid, caracteristica.nombre, caracteristica.slug,
                                (
                            select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
                            left join caracteristica
                            on caracteristica_lugar.caracteristica_id = caracteristica.id
                            where caracteristica_lugar.lugar_id = lid
                          ) as caracteristica_slug";

      $totalCaracteristicas[] = "(SELECT $caracteristicasFields

                                  FROM lugares

                                  JOIN comuna
                                  ON comuna.id = lugares.comuna_id 
                            
                                  LEFT JOIN sector
                                  ON sector.id = lugares.sector_id

                                  LEFT JOIN caracteristica_lugar
                                  ON caracteristica_lugar.lugar_id = lugares.id

                                  JOIN caracteristica
                                  ON caracteristica.id = caracteristica_lugar.caracteristica_id

                                  WHERE lugares.slug LIKE '%$termSlug%'
                                  AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                  $filterSector $filterComuna $filterPrecio $filterCiudad
                                  $filterCaracteristica)";

      //Total de Categorias Generadas por la Categoria
      $totalCaracteristicas[] = "(SELECT $caracteristicasFields

                                  FROM lugares

                                  JOIN comuna
                                  ON comuna.id = lugares.comuna_id 
                          
                                  LEFT JOIN sector
                                  ON sector.id = lugares.sector_id
                      
                                  LEFT JOIN caracteristica_lugar
                                  ON caracteristica_lugar.lugar_id = lugares.id
 
                                  JOIN caracteristica
                                  ON caracteristica.id = caracteristica_lugar.caracteristica_id

                                  LEFT JOIN categoria_lugar
                                  ON categoria_lugar.lugar_id = lugares.id

                                  JOIN categorias
                                  ON categoria_lugar.categoria_id = categorias.id

                                  WHERE categorias.slug LIKE '%$termSlug%'
                                  AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                  $filterSector $filterComuna $filterPrecio $filterCiudad
                                  $filterCaracteristica)";

      foreach($termArray as $key => $value){
        $totalCaracteristicas[] = "(SELECT $caracteristicasFields
                                   
                                    FROM lugares

                                    JOIN comuna
                                    ON comuna.id = lugares.comuna_id 
                            
                                    LEFT JOIN sector
                                    ON sector.id = lugares.sector_id

                                    LEFT JOIN caracteristica_lugar
                                    ON caracteristica_lugar.lugar_id = lugares.id

                                    JOIN caracteristica
                                    ON caracteristica.id = caracteristica_lugar.caracteristica_id

                                    WHERE lugares.slug LIKE '%$value%'
                                    AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                    $filterSector $filterComuna $filterPrecio $filterCiudad
                                    $filterCaracteristica)";
      }

        $totalCaracteristicas[] = "(SELECT $caracteristicasFields
                                   
                                    FROM lugares

                                    JOIN comuna
                                    ON comuna.id = lugares.comuna_id 
                              
                                    LEFT JOIN sector
                                    ON sector.id = lugares.sector_id

                                    LEFT JOIN caracteristica_lugar
                                    ON caracteristica_lugar.lugar_id = lugares.id

                                    JOIN caracteristica
                                    ON caracteristica.id = caracteristica_lugar.caracteristica_id

                                    WHERE lugares.calle LIKE '%$term%'
                                    AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                    $filterSector $filterComuna $filterPrecio $filterCiudad
                                    $filterCaracteristica)";
    } 

    //Query por Calles
    $unionQuery[] = "(SELECT $fields

                      FROM lugares

                      JOIN comuna
                      ON comuna.id = lugares.comuna_id 
                
                      LEFT JOIN sector
                      ON sector.id = lugares.sector_id

                      LEFT JOIN categoria_lugar
                      ON categoria_lugar.lugar_id = lugares.id

                      JOIN categorias
                      ON categoria_lugar.categoria_id = categorias.id

                      LEFT JOIN subcategoria_lugar
                      ON subcategoria_lugar.lugar_id = lugares.id

                      LEFT JOIN subcategoria
                      ON subcategoria_lugar.subcategoria_id = subcategoria.id   
          
                      LEFT JOIN caracteristica_lugar
                      ON caracteristica_lugar.lugar_id = lugares.id

                      JOIN caracteristica
                      ON caracteristica.id = caracteristica_lugar.caracteristica_id

                      WHERE lugares.calle like '%$term%' 
                      AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                      $filterComuna $filterSector $filterCat $filterSubCat $filterCiudad

                      GROUP BY lugares.id $order LIMIT 3000)";

      /*
      * Totales de Sectores
      */

      $sectoresFields = "distinct lugares.id as lid, sector.id as sid, sector.nombre, sector.slug,                                 
                          (
                            select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
                            left join caracteristica
                            on caracteristica_lugar.caracteristica_id = caracteristica.id
                            where caracteristica_lugar.lugar_id = lid
                          ) as caracteristica_slug";

      $totalSectores[] = "(SELECT $sectoresFields

                           FROM lugares

                           JOIN sector
                           ON sector.id = lugares.sector_id

                           WHERE lugares.slug LIKE '%$termSlug%'
                           AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                           $filterPrecio $filterCiudadSector
                           $filterCaracteristica)";

      //Total de Categorias Generadas por la Categoria
      $totalSectores[] = "(SELECT $sectoresFields

                           FROM lugares
                
                           JOIN sector
                           ON sector.id = lugares.sector_id

                           LEFT JOIN categoria_lugar
                           ON categoria_lugar.lugar_id = lugares.id

                           JOIN categorias
                           ON categoria_lugar.categoria_id = categorias.id

                           WHERE categorias.slug LIKE '%$termSlug%' 
                           AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                           $filterPrecio $filterCiudadSector
                           $filterCaracteristica)";

      foreach($termArray as $key => $value){
        $totalSectores[] = "(SELECT $sectoresFields

                             FROM lugares

                             JOIN sector
                             ON sector.id = lugares.sector_id

                             WHERE lugares.slug LIKE '%$value%'
                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                             $filterPrecio $filterCiudadSector
                             $filterCaracteristica)";
      }

        $totalSectores[] = "(SELECT $sectoresFields
                             FROM lugares

                             JOIN sector
                             ON sector.id = lugares.sector_id

                             WHERE lugares.calle LIKE '%$term%'
                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                             $filterPrecio $filterCiudadSector
                             $filterCaracteristica)";

      /*
      * Totales de Comunas
      */

      $comunasFields = "distinct lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug,
                          (
                            select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
                            left join caracteristica
                            on caracteristica_lugar.caracteristica_id = caracteristica.id
                            where caracteristica_lugar.lugar_id = lid
                          ) as caracteristica_slug";

      //Total de Categorias Generadas por la Categoria
      $totalComunas[] = "(SELECT $comunasFields

                          FROM lugares

                          JOIN comuna
                          ON comuna.id = lugares.comuna_id 

                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id

                          JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id

                          WHERE categorias.slug LIKE '%$termSlug%'
                          AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                          $filterPrecio $filterCiudad
                          $filterCaracteristica)";

      $totalComunas[] = "(SELECT $comunasFields

                          FROM lugares

                          JOIN comuna
                          ON comuna.id = lugares.comuna_id

                          WHERE lugares.slug LIKE '%$termSlug%'
                          AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                          $filterPrecio $filterCiudad
                          $filterCaracteristica)";

      foreach($termArray as $key => $value){
        $totalComunas[] = "(SELECT $comunasFields

                            FROM lugares

                            JOIN comuna
                            ON comuna.id = lugares.comuna_id

                            WHERE lugares.slug LIKE '%$value%'
                            AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                            $filterPrecio $filterCiudad
                            $filterCaracteristica)";
      }

        $totalComunas[] = "(SELECT $comunasFields
                            FROM lugares

                            JOIN comuna
                            ON comuna.id = lugares.comuna_id

                            WHERE lugares.calle LIKE '%$term%'
                            AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                            $filterPrecio $filterCiudad
                            $filterCaracteristica)";

    if($categoria){
      $tipo_categoria = $categoria_repo->findOneBySlug($termSlug)->getTipoCategoria();

      if($tipo_categoria->getSlug() == 'donde-comer' || $tipo_categoria->getSlug() == 'donde-dormir' || $termSlug == 'night-clubs') {
        $mostrarPrecio = $tipo_categoria->getSlug();
      }

      $unionQuery = array();
      $unionQuery[] = "SELECT SQL_CALC_FOUND_ROWS $fields
                        FROM lugares
                        
                        JOIN comuna
                        ON comuna.id = lugares.comuna_id 
                  
                        LEFT JOIN sector
                        ON sector.id = lugares.sector_id

                        LEFT JOIN categoria_lugar
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
                        
                        WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)

                        $filterSubCat $filterSector $filterComuna $filterPrecio $filterCiudad
                        GROUP BY lugares.id
                        $filterCaracteristica
                        $order";

      $totalSubCategorias = array();
      $totalSubCategorias[] = "(SELECT $subCategoriasFields

                                FROM lugares

                                JOIN comuna
                                ON comuna.id = lugares.comuna_id 
                          
                                LEFT JOIN sector
                                ON sector.id = lugares.sector_id

                                LEFT JOIN categoria_lugar
                                ON categoria_lugar.lugar_id = lugares.id

                                JOIN categorias
                                ON categoria_lugar.categoria_id = categorias.id

                                LEFT JOIN subcategoria_lugar
                                ON subcategoria_lugar.lugar_id = lugares.id

                                JOIN subcategoria
                                ON subcategoria_lugar.subcategoria_id = subcategoria.id  

                                WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
                                AND subcategoria.categoria_id = (select id from categorias where categorias.slug = '$termSlug')
                                AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                                $filterSector $filterComuna $filterPrecio $filterCiudad
                                $filterCaracteristica)";

      $totalSectores = array();
      $totalComunas = array();
      $totalCaracteristicas = array();

      $totalCaracteristicas[] = "(SELECT $caracteristicasFields

                            FROM lugares

                            JOIN comuna
                            ON comuna.id = lugares.comuna_id 
                    
                            LEFT JOIN sector
                            ON sector.id = lugares.sector_id
                
                            LEFT JOIN caracteristica_lugar
                            ON caracteristica_lugar.lugar_id = lugares.id

                            JOIN caracteristica
                            ON caracteristica.id = caracteristica_lugar.caracteristica_id

                            LEFT JOIN categoria_lugar
                            ON categoria_lugar.lugar_id = lugares.id

                            JOIN categorias
                            ON categoria_lugar.categoria_id = categorias.id

                            LEFT JOIN subcategoria_lugar
                            ON lugares.id = subcategoria_lugar.lugar_id
                           
                            LEFT JOIN subcategoria
                            ON subcategoria_lugar.subcategoria_id = subcategoria.id

                            WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
                            AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                            $filterSubCat $filterSector $filterComuna $filterPrecio $filterCiudad
                            $filterCaracteristica)";

      $totalSectores[] = "(SELECT $sectoresFields

                           FROM lugares
                           
                           JOIN comuna
                           ON comuna.id = lugares.comuna_id 

                           JOIN sector
                           ON sector.id = lugares.sector_id

                           LEFT JOIN categoria_lugar
                           ON categoria_lugar.lugar_id = lugares.id

                           JOIN categorias
                           ON categoria_lugar.categoria_id = categorias.id

                           LEFT JOIN subcategoria_lugar
                           ON lugares.id = subcategoria_lugar.lugar_id

                           LEFT JOIN subcategoria
                           ON subcategoria_lugar.subcategoria_id = subcategoria.id

                           WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
                           AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                           $filterPrecio $filterSubCat $filterCiudad
                           $filterCaracteristica)";

      $totalComunas[] = "(SELECT $comunasFields

                          FROM lugares

                          JOIN comuna
                          ON comuna.id = lugares.comuna_id 

                          LEFT JOIN categoria_lugar
                          ON categoria_lugar.lugar_id = lugares.id

                          JOIN categorias
                          ON categoria_lugar.categoria_id = categorias.id

                          LEFT JOIN subcategoria_lugar
                          ON lugares.id = subcategoria_lugar.lugar_id

                          LEFT JOIN subcategoria
                          ON subcategoria_lugar.subcategoria_id = subcategoria.id

                          WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
                          AND (lugares.estado_id = 1 or lugares.estado_id = 2)
                          $filterPrecio $filterSubCat $filterCiudad
                          $filterCaracteristica)";
}


    //Armamos y ejecutamos las queries
    if(is_array($unionQuery)){
      if($noCategorias == false){
       //Generacion y Ejecucion de Query
        $totalCategorias = join(" UNION ", $totalCategorias);
        $totalCategorias =  "select count(lid) as total, lid, cid, nombre, slug from (" . $totalCategorias . ") sq group by cid order by nombre asc";
        $results['totalPorCategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalCategorias);
      }else{
        //Generacion y Ejecucion de Query
        $totalSubCategorias = join(" UNION ", $totalSubCategorias);
        $totalSubCategorias =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalSubCategorias . ") sq group by sid order by nombre asc";
        $results['totalPorSubcategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalSubCategorias);

        //Generacion y Ejecucion de Query
        $totalCaracteristicas = join(" UNION ", $totalCaracteristicas);
        $totalCaracteristicas =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalCaracteristicas . ") sq group by sid order by nombre asc";
        $results['totalPorCaracteristica'] = $this->getDoctrine()->getConnection()->fetchAll($totalCaracteristicas);
      }

      //Generacion y Ejecucion de Query
      $totalSectores = join(" UNION ", $totalSectores);
      $totalSectores =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalSectores . ") sq group by sid order by nombre asc";
      $results['totalPorSectores'] = $this->getDoctrine()->getConnection()->fetchAll($totalSectores);

      //Generacion y Ejecucion de Query
      $totalComunas = join(" UNION ", $totalComunas);
      $totalComunas =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalComunas . ") sq group by sid order by nombre asc";
      $results['totalPorComunas'] = $this->getDoctrine()->getConnection()->fetchAll($totalComunas);

      $unionQuery = join(" UNION ", $unionQuery);
      $unionQuery .= " LIMIT $resultadosPorPagina OFFSET $offset";  
      $arr['lugares'] = $this->getDoctrine()->getConnection()->fetchAll($unionQuery);
      $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");   
    }

    $data = array();

    //Sacamos los otros datos de los 30 resultados que corresponden
    foreach($arr['lugares'] as $key => $lugar){
      $q = $em->createQuery("SELECT l from Loogares\LugarBundle\Entity\Lugar l
                             WHERE l.id = ?1");

      $q2 = $em->createQuery("SELECT count(r.id) as totalRecomendaciones, AVG(r.estrellas) as promedioEstrellas from Loogares\UsuarioBundle\Entity\Recomendacion r
                              WHERE r.lugar = ?1 and r.estado != 3 ORDER BY r.id DESC");

      $q2->setMaxResults(1);
      $q2->setParameter(1, $lugar['id']);

      $q->setMaxResults(1);
      $q->setParameter(1, $lugar['id']);

      $buffer = $q->getOneOrNullResult();
      $bufferRec = $q2->getOneOrNullResult();

      $lugar = $buffer;
      $recomendacion = $bufferRec;

      $lugar->mostrarPrecio = $fn->mostrarPrecio($lugar);

      $data[]['nombre'] = $lugar->getNombre();
      $data[sizeOf($data)-1]['slug'] = $lugar->getSlug();
      $data[sizeOf($data)-1]['estrellas'] = $recomendacion['promedioEstrellas'];
      $data[sizeOf($data)-1]['calle'] = $lugar->getCalle();
      $data[sizeOf($data)-1]['mapx'] = $lugar->getMapy();
      $data[sizeOf($data)-1]['mapy'] = $lugar->getMapx();
      $data[sizeOf($data)-1]['numero'] = $lugar->getNumero();

      $categoria =  $lugar->getCategoriaLugar();
      $data[sizeOf($data)-1]['categoria'] = $categoria[0]->getCategoria()->getNombre();
      $data[sizeOf($data)-1]['tipoCategoria'] = $categoria[0]->getCategoria()->getTipoCategoria()->getNombre();

      $imagenesActivas = $lugar->getImagenesActivasLugar();
      $ultimaImagenActiva = $imagenesActivas[sizeOf($imagenesActivas)-1]->getImagenFull();

      if($ultimaImagenActiva != '' && file_exists('assets/images/lugares/'.$ultimaImagenActiva)){
        if(!file_exists('assets/media/cache/medium_lugar/assets/images/lugares/'.$ultimaImagenActiva)){
            $this->get('imagine.controller')->filter('assets/images/lugares/'.$ultimaImagenActiva, "medium_lugar");
        }
        $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/medium_lugar/assets/images/lugares/'.$ultimaImagenActiva;
      }else{
        if(!file_exists('assets/media/cache/medium_lugar/assets/images/lugares/default.gif')){
            $this->get('imagine.controller')->filter('assets/images/lugares/default.gif', "medium_lugar");
        }
        $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/medium_lugar/assets/images/lugares/default.gif';
      }

      $data[sizeOf($data)-1]['totalRecomendaciones'] = $recomendacion['totalRecomendaciones'];
    }

        $json = json_encode(array_reverse(array('lugares'=>$data, 'total' => $resultSetSize[0]['rows'])));
            return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
  }
}
