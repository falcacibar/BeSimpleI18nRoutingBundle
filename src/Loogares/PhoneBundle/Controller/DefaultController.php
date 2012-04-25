<?php

namespace Loogares\PhoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository('LoogaresLugarBundle:Categoria');

        $categorias = $cr->findAll();
        $json = array();

        foreach($categorias as $categoria){
        	$json[]['nombre'] = $categoria->getNombre();
        	$json[sizeOf($json)-1]['slug'] = $categoria->getSlug();
        }

        $json = json_encode($json);

        return $this->render('LoogaresPhoneBundle:Default:index.html.twig', array('name' => $json));
    }
}
