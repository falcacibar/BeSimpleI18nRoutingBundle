<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{ 
    public function indexAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugares = $lr->getLugares(null, 30, 0);
        return $this->render('LoogaresAdminBundle:Admin:lugares.html.twig', array('lugares' => $lugares));
    }
}
