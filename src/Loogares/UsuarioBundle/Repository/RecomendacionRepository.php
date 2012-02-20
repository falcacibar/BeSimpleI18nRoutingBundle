<?php

namespace Loogares\UsuarioBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RecomendacionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RecomendacionRepository extends EntityRepository
{
	public function getRecomendacionUsuarioLugar($usuario, $lugar) {
        $em = $this->getEntityManager();

        //Query para obtener la recomendación del usuario en un lugar determinado       
        $q = $em->createQuery("SELECT r
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.usuario = ?1
                               AND r.lugar = ?2
                               AND r.estado != ?3");
        $q->setParameter(1, $usuario);
        $q->setParameter(2, $lugar);
        $q->setParameter(3, 3);

        return $q->getSingleResult();
  }

  public function getUltimaRecomendacion($lugar) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT r
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             WHERE r.lugar = ?1
                             AND r.estado != ?2
                             ORDER BY r.id DESC");
      $q->setParameter(1, $lugar);
      $q->setParameter(2, 3);
      $q->setMaxResults(1);

      return $q->getOneOrNullResult();
  }

  public function getReportesRecomendacionUsuario($recomendacion, $usuario, $estado) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT rr
                             FROM Loogares\LugarBundle\Entity\ReportarRecomendacion rr
                             WHERE rr.recomendacion = ?1
                             AND rr.usuario = ?2
                             AND rr.estado = ?3");
      $q->setParameter(1, $recomendacion);
      $q->setParameter(2, $usuario);
      $q->setParameter(3, $estado);
      return $q->getResult();
  }

  public function getTotalRecomendaciones() {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT count(r.id)
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             WHERE r.estado != ?1");
      $q->setParameter(1, 3);

      return $q->getSingleScalarResult();
  }

  public function getRecomendacionDelDia($ciudad) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT r
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             JOIN r.lugar l
                             JOIN l.comuna c
                             WHERE r.fecha_ultima_vez_destacada = CURRENT_DATE()
                             AND c.ciudad = ?1");

      $q->setParameter(1, $ciudad);
      $rec = $q->getOneOrNullResult();
      if($rec ==  null) {
          // Seleccionamos una como destacada
          $q = $em->createQuery("SELECT r, l, SIZE(r.util) util
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             JOIN r.lugar l
                             JOIN l.comuna c
                             WHERE c.ciudad = ?1
                             AND l.estado != ?2
                             AND DATE_SUB(CURRENT_DATE(), 90, 'DAY') >= r.fecha_ultima_vez_destacada
                             AND DATE_SUB(CURRENT_DATE(), 8, 'MONTH') <= r.fecha_creacion
                             GROUP BY r.id
                             ORDER BY util DESC, r.estrellas DESC, r.fecha_creacion DESC");
          $q->setParameter(1, $ciudad);
          $q->setParameter(2, 3);
          $q->setMaxResults(1);
          $rec = $q->getOneOrNullResult();
          if($rec != null) {
              $rec = $rec[0];

              $rec->setFechaUltimaVezDestacada(new \DateTime());
              $em->flush();
          }
      }      
      return $rec;
  }
}