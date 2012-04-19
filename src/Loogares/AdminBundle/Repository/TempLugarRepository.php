<?php

namespace Loogares\AdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TempLugarRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TempLugarRepository extends EntityRepository
{

    public function getTotalLugaresARevisarPorCiudad($ciudad){
        $em = $this->getEntityManager();

        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $idCiudad = $cr->findOneBySlug($ciudad);

        $q = $em->createQuery("SELECT count(distinct u.lugar) 
                               FROM Loogares\AdminBundle\Entity\TempLugar u 
                               LEFT JOIN u.comuna c 
                               where u.estado = ?1 and c.ciudad = ?2");
        $q->setParameter(1, 1);
        $q->setParameter(2, $idCiudad);

        return $q->getSingleScalarResult();
    }

    public function getRevisionesPendientes($id){
        $em = $this->getEntityManager();
        $q = $em->createQuery("SELECT count(distinct u.lugar) FROM Loogares\AdminBundle\Entity\TempLugar u where u.lugar = ?1");
        $q->setParameter(1, $id);
        $revisionesPendientesResult = $q->getSingleScalarResult();

        return $revisionesPendientesResult;
    }
}