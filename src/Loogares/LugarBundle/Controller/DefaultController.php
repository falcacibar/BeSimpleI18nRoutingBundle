<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Loogares\LugarBundle\Entity\ReportarLugar;
use Loogares\LugarBundle\Entity\ReportarImagen;
use Loogares\LugarBundle\Entity\ReportarRecomendacion;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



class DefaultController extends Controller
{
	public function reportarFotoAction(Request $request, $slug, $id) {
		$em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $formErrors = array();

        $imagen = $ilr->find($id);

        if($imagen->getLugar()->getSlug() != $slug) {
            throw $this->createNotFoundException('La foto especificada no corresponde al lugar '.$lugar->getNombre());
        }

        if($this->get('security.context')->getToken()->getUser() == $imagen->getUsuario()) {
            throw $this->createNotFoundException('No puedes reportar una imagen agregada por ti.');   
        }
        else {
            $reportes = $lr->getReportesImagenesUsuarioLugar($imagen->getId(), $this->get('security.context')->getToken()->getUser(), 1);
            if(sizeof($reportes) > 0)
                throw $this->createNotFoundException('Ya has reportado esta imagen anteriormente, y aún está en revisión. Una vez finalizado este proceso, podrás reportar la imagen nuevamente.');   
        }

        $reporte = new ReportarImagen();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') { 
            $form->bindRequest($request);

            if ($form->isValid()) {

                $reporte->setImagenLugar($imagen);
                $reporte->setUsuario($this->get('security.context')->getToken()->getUser());
                $reporte->setFecha(new \Datetime());

                $estadoReporte = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Por revisar');
                $reporte->setEstado($estadoReporte);

                $em->persist($reporte);
                $em->flush();

                $estadoImagen = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Reportado');
                $imagen->setEstado($estadoImagen);
                $em->flush();

               // Se envía mail a administradores notificando reporte
                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('reportes.mail.imagen.asunto').' '.$imagen->getLugar()->getNombre();
                $mail['reporte'] = $reporte;
                $mail['tipo'] = "imagen";
                $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo('reportar@loogares.com');
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);               

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('reportar-imagen','reportes.flash');
                    
                // Redirección a galería de fotos
                return $this->redirect($this->generateUrl('_galeria', array('slug' => $imagen->getLugar()->getSlug())));
            }
            else {
                foreach($this->get('validator')->validate( $form ) as $formError){
                    $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
                }
            }         

        }
        return $this->render('LoogaresLugarBundle:Lugares:reporte.html.twig', array(
            'imagen' => $imagen,
            'form' => $form->createView(),
            'errors' => $formErrors,
        ));
	}

    public function reportarRecomendacionAction(Request $request, $slug, $usuarioSlug) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $formErrors = array();

        if(preg_match('/\w/', $usuarioSlug)){
            $usuario = $ur->findOneBySlug($usuarioSlug);
        }else{
            $usuario = $ur->findOneById($usuarioSlug);
        }
        $lugar = $lr->findOneBySlug($slug);

        $recomendacion = $rr->getRecomendacionUsuarioLugar($usuario->getId(),$lugar->getId());

        if($this->get('security.context')->getToken()->getUser() == $recomendacion->getUsuario()) {
            throw $this->createNotFoundException('No puedes reportar una recomendación hecha por ti.');   
        }
        else {
            $reportes = $rr->getReportesRecomendacionUsuario($recomendacion->getId(), $this->get('security.context')->getToken()->getUser(), 1);
            if(sizeof($reportes) > 0)
                throw $this->createNotFoundException('Ya has reportado esta recomendación anteriormente, y aún está en revisión. Una vez finalizado este proceso, podrás reportar la recomendación nuevamente.');   
        }

        $reporte = new ReportarRecomendacion();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') { 
            $form->bindRequest($request);

            if ($form->isValid()) {

                $reporte->setRecomendacion($recomendacion);
                $reporte->setUsuario($this->get('security.context')->getToken()->getUser());
                $reporte->setFecha(new \Datetime());

                $estadoReporte = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Por revisar');
                $reporte->setEstado($estadoReporte);

                $em->persist($reporte);
                $em->flush();

                $estadoRecomendacion = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Reportado');
                $recomendacion->setEstado($estadoRecomendacion);
                $em->flush();

               // Se envía mail a administradores notificando reporte
                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('reportes.mail.recomendacion.asunto').' '.$recomendacion->getLugar()->getNombre();
                $mail['reporte'] = $reporte;
                $mail['tipo'] = "recomendacion";
                $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo('reportar@loogares.com');
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);               

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('reportar-recomendacion','reportes.flash');
                    
                // Redirección a ficha del lugar 
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }
            else {
                foreach($this->get('validator')->validate( $form ) as $formError){
                    $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
                }
            }         

        }
        return $this->render('LoogaresLugarBundle:Lugares:reporte.html.twig', array(
            'recomendacion' => $recomendacion,
            'form' => $form->createView(),
            'errors' => $formErrors,
        ));
    }

	public function reportarLugarAction(Request $request, $slug) {
		$em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $formErrors = array();

        $lugar = $lr->findOneBySlug($slug);
        
        $reportes = $lr->getReportesUsuarioLugar($lugar->getId(), $this->get('security.context')->getToken()->getUser(), 1);
        if(sizeof($reportes) > 0)
            throw $this->createNotFoundException('Nos has notificado anteriormente que este lugar cerró. Aún estamos corroborando datos al respecto.');

        $reporte = new ReportarLugar();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') { 
            $form->bindRequest($request);

            if ($form->isValid()) {
                $reporte->setLugar($lugar);
                $reporte->setUsuario($this->get('security.context')->getToken()->getUser());
                $reporte->setFecha(new \Datetime());

                $estadoReporte = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Por revisar');

                $reporte->setEstado($estadoReporte);

                $em->persist($reporte);
                $em->flush();

                $estadoLugar = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Reportado');
                $lugar->setEstado($estadoLugar);
                $em->flush();
                
                // Se envía mail a administradores notificando reporte
                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('reportes.mail.lugar.asunto').' '.$lugar->getNombre();
                $mail['reporte'] = $reporte;
                $mail['tipo'] = "lugar";
                $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo('reportar@loogares.com');
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);               

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('reportar-lugar','reportes.flash');
                    
                // Redirección a ficha del lugar 
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }
            else {
                foreach($this->get('validator')->validate( $form ) as $formError){
                    $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
                }
            }         

        }
        return $this->render('LoogaresLugarBundle:Lugares:reporte.html.twig', array(
            'lugar' => $lugar,
            'form' => $form->createView(),
            'errors' => $formErrors,
        ));
	}
    
}
