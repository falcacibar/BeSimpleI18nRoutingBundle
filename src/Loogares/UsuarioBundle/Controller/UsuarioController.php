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
        $q = $em->createQuery("SELECT u 
                               FROM Loogares\UsuarioBundle\Entity\Usuario u 
                               WHERE u.slug = :slug");
        $q->setParameter('slug', $slug);        

        try {
            $usuarioResult = $q->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            throw $this->createNotFoundException('No user found for slug '.$slug);
        }

        //Query para obtener el total de recomendaciones del usuario
        $q = $em->createQuery("SELECT r
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion r 
                               WHERE r.usuario = ?1");
        $q->setParameter(1, $usuarioResult->getId());
        $totalRecomendaciones = count($q->getResult());

        //Query para obtener el total de lugares agregados por el usuario
        $q = $em->createQuery("SELECT COUNT(l) total 
                               FROM Loogares\LugarBundle\Entity\Lugar l 
                               WHERE l.usuario_id = ?1");
        $q->setParameter(1, $usuarioResult->getId());
        $totalLugaresAgregados = $q->getSingleResult();

        //Query para obtener el total de fotos de lugares agregadas por el usuario
        $q = $em->createQuery("SELECT COUNT(im) total 
                               FROM Loogares\LugarBundle\Entity\ImagenLugar im 
                               WHERE im.usuario = ?1");
        $q->setParameter(1, $usuarioResult->getId());
        $totalImagenesLugar= $q->getSingleResult();

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
        $data->totalLugaresAgregados = $totalLugaresAgregados['total'];
        $data->totalImagenesLugar = $totalImagenesLugar['total'];
        $data->edadResult = $edad;
        $data->sexoResult = $sexoResult;
        $data->desdeResult = $usuarioResult->getFechaRegistro()->format('d-m-Y');
        $data->links = $links;
        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data));  
    }
}
