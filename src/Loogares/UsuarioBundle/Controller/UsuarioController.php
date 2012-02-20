<?php

namespace Loogares\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Loogares\UsuarioBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Image\Box;
use Imagine\Image;


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
        $router = $this->get('router');
        $fn = $this->get('fn');
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $porPagina = 10;
        
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

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*10);

        $recomendaciones = $ur->getUsuarioRecomendaciones($usuarioResult->getId(), $orderBy, $offset);
        
        $data = $ur->getDatosUsuario($usuarioResult, $orderBy);
        $data->tipo = 'recomendaciones';
        $data->orden = $orden;
        $data->pagina = $paginaActual;
        $data->totalPaginas = ($data->totalRecomendaciones > 10) ? ceil($data->totalRecomendaciones / 10) : 1;
        $data->offset = $offset;
        $data->recomendacionesTodas = $recomendaciones;

        $data->loggeadoCorrecto = $loggeadoCorrecto;

        $extras = array(
            'param' => $usuarioResult->getSlug()
        );

        $paginacion = $fn->paginacion($data->totalRecomendaciones, $porPagina, 'recomendacionesUsuario', $extras, $router );

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data, 'paginacion' => $paginacion));  
    }

    public function fotosAction($param) {
        $fn = $this->get('fn');
        $router = $this->get('router');
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
        $ppag = 30;
        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);
        

        $imagenesLugar= $ur->getFotosLugaresAgregadasUsuario($usuarioResult->getId(), $orderBy, $offset);

        $data = $ur->getDatosUsuario($usuarioResult);

        $data->tipo = 'fotos';
        $data->orden = $orden;
        $data->pagina = $pagina;
        $data->totalPaginas = ($data->totalImagenesLugar > $ppag) ? floor($data->totalImagenesLugar / $ppag) : 1;
        $data->offset = $offset;
        $data->imagenesLugar = $imagenesLugar;

        $data->loggeadoCorrecto = $loggeadoCorrecto;

        $params = array(
            'param' => $data->getSlug()
        );
            
        $paginacion = $fn->paginacion($data->totalImagenesLugar, $ppag, 'fotosLugaresUsuario', $params, $router );

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array(
            'usuario' => $data,
            'paginacion' => $paginacion
        ));  
    }

    public function lugaresAction($param) {
        $fn = $this->get('fn');
        $router = $this->get('router');
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

        $accion = (!$this->getRequest()->query->get('accion')) ? 3 : $this->getRequest()->query->get('accion');

        $pagina = (!$this->getRequest()->query->get('pagina')) ? 1 : $this->getRequest()->query->get('pagina');
        $ppag = 30;
        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);

        $acciones = $ur->getAccionUsuario($this->get('security.context')->getToken()->getUser(), $accion, $offset);
        $totalAcciones = $ur->getTotalAccionesUsuario($accion, $this->get('security.context')->getToken()->getUser());

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = 'lugares';
        $data->accion = $accion;
        $data->pagina = $pagina;
        $data->totalPaginas = ($data->totalAcciones[$accion - 1] > $ppag) ? ceil($data->totalAcciones[$accion - 1] / $ppag) : 1;
        $data->offset = $offset;
        $data->acciones = $acciones;
        $data->loggeadoCorrecto = $loggeadoCorrecto;

        $params = array(
            'param' => $data->getSlug()
        );
            
        $paginacion = $fn->paginacion($data->totalAcciones[$accion - 1], $ppag, 'lugaresUsuario', $params, $router );

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->render('LoogaresUsuarioBundle:Usuarios:lugares_usuario.html.twig', array(
                'usuario' => $data,
                'paginacion' => $paginacion,
            ));

        }

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array(
            'usuario' => $data,
            'paginacion' => $paginacion,
        ));  
    }

    public function editarAction($param) {
        return $this->forward('LoogaresUsuarioBundle:Usuario:editarCuenta', array('param' => $param));
    }

    public function editarCuentaAction(Request $request, $param) {

        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $tur = $em->getRepository("LoogaresUsuarioBundle:TipoUsuario");
        $formErrors = array();
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult)
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        
        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        $rolAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        if($rolAdmin == 0 && !$loggeadoCorrecto)
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
                                'empty_value' => array('year' => $this->get('translator')->trans('usuario.edicion.cuenta.nacimiento.year'), 'month' => $this->get('translator')->trans('usuario.edicion.cuenta.nacimiento.month'), 'day' => $this->get('translator')->trans('usuario.edicion.cuenta.nacimiento.day'))
                         ))
                     ->add('mostrar_edad', 'checkbox')
                     ->add('sexo', 'choice', array(
                                'choices' => array('m' => $this->get('translator')->trans('usuario.edicion.cuenta.sexo.hombre'), 'f' => $this->get('translator')->trans('usuario.edicion.cuenta.sexo.mujer'), 'n' => $this->get('translator')->trans('usuario.edicion.cuenta.sexo.ninguno')),
                                'expanded' => true
                         ))
                     ->add('web', 'text')
                     ->add('facebook', 'text')
                     ->add('twitter', 'text')
                     ->add('newsletter_activo', 'checkbox', array(
                                'label' => $this->get('translator')->trans('usuario.edicion.cuenta.newsletter')
                         ))
                     ->getForm();                     

        // Guardamos mail de usuario actual
        $mail = $usuarioResult->getMail();
        
        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST') {

            $usuario = $ur->findOneByMail($usuarioResult->getMail());
            $form->bindRequest($request);            

            if ($form->isValid()) {
                if(isset($_POST['tipo_usuario'])){
                    $tipoUsuario = $tur->find($_POST['tipo_usuario']);
                    $usuarioResult->setTipoUsuario($tipoUsuario);
                }  
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
                /*$mc = $this->get('mail_chimp.client');
                $mcInfo = $mc->listMemberInfo( $this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail() );
                echo "respuesta";
                /*$mcId = 0;

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
                $this->get('session')->setFlash('usuario_flash','usuario.flash.edicion.cuenta');
                    
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
            'errors' => $formErrors,
            'tipoUsuarios' => $tur->findAll()
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
                if($usuarioResult->file == null) {
                    $formErrors['valida'] = "usuario.errors.editar.foto.blanco";        
                }

                if ($form->isValid() && sizeof($formErrors) == 0) {
                    $usuarioResult->setImagenFull(' ');
                    $em->flush();

                    // Mensaje de éxito en la edición
                    $this->get('session')->setFlash('usuario_flash','usuario.flash.edicion.foto');

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
                $formErrors['actual'] = "usuario.errors.editar.password.actual";        
            }

            $form->bindRequest($request);           

            if ($form->isValid() && sizeof($formErrors) == 0) {
            
                // Verificación de confirmación de password
                if($request->request->get('confirmarPassword') != $usuarioResult->getPassword()) {
                $formErrors['confirmar'] = "usuario.errors.editar.password.confirmar";        
                }

                // Input correcto. Se guarda nuevo password
                else{
                    // Encode de password a MD5 (SHA2 más adelante)
                    $usuarioResult->setPassword(md5($usuarioResult->getPassword()));
                    $em->flush();

                    // Mensaje de éxito en la edición
                    $this->get('session')->setFlash('usuario_flash','usuario.flash.edicion.password');
                    
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
                $formErrors['password'] = "usuario.errors.editar.borrar.pass_obligatorio";
            else if(md5($request->request->get('password')) != $usuarioResult->getPassword())
                $formErrors['password'] = "usuario.errors.editar.borrar.pass_incorrecto";

            if($request->request->get('motivo') == '')
                $formErrors['motivo'] = "usuario.errors.editar.borrar.motivo";


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
                $mail['asunto'] = $this->get('translator')->trans('usuario.edicion.borrar.mail.asunto');
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
                $this->get('session')->setFlash('usuario_flash','usuario.flash.edicion.borrar_cuenta');
                    
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
                    $formErrors['confirmar'] = "usuario.errors.validacion.confirmar_password";        
                }
                                
                else {
                    // Form válido, generamos campos requeridos

                    // Slug como nombre-apellido-repetido
                    $fn = $this->get('fn');
                    $slug = $fn->generarSlug($usuario->getNombre().'-'.$usuario->getApellido());
                    $repetidos = $ur->getUsuarioSlugRepetido($slug);
                    if($repetidos > 0)
                        $slug = $slug.'-'.++$repetidos;                    
                    $usuario->setSlug($slug);
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
                            ->setSubject($this->get('translator')->trans('usuario.registro.confirmar.mail.asunto'))
                            ->setFrom('noreply@loogares.com')
                            ->setTo($usuario->getMail());
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                    $boton = $message->embed(\Swift_Image::fromPath('assets/images/mails/confirmar_cuenta.png'));
                    $message->setBody($this->renderView('LoogaresUsuarioBundle:Usuarios:mail_registro.html.twig', array('usuario' => $usuario, 'logo' => $logo, 'boton' => $boton)), 'text/html')
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
            $this->get('session')->setFlash('usuario_flash','usuario.flash.confirmar_usuario.incorrecto');
            return $this->redirect($this->generateUrl('login'));
        }

        // Si el usuario ya estaba confirmado
        if($usuarioResult->getEstado()->getNombre() == 'Activo') {
            $this->get('session')->setFlash('usuario_flash', 'usuario.flash.confirmar_usuario.anterioridad');
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


        $this->get('session')->setFlash('usuario_flash', 'usuario.flash.confirmar_usuario.exito');
        return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
    }

    public function olvidarPasswordAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");


        // Usuario loggeado es redirigido a su perfil
        if($this->get('security.context')->isGranted('ROLE_USER')) 
            return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($this->get('security.context')->getToken()->getUser())))); 

        $formErrors = array();
        if($request->getMethod() == 'POST') {

            if($request->request->get('mail') == '')
                $formErrors['mail'] = 'usuario.olvidar.form.mail';

            if(sizeof($formErrors) == 0) {
                // Mail ingresado, se obtiene usuario asociado
                $usuario = $ur->findOneByMail($request->request->get('mail'));

                if($usuario == null)
                    $this->get('session')->setFlash('usuario-no-existe', 'usuario.olvidar.flash.usuario_no_existe');
                else {
                    // Enviamos E-mail con link para resetear password
                    $mail = array();
                    $mail['asunto'] = $this->get('translator')->trans('usuario.olvidar.mail.asunto');
                    $mail['usuario'] = $usuario;

                    $paths = array();
                    $paths['logo'] = 'assets/images/mails/logo_mails.png';

                    $message = $this->get('fn')->enviarMail($mail['asunto'], $usuario->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresUsuarioBundle:Mails:mail_olvidar_password.html.twig', $this->get('templating'));
                    $this->get('mailer')->send($message);

                    return $this->render('LoogaresUsuarioBundle:Usuarios:mensaje_olvidar_password.html.twig');
                }
            }            
        }


        $tipo = 'olvidar';
        return $this->render('LoogaresUsuarioBundle:Usuarios:olvidar_password.html.twig', array(
            'errors' => $formErrors,
            'tipo' => $tipo,
        ));
    }

    public function regenerarPasswordAction(Request $request, $hash) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");


        // Usuario loggeado es redirigido a su perfil
        if($this->get('security.context')->isGranted('ROLE_USER')) 
            return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($this->get('security.context')->getToken()->getUser()))));

        $usuario = $ur->findOneBy(array('hash_confirmacion' => $hash));

        if($usuario == null) {
            $this->get('session')->setFlash('hash-incorrecto','usuario.regenerar.flash.hash_incorrecto');
            return $this->redirect($this->generateUrl('login'));
        }

        $formErrors = array();
        if($request->getMethod() == 'POST') {

            if($request->request->get('nuevo') == '')
                $formErrors['nuevo'] = 'usuario.regenerar.form.errors.nuevo';
            if($request->request->get('confirmar') == '')
                $formErrors['confirmar'] = 'usuario.regenerar.form.errors.confirmar';

            if(sizeof($formErrors) == 0) {
                // Verificamos que nuevo password coincida con confirmación
                if($request->request->get('nuevo') != $request->request->get('confirmar'))
                    $formErrors['confirmar_incorrecto'] = 'usuario.errors.validacion.confirmar_password';
                else {
                    // Todo ok. Guardamos nuevo password encoded MD5 (SHA2 más adelante)
                    $usuario->setPassword(md5($request->request->get('nuevo')));
                    $em->flush();

                    // Usuario inicia sesión automáticamente
                    $token = new UsernamePasswordToken($usuario, $usuario->getPassword(),'main', $usuario->getRoles());
                    $this->container->get('security.context')->setToken($token);

                    // Mensaje de éxito en la edición
                    $this->get('session')->setFlash('usuario_flash','usuario.flash.edicion.password');
                    
                    // Redirección a perfil de usuario
                    return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($usuario))));
                }
            }            
        }


        $tipo = 'regenerar';
        return $this->render('LoogaresUsuarioBundle:Usuarios:olvidar_password.html.twig', array(
            'usuario' => $usuario,
            'errors' => $formErrors,
            'tipo' => $tipo,
        ));
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
            $formErrors['completo'] = 'usuario.errors.login.completo';
        else if($error != null && $error->getMessage() == 'The presented password is invalid.')
            $formErrors['password'] = 'usuario.errors.login.password';
        else if($error != null && $error->getMessage() == 'The presented password cannot be empty.')
            $formErrors['emptyPassword'] = 'usuario.errors.login.emptyPassword';
        else if($error != null && $error->getMessage() == 'User account is disabled.')
            $formErrors['noActivo'] = 'usuario.errors.login.noActivo';
            
        $session->set(SecurityContext::AUTHENTICATION_ERROR, null);

        return $this->render('LoogaresUsuarioBundle:Usuarios:login.html.twig', array(
            'last_mail' => $session->get(SecurityContext::LAST_USERNAME),
            'errors' => $formErrors,
            'locale' => $this->get('session')->getLocale()
        ));
    }

    public function totalAccionesPendientesAction($accion) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        $cantidad = $ur->getTotalAccionesUsuario($accion, $this->get('security.context')->getToken()->getUser());

        return new Response($cantidad);
    }

}
