<?php

namespace Loogares\CampanaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

		$q = $em->createQuery("SELECT count(p.id) FROM Loogares\BlogBundle\Entity\Participante p
																		JOIN p.concurso c
																		JOIN c.post po
																		WHERE po.lugar = ?1");
		$q->setParameter(1, $lugar);
		$seguidores = $q->getSingleScalarResult();

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
			'campanas' => $campanas
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
  
  public function detalleConcursoAction($slug, $id) {
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
    $q->setParameter(2, $id);

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
    return $this->render('LoogaresCampanaBundle:Default:reporte_descuento.html.twig', array('slug' => $slug, 'id' => $id));
 	}


	public function seguidoresAction($slug){
		$em = $this->getDoctrine()->getEntityManager();
		$lr = $em->getRepository("LoogaresLugarBundle:Lugar");
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

		return $this->render('LoogaresCampanaBundle:Default:seguidores.html.twig', array(
			'seguidores' => $seguidores,
			'lugar' => $lugar,
			'comunas' => $comunas,
			'filtrado' => (isset($_GET['comuna'])?$_GET['comuna']:null) 
		));
	}

	public function nuevoDescuentoAction($slug, $id){
		$em = $this->getDoctrine()->getEntityManager();
		$lr = $em->getRepository("LoogaresLugarBundle:Lugar");
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
			'filtrado' => (isset($_GET['comuna'])?$_GET['comuna']:null) 
		));
	}

	public function submitDescuentoAction(Request $request, $slug){
		if ($request->getMethod() == 'POST') {
			$em = $this->getDoctrine()->getEntityManager();
    	$ur = $em->getRepository('LoogaresUsuarioBundle:Usuario');
    	
    	$post = $_POST;
   		$descuento = new Descuento();

    	$descuento->setFechaInicio(new \DateTime());
    	$descuento->setCondiciones($post['condiciones']);
    	$descuento->setFechaTermino(new \DateTime('+5'));
    	$descuento->setCantidad($post['cantidad']);

    	$em->persist($descuento);
    	$em->flush();

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
