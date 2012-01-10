<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('LoogaresLugarBundle:Lugares:ajax.html.twig');
    }

    public function editarFotoAction(Request $request, $slug, $id) {
        $em = $this->getDoctrine()->getEntityManager();
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        $imagen = $ilr->find($id);

        if($imagen->getLugar()->getSlug() != $slug) {
        	throw $this->createNotFoundException('La foto especificada no corresponde al lugar '.$imagen->getLugar()->getNombre());
        }

        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser() == $imagen->getUsuario();
        if(!$loggeadoCorrecto)
            throw new AccessDeniedException('No puedes editar una foto agregada por otro usuario'); 
        
        $form = $this->createFormBuilder($imagen)
                         ->add('titulo_enlace', 'text')
                         ->getForm();

        // Si el request es POST, se procesa la edición de la foto
        if ($request->getMethod() == 'POST') { 
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->flush();

                // Mensaje de éxito en la edición
                $this->get('session')->setFlash('edicion-foto-lugar','¡Ese es el espíritu, '.$imagen->getUsuario()->getNombre().' '.$imagen->getUsuario()->getApellido().'! Si sigues subiendo fotos, cuando tengamos un hijo le pondremos tu nombre.');
                    
                // Redirección a vista de fotos del usuario
                return $this->redirect($this->generateUrl('fotosLugaresUsuario', array('param' => $ur->getIdOrSlug($imagen->getUsuario()))));
            }
        }

        return $this->render('LoogaresLugarBundle:Lugares:editar_foto.html.twig', array(
            'imagen' => $imagen,
            'form' => $form->createView(),
        ));
    }

    public function eliminarFotoAction(Request $request, $slug, $id) {
    	$em = $this->getDoctrine()->getEntityManager();
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        $imagen = $ilr->find($id);

        if($imagen->getLugar()->getSlug() != $slug) {
        	throw $this->createNotFoundException('La foto especificada no corresponde al lugar '.$imagen->getLugar()->getNombre());
        }

        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser() == $imagen->getUsuario();
        if(!$loggeadoCorrecto)
            throw new AccessDeniedException('No puedes eliminar una foto agregada por otro usuario');

        // La imagen y el usuario son los correpondientes.

        //Se cambia estado de la imagen a 'Eliminado'
        $estadoImagen = $em->getRepository("LoogaresExtraBundle:Estado")
                           ->findOneByNombre('Eliminado');
        $imagen->setEstado($estadoImagen);

        $em->flush();
                   
        // Mensaje de éxito de la eliminación
        $this->get('session')->setFlash('eliminar-foto-lugar','Tu foto acaba de ser borrada. Agrega otra cuando quieras.');
                    
        // Redirección a vista de fotos del usuario
        return $this->redirect($this->generateUrl('fotosLugaresUsuario', array('param' => $ur->getIdOrSlug($imagen->getUsuario())))); 
           
    }
}
