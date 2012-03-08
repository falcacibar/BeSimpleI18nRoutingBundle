<?php

namespace Loogares\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction(){
        return $this->render('LoogaresBlogBundle:Default:index.html.twig' );
    }

    public function postAction($slug){
    	$em = $this->getDoctrine()->getEntityManager();
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $post = $pr->findOneBySlug($slug);

        return $this->render('LoogaresBlogBundle:Default:post.html.twig', array('post' => $post));
    }
}
