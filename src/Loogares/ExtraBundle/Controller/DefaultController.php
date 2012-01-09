<?php

namespace Loogares\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function sindexAction($name)
    {
        return $this->render('LoogaresExtraBundle:Default:index.html.twig', array('name' => $name));
    }

    public function menuAction(){

    	$em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");        

        $tipoCategorias = $lr->getTipoCategoriaPorPrioridad();
        $categorias = $em->getRepository("LoogaresLugarBundle:Categoria")->findAll();             
        
        $data = array();           
        
        $data['tipoCategorias'] = $tipoCategorias;
        $data['categorias']  = $categorias;

        foreach($data['categorias'] as $categoria){
            $lugares = $em->getRepository("LoogaresLugarBundle:Lugar")->getTotalLugaresPorCategoria($categoria->getId()); 

            $categoria->lugares = $lugares['total'];
        }

    	return $this->render('::menu.html.twig', array('menu' => $data));
    }

    public function ciudadAction(){

        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");

        $tipoCiudades = $cr->getCiudadesActivas();

        $data = $tipoCiudades;
        return $this->render('::ciudad.html.twig', array('ciudades' => $data));
    }

    public function homepageAction() {
        return $this->forward('LoogaresUsuarioBundle:Usuario:login');
    }

}
