<?php

namespace Loogares\UsuarioBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AccionUsuarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AccionUsuarioRepository extends EntityRepository
{
    public function actualizarAcccionesUsuario($idLugar, $idLugarNuevo, $idUsuario){
        //Actualizamos las Acciones
        $em = $this->getEntityManager();
        $lr = $em->getRepository('LoogaresLugarBundle:Lugar');
        $lugar = $lr->findOneById($idLugarNuevo);

        $q = $em->createQuery("SELECT au FROM Loogares\UsuarioBundle\Entity\AccionUsuario au
                           WHERE au.lugar = ?1 and au.usuario = ?2 and (au.accion = ?3)");
        $q->setParameter(1, $idLugar)
          ->setParameter(2, $idUsuario)
          ->setParameter(3, 3);

        $accionesUsuario = $q->getOneOrNullResult();

        if($accionesUsuario != null){
            $accionesUsuario->setLugar($lugar);
            $em->persist($accionesUsuario);
        }

        $q->setParameter(3, 2);
        $accionesUsuario = $q->getOneOrNullResult();

        if($accionesUsuario != null){
            $accionesUsuario->setLugar($lugar);
            $em->persist($accionesUsuario);
        }

        $em->flush();
    }

    public function borrarAccionesUsuario($idLugar, $idUsuario){
        //Actualizamos las Acciones
        $em = $this->getEntityManager();
        $lr = $em->getRepository('LoogaresLugarBundle:Lugar');
        
        $q = $em->createQuery("SELECT au FROM Loogares\UsuarioBundle\Entity\AccionUsuario au
                           WHERE au.lugar = ?1 and au.usuario = ?2 and (au.accion = ?3)");
        $q->setParameter(1, $idLugar)
          ->setParameter(2, $idUsuario)
          ->setParameter(3, 3);

        $accionesUsuario = $q->getOneOrNullResult();

        if($accionesUsuario != null){
            $em->remove($accionesUsuario);
        }

        $q->setParameter(3, 2);
        $accionesUsuario = $q->getOneOrNullResult();

        if($accionesUsuario != null){
			$em->remove($accionesUsuario);
        }
    }
}