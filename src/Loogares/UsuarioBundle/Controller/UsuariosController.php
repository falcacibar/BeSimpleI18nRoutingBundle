<?php

namespace Loogares\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class UsuariosController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('LoogaresUsuarioBundle:Usuarios:index.html.twig', array('name' => $name));
    }

    public function showAction($slug)
    {
        $usuario = $this->getDoctrine()
                        ->getRepository('LoogaresUsuarioBundle:Usuario')
                        ->findOneBySlug($slug);

        if (!$usuario) {
            throw $this->createNotFoundException('No user found for slug '.$slug);
        }

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $usuario));  
    }
}
