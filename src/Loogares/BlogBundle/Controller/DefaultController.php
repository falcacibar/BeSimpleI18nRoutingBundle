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
        $q = $em->createQuery('SELECT u FROM Loogares\BlogBundle\Entity\Posts u WHERE u.tipo = ?1 and u.lugar = ?2 and u.id != ?3');
        $q->setParameter(1, $post->getTipo());
        $q->setParameter(2, $post->getLugar());
        $q->setParameter(3, $post->getId());
        $anteriores = $q->getResult();

        return $this->render('LoogaresBlogBundle:Default:post.html.twig', array('post' => $post, 'anteriores' => $anteriores));
    }
}
