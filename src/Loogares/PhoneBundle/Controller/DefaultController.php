<?php

namespace Loogares\PhoneBundle\Controller;

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
            $orderBy = "ORDER BY distance DESC";
        }else{
            $geoloc = null;
            $geolocCondition = null;
            $orderBy = "ORDER BY ranking DESC";
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
            $data[sizeOf($data)-1]['categoria'] = $lugares[$i]['categoria'];
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

        array_reverse($data);
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
}
