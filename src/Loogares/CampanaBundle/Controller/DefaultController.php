<?php

namespace Loogares\CampanaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


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

	public function campanasAction($slug, $tipo){
		$em = $this->getDoctrine()->getEntityManager();
		$lugarRepository = $em->getRepository('LoogaresLugarBundle:Lugar');
		$concursoRepository = $em->getRepository('LoogaresBlogBundle:Concurso');
		$campanaRepository = $em->getRepository('LoogaresCampanaBundle:Campana');
		$detalles = array();

		$lugar = $lugarRepository->findOneBySlug($slug);

		if($tipo == 'concursos'){
			$q = $em->createQuery("SELECT c FROM Loogares\BlogBundle\Entity\Concurso c
																										JOIN c.post p
																										WHERE p.lugar = ?1");
			$q->setParameter(1, $lugar);
			$concursos = $q->getResult();

			foreach($concursos as $concurso){
				$detalles[]['titulo'] = $concurso->getPost()->getTitulo();
				$detalles[sizeOf($detalles)-1]['fechaInicio'] = $concurso->getFechaInicio();
				$detalles[sizeOf($detalles)-1]['descripcion'] = $concurso->getDescripcion();
			}
		}else if($tipo == 'descuentos'){
			$q = $em->createQuery("SELECT d FROM Loogares\CampanaBundle\Entity\Descuento d
																										WHERE d.id = ?1");
			$q->setParameter(1, 5555);
			$descuentos = $q->getResult();

			if($descuentos){
				foreach($concursos as $concurso){
					$detalles[]['titulo'] = $concurso->getPost()->getTitulo();
					$detalles[sizeOf($detalles)-1]['fechaInicio'] = $concurso->getFechaInicio();
					$detalles[sizeOf($detalles)-1]['descripcion'] = $concurso->getDescripcion();
				}
			}
		}

		$fn = $this->get('fn');

		return $this->render('LoogaresCampanaBundle:Default:campanas.html.twig',array(
			'detalles' => $detalles,
			'tipo' => $tipo,
			'slug' => $lugar->getSlug()
		));
	}
  
  public function reporteLocalAction($slug, $id) {
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

    return $this->render('LoogaresCampanaBundle:Default:be.html.twig', array(
        'concurso' => $concurso
    ));
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

	public function descuentosAction($slug){
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

		return $this->render('LoogaresCampanaBundle:Default:descuentos.html.twig', array(
			'seguidores' => $seguidores,
			'lugar' => $lugar,
			'comunas' => $comunas,
			'filtrado' => (isset($_GET['comuna'])?$_GET['comuna']:null) 
		));
	}

}
