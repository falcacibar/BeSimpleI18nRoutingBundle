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

    public function getLugares($slug = null, $limit = null, $offset = null, $orderBy = null){
        $em = $this->getEntityManager();

        if($slug){
          $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.slug = '$slug' $orderBy");
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Lugar u $orderBy");
        }

        if($limit){
          $q->setMaxResults($limit);
        }

        if($offset){
          $q->setFirstResult($offset);
        }

        if($orderBy){
          //D:
        }

        $lugarResult = $q->getResult();
        return $lugarResult;
    }

      public function getTotalLugares(){
        $em = $this->getEntityManager();
        $q = $em->createQuery("SELECT count(u) FROM Loogares\LugarBundle\Entity\Lugar u");
        
        $totalLugaresResult = $q->getSingleScalarResult();

        return $totalLugaresResult;
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

    public function getPaises(){
        $em = $this->getEntityManager();
        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Pais u order by u.id asc");
        $paisResult = $q->getResult();

        return $paisResult;
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

    public function getCiudadById($id){
        $em = $this->getEntityManager();
        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u  where u.mostrar_lugar = 1 and u.id = '$id' order by u.id asc");
        $ciudadesResult = $q->getSingleResult();

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

    public function getEstado($id){
      $em = $this->getEntityManager();
      $q = $em->createQuery(" SELECT u FROM Loogares\ExtraBundle\Entity\Estado u where u.id = ?1");
      $q->setParameter(1, $id);
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

    public function getSubCategorias($slug = null){
      $em = $this->getEntityManager();
      if($slug){
        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\SubCategoria u where u.slug = ?1");
        $q->setParameter(1, $slug);
      }else{
        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\SubCategoria u");  
      }

      $subCategoriaResult = $q->getResult();
      return $subCategoriaResult;    
    }

    public function getTipoCategoriaPorPrioridad() {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\TipoCategoria u order by u.prioridad_web asc");
      $tipoCategoriasResult = $q->getResult();

      return $tipoCategoriasResult;
    }

    public function getTotalLugaresPorCategoria($categoria) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT COUNT(cl) total
                             FROM Loogares\LugarBundle\Entity\CategoriaLugar cl
                             WHERE cl.categoria = ?1");
      $q->setParameter(1, $categoria);
      return $q->getSingleResult();
    }

    public function cleanUp($id){
      $em = $this->getEntityManager();
      $q = $em->createQuery("DELETE Loogares\LugarBundle\Entity\CategoriaLugar u WHERE u.lugar = ?1");
      $q->setParameter(1, $id);
      $q->getResult();
      $q = $em->createQuery("DELETE Loogares\LugarBundle\Entity\SubcategoriaLugar u WHERE u.lugar = ?1");
      $q->setParameter(1, $id);
      $q->getResult();
      $q = $em->createQuery("DELETE Loogares\LugarBundle\Entity\CaracteristicaLugar u WHERE u.lugar = ?1");
      $q->setParameter(1, $id);
      $q->getResult();
      $q = $em->createQuery("DELETE Loogares\LugarBundle\Entity\Horario u WHERE u.lugar = ?1");
      $q->setParameter(1, $id);
      $q->getResult();
    }
}