<?php

namespace Loogares\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Loogares\UsuarioBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class UsuarioController extends Controller
{    
    public function showAction($param) {
        
        return $this->forward('LoogaresUsuarioBundle:Usuario:actividad', array('param' => $param));          
    }

    public function actividadAction($param) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }

        if($this->get('security.context')->isGranted('ROLE_USER'))
            $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        else
            $loggeadoCorrecto = false;
        
        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = 'actividad';

        $data->loggeadoCorrecto = $loggeadoCorrecto;

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data));  
    }

    public function recomendacionesAction($param) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }

        if($this->get('security.context')->isGranted('ROLE_USER'))
            $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        else
            $loggeadoCorrecto = false;

        if(!$loggeadoCorrecto)
            return $this->redirect($this->generateUrl('actividadUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
        
        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = 'recomendaciones';

        $data->loggeadoCorrecto = $loggeadoCorrecto;

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data));  
    }

    public function fotosAction($param) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }
        
        if($this->get('security.context')->isGranted('ROLE_USER'))
            $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        else
            $loggeadoCorrecto = false;

        if(!$loggeadoCorrecto)
            return $this->redirect($this->generateUrl('actividadUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = 'fotos';

        $data->loggeadoCorrecto = $loggeadoCorrecto;

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data));  
    }

    public function editarAction($param) {
        return $this->forward('LoogaresUsuarioBundle:Usuario:editarCuenta', array('param' => $param));
    }

    public function editarCuentaAction(Request $request, $param) {

        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $formErrors = array();
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult)
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        
        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        if(!$loggeadoCorrecto)
            throw new AccessDeniedException('No puedes editar información de otro usuario');      
        
        $usuario = $usuarioResult;
        $form = $this->createFormBuilder($usuario)
                     ->add('mail', 'text')
                     ->add('nombre', 'text')
                     ->add('apellido', 'text')
                     ->add('slug', 'text')
                     ->add('fecha_nacimiento', 'birthday', array(
                                'years' => range(date('Y')-14, date('Y')-70),
                                'format' => 'dd   MM   yyyy',
                                'empty_value' => array('year' => 'Año', 'month' => 'Mes', 'day' => 'Día')
                         ))
                     ->add('sexo', 'choice', array(
                                'choices' => array('m' => 'Hombre', 'f' => 'Mujer', 'n' => 'No quiero definir'),
                                'expanded' => true
                         ))
                     ->add('web', 'text')
                     ->add('facebook', 'text')
                     ->add('twitter', 'text')
                     ->add('newsletter_activo', 'checkbox', array(
                                'label' => 'Recibir newsletter por E-mail'
                         ))
                     ->getForm();
                     

        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {  
                $em->flush();

                if($usuarioResult->getNewsletterActivo()) {
                    // Verificar suscripción Mailchimp
                }
                else {
                    // Borrar suscripción Mailchimp
                }
                
                // Mensaje de éxito en la edición
                $this->get('session')->setFlash('edicion-cuenta','¡Tu perfil acaba de actualizarse con los nuevos cambios!');
                    
                // Redirección a vista de edición de password 
                return $this->redirect($this->generateUrl('editarCuentaUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
            }
        }

        //Errores
        foreach($this->get('validator')->validate( $form ) as $formError){
            $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
        }

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'cuenta';       
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'form' => $form->createView(),
            'errors' => $formErrors
        )); 
    }

    public function editarFotoAction(Request $request, $param) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $formErrors = array();
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }

        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        if(!$loggeadoCorrecto)
            throw new AccessDeniedException('No puedes editar información de otro usuario');
        
        $form = $this->createFormBuilder($usuarioResult)
                     ->add('file')
                     ->add('slug','hidden')
                     ->getForm();
        
        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            // Verificación de selección de foto
            
            //echo $_FILES['file'];

            /*if(!$_FILES['form']['file']) {
                $formErrors['valida'] = "No tienes seleccionado ningún archivo. Por favor, elige uno.";        
            }*/


            if ($form->isValid() && sizeof($formErrors) == 0) {
                
                $em->flush();

                // Mensaje de éxito en la edición
                $this->get('session')->setFlash('edicion-foto','Cambiaste tu foto de perfil. ¡Nada de mal!');

                // Redirección a vista de edición de foto 
                return $this->redirect($this->generateUrl('editarFotoUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
            }
        }

        //Errores
        foreach($this->get('validator')->validate( $form ) as $formError){
            $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
        }

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = '';
        $data->edicion = 'foto';
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'form' => $form->createView(),
            'errors' => $formErrors
        ));  
    }

    public function editarPasswordAction(Request $request, $param) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $formErrors = array();
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }

        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        if(!$loggeadoCorrecto)
            throw new AccessDeniedException('No puedes editar información de otro usuario');
        
        $form = $this->createFormBuilder($usuarioResult)
                     ->add('password', 'password')
                     ->getForm();
                     

        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST') {

            // Verificación de password actual
            if(md5($request->request->get('passwordActual')) != $usuarioResult->getPassword()) {
                $formErrors['actual'] = "Password actual incorrecto";        
            }

            $form->bindRequest($request);           

            if ($form->isValid() && sizeof($formErrors) == 0) {
            
                // Verificación de confirmación de password
                if($request->request->get('confirmarPassword') != $usuarioResult->getPassword()) {
                $formErrors['confirmar'] = "Debes escribir el mismo password nuevo";        
                }

                // Input correcto. Se guarda nuevo password
                else{
                    // Encode de password a MD5 (SHA2 más adelante)
                    $usuarioResult->setPassword(md5($usuarioResult->getPassword()));
                    $em->flush();

                    // Mensaje de éxito en la edición
                    $this->get('session')->setFlash('edicion-password','Has cambiado tu password exitosamente. Puedes comprobarlo entrando al sitio nuevamente.');
                    
                    // Redirección a vista de edición de password 
                    return $this->redirect($this->generateUrl('editarPasswordUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));    
                } 
            }
        }

        //Errores
        foreach($this->get('validator')->validate( $form ) as $formError){
            $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
        }

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'password';
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'form' => $form->createView(),
            'errors' => $formErrors
        ));  
    }

    public function editarBorrarAction(Request $request, $param) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }

        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        if(!$loggeadoCorrecto)
            throw new AccessDeniedException('No puedes editar información de otro usuario');
        
        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = '';
        $data->edicion = 'borrar';
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array('usuario' => $data));  
    }

    public function registroAction(Request $request) {

        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        // Usuario loggeado es redirigido a su perfil
        if($this->get('security.context')->isGranted('ROLE_USER')) 
            return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($this->get('security.context')->getToken()->getUser()))));

        // Construcción del form del nuevo usuario, con objeto $usuario asociado
        $usuario = new Usuario();        

        $form = $this->createFormBuilder($usuario)
                     ->add('mail', 'text')
                     ->add('password', 'repeated', array(
                                'type' => 'password',
                                'invalid_message' => 'Los passwords no coinciden. Por favor, corrígelos.',
                                'first_name' => 'Password',
                                'second_name' => 'Confirmar password',
                                'error_bubbling' => true
                            ))
                     ->add('nombre', 'text')
                     ->add('apellido', 'text')
                     ->getForm();

        // Si el request es POST, se procesa registro
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {               

                // Form válido, generamos campos requeridos
                $usuario->setSlug('');
                $usuario->setImagenFull("default.gif");
                $usuario->setFechaRegistro(new \DateTime());

                // Password codificado en SHA2 (por ahora MD5 por compatibilidad)
                $usuario->setPassword(md5($usuario->getPassword()));                

                // Usuario queda con el estado 'Por confirmar' y se genera hash confirmación
                $estadoUsuario = $em->getRepository("LoogaresExtraBundle:Estado")
                                  ->findOneByNombre('Por confirmar');
                $usuario->setEstado($estadoUsuario);
                $usuario->setNewsletterActivo(1);
                $hashConfirmacion = md5($usuario->getMail().$usuario->getId().time());
                $usuario->setHashConfirmacion($hashConfirmacion);
                $usuario->setSalt('');

                // Seteamos tipo_usuario a ROLE_USER
                $tipoUsuario = $em->getRepository("LoogaresUsuarioBundle:TipoUsuario")
                                  ->findOneByNombre('ROLE_USER');
                $usuario->setTipoUsuario($tipoUsuario);

                // Agregamos registro a la base de datos
                $em->persist($usuario);
                $em->flush();

                // Se envía mail de confirmación a usuario
                $message = \Swift_Message::newInstance()
                        ->setSubject('Confirma tu cuenta en Loogares.com')
                        ->setFrom('noreply@loogares.com')
                        ->setTo($usuario->getMail())
                        ->setBody($this->renderView('LoogaresUsuarioBundle:Usuarios:mail_registro.html.twig', array('usuario' => $usuario)), 'text/html')
                        ->addPart($this->renderView('LoogaresUsuarioBundle:Usuarios:mail_registro.txt.twig', array('usuario' => $usuario)), 'text/plain');
                $this->get('mailer')->send($message);

                // Armado de datos para pasar a Twig
                $data = array('nombre' => $usuario->getNombre(), 'apellido' => $usuario->getApellido());
                return $this->render('LoogaresUsuarioBundle:Usuarios:mensaje_registro.html.twig', array('usuario' => $data)); 
            }
        }


        // Manejo del login incluido en la vista de registro
        $request = $this->getRequest();
        $session = $request->getSession();
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('LoogaresUsuarioBundle:Usuarios:registro.html.twig', array(
            'form' => $form->createView(),
            'last_mail' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            ));  
    }

    public function confirmarAction($hash) {

        // Verificamos que el $hash pertenezca a un usuario
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $ur->findOneBy(array('hash_confirmacion' => $hash));

        //Si el usuario con el $hash no existe
        if(!$usuarioResult) {
            $this->get('session')->setFlash('confirmacion-registro','Código de confirmación incorrecto');
            return $this->redirect($this->generateUrl('showUsuario', array('param' => 'sebastian-vicencio')));
        }

        // Si el usuario ya estaba confirmado
        if($usuarioResult->getEstado()->getNombre() == 'Activo') {
            $this->get('session')->setFlash('confirmacion-registro', 'Confirmación realizada con anterioridad');
            return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
        }    
        
        // Hash correcto y usuario no confirmado
        $estadoUsuario = $em->getRepository("LoogaresExtraBundle:Estado")
                            ->findOneByNombre('Activo');
        $usuarioResult->setEstado($estadoUsuario);
        $em->flush();

        // Se agrega usuario a lista de correos de Mailchimp
        /*$mc = new \MCAPI_MCAPI($this->container->getParameter('mailchimp_apikey'));
        $merge_vars = array(
            'EMAIL' => utf8_encode($usuarioResult->getMail()),
            'FNAME' => utf8_encode($usuarioResult->getNombre()),
            'LNAME' => utf8_encode($usuarioResult->getApellido()),
            'USER' => utf8_encode($usuarioResult->getUsuario()),
            'IDUSER' => '4000'
        );
        $r = $mc->listSubscribe($this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail(), $merge_vars, 'html', false, true, true);*/


        // Usuario inicia sesión automáticamente
        $token = new UsernamePasswordToken($usuarioResult, $usuarioResult->getPassword(),'main', $usuarioResult->getRoles());
        $this->container->get('security.context')->setToken($token);


        $this->get('session')->setFlash('confirmacion-registro', '¡Bienvenido! Has confirmado tu cuenta exitosamente. ¿Algún lugar para recomendar?');
        return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
    }

    public function loginAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $formErrors = array();

        // Usuario loggeado es redirigido a su perfil
        if($this->get('security.context')->isGranted('ROLE_USER'))
            return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($this->get('security.context')->getToken()->getUser()))));
            
        $request = $this->getRequest();
        $session = $request->getSession();
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        if($error == 'usuarios.error.completo')
            $formErrors['completo'] = $error;
        else if($error == 'usuarios.error.password')
            $formErrors['password'] = $error;
        else
            $formErrors['emptyPassword'] = $error;

        // Variable de sesión con ciudad (temporal)
        $er = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $this->get('session')->set('ciudad',$er->find(1)->getId());

        return $this->render('LoogaresUsuarioBundle:Usuarios:login.html.twig', array(
            'last_mail' => $session->get(SecurityContext::LAST_USERNAME),
            'errors'         => $formErrors,
        ));
    }
}
