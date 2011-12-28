<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('LoogaresAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
