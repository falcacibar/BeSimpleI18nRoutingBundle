<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Loogares\LugarBundle\Entity\Lugar;

class LugarController extends Controller
{

    public function listadoAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $q = $em->createQuery('SELECT u FROM Loogares\LugarBundle\Entity\Lugar u');
        $q->setFirstResult(rand(1, 6000));
        $q->setMaxResults(10);
        $result = $q->getResult();

        $repo = $this->getDoctrine()
                     ->getRepository('LoogaresLugarBundle:Lugar');
        
        return $this->render('LoogaresLugarBundle:Lugares:listado.html.twig', array('lugares' => $result));
    }    

    public function lugarAction($slug){
                $paginaActual = (!isset($_GET['pagina']))?1:$_GET['pagina'];
                $orden = (!isset($_GET['orden']))?'ultimas':$_GET['orden'];
                $offset = ($paginaActual - 1) * 10;

                $em = $this->getDoctrine()->getEntityManager();
                  $qb = $em->createQueryBuilder();

                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\Lugar u 
                                       WHERE u.slug = ?1");
                $q->setParameter(1, $slug);
                $lugarResult = $q->getResult();

                //Id del Lugar
                $idLugar = $lugarResult[0]->getId();

                //Query para categorias del lugar
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\CategoriaLugar u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $categoriasResult = $q->getResult();

                //Query para horarios del lugar
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\Horario u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $horarioResult = $q->getResult();

                //Query para sacar la primera recomendacion
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\UsuarioBundle\Entity\Recomendacion u 
                                       WHERE u.lugar = ?1 
                                       ORDER BY u.id ASC");
                $q->setParameter(1, $idLugar)
                  ->setMaxResults(1);
                $primeroRecomendarResult = $q->getResult();

                //Query para sacar el Total de las recomendaciones
                $q = $em->createQuery("SELECT count(u.id) 
                                       FROM Loogares\UsuarioBundle\Entity\Recomendacion u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $totalRecomendacionesResult = $q->getSingleScalarResult();

                if($orden == 'ultimas'){
                        $orderBy = "ORDER BY recomendacion.fecha_creacion DESC";
                }else if($orden == 'mas-utiles'){
                        $orderBy = "ORDER BY utiles DESC";
                }else if($orden == 'mejor-evaluadas'){
                        $orderBy = "ORDER BY recomendacion.estrellas desc, recomendacion.fecha_creacion DESC";
                }

                //Query para las recomendaciones a mostrar


                //Query para sacar todos los tags asociados a la recomendacion, Doctrine, ih8u
                // $q = $em->createQuery("SELECT u, (b.tag)
                //                        FROM Loogares\UsuarioBundle\Entity\Recomendacion u
                //                        LEFT JOIN u.tag_recomendacion a
                //                        LEFT JOIN a.tag b
                //                        WHERE u.lugar = ?1
                //                        GROUP BY u.id
                //                        ORDER BY u.fecha_creacion");


                // $q->setParameter(1, $idLugar);
                // $asd = $q->getResult();

                $recomendacionesResult = $this->getDoctrine()->getConnection()->fetchAll("SELECT recomendacion.*, group_concat(tag.tag) as tags, count(DISTINCT util.id) AS utiles, usuarios.*
                                                                         FROM recomendacion
                                                                         LEFT JOIN util
                                                                         ON util.recomendacion_id = recomendacion.id
                                                                         LEFT JOIN tag_recomendacion
                                                                         ON tag_recomendacion.recomendacion_id = recomendacion.id
                                                                         LEFT JOIN tag
                                                                         ON tag_recomendacion.tag_id = tag.id
                                                                         LEFT JOIN usuarios
                                                                         ON recomendacion.usuario_id = usuarios.id
                                                                         WHERE recomendacion.lugar_id = $idLugar
                                                                         GROUP BY recomendacion.id 
                                                                         $orderBy
                                                                         LIMIT 10
                                                                         OFFSET $offset");


                //Odio Doctrine, hacemos un array nuevo con los cant(utiles) + datos de las recomendaciones que necesitamos
                $i = 0;
                echo "<pre>";
                foreach($recomendacionesResult as $key => $value){
                       
                }
                echo "</pre>";
                /*
                *  Armado de Datos para pasar a Twig
                */
                $data = $lugarResult[0];

                //Armamos un array con todas las categorias
                foreach($categoriasResult as $value){
                        $data->categorias[] = $value->getCategoria()->getNombre();
                }

                //Armando los datos a pasar, solo pasamos un objeto con todo lo que necesitamos
                $data->horarios = $horarioResult;
                $data->primero = (isset($primeroRecomendarResult[0]))?$primeroRecomendarResult[0]:'';
                $data->recomendaciones = $recomendacionesResult;
                //Total de Pagina que debemos mostrar/generar
                $data->totalPaginas = ($totalRecomendacionesResult > 10)?(int) $totalRecomendacionesResult / 10:1;
                $data->totalRecomendaciones = $totalRecomendacionesResult;
                //Offset de comentarios mostrados, "mostrando 1 a 10 de 20"
                $data->mostrandoComentariosDe = $paginaActual * ($paginaActual != 1)?(10 + 1):1;
                $data->paginaActual = $paginaActual;
                $data->orden = $orden;

                //Render ALL THE VIEWS
                return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => $data));            
    }
    
    public function agregarAction()
    {
        $lugar = new Lugar();
        $lugar->setNombre('Lugar X');
        //$lugar->setUsuario('2');
        $lugar->setSlug('lugar-x');
        $lugar->setDireccion('A esquina B');
        $lugar->setDetalle('asdasd');
        $lugar->setIdComuna('1');
        $lugar->setIdBarrio('2');
        $lugar->setMapx('123');
        $lugar->setMapy('123');
        $lugar->setProfesional('pedro');
        $lugar->setAgnoConstruccion('11');
        $lugar->setMateriales('Ningunoo');
        $lugar->setSitioWeb('www.google.cl');
        $lugar->setFacebook('/facebook');
        $lugar->setTwitter('twitters');
        $lugar->setMail('loogares@loogares.com');
        $lugar->setEstrellas('5');
        $lugar->setPrecio('11');
        $lugar->setPrecioInicial('222');
        $lugar->setTotalRecomendaciones('2');
        $lugar->setFechaUltimaRecomendacion(new \DateTime("now"));
        $lugar->setUtiles('2');
        $lugar->setVisitas('1');
        $lugar->setDescripcion('descripcion');
        $lugar->setPrioridadWeb('1');
        $lugar->setIdTipoLugar('2');
        $lugar->setIdEstado('2');
        $lugar->setTieneDueno('0');

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($lugar);
        $em->flush();

        #return new Response('dohohoho');
        return $this->render('LoogaresLugarBundle:Lugares:agregar.html.twig', array('lugar' => $lugar->getNombre()));
    }
}
