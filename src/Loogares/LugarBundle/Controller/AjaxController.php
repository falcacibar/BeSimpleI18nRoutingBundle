<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Loogares\UsuarioBundle\Entity\Util;


class AjaxController extends Controller
{
    public function otrosLugaresEnElAreaAction(){
        list($mapxDesde, $mapyDesde) = explode(',',$_GET['southWest']);
        list($mapxHasta, $mapyHasta) = explode(',',$_GET['northEast']);
        $idLugar = $_GET['idLugar'];

        $otrosLugaresResult = $this->getDoctrine()->getConnection()->fetchAll("SELECT lugares.*, group_concat(DISTINCT categorias.nombre) as categorias, imagen_full, count(recomendacion.id) as recomendaciones, tipo_categoria.id as tipo, comuna.nombre as comuna, ciudad.nombre as ciudad
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
                                                                               GROUP BY lugares.id
                                                                               ORDER BY RAND()
                                                                               LIMIT 20");
        
        for($i = 0; $i < sizeOf($otrosLugaresResult); $i++){
            $otrosLugaresResult[$i]['categorias'] = explode(',',$otrosLugaresResult[$i]['categorias']);
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
      $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
      $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
      $lugar = $lr->findOneBySlug($_POST['slug']);
      $usuario = $this->get('security.context')->getToken()->getUser();

      $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u WHERE u.usuario = ?1 and u.lugar = ?2");
      $q->setParameter(1, $this->get('security.context')->getToken()->getUser()->getId());
      $q->setParameter(2, $lugar->getId());
      $recomendacionResult = $q->getSingleResult();


      return $this->render('LoogaresLugarBundle:Lugares:recomendacion.html.twig',array('recomendacion' => $recomendacionResult, 'lugar' => array('slug' => $_POST['slug'])));
    }

    public function utilAction(){
      $em = $this->getDoctrine()->getEntityManager();
      $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
      $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
      $utr = $em->getRepository("LoogaresUsuarioBundle:Util");
      $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

      $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Util u WHERE u.usuario = ?1 and u.recomendacion = ?2");
      $q->setParameter(1, $_POST['usuario']);
      $q->setParameter(2, $_POST['recomendacion']);
      $utilResult = $q->getResult();

      $usuario = $ur->findOneById($_POST['usuario']);
      $recomendacion = $rr->findOneById($_POST['recomendacion']);

      if(sizeOf($utilResult) == 0){
        $util = new Util();
        $util->setUsuario($usuario);
        $util->setRecomendacion($recomendacion);
        $util->setFecha(new \DateTime());

        $em->persist($util);
      }else{
        $em->remove($utilResult[0]);
      }

      $lr->actualizarPromedios($recomendacion->getLugar()->getSlug());
      $em->flush();

      return new Response(sizeOf($utilResult), 200);
    }
}
