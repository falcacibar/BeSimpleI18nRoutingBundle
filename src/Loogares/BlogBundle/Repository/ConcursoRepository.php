<?php

namespace Loogares\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ConcursoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ConcursoRepository extends EntityRepository
{
	  public function getConcursoPost($post) {
        $em = $this->getEntityManager();

        //Query para obtener el total de recomendaciones del usuario        
        $q = $em->createQuery("SELECT c
                               FROM Loogares\BlogBundle\Entity\Concurso c
                               WHERE c.post = ?1");
        $q->setParameter(1, $post);
        
        return $q->getOneOrNullResult();  
    }

    public function getConcursosVigentes($ciudad) {
        $em = $this->getEntityManager();

        $q = $em->createQuery("SELECT c, p
                               FROM Loogares\BlogBundle\Entity\Concurso c
                               JOIN c.post p
                               WHERE p.ciudad = ?1
                               AND c.estado_concurso != ?2
                               ORDER BY c.id DESC");
        $q->setParameter(1, $ciudad);
        $q->setParameter(2, 3);
        return $q->getResult();
    }

    public function getGanadoresConcurso($concurso) {
        $em = $this->getEntityManager();

        $q = $em->createQuery("SELECT g, pt
                               FROM Loogares\BlogBundle\Entity\Ganador g
                               JOIN g.participante pt
                               WHERE pt.concurso = ?1");
        $q->setParameter(1, $concurso);
        return $q->getResult();
    }

    public function isUsuarioParticipando($usuario, $concurso) {
    	$em = $this->getEntityManager();

        //Query para obtener el total de recomendaciones del usuario        
        $q = $em->createQuery("SELECT p
                               FROM Loogares\BlogBundle\Entity\Participante p
                               WHERE p.usuario = ?1
                               AND p.concurso = ?2");
        $q->setParameter(1, $usuario);
        $q->setParameter(2, $concurso);
        
        $participa = $q->getOneOrNullResult();

        if($participa == null) {
        	return false;
        }
        return true;
    }
}