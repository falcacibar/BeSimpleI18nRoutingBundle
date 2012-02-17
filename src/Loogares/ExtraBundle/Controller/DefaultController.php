<?php

namespace Loogares\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Loogares\LugarBundle\Entity\TipoCategoria;


class DefaultController extends Controller
{
    
    public function indexAction($name)
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

    public function ciudadAction() {

        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");

        $tipoCiudades = $cr->getCiudadesActivas();

        $data = $tipoCiudades;
        return $this->render('::ciudad.html.twig', array('ciudades' => $data));
    }

    public function localeAction($slug, $start=null) {
        if((!$this->get('session')->get('ciudad') && $start) || !$start ) {
            $em = $this->getDoctrine()->getEntityManager();
            $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
            $ciudad = $cr->findOneBySlug($slug);

            // Seteamos el locale correspondiente a la ciudad en la sesión
            $this->get('session')->setLocale($ciudad->getPais()->getLocale());

            $ciudadArray = array();
            $ciudadArray['id'] = $ciudad->getId();
            $ciudadArray['nombre'] = $ciudad->getNombre();
            $ciudadArray['slug'] = $ciudad->getSlug();

            $this->get('session')->set('ciudad',$ciudadArray);
        }     

        if($start) {
            return new Response('');
        }
        // Redirección a vista de login 
        return $this->redirect($this->generateUrl('login'));
    }

    public function homepageAction() {
        return $this->render('LoogaresExtraBundle:Default:home.html.twig');
    }

}
