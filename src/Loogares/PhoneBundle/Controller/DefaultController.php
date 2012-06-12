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
        
        $data[]['tipo'] = 'Todas';
        $data[sizeOf($data)-1]['categorias'][0]['categoria_nombre'] = 'Todas';
        $data[sizeOf($data)-1]['categorias'][0]['categoria_slug'] = 'todas';
        
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

    public function listadoCaracteristicasAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresLugarBundle:Caracteristica");

        $q = $em->createQuery('SELECT c FROM Loogares\LugarBundle\Entity\Caracteristica c ORDER BY c.nombre ASC');
        $caracteristicas = $q->getResult();

        foreach($caracteristicas as $caracteristica){
            $data[]['nombre'] = $caracteristica->getNombre();
            $data[sizeOf($data)-1]['slug'] = $caracteristica->getSlug();
        }

        $json = json_encode($data);

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
    }

    public function listadoSubcategoriasAction($categoria){
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresLugarBundle:Categoria");

        $categoria = $cr->findOneBySlug($categoria);

        $q = $em->createQuery("SELECT s FROM Loogares\LugarBundle\Entity\SubCategoria s WHERE s.categoria = ?1 ORDER BY s.nombre ASC");
        $q->setParameter(1, $categoria->getId());

        $subcategorias = $q->getResult();

        $data = array();
        $data['categoria']['nombre'] = $categoria->getNombre();
        foreach($subcategorias as $subcategoria){
            $data['subcategoria'][]['nombre'] = $subcategoria->getNombre();
            $data['subcategoria'][sizeOf($data['subcategoria'])-1]['slug'] = $subcategoria->getSlug();
        }

        $json = json_encode($data);

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
    }

    public function reverseGeoAction($lat, $long){
        $url = "http://api.geonames.org/extendedFindNearby?lat=$lat&lng=$long&username=loogares";
        $xml = file_get_contents($url);
        $sxml = simplexml_load_string($xml);
        $json = json_encode($sxml);
        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
    }

    public function lugaresPorCategoriaAction($categoria = 'todas', $offset = 1, $latitude = null, $longitude = null, $orden){
        if($categoria == 'todas'){ $categoria = null; }

        $em = $this->getDoctrine()->getEntityManager();
        $offset--; $offset = $offset*20;
        $cr = $em->getRepository("LoogaresLugarBundle:Categoria");
        $data = array();

        if($latitude != null && $longitude != null){
            $geoloc = ",( 6371 * acos( cos( radians($latitude) ) * cos( radians( l.mapx ) ) * cos( radians( l.mapy ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( l.mapx ) ) ) ) AS distance";
            $geolocCondition = "HAVING distance < 10";
            $orderBy = "ORDER BY distance asc";
        }else{
            $geoloc = null;
            $geolocCondition = null;
            $orderBy = "ORDER BY distance asc";
        }

        $orderFilters = array(
          'recomendaciones' => 'ranking desc',
          'ultimas_recomendaciones' => 'ultima_recomendacion desc',
          'mas_recomendados' => 'total_recomendaciones desc'
        );

        $orderBy = "ORDER BY ranking desc";
        
        if($categoria == null){
            $lugares = $this->getDoctrine()->getConnection()
        ->fetchAll(
            "SELECT l.nombre, l.slug, l.estrellas, l.calle, l.mapx, l.mapy, l.numero,
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
             LIMIT 50 OFFSET $offset");
        }else{
            $categoria = $cr->findBySlug($categoria);
            $categoriaId = $categoria[0]->getId();
            $lugares = $this->getDoctrine()->getConnection()->fetchAll(
            "SELECT l.nombre, l.slug, l.estrellas, l.calle, l.mapx, l.mapy, l.numero,
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
             $orderBy
             LIMIT 50 OFFSET $offset");
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
            
            if(isset($lugares[$i]['distance']) && $lugares[$i]['distance'] < 1){
                $data[sizeOf($data)-1]['distance'] = round($lugares[$i]['distance'] * 1000);
            }else if(isset($lugares[$i]['distance'])){
                $data[sizeOf($data)-1]['distance'] = round($lugares[$i]['distance']);
            }

            $data[sizeOf($data)-1]['categoria'] = $lugares[$i]['categoria'];

            $data[sizeOf($data)-1]['ranking'] = $lugares[$i]['ranking'];
            $data[sizeOf($data)-1]['tipoCategoria'] = $lugares[$i]['tipo_categoria'];

            $imagenes = explode('.', $lugares[$i]['imagen_full']);
            $imagine = new \Imagine\Gd\Imagine();
            $image = $imagine->create(new \Imagine\Image\Box(136, 136), new \Imagine\Image\Color('000', 100));

            if($lugares[$i]['imagen_full'] != '' && file_exists('assets/images/lugares/'.$lugares[$i]['imagen_full'])){
                if(!file_exists('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$imagenes[0].'.png')){
                    $this->get('imagine.controller')->filter('assets/images/lugares/'.$lugares[$i]['imagen_full'], "phone_thumbnail");
                    
                    $originalImage = $imagine->open('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$lugares[$i]['imagen_full']);
                    $image->paste($originalImage, new \Imagine\Image\Point(10, 8));
                    
                    $image->save('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$imagenes[0].'.png');
                    unlink('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$lugares[$i]['imagen_full']);
                }
                $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/phone_thumbnail/assets/images/lugares/'.$imagenes[0].'.png';
            }else{
                if(!file_exists('assets/media/cache/phone_thumbnail/assets/images/lugares/default.png')){
                    $this->get('imagine.controller')->filter('assets/images/lugares/default.gif', "phone_thumbnail");

                    $originalImage = $imagine->open('assets/media/cache/phone_thumbnail/assets/images/lugares/default.gif');
                    $image->paste($originalImage, new \Imagine\Image\Point(16, 8));

                    $image->save('assets/media/cache/phone_thumbnail/assets/images/lugares/default.png');
                    unlink('assets/media/cache/phone_thumbnail/assets/images/lugares/default.gif');
                }
                $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/phone_thumbnail/assets/images/lugares/default.png';
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
        $cr = $em->getRepository("LoogaresLugarBundle:Caracteristica");

        $fn = $this->get('fn');

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
            $imagen = $recomendaciones[$i]->getUsuario()->getImagenFull();
            $ultimaImagen = explode('.', $imagen);

            $imagine = new \Imagine\Gd\Imagine();      
            $bgImage = $imagine->create(new \Imagine\Image\Box(108, 108), new \Imagine\Image\Color('000', 100));

            if(file_exists('assets/images/lugares/usuarios'.$imagen)){
                if(!file_exists('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/'.$ultimaImagen[0].'.png')){
                    $this->get('imagine.controller')->filter('assets/images/usuarios/'.$imagen, "phone_recomendaciones_thumbnail");

                    $originalImage = $imagine->open('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/'.$imagen);
                    $bgImage->paste($originalImage, new \Imagine\Image\Point(14, 6));
                    
                    $bgImage->save('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/'.$ultimaImagen[0].'.png');
                    unlink('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/'.$imagen);
                }
                $data['recomendaciones'][$i]['imagen'] = 'assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/'.$ultimaImagen[0].'.png';
            }else{
                 if(!file_exists('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/default.png')){
                    $this->get('imagine.controller')->filter('assets/images/usuarios/default.gif', "phone_recomendaciones_thumbnail");

                    $originalImage = $imagine->open('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/default.gif');
                    $bgImage->paste($originalImage, new \Imagine\Image\Point(14, 6));
                    
                    $bgImage->save('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/default.png');
                    unlink('assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/default.gif');
                }
                $data['recomendaciones'][$i]['imagen'] = 'assets/media/cache/phone_recomendaciones_thumbnail/assets/images/usuarios/default.png';               
            }
        }
        if(isset($data['recomendaciones'])) $data['recomendaciones'] = array_reverse($data['recomendaciones']);

        $data['caracteristicas'] = array();
        $totalCaracteristicas = $cr->findAll();
        $caracteristicas = $lugar->getCaracteristicaLugar();

        foreach($totalCaracteristicas as $totalCaracteristica){
            $data['caracteristicas'][]['slug'] = $totalCaracteristica->getSlug();
            $data['caracteristicas'][sizeOf($data['caracteristicas']) -1]['nombre'] = $totalCaracteristica->getNombre();
            
            foreach($caracteristicas as $caracteristica){
                if($caracteristica->getCaracteristica()->getSlug() == $totalCaracteristica->getSlug()){
                    $data['caracteristicas'][sizeOf($data['caracteristicas']) -1]['tiene'] = true;
                }
            }

            if(!isset($data['caracteristicas'][sizeOf($data['caracteristicas']) -1]['tiene'])){
                $data['caracteristicas'][sizeOf($data['caracteristicas']) -1]['tiene'] = false;
            }
        }

        $data['nombre'] = $lugar->getNombre();
        $data['slug'] = $lugar->getSlug();
        $data['mapx'] = $lugar->getMapx();
        $data['mapy'] = $lugar->getMapy();
        $data['localidad'] = $lugar->getComuna()->getNombre() . ', ' . $lugar->getComuna()->getCiudad()->getNombre();
        $data['direccion'] = $lugar->getCalle() . " - " . $lugar->getNumero();
        $data['telefonos'] = array();
        if($lugar->getTelefono1() != '') $data['telefonos'][] = $codigo . $lugar->getTelefono1();
        if($lugar->getTelefono2() != '') $data['telefonos'][] = $codigo . $lugar->getTelefono2();
        if($lugar->getTelefono3() != '') $data['telefonos'][] = $codigo . $lugar->getTelefono3();
        $data['estrellas'] = $lugar->getEstrellas();
        $data['facebook'] = $lugar->getFacebook();
        $data['twitter'] = $lugar->getTwitter();
        $data['web'] = $lugar->getSitioWeb();
        $data['mostrarPrecio'] = $fn->mostrarPrecio($lugar);
        $horario = $fn->generarHorario($lugar->getHorario());
        $data['horario'] = '';

        if($horario){
            foreach($horario as $hora){
                $data['horario'] = $data['horario'] . $hora . '\n';
            }
        }

        $categorias = $lugar->getCategoriaLugar();
        foreach($categorias as $categoria){
            $data['categorias'][] = $categoria->getCategoria()->getNombre();
        }
        $data['categorias'] = implode(', ', $data['categorias']);

        $subcategorias = $lugar->getSubcategoriaLugar();
        foreach($subcategorias as $subcategoria){
            $data['subcategorias'][] = $subcategoria->getSubcategoria()->getNombre();
        }
        if(isset($data['subcategorias'])) $data['subcategorias'] = implode(', ', $data['subcategorias']);

        $data['totalRecomendaciones'] = sizeOf($recomendaciones);

        $imagenesActivas = $lugar->getImagenesActivasLugar();
        
        if(sizeOf($imagenesActivas) > 0){
            $ultimaImagen = $imagenesActivas[sizeOf($imagenesActivas) - 1]->getImagenFull();
            $ultimaImagen = explode('.', $ultimaImagen);
        }

        $imagine = new \Imagine\Gd\Imagine();      
        $bgImage = $imagine->create(new \Imagine\Image\Box(248, 248), new \Imagine\Image\Color('000', 100));

        if(sizeOf($imagenesActivas) != 0 && file_exists('assets/images/lugares/'.$imagenesActivas[sizeOf($imagenesActivas)-1]->getImagenFull())){
            if(!file_exists('assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/'.$ultimaImagen[0].'.png')){
                $this->get('imagine.controller')->filter('assets/images/lugares/'.$imagenesActivas[sizeOf($imagenesActivas)-1]->getImagenFull(), "phone_ficha_thumbnail");

                $originalImage = $imagine->open('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$imagenesActivas[sizeOf($imagenesActivas)-1]->getImagenFull());
                $bgImage->paste($originalImage, new \Imagine\Image\Point(16, 10));
                
                $bgImage->save('assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/'.$ultimaImagen[0].'.png');
                unlink('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$imagenesActivas[sizeOf($imagenesActivas)-1]->getImagenFull());
            }
            $data['imagen130'] = 'assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/'.$ultimaImagen[0].'.png';
        }else{
            if(!file_exists('assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/default.png')){
                $this->get('imagine.controller')->filter('assets/images/lugares/default.gif', "phone_ficha_thumbnail");

                $originalImage = $imagine->open('assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/default.gif');
                $bgImage->paste($originalImage, new \Imagine\Image\Point(16, 10));
                
                $bgImage->save('assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/default.png');
                unlink('assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/default.gif');
            }
            $data['imagen130'] = 'assets/media/cache/phone_ficha_thumbnail/assets/images/lugares/default.png';
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
    $offset = 0;

    $latitude = null;
    $longitude = null;

    $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
    $ciudad = $cr->findOneBySlug($slug);

    if($latitude != null && $longitude != null){
        $geoloc = ",( 6371 * acos( cos( radians($latitude) ) * cos( radians( lugares.mapx ) ) * cos( radians( lugares.mapy ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( lugares.mapx ) ) ) ) AS distance";
        $geolocCondition = "HAVING distance < 10";
        $orderBy = "ORDER BY distance asc";
    }else{
        $geoloc = null;
        $geolocCondition = null;
        $orderBy = "ORDER BY distance asc";
    }

    $orderFilters = array(
      'recomendaciones' => 'lugares.total_recomendaciones desc',
      'alfabetico' => 'lugares.nombre asc',
      'recomendaciones' => 'ranking desc',
      'ultimas_recomendaciones' => 'ultima_recomendacion desc',
      'mas_recomendados' => 'lugares.total_recomendaciones desc',
      'distance' => 'distance asc'
    );

    if(isset($_GET['orden'])){
      if(isset($orderFilters[$_GET['orden']])){
        $order = "ORDER BY " . $orderFilters[$_GET['orden']];
      }
    }
    $order = "ORDER BY lugares.total_recomendaciones desc";

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
          array("\\", "¨", "º", "~",
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

    $sector_repo = $em->getRepository('LoogaresExtraBundle:Sector');
    $comuna_repo = $em->getRepository('LoogaresExtraBundle:Comuna');

    $categoria_repo = $em->getRepository('LoogaresLugarBundle:Categoria');
    $subcat_repo = $em->getRepository('LoogaresLugarBundle:SubCategoria');

    $resultadosPorPagina = 50;

    $term = $_GET['q'];
    $termSlug = $fn->generarSlug($term);
    $termArray = preg_split('/\s/', $term);
    if($term == 'todas'){ $term = ''; }

    $fields = "STRAIGHT_JOIN lugares.mapx, lugares.mapy, lugares.id, lugares.nombre as nombre_lugar, lugares.slug as lugar_slug, lugares.calle, lugares.numero, lugares.estrellas, lugares.precio, lugares.total_recomendaciones, lugares.fecha_ultima_recomendacion, lugares.utiles, lugares.visitas, (lugares.estrellas*6 + lugares.utiles + lugares.total_recomendaciones*2+lugares.visitas*0) as ranking, categorias.slug, categorias.nombre, ( select max(recomendacion.fecha_creacion) from recomendacion where recomendacion.lugar_id = lugares.id and recomendacion.estado_id != 3 ) as ultima_recomendacion ".$geoloc;   

    $noCategorias = false;
    $filterCat = false;
    $filterSubCat = false;
    $filterComuna = false;
    $filterSector = false;
    $filterPrecio = false;
    $filterCaracteristica = false;
    $filterCiudad = " AND comuna.ciudad_id = $idCiudad";
    $filterCiudadSector = " AND sector.ciudad_id = $idCiudad";

    if(isset($_GET['categoria']) || isset($_GET['subcategoria'])){
      $noCategorias = true;
    }

    if(isset($_GET['comuna'])){
      $filterComuna .= ' AND comuna.slug = "' . $_GET['comuna'] . '"';      
    }

    if(isset($_GET['sector'])){
      $filterSector .= ' AND sector.slug = "' . $_GET['sector'] . '"';      
    }

    $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Categoria u WHERE u.slug = '$termSlug'");
    $esCategoria = $q->getOneOrNullResult();

    if($esCategoria != null){
        $term = 'somethingneverfound';
        $termArray = array();
        $noCategorias = true;
        $_GET['categoria'] = $termSlug;
        $categoriaResult = $esCategoria;
        $categoria = $categoriaResult->getSlug();
    }else{
      //Vemos si no es igual a una subcategoria...
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\SubCategoria u WHERE u.slug = '$termSlug'");
      $esSubCategoria = $q->getOneOrNullResult();

      if($esSubCategoria != null){
        $term = 'somethingneverfound';
        $termArray = array();
        $noCategorias = true;

        //Subcategoria pasa a ser condicion
        $_GET['subcategoria'] = $termSlug;
        $subcategoriaResult = $esSubCategoria;
        //$categoriaResult = $subcategoriaResult->getCategoria();

        //Categoria pasa a ser el termino buscado
        $termSlug = $esSubCategoria->getCategoria()->getSlug();
        $_GET['categoria'] = $termSlug;
      }
    }

    if($noCategorias == false){
      //Buscamos por Categorias
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

      // //Total de Categorias Generadas por el Slug
      // $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
      //                        FROM lugares

      //                        JOIN comuna
      //                        ON lugares.comuna_id = comuna.id

      //                        LEFT JOIN sector
      //                        ON lugares.sector_id = sector.id

      //                        LEFT JOIN categoria_lugar
      //                        ON categoria_lugar.lugar_id = lugares.id

      //                        JOIN categorias
      //                        ON categoria_lugar.categoria_id = categorias.id

      //                        WHERE lugares.slug LIKE '%$termSlug%' 
      //                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                        $filterSector $filterComuna $filterCiudad)";

      // foreach($termArray as $key => $value){
      //   $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
      //                          FROM lugares

      //                          JOIN comuna
      //                          ON lugares.comuna_id = comuna.id

      //                          LEFT JOIN sector
      //                          ON lugares.sector_id = sector.id

      //                          LEFT JOIN categoria_lugar
      //                          ON categoria_lugar.lugar_id = lugares.id

      //                          JOIN categorias
      //                          ON categoria_lugar.categoria_id = categorias.id

      //                          WHERE lugares.slug LIKE '%$value%' 
      //                          AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                          $filterSector $filterComuna $filterCiudad)";
      // }

      // //Total de Categorias Generadas por la Categoria
      // $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
      //                        FROM lugares

      //                        JOIN comuna
      //                        ON lugares.comuna_id = comuna.id

      //                        LEFT JOIN sector
      //                        ON lugares.sector_id = sector.id

      //                        LEFT JOIN categoria_lugar
      //                        ON categoria_lugar.lugar_id = lugares.id

      //                        JOIN categorias
      //                        ON categoria_lugar.categoria_id = categorias.id

      //                        WHERE categorias.slug LIKE '%$termSlug%' 
      //                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                        $filterSector $filterComuna $filterCiudad)";
                             
 
      // //Total de Categorias por Calle
      // $totalCategorias[] = "(SELECT lugares.id as lid, categorias.id as cid, categorias.nombre, categorias.slug
      //                        FROM lugares

      //                        JOIN comuna
      //                        ON lugares.comuna_id = comuna.id

      //                        LEFT JOIN sector
      //                        ON lugares.sector_id = sector.id

      //                        LEFT JOIN categoria_lugar
      //                        ON categoria_lugar.lugar_id = lugares.id

      //                        JOIN categorias
      //                        ON categoria_lugar.categoria_id = categorias.id

      //                        WHERE lugares.calle LIKE '%$term%' 
      //                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                        $filterSector $filterComuna $filterCiudad)";


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

      // $subCategoriasFields = "distinct lugares.id as lid, subcategoria.id as sid, subcategoria.nombre, subcategoria.slug,
      //                           (
      //                             select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
      //                             left join caracteristica
      //                             on caracteristica_lugar.caracteristica_id = caracteristica.id
      //                             where caracteristica_lugar.lugar_id = lid
      //                           ) as caracteristica_slug";

      // //Total de Categorias generadas por el Slug
      // $totalSubCategorias[] = "(SELECT $subCategoriasFields

      //                           FROM lugares

      //                           JOIN comuna
      //                           ON lugares.comuna_id = comuna.id

      //                           LEFT JOIN sector
      //                           ON lugares.sector_id = sector.id

      //                           LEFT JOIN categoria_lugar
      //                           ON categoria_lugar.lugar_id = lugares.id

      //                           JOIN categorias
      //                           ON categoria_lugar.categoria_id = categorias.id

      //                           LEFT JOIN subcategoria_lugar
      //                           ON subcategoria_lugar.lugar_id = lugares.id

      //                           JOIN subcategoria
      //                           ON subcategoria_lugar.subcategoria_id = subcategoria.id

      //                           WHERE lugares.slug LIKE '%$termSlug%' 
      //                           AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                           $filterCat $filterComuna $filterSector  $filterCiudad
      //                           $filterCaracteristica)";


      // foreach($termArray as $key => $value){
      //   $totalSubCategorias[] = "(SELECT $subCategoriasFields

      //                             FROM lugares

      //                             JOIN comuna
      //                             ON lugares.comuna_id = comuna.id

      //                             LEFT JOIN sector
      //                             ON lugares.sector_id = sector.id

      //                             LEFT JOIN categoria_lugar
      //                             ON categoria_lugar.lugar_id = lugares.id

      //                             JOIN categorias
      //                             ON categoria_lugar.categoria_id = categorias.id

      //                             LEFT JOIN subcategoria_lugar
      //                             ON subcategoria_lugar.lugar_id = lugares.id

      //                             JOIN subcategoria
      //                             ON subcategoria_lugar.subcategoria_id = subcategoria.id

      //                             WHERE lugares.slug LIKE '%$value%' 
      //                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                             $filterCat $filterSector $filterComuna $filterPrecio  $filterCiudad
      //                             $filterCaracteristica)";
      // }

      // //Total de Categorias generadas por la Subcategoria
      // $totalSubCategorias[] = "(SELECT $subCategoriasFields

      //                           FROM lugares

      //                           JOIN comuna
      //                           ON comuna.id = lugares.comuna_id 
                          
      //                           LEFT JOIN sector
      //                           ON sector.id = lugares.sector_id

      //                           LEFT JOIN categoria_lugar
      //                           ON categoria_lugar.lugar_id = lugares.id

      //                           JOIN categorias
      //                           ON categoria_lugar.categoria_id = categorias.id

      //                           LEFT JOIN subcategoria_lugar
      //                           ON subcategoria_lugar.lugar_id = lugares.id

      //                           JOIN subcategoria
      //                           ON subcategoria_lugar.subcategoria_id = subcategoria.id  

      //                           WHERE categorias.slug LIKE '%$termSlug%'
      //                           AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                           $filterCat $filterSector $filterComuna $filterPrecio  $filterCiudad
      //                           $filterCaracteristica)";
                
      // //Total de Calles en Subcategorias
      // $totalSubCategorias[] = "(SELECT $subCategoriasFields

      //                           FROM lugares

      //                           JOIN comuna
      //                           ON comuna.id = lugares.comuna_id 
                          
      //                           LEFT JOIN sector
      //                           ON sector.id = lugares.sector_id

      //                           LEFT JOIN categoria_lugar
      //                           ON categoria_lugar.lugar_id = lugares.id

      //                           JOIN categorias
      //                           ON categoria_lugar.categoria_id = categorias.id

      //                           LEFT JOIN subcategoria_lugar
      //                           ON subcategoria_lugar.lugar_id = lugares.id

      //                           JOIN subcategoria
      //                           ON subcategoria_lugar.subcategoria_id = subcategoria.id  

      //                           WHERE lugares.calle like '%$termSlug%' 
      //                           AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                           $filterCat $filterSector $filterComuna $filterPrecio $filterCiudad
      //                           $filterCaracteristica)";
      /*
      * Totales de Caracteristicas
      */

      // $caracteristicasFields = "distinct lugares.id as lid, caracteristica.id as sid, caracteristica.nombre, caracteristica.slug,
      //                           (
      //                       select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
      //                       left join caracteristica
      //                       on caracteristica_lugar.caracteristica_id = caracteristica.id
      //                       where caracteristica_lugar.lugar_id = lid
      //                     ) as caracteristica_slug";

      // $totalCaracteristicas[] = "(SELECT $caracteristicasFields

      //                             FROM lugares

      //                             JOIN comuna
      //                             ON comuna.id = lugares.comuna_id 
                            
      //                             LEFT JOIN sector
      //                             ON sector.id = lugares.sector_id

      //                             LEFT JOIN caracteristica_lugar
      //                             ON caracteristica_lugar.lugar_id = lugares.id

      //                             JOIN caracteristica
      //                             ON caracteristica.id = caracteristica_lugar.caracteristica_id

      //                             WHERE lugares.slug LIKE '%$termSlug%'
      //                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                             $filterSector $filterComuna $filterPrecio $filterCiudad
      //                             $filterCaracteristica)";

      // //Total de Categorias Generadas por la Categoria
      // $totalCaracteristicas[] = "(SELECT $caracteristicasFields

      //                             FROM lugares

      //                             JOIN comuna
      //                             ON comuna.id = lugares.comuna_id 
                          
      //                             LEFT JOIN sector
      //                             ON sector.id = lugares.sector_id
                      
      //                             LEFT JOIN caracteristica_lugar
      //                             ON caracteristica_lugar.lugar_id = lugares.id
 
      //                             JOIN caracteristica
      //                             ON caracteristica.id = caracteristica_lugar.caracteristica_id

      //                             LEFT JOIN categoria_lugar
      //                             ON categoria_lugar.lugar_id = lugares.id

      //                             JOIN categorias
      //                             ON categoria_lugar.categoria_id = categorias.id

      //                             WHERE categorias.slug LIKE '%$termSlug%'
      //                             AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                             $filterSector $filterComuna $filterPrecio $filterCiudad
      //                             $filterCaracteristica)";

      // foreach($termArray as $key => $value){
      //   $totalCaracteristicas[] = "(SELECT $caracteristicasFields
                                   
      //                               FROM lugares

      //                               JOIN comuna
      //                               ON comuna.id = lugares.comuna_id 
                            
      //                               LEFT JOIN sector
      //                               ON sector.id = lugares.sector_id

      //                               LEFT JOIN caracteristica_lugar
      //                               ON caracteristica_lugar.lugar_id = lugares.id

      //                               JOIN caracteristica
      //                               ON caracteristica.id = caracteristica_lugar.caracteristica_id

      //                               WHERE lugares.slug LIKE '%$value%'
      //                               AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                               $filterSector $filterComuna $filterPrecio $filterCiudad
      //                               $filterCaracteristica)";
      // }

      //   $totalCaracteristicas[] = "(SELECT $caracteristicasFields
                                   
      //                               FROM lugares

      //                               JOIN comuna
      //                               ON comuna.id = lugares.comuna_id 
                              
      //                               LEFT JOIN sector
      //                               ON sector.id = lugares.sector_id

      //                               LEFT JOIN caracteristica_lugar
      //                               ON caracteristica_lugar.lugar_id = lugares.id

      //                               JOIN caracteristica
      //                               ON caracteristica.id = caracteristica_lugar.caracteristica_id

      //                               WHERE lugares.calle LIKE '%$term%'
      //                               AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                               $filterSector $filterComuna $filterPrecio $filterCiudad
      //                               $filterCaracteristica)";
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

      // $sectoresFields = "distinct lugares.id as lid, sector.id as sid, sector.nombre, sector.slug,                                 
      //                     (
      //                       select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
      //                       left join caracteristica
      //                       on caracteristica_lugar.caracteristica_id = caracteristica.id
      //                       where caracteristica_lugar.lugar_id = lid
      //                     ) as caracteristica_slug";

      // $totalSectores[] = "(SELECT $sectoresFields

      //                      FROM lugares

      //                      JOIN sector
      //                      ON sector.id = lugares.sector_id

      //                      WHERE lugares.slug LIKE '%$termSlug%'
      //                      AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                      $filterPrecio $filterCiudadSector
      //                      $filterCaracteristica)";

      // //Total de Categorias Generadas por la Categoria
      // $totalSectores[] = "(SELECT $sectoresFields

      //                      FROM lugares
                
      //                      JOIN sector
      //                      ON sector.id = lugares.sector_id

      //                      LEFT JOIN categoria_lugar
      //                      ON categoria_lugar.lugar_id = lugares.id

      //                      JOIN categorias
      //                      ON categoria_lugar.categoria_id = categorias.id

      //                      WHERE categorias.slug LIKE '%$termSlug%' 
      //                      AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                      $filterPrecio $filterCiudadSector
      //                      $filterCaracteristica)";

      // foreach($termArray as $key => $value){
      //   $totalSectores[] = "(SELECT $sectoresFields

      //                        FROM lugares

      //                        JOIN sector
      //                        ON sector.id = lugares.sector_id

      //                        WHERE lugares.slug LIKE '%$value%'
      //                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                        $filterPrecio $filterCiudadSector
      //                        $filterCaracteristica)";
      // }

      //   $totalSectores[] = "(SELECT $sectoresFields
      //                        FROM lugares

      //                        JOIN sector
      //                        ON sector.id = lugares.sector_id

      //                        WHERE lugares.calle LIKE '%$term%'
      //                        AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                        $filterPrecio $filterCiudadSector
      //                        $filterCaracteristica)";

      // /*
      // * Totales de Comunas
      // */

      // $comunasFields = "distinct lugares.id as lid, comuna.id as sid, comuna.nombre, comuna.slug,
      //                     (
      //                       select group_concat(distinct caracteristica.slug order by caracteristica.slug asc) as caracteristica_slug from caracteristica_lugar
      //                       left join caracteristica
      //                       on caracteristica_lugar.caracteristica_id = caracteristica.id
      //                       where caracteristica_lugar.lugar_id = lid
      //                     ) as caracteristica_slug";

      // //Total de Categorias Generadas por la Categoria
      // $totalComunas[] = "(SELECT $comunasFields

      //                     FROM lugares

      //                     JOIN comuna
      //                     ON comuna.id = lugares.comuna_id 

      //                     LEFT JOIN categoria_lugar
      //                     ON categoria_lugar.lugar_id = lugares.id

      //                     JOIN categorias
      //                     ON categoria_lugar.categoria_id = categorias.id

      //                     WHERE categorias.slug LIKE '%$termSlug%'
      //                     AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                     $filterPrecio $filterCiudad
      //                     $filterCaracteristica)";

      // $totalComunas[] = "(SELECT $comunasFields

      //                     FROM lugares

      //                     JOIN comuna
      //                     ON comuna.id = lugares.comuna_id

      //                     WHERE lugares.slug LIKE '%$termSlug%'
      //                     AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                     $filterPrecio $filterCiudad
      //                     $filterCaracteristica)";

      // foreach($termArray as $key => $value){
      //   $totalComunas[] = "(SELECT $comunasFields

      //                       FROM lugares

      //                       JOIN comuna
      //                       ON comuna.id = lugares.comuna_id

      //                       WHERE lugares.slug LIKE '%$value%'
      //                       AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                       $filterPrecio $filterCiudad
      //                       $filterCaracteristica)";
      // }

      //   $totalComunas[] = "(SELECT $comunasFields
      //                       FROM lugares

      //                       JOIN comuna
      //                       ON comuna.id = lugares.comuna_id

      //                       WHERE lugares.calle LIKE '%$term%'
      //                       AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                       $filterPrecio $filterCiudad
      //                       $filterCaracteristica)";

    if($categoria){
      $tipo_categoria = $categoria_repo->findOneBySlug($termSlug)->getTipoCategoria();

      if($tipo_categoria->getSlug() == 'donde-comer' || $tipo_categoria->getSlug() == 'donde-dormir' || $termSlug == 'night-clubs') {
        $mostrarPrecio = $tipo_categoria->getSlug();
      }

      $unionQuery = array();
      $unionQuery[] = "SELECT $fields
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
                        $geolocCondition
                        $order";

      // $totalSubCategorias = array();
      // $totalSubCategorias[] = "(SELECT $subCategoriasFields

      //                           FROM lugares

      //                           JOIN comuna
      //                           ON comuna.id = lugares.comuna_id 
                          
      //                           LEFT JOIN sector
      //                           ON sector.id = lugares.sector_id

      //                           LEFT JOIN categoria_lugar
      //                           ON categoria_lugar.lugar_id = lugares.id

      //                           JOIN categorias
      //                           ON categoria_lugar.categoria_id = categorias.id

      //                           LEFT JOIN subcategoria_lugar
      //                           ON subcategoria_lugar.lugar_id = lugares.id

      //                           JOIN subcategoria
      //                           ON subcategoria_lugar.subcategoria_id = subcategoria.id  

      //                           WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
      //                           AND subcategoria.categoria_id = (select id from categorias where categorias.slug = '$termSlug')
      //                           AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                           $filterSector $filterComuna $filterPrecio $filterCiudad
      //                           $filterCaracteristica)";

      // $totalSectores = array();
      // $totalComunas = array();
      // $totalCaracteristicas = array();

      // $totalCaracteristicas[] = "(SELECT $caracteristicasFields

      //                       FROM lugares

      //                       JOIN comuna
      //                       ON comuna.id = lugares.comuna_id 
                    
      //                       LEFT JOIN sector
      //                       ON sector.id = lugares.sector_id
                
      //                       LEFT JOIN caracteristica_lugar
      //                       ON caracteristica_lugar.lugar_id = lugares.id

      //                       JOIN caracteristica
      //                       ON caracteristica.id = caracteristica_lugar.caracteristica_id

      //                       LEFT JOIN categoria_lugar
      //                       ON categoria_lugar.lugar_id = lugares.id

      //                       JOIN categorias
      //                       ON categoria_lugar.categoria_id = categorias.id

      //                       LEFT JOIN subcategoria_lugar
      //                       ON lugares.id = subcategoria_lugar.lugar_id
                           
      //                       LEFT JOIN subcategoria
      //                       ON subcategoria_lugar.subcategoria_id = subcategoria.id

      //                       WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
      //                       AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                       $filterSubCat $filterSector $filterComuna $filterPrecio $filterCiudad
      //                       $filterCaracteristica)";

      // $totalSectores[] = "(SELECT $sectoresFields

      //                      FROM lugares
                           
      //                      JOIN comuna
      //                      ON comuna.id = lugares.comuna_id 

      //                      JOIN sector
      //                      ON sector.id = lugares.sector_id

      //                      LEFT JOIN categoria_lugar
      //                      ON categoria_lugar.lugar_id = lugares.id

      //                      JOIN categorias
      //                      ON categoria_lugar.categoria_id = categorias.id

      //                      LEFT JOIN subcategoria_lugar
      //                      ON lugares.id = subcategoria_lugar.lugar_id

      //                      LEFT JOIN subcategoria
      //                      ON subcategoria_lugar.subcategoria_id = subcategoria.id

      //                      WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
      //                      AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                      $filterPrecio $filterSubCat $filterCiudad
      //                      $filterCaracteristica)";

      // $totalComunas[] = "(SELECT $comunasFields

      //                     FROM lugares

      //                     JOIN comuna
      //                     ON comuna.id = lugares.comuna_id 

      //                     LEFT JOIN categoria_lugar
      //                     ON categoria_lugar.lugar_id = lugares.id

      //                     JOIN categorias
      //                     ON categoria_lugar.categoria_id = categorias.id

      //                     LEFT JOIN subcategoria_lugar
      //                     ON lugares.id = subcategoria_lugar.lugar_id

      //                     LEFT JOIN subcategoria
      //                     ON subcategoria_lugar.subcategoria_id = subcategoria.id

      //                     WHERE categorias.id = (select id from categorias where categorias.slug = '$termSlug')
      //                     AND (lugares.estado_id = 1 or lugares.estado_id = 2)
      //                     $filterPrecio $filterSubCat $filterCiudad
      //                     $filterCaracteristica)";
}


    //Armamos y ejecutamos las queries
    if(is_array($unionQuery)){
      if($noCategorias == false){
       //Generacion y Ejecucion de Query
        // $totalCategorias = join(" UNION ", $totalCategorias);
        // $totalCategorias =  "select count(lid) as total, lid, cid, nombre, slug from (" . $totalCategorias . ") sq group by cid order by nombre asc";
        // $results['totalPorCategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalCategorias);
      }else{
        //Generacion y Ejecucion de Query
        // $totalSubCategorias = join(" UNION ", $totalSubCategorias);
        // $totalSubCategorias =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalSubCategorias . ") sq group by sid order by nombre asc";
        // $results['totalPorSubcategoria'] = $this->getDoctrine()->getConnection()->fetchAll($totalSubCategorias);

        // //Generacion y Ejecucion de Query
        // $totalCaracteristicas = join(" UNION ", $totalCaracteristicas);
        // $totalCaracteristicas =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalCaracteristicas . ") sq group by sid order by nombre asc";
        // $results['totalPorCaracteristica'] = $this->getDoctrine()->getConnection()->fetchAll($totalCaracteristicas);
      }

      // //Generacion y Ejecucion de Query
      // $totalSectores = join(" UNION ", $totalSectores);
      // $totalSectores =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalSectores . ") sq group by sid order by nombre asc";
      // $results['totalPorSectores'] = $this->getDoctrine()->getConnection()->fetchAll($totalSectores);

      // //Generacion y Ejecucion de Query
      // $totalComunas = join(" UNION ", $totalComunas);
      // $totalComunas =  "select count(lid) as total, lid, sid, nombre, slug from (" . $totalComunas . ") sq group by sid order by nombre asc";
      // $results['totalPorComunas'] = $this->getDoctrine()->getConnection()->fetchAll($totalComunas);

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
      if(sizeOf($imagenesActivas) > 0){
        $ultimaImagenActiva = $imagenesActivas[sizeOf($imagenesActivas)-1]->getImagenFull();
         $imagenes = explode('.', $ultimaImagenActiva);
     }
     

      $imagine = new \Imagine\Gd\Imagine();      
      $image = $imagine->create(new \Imagine\Image\Box(136, 136), new \Imagine\Image\Color('000', 100));

      if(isset($ultimaImagenActiva) && file_exists('assets/images/lugares/'.$ultimaImagenActiva)){
        if(!file_exists('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$imagenes[0].'.png')){
            $this->get('imagine.controller')->filter('assets/images/lugares/'.$ultimaImagenActiva, "phone_thumbnail");

            $originalImage = $imagine->open('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$ultimaImagenActiva);
            $image->paste($originalImage, new \Imagine\Image\Point(16, 8));

            $image->save('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$imagenes[0].'.png');
            unlink('assets/media/cache/phone_thumbnail/assets/images/lugares/'.$ultimaImagenActiva);
        }
        $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/medium_lugar/assets/images/lugares/'.$ultimaImagenActiva;
      }else{
        if(!file_exists('assets/media/cache/medium_lugar/assets/images/lugares/default.png')){
            $this->get('imagine.controller')->filter('assets/images/lugares/default.gif', "phone_thumbnail");

            $originalImage = $imagine->open('assets/media/cache/phone_thumbnail/assets/images/lugares/default.gif');
            $image->paste($originalImage, new \Imagine\Image\Point(16, 8));

            unlink('assets/media/cache/phone_thumbnail/assets/images/lugares/default.gif');
        }
        $data[sizeOf($data)-1]['imagen36'] = 'assets/media/cache/phone_thumbnail/assets/images/lugares/default.png';
      }

      $data[sizeOf($data)-1]['totalRecomendaciones'] = $recomendacion['totalRecomendaciones'];
    }

        $json = json_encode(array_reverse(array('lugares'=>$data, 'total' => $resultSetSize[0]['rows'])));
            return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
  }
}
