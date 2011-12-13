<?php

namespace Loogares\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Loogares\UsuarioBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;


class UsuarioController extends Controller
{
    
    public function indexAction($name) {
        return $this->render('LoogaresUsuarioBundle:Usuarios:index.html.twig', array('name' => $name));
    }
    
    public function showAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $ur->findOneBySlug($slug); 
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No user found for slug '.$slug);
        }
        
        //Total recomendaciones usuario
        $recomendaciones = $ur->getUsuarioRecomendaciones($usuarioResult->getId()); 
        $totalRecomendaciones = count($recomendaciones);

        //Primeras recomendaciones usuario
        $primerasRecomendaciones = $ur->getPrimerasRecomendaciones($usuarioResult->getId());
        $totalPrimerasRecomendaciones = count($primerasRecomendaciones);   

        //Total de lugares agregados por el usuario
        $totalLugaresAgregados = $ur->getLugaresAgregadosUsuario($usuarioResult->getId());

        //Total de fotos de lugares agregadas por el usuario
        $totalImagenesLugar= $ur->getFotosLugaresAgregadasUsuario($usuarioResult->getId());

        //Cálculo de edad
        if($usuarioResult->getFechaNacimiento() != null) {
            $birthday = $usuarioResult->getFechaNacimiento()->format('d-m-Y');
            if($birthday != '30-11--0001') {
                list($d,$m,$Y)    = explode("-",$birthday);
                $edad = date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y;
            }
            else {
                $edad = '0';
            }            
        } else {
            $edad = '0';
        }
        

        //Nombre del sexo
        if($usuarioResult->getSexo() != null) {
            if($usuarioResult->getSexo() == "m") {
                $sexoResult = "Hombre";
            }
            else {
                $sexoResult = "Mujer";
            }
        } else {
            $sexoResult = null;
        }

        //Array con links de usuario
        $links = array();
        if($usuarioResult->getLink1() != null || $usuarioResult->getLink1() != '') {
            $links[] = $usuarioResult->getLink1();
        }
        if($usuarioResult->getLink2() != null || $usuarioResult->getLink2() != '') {
            $links[] = $usuarioResult->getLink2();
        }
        if($usuarioResult->getLink3() != null || $usuarioResult->getLink3() != '') {
            $links[] = $usuarioResult->getLink3();
        }
        
        /*
         *  Armado de Datos para pasar a Twig
         */
        $data = $usuarioResult;
        $data->totalRecomendaciones = $totalRecomendaciones;
        $data->totalPrimerasRecomendaciones = $totalPrimerasRecomendaciones;
        $data->totalLugaresAgregados = $totalLugaresAgregados['total'];
        $data->totalImagenesLugar = $totalImagenesLugar['total'];
        $data->edadResult = $edad;
        $data->sexoResult = $sexoResult;
        $data->desdeResult = $usuarioResult->getFechaRegistro()->format('d-m-Y');
        $data->links = $links;
        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array('usuario' => $data));  
    }

    public function registroAction(Request $request) {

        $usuario = new Usuario();        

        $form = $this->createFormBuilder($usuario)
                     ->add('usuario', 'text')
                     ->add('mail', 'text')
                     ->add('password', 'password')
                     ->add('nombre', 'text')
                     ->add('apellido', 'text')
                     ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

                // Form válido, generamos slug y fecha creación
                $nombreUsuario = $usuario->getUsuario();
                $nombreUsuario = strtolower($ur->getUsuarioSinCaracteresRaros($nombreUsuario));
                $nombreUsuario = str_replace(" ","-",$nombreUsuario);
                $usuario->setSlug($nombreUsuario);
                $usuario->setImagenFull("default.png");
                $usuario->setFechaRegistro(new \DateTime());

                // Password codificado en SHA2 (por ahora MD5 por compatibilidad)
                $usuario->setPassword(md5($usuario->getPassword()));                

                // Usuario queda como no confirmado y se genera hash confirmación
                $usuario->setConfirmado(0);
                $usuario->setNewsletterActivo(1);
                $hashConfirmacion = md5($usuario->getMail().$usuario->getId().time());
                $usuario->setHashConfirmacion($hashConfirmacion);
                $usuario->setSalt('');

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
            return $this->redirect($this->generateUrl('showUsuario', array('slug' => 'sebastian-vicencio')));
        }

        // Si el usuario ya estaba confirmado
        if($usuarioResult->getConfirmado() == 1) {
            $this->get('session')->setFlash('confirmacion-registro', 'Confirmación realizada con anterioridad');
            return $this->redirect($this->generateUrl('showUsuario', array('slug' => $usuarioResult->getSlug())));
        }    
        
        // Hash correcto y usuario no confirmado
        $usuarioResult->setConfirmado(1);
        $em->flush();

        // Se agrega usuario a lista de correos de Mailchimp
        $mc = new \MCAPI_MCAPI($this->container->getParameter('mailchimp_apikey'));
        $merge_vars = array(
            'EMAIL' => utf8_encode($usuarioResult->getMail()),
            'FNAME' => utf8_encode($usuarioResult->getNombre()),
            'LNAME' => utf8_encode($usuarioResult->getApellido()),
            'USER' => utf8_encode($usuarioResult->getUsuario()),
            'IDUSER' => '4000'
        );
        $r = $mc->listSubscribe($this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail(), $merge_vars, 'html', false, true, true);


        // Usuario inicia sesión automáticamente



        $this->get('session')->setFlash('confirmacion-registro', '¡Bienvenido! Has confirmado tu cuenta exitosamente. ¿Algún lugar para recomendar?');
        return $this->redirect($this->generateUrl('showUsuario', array('slug' => $usuarioResult->getSlug())));
    }

    public function loginAction()
    {
        if($this->get('security.context')->isGranted('ROLE_USER'))
            return $this->redirect($this->generateUrl('showUsuario', array('slug' => $this->get('security.context')->getToken()->getUser()->getSlug())));
            
        $request = $this->getRequest();
        $session = $request->getSession();
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('LoogaresUsuarioBundle:Usuarios:login.html.twig', array(
            'last_mail' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }
}
