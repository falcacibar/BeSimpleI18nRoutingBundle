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

    public function recomendacionesAction($param, $orden=null, $pagina=null) {
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

        $orden = (!$this->getRequest()->query->get('orden')) ? 'mejor-evaluadas' : $this->getRequest()->query->get('orden');

        if($orden == 'mejor-evaluadas')
            $orderBy = 'ORDER BY r.estrellas DESC, l.nombre';
        else if($orden == 'ultimas')
            $orderBy = 'ORDER BY r.fecha_creacion DESC, l.nombre';
        else if($orden == 'nombre')
            $orderBy = 'ORDER BY l.nombre';
        else
            $orderBy = '';

        $pagina = (!$this->getRequest()->query->get('pagina')) ? 1 : $this->getRequest()->query->get('pagina');
        $offset = ($pagina - 1) * 10;
        
        $data = $ur->getDatosUsuario($usuarioResult, $orderBy);
        $data->tipo = 'recomendaciones';
        $data->orden = $orden;
        $data->pagina = $pagina;
        $data->totalPaginas = ($data->totalRecomendaciones > 10) ? ceil($data->totalRecomendaciones / 10) : 1;
        $data->offset = $offset;

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

        $orden = (!$this->getRequest()->query->get('orden')) ? 'ultimas' : $this->getRequest()->query->get('orden');

        if($orden == 'ultimas')
            $orderBy = 'ORDER BY im.fecha_creacion DESC, l.nombre';
        else if($orden == 'nombre')
            $orderBy = 'ORDER BY l.nombre';
        else
            $orderBy = '';

        $pagina = (!$this->getRequest()->query->get('pagina')) ? 1 : $this->getRequest()->query->get('pagina');
        $offset = ($pagina - 1) * 15;

        $data = $ur->getDatosUsuario($usuarioResult, null, $orderBy);
        $data->tipo = 'fotos';
        $data->orden = $orden;
        $data->pagina = $pagina;
        $data->totalPaginas = ($data->totalImagenesLugar > 15) ? ceil($data->totalImagenesLugar / 15) : 1;
        $data->offset = $offset;

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

        // Guardamos mail de usuario actual
        $mail = $usuarioResult->getMail();
        
        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST') {

            $usuario = $ur->findOneByMail($usuarioResult->getMail());
            $form->bindRequest($request);            

            if ($form->isValid()) {

                 // Stripeamos las URLs de http://
                $usuarioResult->setWeb(preg_replace("/^https?:\/\/(.+)$/i","\\1",$usuarioResult->getWeb()));
                $usuarioResult->setFacebook(preg_replace("/^https?:\/\/(.+)$/i","\\1",$usuarioResult->getFacebook()));
                $twitter = $usuarioResult->getTwitter(); 
                if(substr($twitter,0,1) == '@')
                    $usuarioResult->setTwitter(str_replace('@','www.twitter.com/',$twitter));
                else               
                    $usuarioResult->setTwitter(preg_replace("/^https?:\/\/(.+)$/i","\\1",$usuarioResult->getTwitter()));


                $em->flush();

                /* Manejo de suscripción a Mailchimp */
                //$mc = $this->get('mail_chimp.client');
                /*$mcInfo = $mc->listMemberInfo( $this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail() );
                echo "respuesta";
                $mcId = 0;

                if (!$mc->errorCode){
                    if(!empty($mcInfo['success'])){
                        if(isset($mcInfo['data'])){ // tiene que estar en la lista para considerarse "suscrito"??
                            $mcId = $mcInfo['data'][0]['id'];
                        }
                    }
                }
                else {
                    echo "Conexión falló!";
                }

                if($usuarioResult->getNewsletterActivo()) {
                   $merge_vars = array(
                        'EMAIL' => utf8_encode($usuarioResult->getMail()),
                        'FNAME' => utf8_encode($usuarioResult->getNombre()),
                        'LNAME' => utf8_encode($usuarioResult->getApellido()),
                        'USER' => utf8_encode($usuarioResult->getSlug()),
                        'IDUSER' => $usuarioResult->getId()
                    );
                    // Verificar suscripción Mailchimp
                    if($mcId == 0) {
                        // Nueva suscripción
                        $mc->listSubscribe($this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail(), $merge_vars, 'html', false, true, true );
                    }
                    else {
                        // Usuario suscrito. Se actualizan datos
                        $mc->listUpdateMember($this->container->getParameter('mailchimp_list_id'), $mcId, $merge_vars, 'html', false);
                    }
                }
                else {
                    // Borrar suscripción Mailchimp
                    if($mcId > 0)
                        $mc->listUnsubscribe( $this->container->getParameter('mailchimp_list_id'), $mcId, true, false );
                }*/         
                
                // Mensaje de éxito en la edición
                $this->get('session')->setFlash('edicion-cuenta','¡Tu perfil acaba de actualizarse con los nuevos cambios!');
                    
                // Redirección a vista de edición de password 
                return $this->redirect($this->generateUrl('editarCuentaUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
            }
        }

        //Errores
        foreach($this->get('validator')->validate( $form ) as $formError){
            $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();

            if(substr($formError->getPropertyPath(), 5) == 'mail') {
                $usuarioResult->setMail($mail);
            }
        }

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'cuenta';
        $data->loggeadoCorrecto = $loggeadoCorrecto;       
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
                     ->add('mail','hidden')
                     ->getForm();
        
        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST') {            
           
            if($request->request->get("borrarFoto")) {
                $usuarioResult->setImagenFull('default.gif');
                $em->flush();
            }
            else {
                $form->bindRequest($request);

                // Verificación de selección de foto
                
                //echo $_FILES['file'];

                if($usuarioResult->file == null) {
                    $formErrors['valida'] = "No tienes seleccionado ningún archivo. Por favor, elige uno.";        
                }

                if ($form->isValid() && sizeof($formErrors) == 0) {
                    $usuarioResult->setImagenFull(' ');
                    $em->flush();

                    // Mensaje de éxito en la edición
                    $this->get('session')->setFlash('edicion-foto','Cambiaste tu foto de perfil. ¡Nada de mal!');

                    // Redirección a vista de edición de foto 
                    return $this->redirect($this->generateUrl('editarFotoUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
                }
            }
        }

        //Errores
        foreach($this->get('validator')->validate( $form ) as $formError){
            $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
        }

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'foto';
        $data->loggeadoCorrecto = $loggeadoCorrecto;
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
        $data->loggeadoCorrecto = $loggeadoCorrecto;
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'form' => $form->createView(),
            'errors' => $formErrors
        ));  
    }

    public function editarBorrarAction(Request $request, $param) {
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

        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST') {

            // Verificación de password actual
            if($request->request->get('password') == '')
                $formErrors['password'] = "El campo password es obligatorio. Por favor, complétalo.";
            else if(md5($request->request->get('password')) != $usuarioResult->getPassword())
                $formErrors['password'] = "Los passwords no coinciden. Por favor, corrígelos.";

            if($request->request->get('motivo') == '')
                $formErrors['motivo'] = "Queremos saber por qué te vas. Por favor, completa el campo.";


            if(sizeof($formErrors) == 0) {                        
                // Input correcto. La cuenta se deja como inactiva
                $estadoUsuario = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Inactivo');
                $usuarioResult->setEstado($estadoUsuario);
                $em->flush();

                // Borrar suscripción Mailchimp
                $mc = $this->get('mail_chimp.client');
                /*$mcInfo = $mc->listMemberInfo( $this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail() );
                echo "respuesta";
                $mcId = 0;

                if (!$mc->errorCode){
                    if(!empty($mcInfo['success'])){
                        if(isset($mcInfo['data'])){ // tiene que estar en la lista para considerarse "suscrito"??
                            $mcId = $mcInfo['data'][0]['id'];
                        }
                    }
                }
                else {
                    echo "Conexión falló!";
                }
                if($mcId > 0)
                    $mc->listUnsubscribe( $this->container->getParameter('mailchimp_list_id'), $mcId, true, false );
                */         

                // Enviamos correo a administrador con la razón del cierre de la cuenta
                $mail = array();
                $mail['asunto'] = 'Usuario eliminado';
                $mail['usuario'] = $usuarioResult;
                $mail['fechaRegistro'] = $usuarioResult->getFechaRegistro()->format('d-m-Y');
                $mail['motivo'] = $request->request->get('motivo');

                $message = \Swift_Message::newInstance()
                            ->setSubject($mail['asunto'])
                            ->setFrom('noreply@loogares.com')
                            ->setTo('cuenta.usuarios@loogares.com');

                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $mail['logo'] = $logo;
                $message->setBody($this->renderView('LoogaresUsuarioBundle:Usuarios:mail_borrar_cuenta.html.twig', array('mail' => $mail)), 'text/html');
                $this->get('mailer')->send($message);

                // Cerramos la sesión
                $this->container->get('security.context')->setToken(null);

                // Mensaje de éxito en la edición
                $this->get('session')->setFlash('edicion-borrar','Tu cuenta acaba de ser borrada. Fue bonito mientras duró.');
                    
                // Redirección a vista de edición de password 
                return $this->redirect($this->generateUrl('logout'));
            }
        }
        
        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'borrar';
        $data->loggeadoCorrecto = $loggeadoCorrecto;
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'errors' => $formErrors
        ));  
    }

    public function registroAction(Request $request) {

        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $formErrors = array();
        
        // Usuario loggeado es redirigido a su perfil
        if($this->get('security.context')->isGranted('ROLE_USER')) 
            return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($this->get('security.context')->getToken()->getUser()))));

        // Construcción del form del nuevo usuario, con objeto $usuario asociado
        $usuario = new Usuario();        

        $form = $this->createFormBuilder($usuario)
                     ->add('nombre', 'text')
                     ->add('apellido', 'text')
                     ->add('mail', 'text')
                     ->add('password', 'password')                     
                     ->getForm();

        // Si el request es POST, se procesa registro
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {   
            
                // Verificación de confirmación de password
                if($request->request->get('confirmarPassword') != $usuario->getPassword()) {
                    $formErrors['confirmar'] = "Los passwords no coinciden. Por favor, corrígelos.";        
                }
                                
                else {
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
                            ->setTo($usuario->getMail());
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                    $message->setBody($this->renderView('LoogaresUsuarioBundle:Usuarios:mail_registro.html.twig', array('usuario' => $usuario, 'logo' => $logo)), 'text/html')
                            ->addPart($this->renderView('LoogaresUsuarioBundle:Usuarios:mail_registro.txt.twig', array('usuario' => $usuario)), 'text/plain');
                    $this->get('mailer')->send($message);

                    // Armado de datos para pasar a Twig
                    $data = array('nombre' => $usuario->getNombre(), 'apellido' => $usuario->getApellido());
                    return $this->render('LoogaresUsuarioBundle:Usuarios:mensaje_registro.html.twig', array('usuario' => $data));
                }
            }
        }

        //Errores
        if ($request->getMethod() == 'POST') {
            foreach($this->get('validator')->validate( $form ) as $formError){
                $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
            }
        }
        
        $this->get('session')->set(SecurityContext::AUTHENTICATION_ERROR, null);       

        return $this->render('LoogaresUsuarioBundle:Usuarios:registro.html.twig', array(
            'form' => $form->createView(),
            'last_mail' => $this->get('session')->get(SecurityContext::LAST_USERNAME),
            'errors' => array(),
            'formErrors' => $formErrors
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
        /*$mc = $this->get('mail_chimp.client');
        $merge_vars = array(
            'EMAIL' => utf8_encode($usuarioResult->getMail()),
            'FNAME' => utf8_encode($usuarioResult->getNombre()),
            'LNAME' => utf8_encode($usuarioResult->getApellido()),
            'USER' => utf8_encode($usuarioResult->getSlug()),
            'IDUSER' => $usuarioResult->getId()
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
        if($error != null && $error->getMessage() == 'Bad credentials')
            $formErrors['completo'] = 'usuario.errors.completo';
        else if($error != null && $error->getMessage() == 'The presented password is invalid.')
            $formErrors['password'] = 'usuario.errors.password';
        else if($error != null && $error->getMessage() == 'The presented password cannot be empty.')
            $formErrors['emptyPassword'] = 'usuario.errors.emptyPassword';
        else if($error != null && $error->getMessage() == 'User account is disabled.')
            $formErrors['noActivo'] = 'usuario.errors.noActivo';

        $session->set(SecurityContext::AUTHENTICATION_ERROR, null);

        // Variable de sesión con ciudad (temporal)
        $er = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $this->get('session')->set('ciudad',$er->find(1)->getId());

        return $this->render('LoogaresUsuarioBundle:Usuarios:login.html.twig', array(
            'last_mail' => $session->get(SecurityContext::LAST_USERNAME),
            'errors' => $formErrors
        ));
    }
}
