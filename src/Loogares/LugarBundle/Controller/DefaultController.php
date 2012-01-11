<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Loogares\LugarBundle\Entity\ImagenLugar;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('LoogaresLugarBundle:Lugares:ajax.html.twig');
    }
    
}
