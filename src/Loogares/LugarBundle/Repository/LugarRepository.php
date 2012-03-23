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
          $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.slug = '$slug' and u.estado != ?1 $orderBy");
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Lugar u  and u.estado != ?1 $orderBy");
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
        $q->setParameter(1, 3);
        $lugarResult = $q->getResult();
        return $lugarResult;
    }

      public function getTotalLugaresPorCiudad($ciudad){
        $em = $this->getEntityManager();

        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $idCiudad = $cr->findOneBySlug($ciudad);

        $q = $em->createQuery("SELECT count(u) 
                               FROM Loogares\LugarBundle\Entity\Lugar u
                               LEFT JOIN u.comuna c
                               WHERE c.ciudad = ?1");
        $q->setParameter(1, $idCiudad);
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
        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Pais u where (u.mostrar_lugar = 1 or u.mostrar_lugar = 3) order by u.nombre asc");
        $paisResult = $q->getResult();

        return $paisResult;
    }

    public function getCiudades($slug = null){
        $em = $this->getEntityManager();
        if($slug){
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u  where (u.mostrar_lugar = 1 or u.mostrar_lugar = 3) and u.slug = '$slug' order by u.nombre asc"); 
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u  where (u.mostrar_lugar = 1 or u.mostrar_lugar = 3) order by u.nombre asc");
        }
        $ciudadesResult = $q->getResult();

        return $ciudadesResult;
    }

    public function getCiudadById($id){
        $em = $this->getEntityManager();
        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u  where (u.mostrar_lugar = 1 or u.mostrar_lugar = 3) and u.id = '$id' order by u.nombre asc");
        $ciudadesResult = $q->getSingleResult();

        return $ciudadesResult;
    }

    public function getComunas($comuna = null, $ciudad = null){
      $em = $this->getEntityManager();
        if($comuna){
          $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
          $ciudad = $cr->findOneBySlug($ciudad);
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u where u.slug = '$comuna' and u.ciudad = ?1 order by u.nombre asc"); 
          $q->setParameter(1, $ciudad->getId());
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u order by u.nombre asc");
        }
      $comunasResult = $q->getResult();

      return $comunasResult;
    }

    public function getSectores($slug = null){
      $em = $this->getEntityManager();
        if($slug){
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Sector u where u.slug = '$slug' order by u.nombre asc"); 
        }else{
          $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Sector u order by u.nombre asc");
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
      $estadoResult = $q->getSingleResult();

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

    public function getTagsPopulares($id){
      return $this->_em
             ->getConnection()
             ->fetchAll("select DISTINCT tag.tag as nombre, count(tag_recomendacion.tag_id) as freq from tag_recomendacion 

                        left join recomendacion
                        on tag_recomendacion.recomendacion_id = recomendacion.id

                        left join tag 
                        on tag_recomendacion.tag_id = tag.id

                        where recomendacion.lugar_id = $id
                        group by tag_recomendacion.tag_id
                        order by freq desc
                        limit 5");
    }

    public function getImagenLugarMasReciente($lugar) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT im
                             FROM Loogares\LugarBundle\Entity\ImagenLugar im
                             WHERE im.lugar = ?1
                             AND im.estado != ?2
                             ORDER BY im.fecha_creacion DESC, im.id DESC");
      $q->setParameter(1, $lugar);
      $q->setParameter(2, 3);
      $q->setMaxResults(1);
      //IF (im.fecha_modificacion IS NULL, im.fecha_creacion, im.fecha_modificacion)
      return $q->getSingleResult();
    }

    public function getFotosVecinas($id, $lugar) {
      $em = $this->getEntityManager();
      $q1 = $em->createQuery("SELECT MIN(im1.id)
                             FROM Loogares\LugarBundle\Entity\ImagenLugar im1
                             WHERE im1.id > ?1
                             AND im1.estado != ?2
                             AND im1.lugar = ?3");
      $q1->setParameter(1, $id);
      $q1->setParameter(2, 3);
      $q1->setParameter(3, $lugar);
      $q1->setMaxResults(1);

      $q2 = $em->createQuery("SELECT MAX(im2.id)
                             FROM Loogares\LugarBundle\Entity\ImagenLugar im2
                             WHERE im2.id < ?1
                             AND im2.estado != ?2
                             AND im2.lugar = ?3");
      $q2->setParameter(1, $id);
      $q2->setParameter(2, 3);
      $q2->setParameter(3, $lugar);
      $q2->setMaxResults(1);

      $vecinas = array();
      $vecinas['prev'] = $q1->getSingleScalarResult();
      $vecinas['next'] = $q2->getSingleScalarResult();
      return $vecinas;
    }

    public function getReportesImagenesUsuarioLugar($imagen, $usuario, $estado) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT ri
                             FROM Loogares\LugarBundle\Entity\ReportarImagen ri
                             WHERE ri.imagen_lugar = ?1
                             AND ri.usuario = ?2
                             AND ri.estado = ?3");
      $q->setParameter(1, $imagen);
      $q->setParameter(2, $usuario);
      $q->setParameter(3, $estado);
      return $q->getResult();
    }

    public function getReportesUsuarioLugar($lugar, $usuario, $estado) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT rl
                             FROM Loogares\LugarBundle\Entity\ReportarLugar rl
                             WHERE rl.lugar = ?1
                             AND rl.usuario = ?2
                             AND rl.estado = ?3");
      $q->setParameter(1, $lugar);
      $q->setParameter(2, $usuario);
      $q->setParameter(3, $estado);
      return $q->getResult();
    }

    public function getTotalAccionesLugar($lugar) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT a.id, a.nombre,
                             (SELECT count(au.id)
                             FROM Loogares\UsuarioBundle\Entity\AccionUsuario au
                             WHERE au.accion = a.id
                             AND au.lugar = ?1) total
                             FROM Loogares\UsuarioBundle\Entity\Accion a");
      $q->setParameter(1, $lugar);
      return $q->getResult();
    }

    public function getAccionesUsuario($lugar, $usuario=null) {
      
      $em = $this->getEntityManager();
      $sq = "SELECT count(au.id)
             FROM Loogares\UsuarioBundle\Entity\AccionUsuario au
             WHERE au.accion = a.id
             AND au.lugar = ?1
             AND au.usuario = ?2";
      if($usuario ==  null) {
        $sq = "0";
      }
      $q = $em->createQuery("SELECT a.id, a.nombre,
                             (" . $sq . ") hecho
                             FROM Loogares\UsuarioBundle\Entity\Accion a");          
      if($usuario) {
        $q->setParameter(1, $lugar);      
        $q->setParameter(2, $usuario);
      }    
      return $q->getResult();
    }

    public function getAccionUsuarioLugar($lugar, $usuario, $accion) {   
      if($accion == 'quiero_ir'){ $accion = 1; }
      else if($accion == 'quiero_volver'){ $accion = 2; }
      else if($accion == 'estuve_alla'){ $accion = 3; }
      else if($accion == 'favoritos'){ $accion = 4; }
      else if($accion == 'recomendar_despues'){ $accion = 5; }
        
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT au 
                        FROM Loogares\UsuarioBundle\Entity\AccionUsuario au 
                        WHERE au.lugar = ?1 
                        AND au.usuario = ?2
                        AND au.accion = ?3");
      $q->setParameter(1, $lugar);
      $q->setParameter(2, $usuario);
      $q->setParameter(3, $accion);
      $result = $q->getOneOrNullResult();
      if($result == null){
        return $accion;
      }else{
        return $result;
      }
    }

    public function getPedidosLugar($lugar, $tipo) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT pl
                             FROM Loogares\LugarBundle\Entity\PedidoLugar pl
                             WHERE pl.lugar = ?1
                             AND pl.tipo_pedido = ?2
                             ORDER BY pl.prioridad ASC");
      $q->setParameter(1, $lugar);
      $q->setParameter(2, $tipo);
      return $q->getResult();
    }

    public function getPedidosRandom($ciudad) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT DISTINCT p, pl
                             FROM Loogares\LugarBundle\Entity\Promocion p
                             JOIN p.pedido_lugar pl
                             JOIN pl.lugar l
                             JOIN l.comuna c
                             WHERE c.ciudad = ?1
                             AND l.estado != ?2
                             GROUP BY l.id");
      $q->setParameter(1, $ciudad);
      $q->setParameter(2, 3);
      
      return $q->getResult();
    }

    public function getTopFivePorCategoria($categoria, $ciudad) {
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT cl, l
                             FROM Loogares\LugarBundle\Entity\CategoriaLugar cl
                             JOIN cl.lugar l
                             JOIN l.comuna c
                             WHERE cl.categoria = ?1
                             AND c.ciudad = ?2
                             AND l.estado != ?3
                             AND cl.principal = 1
                             GROUP BY l.id
                             ORDER BY l.fecha_ultima_recomendacion DESC");
      $q->setParameter(1, $categoria);
      $q->setParameter(2, $ciudad);
      $q->setParameter(3, 3);
      $q->setMaxResults(5);

      return $q->getResult();
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

    public function getLugaresPorRevisar($id, $estado){
      $em = $this->getEntityManager();
      $q = $em->createQuery("SELECT tl
                             FROM Loogares\AdminBundle\Entity\TempLugar tl
                             WHERE tl.estado = ?1 and tl.lugar = ?2");
      $q->setParameter(1, $estado);
      $q->setParameter(2, $id);
      return $q->getResult();
    }

      public function actualizarPromedios($slug){
        $em = $this->getEntityManager();
        $lr = $em->getRepository('LoogaresLugarBundle:Lugar');
        $rr = $em->getRepository('LoogaresUsuarioBundle:Recomendacion');
        $ur = $em->getRepository('LoogaresUsuarioBundle:Util');

        $lugar = $lr->findOneBySlug($slug);
        $q = $em->createQuery("SELECT u, count(u.id) as total, avg(u.estrellas) as avgestrellas, sum(u.precio) as precio 
        FROM Loogares\UsuarioBundle\Entity\Recomendacion u 
        where u.lugar = ?1 AND u.estado != ?2");
        $q->setParameter(1, $lugar->getId());
        $q->setParameter(2, 3);

        $avg = $q->getResult();

        $precio = $lugar->getPrecioInicial();
        $precio = ($precio+$avg[0]['precio']) / (($avg[0]['total']==0)?1:$avg[0]['total']);
        $lugar->setPrecio($precio);
        $lugar->setEstrellas($avg[0]['avgestrellas']);
        $lugar->setTotalRecomendaciones($avg[0]['total']);

        $q = $em->createQuery("SELECT u, count(u.id) as utiles FROM Loogares\UsuarioBundle\Entity\Util u 
                              LEFT JOIN u.recomendacion r
                              where r.lugar = ?1");
        $q->setParameter(1, $lugar->getId());

        $avg = $q->getResult();

        $lugar->setUtiles($avg[0]['utiles']);

        $em->persist($lugar);
        $em->flush();
      }
}