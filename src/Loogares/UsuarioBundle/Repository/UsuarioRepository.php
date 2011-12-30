<?php

namespace Loogares\UsuarioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UsuarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsuarioRepository extends EntityRepository implements UserProviderInterface
{

    public function loadUserByUsername($mail) {
       return $this->getEntityManager()
           		 ->createQuery('SELECT u
           				FROM LoogaresUsuarioBundle:Usuario u
           				WHERE u.mail = :mail
           				OR u.slug = :mail')
          		 ->setParameter('mail', $mail)
          		 ->getOneOrNullResult();
    }
   
    public function refreshUser(UserInterface $user) {
        return $this->loadUserByUsername($user->getId());
    }
   
    public function supportsClass($class) {
        return $class === 'Loogares\UsuarioBundle\Entity\Usuario';
    }

    public function findOneByIdOrSlug($param) {
        $usuarioResult = $this->find($param);          
        if(!$usuarioResult) {
            $usuarioResult = $this->findOneBySlug($param);
        }
        return $usuarioResult; 
    }

    public function getIdOrSlug($usuario){
        if($usuario->getSlug() != '')
          return $usuario->getSlug();

        return $usuario->getId();
    }

  	public function getUsuarioRecomendaciones($id) {
  		  $em = $this->getEntityManager();

  		  //Query para obtener el total de recomendaciones del usuario
        $q = $em->createQuery("SELECT r
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.usuario = ?1");
        $q->setParameter(1, $id);

        return $q->getResult();
  	}

  	public function getTotalLugaresAgregadosUsuario($id) {
    		$em = $this->getEntityManager();

    		//Query para obtener el total de lugares agregados por el usuario
        $q = $em->createQuery("SELECT COUNT(l) total 
                               FROM Loogares\LugarBundle\Entity\Lugar l 
                               WHERE l.usuario = ?1");
        $q->setParameter(1, $id);
        return $q->getSingleResult();
  	}

  	public function getTotalFotosLugaresAgregadasUsuario($id) {
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
    
    public function getDatosUsuario($usuario) {

        //Total recomendaciones usuario
        $recomendaciones = $this->getUsuarioRecomendaciones($usuario->getId()); 
        $totalRecomendaciones = count($recomendaciones);

        //Primeras recomendaciones usuario
        $primerasRecomendaciones = $this->getPrimerasRecomendaciones($usuario->getId());
        $totalPrimerasRecomendaciones = count($primerasRecomendaciones);   

        //Total de lugares agregados por el usuario
        $totalLugaresAgregados = $this->getTotalLugaresAgregadosUsuario($usuario->getId());

        //Total de fotos de lugares agregadas por el usuario
        $totalImagenesLugar= $this->getTotalFotosLugaresAgregadasUsuario($usuario->getId());

        //Cálculo de edad
        if($usuario->getFechaNacimiento() != null) {
            $birthday = $usuario->getFechaNacimiento()->format('d-m-Y');
            if($birthday != '30-11--0001') {
                list($d,$m,$Y)    = explode("-",$birthday);
                $edad = date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y;
            }
            else {
                $edad = '0';
            }            
        } else {
            $edad = '0';
        }
        

        //Nombre del sexo
        if($usuario->getSexo() != null) {
            if($usuario->getSexo() == "m") {
                $sexoResult = "Hombre";
            }
            else {
                $sexoResult = "Mujer";
            }
        } else {
            $sexoResult = null;
        }

        //Array con links de usuario
        $links = array();
        if($usuario->getWeb() != null || $usuario->getWeb() != '') {
            $links[] = $usuario->getWeb();
        }
        if($usuario->getFacebook() != null || $usuario->getFacebook() != '') {
            $links[] = $usuario->getFacebook();
        }
        if($usuario->getTwitter() != null || $usuario->getTwitter() != '') {
            $links[] = $usuario->getTwitter();
        }
        
        /*
         *  Armado de Datos para pasar a Twig
         */
        $data = $usuario;
        $data->totalRecomendaciones = $totalRecomendaciones;
        $data->totalPrimerasRecomendaciones = $totalPrimerasRecomendaciones;
        $data->totalLugaresAgregados = $totalLugaresAgregados['total'];
        $data->totalImagenesLugar = $totalImagenesLugar['total'];
        $data->edadResult = $edad;
        $data->sexoResult = $sexoResult;
        $data->desdeResult = $usuario->getFechaRegistro()->format('d-m-Y');
        $data->links = $links;

        return $data;
    }
}