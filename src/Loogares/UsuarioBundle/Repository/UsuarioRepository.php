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
        return $this->loadUserByUsername($user->getMail());
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

  	public function getUsuarioRecomendaciones($id, $orden=null, $offset=null) {
  		  $em = $this->getEntityManager();

  		  //Query para obtener el total de recomendaciones del usuario
        if($orden == null)
          $orden = '';

        if($offset == null)
          $offset = '';

        $q = $em->createQuery("SELECT r, l
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               LEFT JOIN r.lugar l
                               WHERE r.usuario = ?1 and r.lugar = l.id "
                               .$orden)
                ->setMaxResults(10)
                ->setFirstResult($offset);
        $q->setParameter(1, $id);

        return $q->getResult();
  	}

    public function getUsuarioSlugRepetido($slug) {
        $em = $this->getEntityManager();

        //Query para obtener el total de recomendaciones del usuario        
        $q = $em->createQuery("SELECT COUNT(u)
                               FROM Loogares\UsuarioBundle\Entity\Usuario u
                               WHERE u.slug LIKE ?1");
        $q->setParameter(1, $slug."%");
        
        return $q->getSingleScalarResult();  
    }

    public function getTotalUsuarioRecomendaciones($id) {
        $em = $this->getEntityManager();

        //Query para obtener el total de recomendaciones del usuario        
        $q = $em->createQuery("SELECT COUNT(r)
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.usuario = ?1");
        $q->setParameter(1, $id);

        return $q->getSingleScalarResult();
    }

  	public function getTotalLugaresAgregadosUsuario($id) {
    		$em = $this->getEntityManager();

    		//Query para obtener el total de lugares agregados por el usuario
        $q = $em->createQuery("SELECT COUNT(l)
                               FROM Loogares\LugarBundle\Entity\Lugar l 
                               WHERE l.usuario = ?1");
        $q->setParameter(1, $id);
        return $q->getSingleScalarResult();
  	}

  	public function getFotosLugaresAgregadasUsuario($id, $orden=null, $offset=null) {
    		$em = $this->getEntityManager();

    		//Query para obtener el total de fotos de lugares agregadas por el usuario
        if($orden == null)
          $orden = '';

        if($offset == null)
          $offset = '';

        $q = $em->createQuery("SELECT im, l
                               FROM Loogares\LugarBundle\Entity\ImagenLugar im 
                               LEFT JOIN im.lugar l
                               WHERE im.usuario = ?1 
                               AND im.estado != ?2 "
                               .$orden)
                ->setMaxResults(15)
                ->setFirstResult($offset);

        $q->setParameter(1, $id);
        $q->setParameter(2, 3);
        return $q->getResult();
  	}

    public function getTotalFotosLugaresAgregadasUsuario($id, $orden=null) {
        $em = $this->getEntityManager();

        //Query para obtener el total de fotos de lugares agregadas por el usuario
        $q = $em->createQuery("SELECT COUNT(im)
                               FROM Loogares\LugarBundle\Entity\ImagenLugar im
                               WHERE im.usuario = ?1
                               AND im.estado != ?2");
        $q->setParameter(1, $id);
        $q->setParameter(2, 3);
        return $q->getSingleScalarResult();
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

    public function getUsuariosAdmin() {
        $em = $this->getEntityManager();

        // Obtenemos al usuario, con sus recomendaciones, estado y tipo de usuario
        $q = $em->createQuery("SELECT u, e, t
                               FROM LoogaresUsuarioBundle:Usuario u
                               JOIN u.recomendaciones r
                               JOIN u.estado e
                               JOIN u.tipo_usuario t
                               ORDER BY u.id");
        return $q->getResult();
    }

    public function getFotosLugarPaginadas($id, $offset=null) {
        $em = $this->getEntityManager();

          //Query para obtener las fotos paginadas de un lugar
          if($offset == null)
            $offset = '';

          $q = $em->createQuery("SELECT im
                               FROM Loogares\LugarBundle\Entity\ImagenLugar im
                               WHERE im.lugar = ?1 
                               AND im.estado != ?2
                               ORDER BY im.fecha_creacion DESC, im.id DESC")
                ->setMaxResults(20)
                ->setFirstResult($offset);

          $q->setParameter(1, $id);
          $q->setParameter(2, 3);
          return $q->getResult();
    }

    public function getTotalFotosLugar($id) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT count(u.id)
                             FROM Loogares\LugarBundle\Entity\ImagenLugar u
                             WHERE u.lugar = ?1
                             AND u.estado != ?2");
      $q->setParameter(1, $id);
      $q->setParameter(2, 3);
      return $q->getSingleScalarResult();
    }
    
    public function getDatosUsuario($usuario) {

        
        //Total recomendaciones usuario         
        $totalRecomendaciones = $this->getTotalUsuarioRecomendaciones($usuario->getId());

        //Primeras recomendaciones usuario
        $primerasRecomendaciones = $this->getPrimerasRecomendaciones($usuario->getId());
        $totalPrimerasRecomendaciones = count($primerasRecomendaciones);   

        //Total de lugares agregados por el usuario
        $totalLugaresAgregados = $this->getTotalLugaresAgregadosUsuario($usuario->getId());

        //Total de fotos de lugares agregadas por el usuario
        $totalImagenesLugar = $this->getTotalFotosLugaresAgregadasUsuario($usuario->getId());
        
        //Cálculo de edad
        if($usuario->getFechaNacimiento() != null) {
            $birthday = $usuario->getFechaNacimiento()->format('d-m-Y');
            if($birthday != '30-11--0001') {
                list($d,$m,$Y)    = explode("-",$birthday);
                $edad = date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y;
            }
            else {
                $edad = null;
            }            
        } else {
            $edad = null;
        }
        

        //Nombre del sexo
        if($usuario->getSexo() != null) {
            if($usuario->getSexo() == "m") {
                $sexoResult = "Hombre";
            }
            else if($usuario->getSexo() == "f"){
                $sexoResult = "Mujer";
            }
            else
                $sexoResult = null;
        } else {
            $sexoResult = null;
        }

        $edadSexo = array();
        $textoEdad = '';
        if($sexoResult)
          $edadSexo[] = $sexoResult;
        if($edad && $usuario->getMostrarEdad()) {
          $edadSexo[] = $edad;
          $textoEdad = $edad;
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
        //$data->recomendacionesTodas = $recomendaciones;
        $data->totalPrimerasRecomendaciones = $totalPrimerasRecomendaciones;
        $data->totalLugaresAgregados = $totalLugaresAgregados;
        $data->totalImagenesLugar = $totalImagenesLugar;
        $data->edadSexo = $edadSexo;
        $data->textoEdad = $textoEdad;
        $data->links = $links;

        return $data;
    }
}