<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Loogares\UsuarioBundle\Entity\Util;
use Loogares\UsuarioBundle\Entity\AccionUsuario;


class AjaxController extends Controller
{
    public function otrosLugaresEnElAreaAction(){
        list($mapxDesde, $mapyDesde) = explode(',',$_GET['southWest']);
        list($mapxHasta, $mapyHasta) = explode(',',$_GET['northEast']);
        $idLugar = $_GET['idLugar'];

        $otrosLugaresResult = $this->getDoctrine()->getConnection()
        ->fetchAll("SELECT lugares.*, group_concat(DISTINCT categorias.nombre) as categorias_nombre, group_concat(DISTINCT categorias.slug) as categorias_slug, imagen_full, 
          count(recomendacion.id) as recomendaciones, tipo_categoria.id as tipo, comuna.nombre as comuna, ciudad.nombre as ciudad, ciudad.slug as ciudad_slug
                   FROM lugares
                   LEFT JOIN categoria_lugar
                   ON categoria_lugar.lugar_id = lugares.id
                   LEFT JOIN categorias
                   ON categorias.id = categoria_lugar.categoria_id
                   LEFT JOIN imagenes_lugar
                   ON imagenes_lugar.lugar_id = lugares.id
                   LEFT JOIN tipo_categoria
                   ON categorias.tipo_categoria_id = tipo_categoria.id
                   LEFT JOIN comuna
                   ON comuna.id = lugares.comuna_id
                   LEFT JOIN ciudad
                   ON comuna.ciudad_id = ciudad.id
                   LEFT JOIN recomendacion
                   ON recomendacion.lugar_id = lugares.id
                   WHERE lugares.id != $idLugar
                   AND mapx BETWEEN $mapxDesde AND $mapxHasta
                   AND mapy BETWEEN $mapyDesde AND $mapyHasta
                   AND imagenes_lugar.estado_id != 3
                   AND lugares.estado_id != 3
                   GROUP BY lugares.id
                   ORDER BY RAND()
                   LIMIT 20");
        
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
      $idCiudad = $_POST['ciudad'];
    
      $em = $this->getDoctrine()->getEntityManager();
      $q = $em->createQuery('SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u where u.ciudad = ?1');
      $q->setParameter(1, $idCiudad);
      $comunasPorCiudadResult = $q->getResult();

      return $this->render('LoogaresLugarBundle:Ajax:generarComunasPorCiudad.html.twig', array('comunas' => $comunasPorCiudadResult));
    }

    public function lugarYaExisteAction(){
      $calle = $_POST['calle'];
      $numero = $_POST['numero'];
      $id = $_POST['id'];

      $em = $this->getDoctrine()->getEntityManager();
      $q = $em->createQuery('SELECT u FROM Loogares\LugarBundle\Entity\Lugar u where u.calle = ?3 and u.numero = ?2 and u.id != ?1 and u.estado != 3');
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

      return new Response(json_encode($asd));
    }

    public function recomendarCalleAction(){
      $d = $_GET['term'];
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
      $em = $this->getDoctrine()->getEntityManager();
      $accion = $_POST['accion'];
      $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
      $utr = $em->getRepository("LoogaresUsuarioBundle:Util");
      $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
      $ar = $em->getRepository("LoogaresUsuarioBundle:Accion");

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

          $em->persist($util);
        }else{
          $em->remove($utilResult[0]);
        }

        $lr->actualizarPromedios($recomendacion->getLugar()->getSlug());
        $em->flush();
        $data[] = ":D";
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
      $em = $this->getDoctrine()->getEntityManager();
      $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");

      $usuario = $this->get('security.context')->getToken()->getUser();
      $recomendacion = $rr->findOneById($_POST['recomendacion']);

      // Se envía mail al usuario que recomendó informándole del útil
      $mail = array();
      $nombreUsuario = ($usuario->getNombre() == '' && $usuario->getApellido() == '') ? $usuario->getSlug() : $usuario->getNombre().' '.$usuario->getApellido();
      $mail['asunto'] = $this->get('translator')->trans('lugar.notificaciones.util_recomendacion.mail.asunto', array('%usuario%' => $nombreUsuario,'%lugar%' => $recomendacion->getLugar()->getNombre()));
      $mail['recomendacion'] = $recomendacion;
      $mail['usuario'] = $usuario;
      $mail['tipo'] = "util-recomendacion";

      $paths = array();
      $paths['logo'] = 'assets/images/extras/logo_mails.jpg';

      //$message = $this->get('fn')->enviarMail($mail['asunto'], $recomendacion->getUsuario()->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresLugarBundle:Mails:mail_recomendar.html.twig', $this->get('templating'));
      //$this->get('mailer')->send($message);

      return new Response("sent", 200);
    }
}
