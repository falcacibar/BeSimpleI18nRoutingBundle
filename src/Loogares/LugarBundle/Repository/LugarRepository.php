<?php

namespace Loogares\LugarBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * LugarRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LugarRepository extends EntityRepository
{

    public function getLugares($slug = null){
        $em = $this->getEntityManager();
        if($slug){
          $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.slug = '$slug'");
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Lugar u");
        }
        $lugarResult = $q->getResult();

        return $lugarResult;
    }

    public function getLugaresPorNombre($nombre = null){
        $em = $this->getEntityManager();
        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.nombre = '$nombre'");
        
        $lugarResult = $q->getResult();

        return $lugarResult;
    }

    public function getCategorias($slug = null){
        $em = $this->getEntityManager();
        if($slug){
           $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Categoria u where u.slug = '$slug' order by u.nombre asc");  
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Categoria u order by u.nombre asc"); 
        }
       
        $categoriasResult = $q->getResult();

        return $categoriasResult;
    }

    public function getTipoCategorias(){
        $em = $this->getEntityManager();
        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\TipoCategoria u order by u.id asc");
        $tipoCategoriasResult = $q->getResult();

        return $tipoCategoriasResult;
    }

    public function getCiudades($slug = null){
        $em = $this->getEntityManager();
        if($slug){
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u  where u.mostrar_lugar = 1 and u.slug = '$slug' order by u.id asc"); 
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u  where u.mostrar_lugar = 1 order by u.id asc");
        }
        $ciudadesResult = $q->getResult();

        return $ciudadesResult;
    }

    public function getComunas($slug = null){
      $em = $this->getEntityManager();
        if($slug){
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u where u.slug = '$slug' order by u.id asc"); 
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u order by u.id asc");
        }
      $comunasResult = $q->getResult();

      return $comunasResult;
    }

    public function getSectores($slug = null){
      $em = $this->getEntityManager();
        if($slug){
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Sector u where u.slug = '$slug' order by u.id asc"); 
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Sector u order by u.id asc");
        }
      $sectorResult = $q->getResult();

      return $sectorResult;
    }

    public function getCaracteristicas(){
      $em = $this->getEntityManager();
      $q = $em->createQuery('SELECT u FROM Loogares\LugarBundle\Entity\Caracteristica u');
      $caracteristicasResult = $q->getResult();

      return $caracteristicasResult;
    }

    public function getEstado($slug){
      $em = $this->getEntityManager();
      $q = $em->createQuery(" SELECT u FROM Loogares\ExtraBundle\Entity\Estado u where u.nombre = '$slug' ");
      $estadoResult = $q->getResult();

      return $estadoResult;
    }

    public function getTipoLugar($slug){
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\TipoLugar u where u.slug = '$slug'");
      $tipoLugarResult = $q->getResult();

      return $tipoLugarResult;
    }

    public function getCaracteristicaPorNombre($nombre){
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Caracteristica u where u.nombre = ?1");
      $q->setParameter(1, $nombre);

      $caracteristicaResult = $q->getResult();
      return $caracteristicaResult;      
    }

    public function getSubcategoriaPorNombre($nombre){
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\SubCategoria u where u.nombre = ?1");
      $q->setParameter(1, $nombre);

      $subCategoriaResult = $q->getResult();
      return $subCategoriaResult;      
    }

    public function getSubCategorias($slug){
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\SubCategoria u where u.slug = ?1");
      $q->setParameter(1, $slug);

      $subCategoriaResult = $q->getResult();
      return $subCategoriaResult;    
    }
}