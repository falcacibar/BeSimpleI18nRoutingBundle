<?php

namespace Loogares\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction(){
        return $this->render('LoogaresBlogBundle:Default:index.html.twig' );
    }

    public function postAction($ciudad = null, $slug){
    	$em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");

        //Si no hay ciudad, entraron directo, redireccionamos
        if($ciudad == null){
            $post = $pr->findOneBySlug($slug);
            return $this->redirect($this->generateUrl('post', array(
                'ciudad' => $post->getCiudad()->getSlug(),
                'slug' => $slug
            )));
        }
        
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

        $q = $em->createQuery('SELECT u FROM Loogares\BlogBundle\Entity\Posts u 
                              WHERE u.slug = ?1');
        $q->setParameter(1, $slug);
        $q->setMaxResults(1);
        $post = $q->getOneOrNullResult();

        if(!$post) {
            throw $this->createNotFoundException('');
        }

        if($post->getBlogEstado()->getNombre() != 'Post Publicado' && !$this->get('security.context')->isGranted('ROLE_ADMIN')){
            throw $this->createNotFoundException('');
        }else if($post->getBlogEstado()->getNombre() == 'Post Borrador'){
            $this->get('session')->setFlash('post_flash', 'Este es un Borradooooooooooooooooooooooooooooor');
        }else if($post->getBlogEstado()->getNombre() == 'Post Eliminado'){
            $this->get('session')->setFlash('post_flash', 'Este Post fue Borrado');
        }else if($post->getBlogEstado()->getNombre() == 'Post Agendado'){
            $date = $post->getFechaPublicacion();
            $date = $date->format('d-m-y');
            $this->get('session')->setFlash('post_flash', 'Post Agendado para: '.$date);
        }

        if($post->getLugar()){
            $post->getLugar()->setSitioWeb($fn->stripHTTP($post->getLugar()->getSitioWeb()));
            $post->getLugar()->setTwitter($fn->stripHTTP($post->getLugar()->getTwitter()));
            $post->getLugar()->setFacebook($fn->stripHTTP($post->getLugar()->getFacebook()));            
        }

        if(gettype($post) == 'object'){
            $q = $em->createQuery('SELECT u FROM Loogares\BlogBundle\Entity\Posts u 
                                  WHERE u.blog_categoria = ?1 and u.ciudad = ?2 and u.id != ?3');
            $q->setParameter(1, $post->getBlogCategoria()->getId());
            $q->setParameter(2, $ciudad->getId());
            $q->setParameter(3, $post->getId());
            $anteriores = $q->getResult();
        }

        return $this->render('LoogaresBlogBundle:Default:post.html.twig', array('ciudad'=>$ciudad, 'post' => $post, 'anteriores' => $anteriores));
    }
}
