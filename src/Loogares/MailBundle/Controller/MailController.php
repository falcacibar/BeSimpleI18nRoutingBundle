<?php

namespace Loogares\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class MailController extends Controller
{
    public function listadoNotificaciones($name){
    	$em = $this->getDoctrine()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $nr = $em->getRepository('LoogaresMailBundle:Notificacion');
        $notificaciones = $nr->findAll();

        return $this->render('LoogaresMailBundle:Default:index.html.twig', array('notificaciones' => $notificaciones));
    }
}
