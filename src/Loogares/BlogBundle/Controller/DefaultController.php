<?php

namespace Loogares\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Loogares\BlogBundle\Entity\Participante;


class DefaultController extends Controller
{
    
    public function indexAction(){
        return $this->render('LoogaresBlogBundle:Default:index.html.twig' );
    }

    public function postAction($ciudad = null, $slug){
    	$em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $conr = $em->getRepository("LoogaresBlogBundle:Concurso");

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

        if($post->getBlogCategoria()->getBlogTipoPost()->getSlug() == 'concurso') {
            $concurso = $conr->getConcursoPost($post->getId());
            $telefonos = array();
            if($post->getLugar()->getTelefono1() != '')
                $telefonos[] = $post->getCiudad()->getPais()->getCodigoArea().' '.$post->getLugar()->getTelefono1();
            if($post->getLugar()->getTelefono2() != '')
                $telefonos[] = $post->getCiudad()->getPais()->getCodigoArea().' '.$post->getLugar()->getTelefono2();
            if($post->getLugar()->getTelefono3() != '')
                $telefonos[] = $post->getCiudad()->getPais()->getCodigoArea().' '.$post->getLugar()->getTelefono3();

            // Concursos vigentes
            $concursos = $conr->getConcursosVigentes($ciudadArray['id']);

            // Obtenemos ganadores si existen
            $ganadores = $conr->getGanadoresConcurso($concurso);
            $concurso->ganadores = $ganadores;

            // Vemos si usuario está o no participando
            $participa = false;
            if($this->get('security.context')->isGranted('ROLE_USER')) {
                $participa = $conr->isUsuarioParticipando($this->get('security.context')->getToken()->getUser(), $concurso);
            }

            $concurso->participa = $participa;

            $post->getLugar()->telefonos = $telefonos;

            $twitterLocal = $post->getLugar()->getTwitter();
            if($twitterLocal != null || $twitterLocal != '') {
                $twitterArray = explode("/", $twitterLocal);
                $twitterLocal = $twitterArray[sizeOf($twitterArray) - 1];
                $post->twitterLocal = " @".$twitterLocal;
            }
            else {
                $post->twitterLocal = '';
            }

            // Se limpian variables de session de concurso si es que existen                
            if($this->get('session')->get('post_slug')) {
                $this->get('session')->remove('post_slug');
            }      

            return $this->render('LoogaresBlogBundle:Default:post_concurso.html.twig', array(
                'post' => $post,
                'concurso' => $concurso,
                'concursos' => $concursos
            ));
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

    public function registroPopUpAction($ciudad = null, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $conr = $em->getRepository("LoogaresBlogBundle:Concurso");

        $post = $pr->findOneBySlug($slug);
        $concurso = $conr->getConcursoPost($post->getId());

        // Seteamos variables de session para concursar automáticamente luego de loggear
        $this->get('session')->set('post_slug', $slug);

        $popup = "registro";
        return $this->render('LoogaresBlogBundle:Default:popup.html.twig', array(
            'popup' => $popup
        ));
    }

    public function compartirPopUpAction($ciudad = null, $slug, $tipo) {
        $em = $this->getDoctrine()->getEntityManager();
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $conr = $em->getRepository("LoogaresBlogBundle:Concurso");

        $post = $pr->findOneBySlug($slug);
        $concurso = $conr->getConcursoPost($post->getId());

        $twitterLocal = $post->getLugar()->getTwitter();
        if($twitterLocal != null || $twitterLocal != '') {
            $twitterArray = explode("/", $twitterLocal);
            $twitterLocal = $twitterArray[sizeOf($twitterArray) - 1];
            $post->twitterLocal = " @".$twitterLocal;
        }
        else {
            $post->twitterLocal = '';
        }


        if($this->getRequest()->getSession()->get('popup_compartir')) {
            $this->getRequest()->getSession()->remove('popup_compartir');
        }

        $popup = "compartir";
        return $this->render('LoogaresBlogBundle:Default:popup.html.twig', array(
            'popup' => $popup,
            'concurso' => $concurso,
            'post' => $post,
            'tipo' => $tipo
        ));
    }

    public function participarAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresBlogBundle:Concurso");

        $concurso = $cr->find($request->request->get('concurso'));
        $usuario = $this->get('security.context')->getToken()->getUser();

        // Sólo si el usuario no estaba participando antes, se ingresa como nuevo participante
        if(!$cr->isUsuarioParticipando($usuario, $concurso)) {
            $participante = new Participante();
            $participante->setConcurso($concurso);
            $participante->setUsuario($usuario);

            // Dejamos como pendiente o no dependiendo del tipo de concurso
            if($concurso->getTipoConcurso()->getSlug() == 'click') {
                $participante->setPendiente(false);
            }
            else if($concurso->getTipoConcurso()->getSlug() == 'recomendacion') {
                $participante->setPendiente(true);
            }

            $em->persist($participante);
            $em->flush();
        }       

        return new Response(json_encode(array('status' => 'ok')));
    }

    public function actualizarParticipantesAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresBlogBundle:Concurso");

        $concurso = $cr->find($request->request->get('concurso'));

        return $this->render('LoogaresBlogBundle:Default:participantes.html.twig', array(
            'concurso' => $concurso
        ));
    }

}
