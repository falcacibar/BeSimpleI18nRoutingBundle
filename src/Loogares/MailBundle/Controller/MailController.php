<?php

namespace Loogares\MailBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class MailController extends Controller{
	public function mailDescuentosUsuarioAction(){
		if(isset($_POST['campana']) && isset($_POST['seguidores'])){
			$campanaId = $_POST['campana'];
			$descontados = explode(',',$_POST['seguidores']);
		}else{
      return $this->redirect($this->generateUrl('locale_santiago_de_chile'));
		}
		
		$em = $this->getDoctrine()->getEntityManager();
  	$cr = $em->getRepository('LoogaresCampanaBundle:Campana');
  	$ur = $em->getRepository('LoogaresUsuarioBundle:Usuario');

  	$campana = $cr->findOneById($campanaId);

  	$mail = array();
  	$mail['lugar'] = $campana->getLugar();
  	$mail['descuento'] = $campana->getDescuento();
    $mail['asunto'] = 'Asunto del Mail';

    $paths = array();
    $paths['logo'] = 'assets/images/mails/logo_mails.png';

  	foreach($descontados as $descontado){
      $usuario = $ur->findOneById($descontado);
      $mail['usuario'] = $usuario;

      $q = $em->createQuery("SELECT du FROM Loogares\CampanaBundle\Entity\DescuentosUsuarios du WHERE du.usuario = ?1 AND du.descuento = ?2");
      $q->setParameter(1, $usuario);
      $q->setParameter(2, $campana->getDescuento());
      $descuento = $q->getOneOrNullResult();
      if($descuento){
        $mail['codigo'] = $descuento[0]->getCodigo();
      }

  		if($usuario != null){
	      $message = $this->get('fn')->enviarMail($mail['asunto'], $usuario->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresCampanaBundle:Mails:mail_descuentos_usuario.html.twig', $this->get('templating'));
	      $this->get('mailer')->send($message);
      }
  	}
  	return new Response(200);
		//return $this->render('LoogaresCampanaBundle:Mails:mail_descuentos_usuario.html.twig', array(
		//	'mail' => $mail
		//));
	}
}
