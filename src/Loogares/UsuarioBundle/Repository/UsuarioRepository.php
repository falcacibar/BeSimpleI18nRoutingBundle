<?php

namespace Loogares\UsuarioBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UsuarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsuarioRepository extends EntityRepository
{

	public function getUsuarioRecomendaciones($id) {
		$em = $this->getEntityManager();

		//Query para obtener el total de recomendaciones del usuario
        $q = $em->createQuery("SELECT r
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion r 
                               WHERE r.usuario = ?1");
        $q->setParameter(1, $id);

        return $q->getResult();
	}

	public function getLugaresAgregadosUsuario($id) {
		$em = $this->getEntityManager();

		 //Query para obtener el total de lugares agregados por el usuario
        $q = $em->createQuery("SELECT COUNT(l) total 
                               FROM Loogares\LugarBundle\Entity\Lugar l 
                               WHERE l.usuario_id = ?1");
        $q->setParameter(1, $id);
        return $q->getSingleResult();
	}

	public function getFotosLugaresAgregadasUsuario($id) {
		$em = $this->getEntityManager();

		//Query para obtener el total de fotos de lugares agregadas por el usuario
        $q = $em->createQuery("SELECT COUNT(im) total 
                               FROM Loogares\LugarBundle\Entity\ImagenLugar im 
                               WHERE im.usuario = ?1");
        $q->setParameter(1, $id);
        return $q->getSingleResult();
	}

	public function getPrimerasRecomendaciones($id) {
		$em = $this->getEntityManager();

		//Verificamos si usuario es el primero en recomendar en el lugar
		$q = $em->createQuery("SELECT l.nombre, r1.texto 
							   FROM LoogaresUsuarioBundle:Recomendacion r1 
							   JOIN r1.lugar l
							   WHERE r1.usuario = ?1
							   AND r1.fecha_creacion = (SELECT MIN(r2.fecha_creacion) 
							   FROM LoogaresUsuarioBundle:Recomendacion r2 
							   WHERE r2.lugar = r1.lugar)");
		$q->setParameter(1,$id);
		return $q->getResult();
	}
}