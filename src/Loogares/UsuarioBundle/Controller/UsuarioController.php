<?php

namespace Loogares\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class UsuarioController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('LoogaresUsuarioBundle:Usuarios:index.html.twig', array('name' => $name));
    }

    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $pr = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $pr->findOneBySlug($slug); 
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No user found for slug '.$slug);
        }
        
        //Total recomendaciones usuario
        $recomendaciones = $pr->getUserReviews($usuarioResult->getId()); 
        $totalRecomendaciones = count($recomendaciones);

        //Primeras recomendaciones usuario
        $primerasRecomendaciones = 0;
        $primRec  = array();
        foreach($recomendaciones as $r) {
            if($pr->verificarPrimeroRecomendar($usuarioResult->getId(), $r->getLugar()->getId())) {
                $primerasRecomendaciones++;
            }
        }

<<<<<<< HEAD
        $resultado = $this->getDoctrine()->getConnection()->fetchAll("SELECT *
                               FROM recomendacion r
                               INNER JOIN lugares l
                               ON l.id = r.lugar_id
                               WHERE r.lugar_id = 2487
                               ORDER BY r.fecha_creacion ASC");
        

        //Total de lugares agregados por el usuario
        $totalLugaresAgregados = $pr->getLugaresAgregadosUsuario($usuarioResult->getId());
=======
        //Query para obtener el total de lugares agregados por el usuario
        $q = $em->createQuery("SELECT COUNT(l) total 
                               FROM Loogares\LugarBundle\Entity\Lugar l 
                               WHERE l.usuario_id = ?1");
        $q->setParameter(1, $usuarioResult->getId());
        $totalLugaresAgregados = $q->getSingleResult();
>>>>>>> ae5d540846abfe01d5a7c50a8e404cd2eef8c1a2

        //Total de fotos de lugares agregadas por el usuario
        $totalImagenesLugar= $pr->getFotosLugaresAgregadasUsuario($usuarioResult->getId());

        //Cálculo de edad
        if($usuarioResult->getFechaNacimiento() != null) {
            $birthday = $usuarioResult->getFechaNacimiento()->format('d-m-Y');
            list($d,$m,$Y)    = explode("-",$birthday);
            $edad = date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y;
        } else {
            $edad = null;
        }
        

        //Nombre del sexo
        if($usuarioResult->getSexo() != null) {
            if($usuarioResult->getSexo() == "m") {
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
        if($usuarioResult->getLink1() != null || $usuarioResult->getLink1() != '') {
            $links[] = $usuarioResult->getLink1();
        }
        if($usuarioResult->getLink2() != null || $usuarioResult->getLink2() != '') {
            $links[] = $usuarioResult->getLink2();
        }
        if($usuarioResult->getLink3() != null || $usuarioResult->getLink3() != '') {
            $links[] = $usuarioResult->getLink3();
        }
        
        /*
         *  Armado de Datos para pasar a Twig
         */
        $data = $usuarioResult;
        $data->totalRecomendaciones = $totalRecomendaciones;
        $data->totalPrimerasRecomendaciones = $primerasRecomendaciones;
        $data->totalLugaresAgregados = $totalLugaresAgregados['total'];
        $data->totalImagenesLugar = $totalImagenesLugar['total'];
        $data->edadResult = $edad;
        $data->sexoResult = $sexoResult;
        $data->desdeResult = $usuarioResult->getFechaRegistro()->format('d-m-Y');
        $data->links = $links;
        $data->resultado = $resultado;
        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data));  
    }
}
