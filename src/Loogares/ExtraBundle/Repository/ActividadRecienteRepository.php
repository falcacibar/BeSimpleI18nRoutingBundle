<?php

namespace Loogares\ExtraBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ActividadRecienteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActividadRecienteRepository extends EntityRepository
{
    public function actualizarActividadReciente($id, $entidad){
    	$em = $this->getEntityManager();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\ActividadReciente u WHERE u.entidad_id = ?1 and u.endidad = $entidad");
        $q->setParameter(1, $id);
        $q->setParameter(2, $entidad);
        $reciente = $q->getResult();
        
        if($reciente != null){
            $em->remove($reciente[0]);
        }
    }

	public function getActividadReciente($results, $ciudad=null, $usuario=null, $entity=null, $offset=null) {
		$em = $this->getEntityManager();
		
		if($entity != null) {
			if($entity == 'recomendaciones')
				$entity = "Loogares\UsuarioBundle\Entity\Recomendacion";
			else if($entity == 'lugares') 
				$entity = "Loogares\LugarBundle\Entity\Lugar";
			else if($entity == 'fotos')
				$entity = "Loogares\LugarBundle\Entity\ImagenLugar";
			else if($entity == 'utiles')
				$entity = "Loogares\UsuarioBundle\Entity\Util";
		}
		
		if($offset == null)
          	$offset = '';
        if($ciudad != null)
        	$where = ' WHERE ar.ciudad = '.$ciudad;
        if($usuario != null)
        	$where = ' WHERE ar.usuario = '.$usuario;
        if($entity != null)
        	$where .= " AND ar.entidad = '".$entity."'";
        $q = $em->createQuery("SELECT ar, u
                               FROM Loogares\ExtraBundle\Entity\ActividadReciente ar
                               JOIN ar.usuario u".
                               $where.
                               " ORDER BY ar.fecha DESC")
                ->setMaxResults($results)
                ->setFirstResult($offset);       

        return $q->getResult();
	}

	public function getTotalActividad($ciudad=null, $usuario=null, $entity=null) {
		$em = $this->getEntityManager();

		$where = '';
		if($ciudad != null)
        	$where = ' WHERE ar.ciudad = '.$ciudad;
        else if($usuario != null)
        	$where = ' WHERE ar.usuario = '.$usuario;

		if($entity != null) {
			if($entity == 'recomendaciones')
				$entity = "Loogares\UsuarioBundle\Entity\Recomendacion";
			else if($entity == 'lugares')
				$entity = "Loogares\LugarBundle\Entity\Lugar";
			else if($entity == 'fotos')
				$entity = "Loogares\LugarBundle\Entity\ImagenLugar";
			else if($entity == 'utiles')
				$entity = "Loogares\UsuarioBundle\Entity\Util";

			$where .= " AND ar.entidad ='".$entity."'";
		}
		$q = $em->createQuery("SELECT count(ar.id)
                               FROM Loogares\ExtraBundle\Entity\ActividadReciente ar".
                               $where);
        return $q->getSingleScalarResult();
	}

}