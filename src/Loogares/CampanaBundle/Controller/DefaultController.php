<?php

namespace Loogares\CampanaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller{

	public function indexAction($slug){
		$em = $this->getDoctrine()->getEntityManager();
		$lugarRepository = $em->getRepository('LoogaresLugarBundle:Lugar');

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

		return $this->render('LoogaresCampanaBundle:Default:index.html.twig', array(
			'concursos' => $concursos,
			'descuentos' => $descuentos,
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
		}

		$fn = $this->get('fn');

		return $this->render('LoogaresCampanaBundle:Default:campanas.html.twig',array(
			'detalles' => $detalles,
			'tipo' => $tipo,
			'slug' => $lugar->getSlug()
		));
	}

}
