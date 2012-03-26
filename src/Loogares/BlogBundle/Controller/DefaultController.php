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
        $anteriores = null;
        $fn = $this->get('fn');
        
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $post = $pr->findOneBySlug($slug);

        if(!$post) {
            throw $this->createNotFoundException('');
        }

        if($post->getLugar()){
        $post->getLugar()->setSitioWeb($fn->stripHTTP($post->getLugar()->getSitioWeb()));
        $post->getLugar()->setTwitter($fn->stripHTTP($post->getLugar()->getTwitter()));
        $post->getLugar()->setFacebook($fn->stripHTTP($post->getLugar()->getFacebook()));            
        }


        if(gettype($post) == 'object'){
            $q = $em->createQuery('SELECT u FROM Loogares\BlogBundle\Entity\Posts u WHERE u.tipo_post = ?1 and u.lugar = ?2 and u.id != ?3');
            $q->setParameter(1, $post->getTipoPost());
            $q->setParameter(2, $post->getLugar());
            $q->setParameter(3, $post->getId());
            $anteriores = $q->getResult();
        }

        return $this->render('LoogaresBlogBundle:Default:post.html.twig', array('post' => $post, 'anteriores' => $anteriores));
    }
}
