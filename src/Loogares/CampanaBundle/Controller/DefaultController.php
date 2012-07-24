<?php

namespace Loogares\CampanaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Loogares\CampanaBundle\Entity\Descuento;
use Loogares\CampanaBundle\Entity\DescuentosUsuarios;


class DefaultController extends Controller{
	public function indexAction($slug){
		$em = $this->getDoctrine()->getEntityManager();
		$lugarRepository = $em->getRepository('LoogaresLugarBundle:Lugar');
		$seguidoresRepository = $em->getRepository('LoogaresBlogBundle:Participante');

		$lugar = $lugarRepository->findOneBySlug($slug);

		$q = $em->createQuery("SELECT count(c) FROM Loogares\BlogBundle\Entity\Concurso c
																JOIN c.post p
																WHERE p.lugar = ?1");
		$q->setParameter(1, $lugar);
		$concursos = $q->getSingleScalarResult();

		$q = $em->createQuery('SELECT count(c) FROM Loogares\CampanaBundle\Entity\Campana c
													 WHERE c.lugar = ?1 AND c.descuento != ?2');
		$q->setParameter(1, $lugar);
		$q->setParameter(2, null);
		$descuentos = $q->getSingleScalarResult();

    $seguidores = $this->getDoctrine()->getConnection()
        					->fetchAll("SELECT count(DISTINCT u.id) as seguidores
															FROM concursos_usuario cu

															INNER JOIN usuarios u ON u.id = cu.usuario_id 
															INNER JOIN concursos c ON c.id = cu.concurso_id 
															INNER JOIN blog_posts p ON p.id = c.post_id 
															LEFT JOIN ganadores g ON g.participante_id = cu.id
															LEFT JOIN descuentos_usuarios du ON du.usuario_id = u.id

															WHERE p.lugar_id = {$lugar->getId()}");
    $seguidores = $seguidores[0]['seguidores'];

		return $this->render('LoogaresCampanaBundle:Default:index.html.twig', array(
			'concursos' => $concursos,
			'descuentos' => $descuentos,
			'seguidores' => $seguidores,
			'lugar' => $lugar
		));
	}

	public function listadoCampanasAction($slug){
		$em = $this->getDoctrine()->getEntityManager();
		$lugarRepository = $em->getRepository('LoogaresLugarBundle:Lugar');
		$campanaRepository = $em->getRepository('LoogaresCampanaBundle:Campana');

		$lugar = $lugarRepository->findOneBySlug($slug);
		$campanas = $campanaRepository->findByLugar($lugar->getId());

		return $this->render('LoogaresCampanaBundle:Default:listado_campanas.html.twig', array(
			'slug' => $slug,
			'campanas' => $campanas,
			'meses' => array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre'	, 'Diciembre')
		));
	}

	public function listadoConcursosAction($slug, $id){
		$em = $this->getDoctrine()->getEntityManager();
		$lugarRepository = $em->getRepository('LoogaresLugarBundle:Lugar');
		$concursoRepository = $em->getRepository('LoogaresBlogBundle:Concurso');
		$campanaRepository = $em->getRepository('LoogaresCampanaBundle:Campana');
		$detalles = array();

		$lugar = $lugarRepository->findOneBySlug($slug);

		$q = $em->createQuery("SELECT c FROM Loogares\BlogBundle\Entity\Concurso c
													 JOIN c.post p
													 WHERE p.lugar = ?1");
		$q->setParameter(1, $lugar);
		$concursos = $q->getResult();

		$meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre'	, 'Diciembre');

		return $this->render('LoogaresCampanaBundle:Default:listado_concursos.html.twig',array(
			'concursos' => $concursos,
			'slug' => $slug,
			'meses' => $meses,
			'id' => $id
		));
	}
  
  public function detalleConcursoAction($slug, $id, $idConcurso) {
    $em = $this->getDoctrine()->getEntityManager();
    $cr = $em->getRepository("LoogaresBlogBundle:Concurso");
    $dr = $em->getRepository("LoogaresUsuarioBundle:Dueno");
    $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
    $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");

    $lugar = $lr->findOneBySlug($slug);

    $concurso = $cr->find($id);

    $q = $em->createQuery("SELECT cr FROM Loogares\BlogBundle\Entity\Concurso cr
    											 JOIN cr.post p	
    											 WHERE p.lugar = ?1 and cr.id = ?2 AND cr.estado_concurso = 3");
    $q->setParameter(1, $lugar);
    $q->setParameter(2, $idConcurso);

    $concurso = $q->getOneOrNullResult();

    if(!$concurso){
      throw $this->createNotFoundException('');
    }
    
     $ganadores = $cr->getGanadoresConcurso($concurso);

      // Asociamos a cada ganador si el usuario ha recomendado con anterioridad o no
      foreach($ganadores as $ganador) {
          $usuario = $ganador->getParticipante()->getUsuario();
          $lugar = $ganador->getParticipante()->getConcurso()->getPost()->getLugar();
          $recomendacion = $rr->findOneBy(array('usuario' => $usuario->getId(), 'lugar' => $lugar->getId()));
          if(!$recomendacion) {
              $ganador->recomendo = false;
          }
          else {
              $ganador->recomendo = true;
              $ganador->recomendacion = $recomendacion;
          }
      }
      $concurso->ganadores = $ganadores;

    return $this->render('LoogaresCampanaBundle:Default:reporte_concurso.html.twig', array(
        'concurso' => $concurso,
        'id' => $id
    ));
	}

	public function detalleDescuentosAction($slug, $id){
    $em = $this->getDoctrine()->getEntityManager();
    $cr = $em->getRepository("LoogaresCampanaBundle:Campana");

    $campana = $cr->findOneById($id);

    if( !$campana->getDescuento() ){
    	return $this->render('LoogaresCampanaBundle:Default:listado_descuento.html.twig', array('slug' => $slug, 'id' => $id));
    }
    
    return $this->render('LoogaresCampanaBundle:Default:reporte_descuento.html.twig', array('slug' => $slug, 'id' => $id));
 	}


	public function seguidoresAction($slug){
		$em = $this->getDoctrine()->getEntityManager();
		$lr = $em->getRepository("LoogaresLugarBundle:Lugar");
		$comuna = null;
		$recomendo = null;
		$orden = " ORDER BY totalRecomendaciones DESC";

		$ordenFilters = array(
			'recomendaciones' => 'totalRecomendaciones',
			'premios' => 'totalBe',
			'descuentos' => 'totalDescuentos'
		);

    $lugar = $lr->findOneBySlug($slug);

    if(isset($_GET['comuna']) && $_GET['comuna'] != 'todas'){
    	$comuna = " AND co.slug = '" . $_GET['comuna'] . "'";
    }

    if(isset($_GET['recomendo'])){
    	$recomendo = " AND r.id != 0";
    }

    if(isset($_GET['orden'])){
    	if(isset($ordenFilters[$_GET['orden']])){
    		$orden = " ORDER BY {$ordenFilters[$_GET['orden']]} DESC";
	    }
    }

    $seguidores = $this->getDoctrine()->getConnection()
        					->fetchAll("SELECT u.id AS usuarioId, u.nombre AS usuarioNombre, u.apellido AS usuarioApellido, u.slug AS usuarioSlug, 
        											count(g.id) as totalBe, co.nombre AS comunaNombre, co.slug AS comunaSlug, count(du.id) AS totalDescuentos, r.id AS recomendo,
														  (SELECT count(id) FROM recomendacion WHERE recomendacion.estado_id = 2 AND usuario_id = u.id) AS totalRecomendaciones
															FROM concursos_usuario cu

															INNER JOIN usuarios u ON u.id = cu.usuario_id 
															LEFT JOIN comuna co ON co.id = u.comuna_id 
															LEFT JOIN recomendacion r ON r.estado_id = 2 AND r.usuario_id = u.id AND r.lugar_id = {$lugar->getId()}


															INNER JOIN concursos c ON c.id = cu.concurso_id 
															INNER JOIN blog_posts p ON p.id = c.post_id 
															LEFT JOIN ganadores g ON g.participante_id = cu.id
															LEFT JOIN descuentos_usuarios du ON du.usuario_id = u.id

															WHERE p.lugar_id = {$lugar->getId()} $comuna $recomendo
															GROUP BY u.id
															$orden");

		  $comunas = $this->getDoctrine()->getConnection()
        				->fetchAll("SELECT co.slug AS slug, co.nombre as nombre
        										FROM concursos_usuario cu

														INNER JOIN usuarios u ON u.id = cu.usuario_id 
        										INNER JOIN comuna co ON co.id = u.comuna_id

														INNER JOIN concursos c ON c.id = cu.concurso_id 
														INNER JOIN blog_posts p ON p.id = c.post_id 

														WHERE p.lugar_id = {$lugar->getId()}
        										GROUP BY co.id");

		return $this->render('LoogaresCampanaBundle:Default:seguidores.html.twig', array(
			'seguidores' => $seguidores,
			'lugar' => $lugar,
			'comunas' => $comunas,
			'filtrado' => (isset($_GET['comuna'])?$_GET['comuna']:null),
			'orden' => (isset($_GET['orden'])?$_GET['orden']:'recomendaciones'),
			'recomendo' => (isset($_GET['recomendo'])?'a':null) 
		));
	}

	public function nuevoDescuentoAction($slug, $id){
		$em = $this->getDoctrine()->getEntityManager();
		$lr = $em->getRepository("LoogaresLugarBundle:Lugar");
		$cr = $em->getRepository("LoogaresCampanaBundle:Campana");

		$campana = $cr->findOneById($id);

		if($campana->getDescuento()){
			return $this->redirect($this->generateUrl('_reporte_descuentos_detalle', array('slug' => $slug, 'id' => $id)));
		}

		$comuna = null;

    $lugar = $lr->findOneBySlug($slug);

    if(isset($_GET['comuna'])){
    	$comuna = " AND com.slug = '" . $_GET['comuna'] . "'";
    }

		$q = $em->createQuery("SELECT p 
													 FROM Loogares\BlogBundle\Entity\Participante p
													 JOIN p.concurso c
													 JOIN c.post po
													 JOIN p.usuario u
													 JOIN u.comuna com
													 WHERE po.lugar = ?1 $comuna
													 GROUP BY p.usuario");

		$q->setParameter(1, $lugar);
		$seguidores = $q->getResult();

		$q = $em->createQuery("SELECT p
													 FROM Loogares\BlogBundle\Entity\Participante p
													 JOIN p.concurso c
													 JOIN c.post po
													 JOIN p.usuario u
													 JOIN u.comuna com
													 WHERE po.lugar = ?1
													 GROUP BY com.id
													 ORDER BY com.nombre ASC");
		$q->setParameter(1, $lugar);
		$comunas = $q->getResult();

		return $this->render('LoogaresCampanaBundle:Default:nuevo_descuento.html.twig', array(
			'seguidores' => $seguidores,
			'lugar' => $lugar,
			'id' => $id,
			'comunas' => $comunas,
			'filtrado' => (isset($_GET['comuna'])?$_GET['comuna']:null),
		));
	}

	public function submitDescuentoAction(Request $request, $slug, $id){
		if ($request->getMethod() == 'POST') {
			$em = $this->getDoctrine()->getEntityManager();
    	$ur = $em->getRepository('LoogaresUsuarioBundle:Usuario');
    	$cr = $em->getRepository('LoogaresCampanaBundle:Campana');
    	
    	$post = $_POST;
   		$descuento = new Descuento();
   		$campana = $cr->findOneById($id);

    	$descuento->setFechaInicio(new \DateTime());
    	$descuento->setCondiciones($post['condiciones']);
    	$descuento->setFechaTermino(new \DateTime('+5'));
    	$descuento->setCantidad($post['dco']);

    	$em->persist($descuento);
    	$em->flush();

    	$campana->setDescuento($descuento);
    	$em->persist($campana);

    	foreach($post['seguidores'] as $seguidor){
    		$descuentosUsuarios = new DescuentosUsuarios();
    		
    		$descuentosUsuarios->setDescuento($descuento);
    		$descuentosUsuarios->setUsuario($ur->findOneBySlug($seguidor));
    		$descuentosUsuarios->setCodigo($this->get('fn')->genRandomString(8));
    		$descuentosUsuarios->setCanjeado(0);

    		$em->persist($descuentosUsuarios);
    	}

    	$em->flush();
    }

		print_r($_POST);
		die();
		return new Response($lugar);
	}

}
