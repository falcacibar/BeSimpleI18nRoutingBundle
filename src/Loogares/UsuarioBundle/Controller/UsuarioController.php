<?php

namespace Loogares\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Loogares\UsuarioBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;


class UsuarioController extends Controller
{
    
    public function indexAction($name) {
        return $this->render('LoogaresUsuarioBundle:Usuarios:index.html.twig', array('name' => $name));
    }

    public function showAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $pr = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $pr->findOneBySlug($slug); 
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No user found for slug '.$slug);
        }
        
        //Total recomendaciones usuario
        $recomendaciones = $pr->getUsuarioRecomendaciones($usuarioResult->getId()); 
        $totalRecomendaciones = count($recomendaciones);

        //Primeras recomendaciones usuario
        $primerasRecomendaciones = $pr->getPrimerasRecomendaciones($usuarioResult->getId());
        $totalPrimerasRecomendaciones = count($primerasRecomendaciones);   

        //Total de lugares agregados por el usuario
        $totalLugaresAgregados = $pr->getLugaresAgregadosUsuario($usuarioResult->getId());

        //Total de fotos de lugares agregadas por el usuario
        $totalImagenesLugar= $pr->getFotosLugaresAgregadasUsuario($usuarioResult->getId());

        //Cálculo de edad
        if($usuarioResult->getFechaNacimiento() != null) {
            $birthday = $usuarioResult->getFechaNacimiento()->format('d-m-Y');
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
        $data->totalPrimerasRecomendaciones = $totalPrimerasRecomendaciones;
        $data->totalLugaresAgregados = $totalLugaresAgregados['total'];
        $data->totalImagenesLugar = $totalImagenesLugar['total'];
        $data->edadResult = $edad;
        $data->sexoResult = $sexoResult;
        $data->desdeResult = $usuarioResult->getFechaRegistro()->format('d-m-Y');
        $data->links = $links;
        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data));  
    }

    public function registroAction(Request $request) {

        $usuario = new Usuario();        

        $form = $this->createFormBuilder($usuario)
                     ->add('usuario', 'text')
                     ->add('mail', 'text')
                     ->add('password', 'password')
                     ->add('nombre', 'text')
                     ->add('apellido', 'text')
                     ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                // perform some action, such as saving the task to the database

                return $this->redirect($this->generateUrl('showUsuario', array('slug' => 'sebastian-vicencio')));
            }
        }

        return $this->render('LoogaresUsuarioBundle:Usuarios:registro.html.twig', array('form' => $form->createView()));  
    }
}
