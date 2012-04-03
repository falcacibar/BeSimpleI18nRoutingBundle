<?php

namespace Loogares\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction(){
        return $this->render('LoogaresBlogBundle:Default:index.html.twig' );
    }

    public function postAction($ciudad, $slug){
    	$em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        
        $ciudad = $cr->findOneBySlug($ciudad);
        $ciudadArray = array();
        $ciudadArray['id'] = $ciudad->getId();
        $ciudadArray['nombre'] = $ciudad->getNombre();
        $ciudadArray['slug'] = $ciudad->getSlug();
        $ciudadArray['pais']['id'] = $ciudad->getPais()->getId();
        $ciudadArray['pais']['nombre'] = $ciudad->getPais()->getNombre();
        $ciudadArray['pais']['slug'] = $ciudad->getPais()->getSlug();

        $this->get('session')->setLocale($ciudad->getPais()->getLocale());
        $this->get('session')->set('ciudad',$ciudadArray);

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
