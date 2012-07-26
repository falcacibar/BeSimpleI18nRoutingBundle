<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Loogares\UsuarioBundle\Entity\Util;
use Loogares\UsuarioBundle\Entity\AccionUsuario;

use Loogares\ExtraBundle\Entity\ActividadReciente;

class AjaxController extends Controller{
    public function lugaresGeoJSONAction() {
        list($minx, $miny, $maxx, $maxy) = explode(',', $_GET['bbox']);
        $id = (int) $_GET['id'];

        $limit    = 20 /** round(pow((double) $_GET['scale'], 0.44)) /**/;
        $lugares  = $this->getDoctrine()
          ->getConnection()
            ->fetchAll(("   SELECT  *
                            FROM    (
                                  SELECT
                                          lug.mapx ,
                                          lug.mapy ,
                                          lug.nombre ,
                                          lug.slug ,
                                          lug.estrellas ,
                                          lug.calle ,
                                          lug.numero ,
                                          group_concat(DISTINCT cat.nombre) as categorias_nombre ,
                                          group_concat(DISTINCT cat.slug) as categorias_slug ,
                                          imglug.imagen_full as imagen,
                                          count(rec.id) as recomendaciones ,
                                          tcat.id as tipo ,
                                          com.nombre as comuna ,
                                          ciu.nombre as ciudad ,
                                          ciu.slug as ciudad_slug

                              FROM        lugares lug

                              LEFT JOIN   categoria_lugar catlug  ON catlug.lugar_id = lug.id
                              LEFT JOIN   categorias      cat     ON cat.id = catlug.categoria_id
                              LEFT JOIN   imagenes_lugar  imglug  ON imglug.lugar_id = lug.id
                              LEFT JOIN   tipo_categoria  tcat    ON cat.tipo_categoria_id = tcat.id
                              LEFT JOIN   comuna          com     ON com.id = lug.comuna_id
                              LEFT JOIN   ciudad          ciu     ON com.ciudad_id = ciu.id
                              LEFT JOIN   recomendacion   rec     ON (rec.lugar_id = lug.id
                                                                  AND rec.estado_id != 3)

                              WHERE       (mapy BETWEEN $minx AND $maxx)
                                          AND (mapx BETWEEN $miny AND $maxy)
                                          AND imglug.estado_id != 3
                                          AND lug.estado_id != 3
                                          AND lug.id != {$id}

                              GROUP BY    lug.id
                              ORDER BY    RAND()
                              LIMIT       {$limit} ) features
                          ORDER BY mapx DESC
            "));

        $geoJSON = array(
                  'type'      => 'FeatureCollection' ,
                  'features'  => array()  ,
                  'crs'       => "EPSG:4326"
        );

        $trans  = $this->get('translator');
        $fnMap  = function($nombre, $slug) {
             // var_dump($nombre, $slug);
              return array('nombre' => $nombre, 'slug' => $slug);
        };

        foreach($lugares as &$lugar) {
          $lugar['actual']      = 'f';

          $lugar['categorias']  = array_map(
              $fnMap ,
              explode(',', $lugar['categorias_nombre']) ,
              explode(',', $lugar['categorias_slug'])
          );

          unset($lugar['categorias_nombre']);
          unset($lugar['categorias_slug']);

          foreach($lugar['categorias'] as &$categoria) {
              $categoria['url'] = $this->generateUrl(
                      '_categoria',
                      array(
                              'categoria' => $categoria['slug'],
                              'slug'      => $lugar['ciudad_slug']
                      )
              );
          }

          $lugar['textoCategoria'] = $trans->transChoice(
                                            'general.terminos.categorias'
                                            , sizeof($lugar['categorias'])
          );

          $lugar['textoRecomendacion'] = $trans->transChoice(
                                            'lugar.buscar.total_recomendaciones'
                                            , (int) $lugar['recomendaciones']
          );

          $geoJSON['features'][] = array(
              'type'      => 'Feature' ,
              'geometry'  =>  array(
                  'type'        => 'Point' ,
                  'coordinates' => array($lugar['mapy'], $lugar['mapx'])
              ) ,
              'properties'  => $lugar
          );
        }

        $response =  new Response(json_encode($geoJSON));
    //    $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);

        return $response;
    }

    public function otrosLugaresEnElAreaAction() {
        foreach($_GET as $key => $value) {
          $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING);
        }

        list($mapxDesde, $mapyDesde) = explode(',',$_GET['southWest']);
        list($mapxHasta, $mapyHasta) = explode(',',$_GET['northEast']);
        $idLugar = $_GET['idLugar'];

        $otrosLugaresResult = $this->getDoctrine()->getConnection()
          ->fetchAll("  SELECT      lugares.* ,
                                    group_concat(DISTINCT categorias.nombre) as categorias_nombre ,
                                    group_concat(DISTINCT categorias.slug) as categorias_slug ,
                                    imagen_full ,
                                    count(recomendacion.id) as recomendaciones ,
                                    tipo_categoria.id as tipo ,
                                    comuna.nombre as comuna ,
                                    ciudad.nombre as ciudad ,
                                    ciudad.slug as ciudad_slug

                        FROM        lugares

                        LEFT JOIN   categoria_lugar     ON categoria_lugar.lugar_id = lugares.id
                        LEFT JOIN   categorias          ON categorias.id = categoria_lugar.categoria_id
                        LEFT JOIN   imagenes_lugar      ON imagenes_lugar.lugar_id = lugares.id
                        LEFT JOIN   tipo_categoria      ON categorias.tipo_categoria_id = tipo_categoria.id
                        LEFT JOIN   comuna              ON comuna.id = lugares.comuna_id
                        LEFT JOIN   ciudad              ON comuna.ciudad_id = ciudad.id
                        LEFT JOIN   recomendacion       ON (recomendacion.lugar_id = lugares.id
                                                            AND recomendacion.estado_id != 3)

                        WHERE       lugares.id != $idLugar
                                    AND mapx BETWEEN $mapxDesde AND $mapxHasta
                                    AND mapy BETWEEN $mapyDesde AND $mapyHasta
                                    AND imagenes_lugar.estado_id != 3
                                    AND lugares.estado_id != 3

                        GROUP BY    lugares.id
                        ORDER BY    RAND()
                        LIMIT       20
          ");

        for($i = 0; $i < sizeOf($otrosLugaresResult); $i++){
            $otrosLugaresResult[$i]['categorias_nombre'] = explode(',', $otrosLugaresResult[$i]['categorias_nombre']);
            $otrosLugaresResult[$i]['categorias_slug'] = explode(',', $otrosLugaresResult[$i]['categorias_slug']);

            foreach($otrosLugaresResult[$i]['categorias_nombre'] as $j => $value){
              $catPath = $this->generateUrl('_categoria', array('categoria' => $otrosLugaresResult[$i]['categorias_slug'][$j], 'slug' => $otrosLugaresResult[$i]['ciudad_slug']));
              $otrosLugaresResult[$i]['categorias_nombre'][$j] = "<a href='$catPath'>".$value."</a>";
            }
        }

        return $this->render('LoogaresLugarBundle:Ajax:otrosLugares.html.twig', array('lugares' => $otrosLugaresResult));
    }

    public function filtroDeLugaresAction(){
        return new Response('<h1>Filtro de Lugares</h1>');
    }

    public function sugerirUnLugarAction(){
        return new Response('<h1>Sugerir un Lugar</h1>');
    }

    public function generarComunasPorCiudadAction(){

      $idCiudad = filter_var($_POST['ciudad'], FILTER_SANITIZE_STRING);

      $em = $this->getDoctrine()->getEntityManager();
      $q = $em->createQuery('SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u where u.ciudad = ?1');
      $q->setParameter(1, $idCiudad);
      $comunasPorCiudadResult = $q->getResult();

      return $this->render('LoogaresLugarBundle:Ajax:generarComunasPorCiudad.html.twig', array('comunas' => $comunasPorCiudadResult));
    }

    public function lugarYaExisteAction(){
      foreach($_POST as $key => $value){
        $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
      }

      $calle = $_POST['calle'];
      $numero = $_POST['numero'];
      $id = $_POST['id'];

      $em = $this->getDoctrine()->getEntityManager();
      $q = $em->createQuery('SELECT u FROM Loogares\LugarBundle\Entity\Lugar u where u.calle = ?3 and u.numero = ?2 and u.id != ?1 and u.estado != 3 ORDER BY u.nombre ASC');
      $q->setParameter(1, $id);
      $q->setParameter(2, $numero);
      $q->setParameter(3, $calle);

      $res = $q->getResult();
      if($res){
        foreach($res as $lugar){
          $asd['lugar'][] = "<a href='".$this->generateUrl('_lugar', array('slug' => $lugar->getSlug()))."'>" . $lugar->getNombre() . "</a> - " . $lugar->getCalle() . " " . $lugar->getNumero() . ", " . $lugar->getComuna()->getNombre();
        }
      }else{
        $asd[] = null;
      }
      //Alto nombre de variable
      return new Response(json_encode($asd));
    }

    public function recomendarCalleAction(){
      $fn = $this->get('fn');

      $d = filter_var($_GET['term'], FILTER_SANITIZE_STRING);
      $d = preg_replace('/^[Av]?[Av\.]?[Avda]?[Avda\.]?[Avenida]?[Avenida]?[Calle]?\s/', '', $d);
      $calles = '';
      $em = $this->getDoctrine()->getEntityManager();
      $q = $em->createQuery('SELECT DISTINCT u.calle FROM Loogares\LugarBundle\Entity\Lugar u where u.calle LIKE ?1');
      $q->setParameter(1, "%".$d."%");
      $q->setMaxResults(7);
      $callesResult = $q->getResult();

      foreach($callesResult as $key => $value){
        $calles[] = $value['calle'];
      }

      return new Response(json_encode($calles));
    }

    public function paginaGaleriaAction(Request $request, $id) {
      $fn = $this->get('fn');

      $em = $this->getDoctrine()->getEntityManager();
      $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

      $pagina = (!$request->query->get('pagina')) ? 1 : $request->query->get('pagina');
      $ppag = 20;
      $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);

      $imagenesLugar = $ur->getFotosLugarPaginadas($id, $offset);
      $totalImagenes = $ur->getTotalFotosLugar($id);
      $params = array(
            'id' => $id
      );

      $paginacion = $fn->paginacion($totalImagenes, $ppag, '_paginaGaleria', $params, $this->get('router') );

      return $this->render('LoogaresLugarBundle:Lugares:pagina_galeria.html.twig', array(
            'imagenes' => $imagenesLugar,
            'paginacion' => $paginacion
        ));
    }

    public function recomendacionAction(){
      $_POST['slug'] = filter_var($_POST['slug'], FILTER_SANITIZE_STRING);
      $em = $this->getDoctrine()->getEntityManager();
      $fn = $this->get('fn');
      $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
      $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
      $lugar = $lr->findOneBySlug($_POST['slug']);
      $usuario = $this->get('security.context')->getToken()->getUser();

      $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u WHERE u.usuario = ?1 and u.lugar = ?2 and u.estado = 2");
      $q->setParameter(1, $this->get('security.context')->getToken()->getUser()->getId());
      $q->setParameter(2, $lugar->getId());
      $recomendacionResult = $q->getSingleResult();

      return $this->render('LoogaresLugarBundle:Lugares:recomendacion.html.twig',array(
        'recomendacion' => $recomendacionResult,
        'lugar' => array(
          'slug' => $_POST['slug'],
          'mostrarPrecio' => $fn->mostrarPrecio($lugar)
        )
      ));
    }

    public function accionAction(){
      foreach($_POST as $key => $value){
        $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
      }
      $em = $this->getDoctrine()->getEntityManager();
      $accion = $_POST['accion'];
      $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
      $utr = $em->getRepository("LoogaresUsuarioBundle:Util");
      $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
      $ar = $em->getRepository("LoogaresUsuarioBundle:Accion");
      $arr = $em->getRepository("LoogaresExtraBundle:ActividadReciente");

      $data = array();

      if($_POST['accion'] == 'util'){
        $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Util u WHERE u.usuario = ?1 and u.recomendacion = ?2");
        $q->setParameter(1, $this->get('security.context')->getToken()->getUser()->getId());
        $q->setParameter(2, $_POST['recomendacion']);
        $utilResult = $q->getResult();

        $recomendacion = $rr->findOneById($_POST['recomendacion']);

        if(sizeOf($utilResult) == 0){
          $util = new Util();
          $util->setUsuario($this->get('security.context')->getToken()->getUser());
          $util->setRecomendacion($recomendacion);
          $util->setFecha(new \DateTime());

          //Actualizamos el contador de utiles de la recomendación
          $recomendacion->setUtiles($recomendacion->getUtiles() + 1);

          $em->persist($util);
          $em->flush();

          //Enviamos el Mail del Util
          //set POST variables
          $fields_string = '';

          $url = "http://".$_SERVER['SERVER_NAME'].$this->generateUrl('_utilMail');
          $fields = array(
              'recomendacion' => urlencode($recomendacion->getId()),
              'usuario' => urlencode($this->get('security.context')->getToken()->getUser()->getId())
          );

          //url-ify the data for the POST
          foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
          $fields_string = rtrim($fields_string,'&');

          //open connection
          $ch = curl_init();
          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_POST,2);
          curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
          curl_setopt($ch, CURLOPT_TIMEOUT, 1);
          curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
          //execute post
          curl_exec($ch);

          curl_close($ch);
          $data[] = 'Up to here, ok';

          // Agregamos a Actividad Reciente
          $actividad = new ActividadReciente();
          $actividad->setEntidad('Loogares\UsuarioBundle\Entity\Util');
          $actividad->setEntidadId($util->getId());
          $actividad->setFecha($util->getFecha());
          $actividad->setUsuario($util->getUsuario());
          $actividad->setCiudad($util->getRecomendacion()->getLugar()->getComuna()->getCiudad());

          $tipoActividad = $em->getRepository('LoogaresExtraBundle:TipoActividadReciente')
                              ->findOneByNombre('agregar');
          $estadoActividad = $em->getRepository("LoogaresExtraBundle:Estado")
                                ->findOneByNombre('Aprobado');
          $actividad->setTipoActividadReciente($tipoActividad);
          $actividad->setEstado($estadoActividad);

          $em->persist($actividad);
          $data[] = "Util Ok";
        }else{
          $arr->actualizarActividadReciente($utilResult[0]->getId(), 'Loogares\UsuarioBundle\Entity\Util');
          $em->remove($utilResult[0]);
          $data[] = "Util Sacado";
        }

        $lr->actualizarPromedios($recomendacion->getLugar()->getSlug());
        $em->flush();
        $data[] = "Flush Done";
      }else if($accion == 'favoritos' || $accion == 'estuve_alla' || $accion == 'quiero_ir'| $accion == 'quiero_volver'| $accion == 'recomendar_despues'){
        $lugar = $lr->find($_POST['lugar']);
        $usuario = $this->get('security.context')->getToken()->getUser();

        $accionResult = $lr->getAccionUsuarioLugar($lugar, $usuario, $accion);

        if(!is_object($accionResult)){
          $accionObj = new AccionUsuario();
          $accionObj->setUsuario($usuario);
          $accionObj->setAccion($ar->findOneById($accionResult));
          $accionObj->setLugar($lugar);
          $accionObj->setFecha(new \DateTime());
          $em->persist($accionObj);

          // Si marcamos 'Ya estuve', verificamos estado de 'Quiero ir'
          if($accionObj->getAccion()->getId() == 3) {
            $quieroIr = $lr->getAccionUsuarioLugar($lugar, $usuario, 'quiero_ir');
            if(is_object($quieroIr)) {
              $em->remove($quieroIr);
            }
          }
          // Si marcamos 'Quiero volver', verificamos estado de 'Quiero ir' y 'Ya estuve'
          else if($accionObj->getAccion()->getId() == 2) {
            $quieroIr = $lr->getAccionUsuarioLugar($lugar, $usuario, 'quiero_ir');
            if(is_object($quieroIr)) {
              $em->remove($quieroIr);
            }

            $yaEstuve = $lr->getAccionUsuarioLugar($lugar, $usuario, 'estuve_alla');
            if(!is_object($yaEstuve)) {
              $yaEstuveObj = new AccionUsuario();
              $yaEstuveObj->setUsuario($usuario);
              $yaEstuveObj->setAccion($ar->findOneById($yaEstuve));
              $yaEstuveObj->setLugar($lugar);
              $yaEstuveObj->setFecha(new \DateTime());
              $em->persist($yaEstuveObj);
            }
          }
        }else{
          $em->remove($accionResult);
        }
        $em->flush();

        $totalAcciones = $lr->getTotalAccionesLugar($lugar->getId());

        $accionesUsuario = $lr->getAccionesUsuario($lugar->getId(), $this->get('security.context')->getToken()->getUser()->getId());

        // Verificamos si el usuario puede o no realizar acciones según sus acciones actuales
        for($i = 0; $i < sizeof($accionesUsuario); $i++) {
            $accionesUsuario[$i]['puede'] = 1;

            // Si el usuario ya estuvo, no puede desmarcar esta opción
            if($accionesUsuario[$i]['id'] == 3 && $accionesUsuario[$i]['hecho'] == 1)
                $accionesUsuario[$i]['puede'] = 0;
            else if($accionesUsuario[$i]['id'] == 5 && $accionesUsuario[$i]['hecho'] == 1)
                $accionesUsuario[$i]['puede'] = 0;
        }
        // Si el usuario ya estuvo o quiere volver, no puede querer ir
        if($accionesUsuario[2]['hecho'] == 1 || $accionesUsuario[1]['hecho'] == 1) {
            $accionesUsuario[0]['puede'] = 0;

        }
        $data['totalAcciones'] = $totalAcciones;
        $data['accionesUsuario'] = $accionesUsuario;
      }


      return new Response(json_encode($data), 200);
    }

    public function utilMailAction() {
      foreach($_POST as $key => $value){
        $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
      }
      $em = $this->getDoctrine()->getEntityManager();
      $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
      $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

      $usuario = $ur->find($_POST['usuario']);
      $recomendacion = $rr->find($_POST['recomendacion']);

      // Se envía mail al usuario que recomendó informándole del útil
      $mail = array();
      $nombreUsuario = ($usuario->getNombre() == '' && $usuario->getApellido() == '') ? $usuario->getSlug() : $usuario->getNombre().' '.$usuario->getApellido();
      $mail['asunto'] = $this->get('translator')->trans('lugar.notificaciones.util_recomendacion.mail.asunto', array('%usuario%' => $nombreUsuario,'%lugar%' => $recomendacion->getLugar()->getNombre()));
      $mail['recomendacion'] = $recomendacion;
      $mail['usuario'] = $usuario;
      $mail['tipo'] = "util-recomendacion";

      $paths = array();
      $paths['logo'] = 'assets/images/mails/logo_mails.png';

      $message = $this->get('fn')->enviarMail($mail['asunto'], $recomendacion->getUsuario()->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresLugarBundle:Mails:mail_recomendar.html.twig', $this->get('templating'));
      $this->get('mailer')->send($message);

      return new Response("sent", 200);
    }

    public function cuponesCanjeadosAction(Request $request) {
      $em = $this->getDoctrine()->getEntityManager();
      if($request->request->get('descuentos') == true){
        $gr = $em->getRepository("LoogaresCampanaBundle:DescuentosUsuarios");
        $cr = $em->getRepository("LoogaresCampanaBundle:Campana");
      }else{
        $gr = $em->getRepository("LoogaresBlogBundle:Ganador");
      }

      foreach($request->request->get('canjes') as $c) {
        $ganador = $gr->find($c);
        $ganador->setCanjeado(true);

        $em->persist($ganador);
        //$em->flush();

        $mail = array();
        $paths = array();
        $paths['logo'] = 'assets/images/mails/logo_mails.png';

        if(!$request->request->get('descuentos')){ 
          // Se envía mail al ganador informando el premio
          $usuario = $ganador->getParicipante()->getusuario();

          $mail['asunto'] = $this->get('translator')->trans('extra.modulo_concursos.canjes.mail.asunto', array('%nombre%' => $ganador->getParticipante()->getConcurso()->getPost()->getLugar()->getNombre()));
          $mail['usuario'] = $ganador->getParticipante()->getUsuario();
          $mail['ganador'] = $ganador;
          $mail['lugar'] = $ganador->getParticipante()->getConcurso()->getPost()->getLugar();
          $message = $this->get('fn')->enviarMail($mail['asunto'], $ganador->getParticipante()->getUsuario()->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresLugarBundle:Mails:mail_canje.html.twig', $this->get('templating'));
        }else{
        // Se envía mail al descontado informando el premio
          $campana = $cr->findOneByDescuento($ganador->getDescuento()->getId());
          $usuario = $ganador->getusuario();

          $mail['asunto'] = $this->get('translator')->trans('extra.modulo_concursos.canjes.mail.asunto', array('%nombre%' => $campana->getLugar()->getNombre()));
          $mail['usuario'] = $ganador->getUsuario();
          $mail['ganador'] = $ganador;
          $mail['lugar'] = $campana->getLugar();
        }

        $message = $this->get('fn')->enviarMail($mail['asunto'], $usuario->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresLugarBundle:Mails:mail_canje.html.twig', $this->get('templating'));
        $this->get('mailer')->send($message); 
      }

      return new Response(json_encode(array('status' => 'ok')));
    }

    public function cuponGanadorAction($slug, $id, $ganador) {
        $em = $this->getDoctrine()->getEntityManager();
        $gr = $em->getRepository("LoogaresBlogBundle:Ganador");

        $ganador = $gr->find($ganador);
        $usuario = $ganador->getParticipante()->getUsuario();
        $concurso = $ganador->getParticipante()->getConcurso();

        return $this->render('LoogaresLugarBundle:Lugares:cupon_ganador.html.twig', array(
            'ganador' => $ganador,
            'concurso' => $concurso,
            'usuario' => $usuario
        ));
    }
}
