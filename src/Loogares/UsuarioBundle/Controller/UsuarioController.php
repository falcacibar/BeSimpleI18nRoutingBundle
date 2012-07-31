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
use Mailchimp\MCAPI;
use Loogares\MailBundle\Entity\Notificacion;

class UsuarioController extends Controller
{    
    public function showAction($param) {
        return $this->forward('LoogaresUsuarioBundle:Usuario:actividad', array('param' => $param));          
    }

    public function actividadAction(Request $request, $param, $actividad_total = false) {
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING); 
        }

        $fn = $this->get('fn');
        $router = $this->get('router');
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");
        $trr = $em->getRepository("LoogaresExtraBundle:TiempoRelativo");
        $paginacion = false;

        if(is_numeric($param)){
            $usuarioResult = $ur->findOneById($param);
            return $this->redirect($this->generateUrl('actividadUsuario', array('param' => $usuarioResult->getSlug())));
        }else{
            $usuarioResult = $ur->findOneBySlug($param); 
        }

        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }

        if($this->get('security.context')->isGranted('ROLE_USER'))
            $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        else
            $loggeadoCorrecto = false;


        $filtro = (!$request->query->get('filtro')) ? 'todo' : $request->query->get('filtro');
        $pagina = (!$request->query->get('pagina')) ? 1 : $request->query->get('pagina');

        if($actividad_total == true){ //Si es actividad total, mostramos por pagina, si no, solo 5
            $ppag = 20;
        }else{
            $ppag = 5;
        }

        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);

        // Actividad reciente del usuario
        $actividad = $ar->getActividadReciente($ppag, null, $usuarioResult->getId(), ($filtro != 'todo') ? $filtro : null, $offset);

        $variable = null;
        foreach($actividad as $key => $a) {
            $r = $em->getRepository($a->getEntidad());
            $entidad = $r->find($a->getEntidadId());
            if($entidad){
                $a->relativeTime = $trr->tiempoRelativo($a->getFecha()->format('Y-m-d H:i:s'));
                $a->ent = $entidad;
            }else{
                unset($actividad[$key]);
            }
        }
        
        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = 'actividad';
        $data->actividad = $actividad;
        $data->pagina = $pagina;
        
        
        $data->offset = $offset;
        $data->filtro = $filtro;
        $data->loggeadoCorrecto = $loggeadoCorrecto;

        if($actividad_total == true){
            $totalActividad = $ar->getTotalActividad(null, $usuarioResult->getId(),($filtro != 'todo') ? $filtro : null);
            $data->totalPaginas = ($totalActividad > $ppag) ? ceil($totalActividad / $ppag) : 1;

            $params = array(
                'param' => $usuarioResult->getSlug(),
                'filtro' => $filtro,
            );

            $paginacion = $fn->paginacion($totalActividad, $ppag, 'actividadUsuario', $params, $router );
            $data->totalActividad = $totalActividad;
        }else{
            //MONKEY PATCHING, Sacamos los 3 visitados recientemente, por visitar, favoritos
            $q = $em->createQuery("SELECT u from Loogares\UsuarioBundle\Entity\AccionUsuario u where u.usuario = ?1 and u.accion = ?2 ORDER BY u.fecha desc");

            $q->setParameter(1, $usuarioResult->getId());
            $q->setParameter(2, 3);
            $q->setMaxResults(3);

            $data->recientemente = $q->getResult();
                        
            //Por visitar
            $q = $em->createQuery("SELECT u from Loogares\UsuarioBundle\Entity\AccionUsuario u where u.usuario = ?1 and u.accion = ?2 ORDER BY u.fecha desc");

            $q->setParameter(1, $usuarioResult->getId());
            $q->setParameter(2, 1);
            $q->setMaxResults(3);

            $data->porVisitar = $q->getResult();

            //Favoritos
            $q = $em->createQuery("SELECT u from Loogares\UsuarioBundle\Entity\AccionUsuario u where u.usuario = ?1 and u.accion = ?2 ORDER BY u.fecha desc");

            $q->setParameter(1, $usuarioResult->getId());
            $q->setParameter(2, 4);
            $q->setMaxResults(3);

            $data->favoritos = $q->getResult();
        }
        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array(
            'usuario' => $data,
            'paginacion' => $paginacion,
            'actividad_total' => $actividad_total
        ));  
    }

    public function recomendacionesAction($param, $orden=null, $pagina=null) {
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING); 
        }

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
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING); 
        }
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

    public function lugaresAction($param, $accionLugar = null) {
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING); 
        }
        $fn = $this->get('fn');
        $router = $this->get('router');
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        $acciones = array(
            'visitar' => 1,
            'volver' => 2,
            'visitados' => 3,
            'favoritos' => 4,
            'recomendar' => 5
        );

        $accion = $acciones[$accionLugar];

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

        $pagina = (!$this->getRequest()->query->get('pagina')) ? 1 : $this->getRequest()->query->get('pagina');
        $ppag = 30;
        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);

        $acciones = $ur->getAccionUsuario($usuarioResult, $accion, $offset);

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->tipo = 'lugares';
        $data->accion = $accion;
        $data->pagina = $pagina;
        $data->totalPaginas = ($data->totalAcciones[$accion - 1] > $ppag) ? ceil($data->totalAcciones[$accion - 1] / $ppag) : 1;
        $data->offset = $offset;
        $data->acciones = $acciones;
        $data->loggeadoCorrecto = $loggeadoCorrecto;

        $params = array(
            'param' => $data->getSlug(),
            'accionLugar' => $accionLugar
        );
            
        $paginacion = $fn->paginacion($data->totalAcciones[$accion - 1], $ppag, 'lugaresUsuario', $params, $router );

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->render('LoogaresUsuarioBundle:Usuarios:lugares_usuario.html.twig', array(
                'usuario' => $data,
                'paginacion' => $paginacion,
                'query' => $_GET
            ));

        }

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array(
            'usuario' => $data,
            'paginacion' => $paginacion,
            'query' => $_GET
        ));  
    }

    public function cuponesAction($param, $tipo) {
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING); 
        }

        $fn = $this->get('fn');
        $router = $this->get('router');
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $cr = $em->getRepository("LoogaresCampanaBundle:Campana");
        $dataCupones = array();
        
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

        $pagina = (!$this->getRequest()->query->get('pagina')) ? 1 : $this->getRequest()->query->get('pagina');
        $ppag = 5;
        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);

        $data = $ur->getDatosUsuario($usuarioResult);

        if($tipo == 'concursos'){
            $ganadores = $ur->getConcursosUsuario($usuarioResult, $ppag, $offset);
            foreach($ganadores as $ganador){
                $dataCupones[] = array(
                    'titulo' => $ganador->getParticipante()->getConcurso()->getTitulo(),
                    'codigo' => $ganador->getCodigo(),
                    'detalles' => $ganador->getParticipante()->getConcurso()->getPost()->getDetalles(),
                    'condiciones' => $ganador->getParticipante()->getConcurso()->getPost()->getCondiciones(),
                    'lugar' => $ganador->getParticipante()->getConcurso()->getPost()->getLugar(),
                    'id' => $ganador->getId(),
                    'fechaTermino' => $ganador->getParticipante()->getConcurso()->getFechaTermino(),
                    'canjeado' => $ganador->getCanjeado()
                );
            }
        }else{
            $descontados = $ur->getDescuentosUsuario($usuarioResult, $ppag, $offset);
            foreach($descontados as $descontado){
                $campana = $cr->findOneByDescuento($descontado->getDescuento()->getId());
                $dataCupones[] = array(
                    'codigo' => $descontado->getCodigo(),
                    'condiciones' => $descontado->getDescuento()->getCondiciones(),
                    'lugar' => $campana->getLugar(),
                    'id' => $descontado->getId(),
                    'cantidad' => $descontado->getDescuento()->getCantidad(),
                    'canjeado' => $descontado->getCanjeado(),
                    'fechaTermino' => $descontado->getDescuento()->getFechaTermino()
                );
            }
        }

        $totalCupones = $ur->getTotalCuponesVigentesUsuario($usuarioResult);
        
        $data->tipo = 'cupones';
        $data->tipoPremio = $tipo;
        $data->cupones = $dataCupones;
        $data->descuentosActivos = sizeOf($ur->getDescuentosVigentesUsuario($usuarioResult, $ppag, 0));
        $data->concursosActivos = sizeOf($ur->getConcursosVigentesUsuario($usuarioResult, $ppag, 0));
        $data->totalCupones = $totalCupones;
        $data->loggeadoCorrecto = $loggeadoCorrecto;

        $params = array(
            'param' => $data->getSlug(),
            'tipo' => $tipo
        );
            
        $paginacion = $fn->paginacion($totalCupones, $ppag, 'cuponesUsuario', $params, $router );

        return $this->render('LoogaresUsuarioBundle:Usuarios:show.html.twig', array(
            'usuario' => $data,
            'paginacion' => $paginacion
        ));
    }

    public function imprimirCuponAction($param, $tipo, $cupon) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $gr = $em->getRepository("LoogaresBlogBundle:Ganador");
        $cr = $em->getRepository("LoogaresCampanaBundle:Campana");
        $dur = $em->getRepository("LoogaresCampanaBundle:DescuentosUsuarios");

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

        $cuponDetalle = array();
        if($tipo == 'concursos') {
            $ganador = $gr->find($cupon);
            $cuponDetalle = array(
                'titulo' => $ganador->getParticipante()->getConcurso()->getTitulo(),
                'codigo' => $ganador->getCodigo(),
                'detalles' => $ganador->getParticipante()->getConcurso()->getPost()->getDetalles(),
                'condiciones' => $ganador->getParticipante()->getConcurso()->getPost()->getCondiciones(),
                'lugar' => $ganador->getParticipante()->getConcurso()->getPost()->getLugar(),
                'id' => $ganador->getId(),
                'fechaTermino' => $ganador->getParticipante()->getConcurso()->getFechaTermino()
            );
        }
        else {
            $descuento = $dur->find($cupon);
            $campana = $cr->findOneByDescuento($descuento->getDescuento()->getId());
            $cuponDetalle = array(
                    'codigo' => $descuento->getCodigo(),
                    'condiciones' => $descuento->getDescuento()->getCondiciones(),
                    'lugar' => $campana->getLugar(),
                    'id' => $descuento->getId(),
                    'cantidad' => $descuento->getDescuento()->getCantidad(),
                    'fechaTermino' => $descuento->getDescuento()->getFechaTermino()
                );
        }

        $template = $this->render('LoogaresUsuarioBundle:Usuarios:imprimir_cupon_usuario.html.twig', array(
            'cupon' => $cuponDetalle,
            'usuario' => $usuarioResult,
            'tipoPremio' => $tipo
        ));

        $html = $template->getContent();

        require(__DIR__.'/../../../../vendor/dompdf/dompdf_config.inc.php');
        $dompdf = new \DOMPDF();
        $dompdf->load_html($html);
        //$dompdf->set_base_path("http://localhost");
        $dompdf->render();
        $dompdf->stream("cupon-".$usuarioResult->getSlug()."-".$cuponDetalle['codigo'].".pdf", array('Attachment' => 0));
    }

    public function editarAction($param) {
        return $this->forward('LoogaresUsuarioBundle:Usuario:editarCuenta', array('param' => $param));
    }

    public function editarCuentaAction(Request $request, $param) {
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $tur = $em->getRepository("LoogaresUsuarioBundle:TipoUsuario");
        $erroresLocalidad = array();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u WHERE u.mostrar_lugar = 2 or u.mostrar_lugar = 3 order by u.nombre asc");
        $ciudades = $q->getResult();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Pais u WHERE u.mostrar_lugar = 2 or u.mostrar_lugar = 3 order by u.nombre asc");
        $paises = $q->getResult();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u order by u.nombre asc");
        $comunas = $q->getResult();

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
                     ->getForm();                     

        // Guardamos mail de usuario actual
        $mail = $usuarioResult->getMail();
        $nombre = $usuarioResult->getNombre();
        $apellido = $usuarioResult->getApellido();
        
        // Si el request es POST, se procesa edición de datos
        if ($request->getMethod() == 'POST'){

            //$usuario = $ur->findOneByMail($usuarioResult->getMail());
            $form->bindRequest($request);            

            if(isset($_POST['pais']) && $_POST['pais'] != 'elige'){
                //hay pais
                if($_POST['pais'] == 'chile'){
                    if(isset($_POST['ciudad']) && $_POST['ciudad'] != 'elige'){
                        //hay ciudad
                        if($_POST['ciudad'] == 'santiago-de-chile'){
                            if(isset($_POST['comuna']) && $_POST['comuna'] == 'elige'){
                                $erroresLocalidad['comuna'] = "Selecciona una Comuna";
                            }
                        }
                        if($_POST['ciudad'] == 'valparaiso'){
                            if($_POST['comuna'] == 'elige'){
                                $erroresLocalidad['comuna'] = "Selecciona una Comuna";
                            }                            
                        }
                        if($_POST['ciudad'] == 'vina-del-mar'){
                            if($_POST['comuna'] == 'elige'){
                                $erroresLocalidad['comuna'] = "Selecciona una Comuna";
                            }
                        }
                    }else{
                        $erroresLocalidad['ciudad'] = "Selecciona una Ciudad";
                    }
                }

                if($_POST['pais'] == 'argentina'){
                    if(isset($_POST['ciudad']) && $_POST['ciudad'] == 'buenos-aires'){
                        if($_POST['comuna'] == 'elige'){
                            $erroresLocalidad['comuna'] = "Selecciona una Comuna";
                        }
                    }
                }

                if($_POST['pais'] == 'elige'){
                    $erroresLocalidad['ciudad'] = "Selecciona una Ciudad";
                }
            }else{
                $erroresLocalidad['pais'] = "Selecciona un País";  
            }

            if ($form->isValid() && empty($erroresLocalidad)) {
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

                if(isset($_POST['pais']) && $request->request->get('pais') != 'elige'){
                    $pr = $em->getRepository("LoogaresExtraBundle:Pais");
                    $pais = $pr->findOneBySlug($request->request->get('pais'));
                    $usuarioResult->setPais($pais);
                }

                if(isset($_POST['comuna']) && $request->request->get('comuna') != 'elige'){
                    $ccr = $em->getRepository("LoogaresExtraBundle:Comuna");
                    $comuna = $ccr->findOneBySlug($request->request->get('comuna'));
                    $usuarioResult->setComuna($comuna);
                }

                if(isset($_POST['ciudad']) && $request->request->get('ciudad') != 'elige'){
                    $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
                    $ciudad = $cr->findOneBySlug($request->request->get('ciudad'));
                    $usuarioResult->setCiudad($ciudad);
                }

                $em->flush();
                
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
            if(substr($formError->getPropertyPath(), 5) == 'nombre') {
                $usuarioResult->setNombre($nombre);
            }
            if(substr($formError->getPropertyPath(), 5) == 'apellido') {
                $usuarioResult->setApellido($apellido);
            }
        }

        $comunaSeleccionada ='';
        $ciudadSeleccionada='';
        $paisSeleccionado = '';

        if( !empty($erroresLocalidad) ){
            $comunaSeleccionada = (isset($_POST['comuna']) && $_POST['comuna'] != 'elige')?$_POST['comuna']:'';
            $ciudadSeleccionada = (isset($_POST['ciudad']) && $_POST['ciudad'] != 'elige')?$_POST['ciudad']:'';
            $paisSeleccionado = (isset($_POST['pais']) && $_POST['pais'] != 'elige')?$_POST['pais']:'';
        }

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'cuenta';
        $data->loggeadoCorrecto = $loggeadoCorrecto;       
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'form' => $form->createView(),
            'errors' => $formErrors,
            'tipoUsuarios' => $tur->findAll(),
            'comunas' => $comunas,
            'paises' => $paises,
            'ciudades' => $ciudades,
            'erroresLocalidad' => $erroresLocalidad,
            'paisSeleccionado' => $paisSeleccionado,
            'comunaSeleccionada' => $comunaSeleccionada,
            'ciudadSeleccionada' => $ciudadSeleccionada
        )); 
    }

    public function editarFotoAction(Request $request, $param) {
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }

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
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }

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

            if($usuarioResult->getSha1password() == 0){
                // Verificación de password actual
                if(md5($request->request->get('passwordActual')) != $usuarioResult->getPassword()) {
                    $formErrors['actual'] = "usuario.errors.editar.password.actual";        
                }
            }else{
                  if(sha1($request->request->get('passwordActual')) != $usuarioResult->getPassword()) {
                    $formErrors['actual'] = "usuario.errors.editar.password.actual";        
                }              
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
                    $usuarioResult->setPassword(sha1($usuarioResult->getPassword()));
                    $usuarioResult->setSha1password(1);
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

    public function editarConexionesAction(Request $request, $param) {

        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        
        $usuarioResult = $ur->findOneByIdOrSlug($param);
        if(!$usuarioResult) {
            throw $this->createNotFoundException('No existe usuario con el id/username: '.$param);
        }

        $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        if(!$loggeadoCorrecto)
            throw new AccessDeniedException('No puedes editar información de otro usuario');

        if($request->query->get('disconnect')) {
            // Desconectamos a usuario
            $usuario = $this->get('security.context')->getToken()->getUser();
            $usuario->setFacebookUid(0);
            $em->flush();

            // Mensaje de éxito en la edición
            $this->get('session')->setFlash('usuario_flash','usuario.flash.edicion.cuenta');
        }

        $fbdata = null;
        if($usuarioResult->getFacebookUid() != null) {
           $fbdata = $this->get('my.facebook.user')->getFacebook()->api(array(
                'method' => 'fql.query',
                'query' => "SELECT name,email FROM user WHERE uid = ".$usuarioResult->getFacebookUid(),
                'callback' => ''
            )); 
        }   

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'conexiones';
        $data->loggeadoCorrecto = $loggeadoCorrecto;
        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'fbdata' => $fbdata
        ));  
    }

    public function editarNotificacionesAction(Request $request, $param){
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository('LoogaresUsuarioBundle:Usuario');
        $tnr = $em->getRepository('LoogaresMailBundle:TipoNotificacion');
        $nr = $em->getRepository('LoogaresMailBundle:Notificacion');
        $notificaciones = array();
        $newsletters = array();

        $usuarioResult = $ur->findOneByIdOrSlug($param);
        $idUsuario = $usuarioResult->getId();

        if($this->get('security.context')->isGranted('ROLE_USER'))
            $loggeadoCorrecto = $this->get('security.context')->getToken()->getUser()->getId() == $usuarioResult->getId();
        else
            $loggeadoCorrecto = false;

        if(!$loggeadoCorrecto)
            return $this->redirect($this->generateUrl('actividadUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));

        

        //Sacamos los tipos de notificaciones de la db
        $tipo_notificaciones = $em->createQuery("SELECT u FROM Loogares\MailBundle\Entity\TipoNotificacion u")->getResult();

        //Iteramos por cada tipo de notificacion
        foreach($tipo_notificaciones as $key => $value){
            $nombre = $value->getNombre();

            //Sacamos la notificacion que corresponde al usuario y al tipo de notificacion
            $notificacion = $em->createQuery("SELECT u FROM Loogares\MailBundle\Entity\Notificacion u
                                              WHERE u.usuario = ?1 and u.tipo_notificacion = ?2");

            $notificacion = $notificacion->setParameter(1, $idUsuario)
                                         ->setParameter(2, $value->getId())->getOneOrNullResult();

            //Asignamos los resultados de las consultas a un array, cada array contiene:
            //['tipoNotificacion'] = Objeto del tipo de notificacion por el cual iteramos
            //['notificacion'] = Objeto de la notificacion relacionada con el usuario y el tipo, si es que existe
            $notificaciones[]['tipoNotificacion'] = $value;
            $notificaciones[sizeOf($notificaciones)-1]['notificacion'] = $notificacion;

            //Si es un request POST (se hizo submit de la form)
            if ($request->getMethod() == 'POST') {
                //Si el nombre del tipo de notificacion esta dentro del array de $_POST['notificacion'], quiere habilitarlo
                if( isset($_POST['notificacion']) && in_array($nombre, $_POST['notificacion']) ){
                    //Si el objeto de notificacion que seleccionamos arriba, tiene algo, seteamos el estado a 1
                    if($notificacion != null){
                        $notificacion->setActiva(true);
                    //Si no esta, es uno nuevo, asi que creamos el objeto con los datos y lo persistimos
                    }else{
                        $notificacion = new Notificacion();
                        $notificacion->setActiva(true);
                        $notificacion->setTipoNotificacion($value);
                        $notificacion->setUsuario($usuarioResult);
                        $em->persist($notificacion);
                    }             

                //Si el nombre del tipo de notificacion, no esta dentro del array de $_POST['notificacion'], quiere sacarlo.
                }else{
                    if($notificacion != null){
                        //Si $notificacion devolvio algo, deshabilitamos ese estado
                        $notificacion->setActiva(false);
                    }else{
                        //Como queremos que todos tengan una entrada por tipo, a medida que se agregen, creamos una entrada nueva
                        //que viene deshabilitada
                        $notificacion = new Notificacion();
                        $notificacion->setActiva(false);
                        $notificacion->setTipoNotificacion($value);
                        $notificacion->setUsuario($usuarioResult);
                        $em->persist($notificacion);                    
                    }
                }
                $notificaciones[sizeOf($notificaciones)-1]['notificacion'] = $notificacion;

                // Si el tipo de notificación es newsletter, agregamos a array correspondiente
                if($value->getNewsletter()) {
                    $newsletters[] = $notificacion;
                }

                // Mensaje de éxito en la edición
                $this->get('session')->setFlash('usuario_flash','usuario.flash.edicion.cuenta'); 
            }
            //Pasamos todo a la db
            $em->flush();
        }

        // Si hay notificaciones de newsletter, actualizamos Mailchimp
        if(sizeOf($newsletters) > 0) {
            // Configuración básica de Mailchimp
            $mc = new MCAPI($this->container->getParameter('mailchimp_apikey'));
            $mcInfo = $mc->listMemberInfo( $this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail() );
            $mcId = 0;

            if (!$mc->errorCode){
                if(!empty($mcInfo['success'])){
                    if(isset($mcInfo['data'])){ // tiene que estar en la lista para considerarse "suscrito"??
                        $mcId = $mcInfo['data'][0]['id'];
                    }
                }
            }

            // Primero revisamos si hay notificaciones activas
            $activas = array();
            foreach($newsletters as $nl) {
                if($nl->getActiva())
                    $activas[] = $nl->getTipoNotificacion()->getNombre();
            }

            if(sizeOf($activas) == 0) {
                if($mcId > 0)
                    $mc->listUnsubscribe( $this->container->getParameter('mailchimp_list_id'), $mcId, true, false );
            }
            else {
                $merge_vars = array(
                    'EMAIL' => $usuarioResult->getMail(),
                    'FNAME' => $usuarioResult->getNombre(),
                    'LNAME' => $usuarioResult->getApellido(),
                    'USER' => $usuarioResult->getSlug(),
                    'IDUSER' => $usuarioResult->getId()
                );

                // Vemos los newsletter que el usuario quiere activar
                $groups = implode(',', $activas);
                $merge_vars['GROUPINGS'] = array(
                    array(
                        'id' => 41,
                        'groups' => $groups
                    )
                );

                // Verificar suscripción Mailchimp
                if($mcId == 0) {
                    // Nueva suscripción
                    $mc->listSubscribe($this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail(), $merge_vars, 'html', false, true, true );
                }
                else {
                    // Usuario suscrito. Se actualizan datos
                    $mc->listUpdateMember($this->container->getParameter('mailchimp_list_id'), $mcId, $merge_vars, 'html', true);
                }
            }
        }

        $data = $ur->getDatosUsuario($usuarioResult);
        $data->edicion = 'notificaciones';
        $data->loggeadoCorrecto = $loggeadoCorrecto;        

        return $this->render('LoogaresUsuarioBundle:Usuarios:editar.html.twig', array(
            'usuario' => $data,
            'tipoNotificaciones' => $tipo_notificaciones,            
            'notificaciones' => $notificaciones
        ));
    }

    public function editarBorrarAction(Request $request, $param) {
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }

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


            else if($usuarioResult->getSha1password() == 0){
                // Verificación de password actual
                if(md5($request->request->get('password')) != $usuarioResult->getPassword()) {
                    $formErrors['password'] = "usuario.errors.editar.borrar.pass_incorrecto";
                }
            }
            else if($usuarioResult->getSha1password() == 1){
                if(sha1($request->request->get('password')) != $usuarioResult->getPassword()) {
                    $formErrors['password'] = "usuario.errors.editar.borrar.pass_incorrecto";    
                }              
            }

            if($request->request->get('motivo') == '')
                $formErrors['motivo'] = "usuario.errors.editar.borrar.motivo";


            if(sizeof($formErrors) == 0) {                        
                // Input correcto. La cuenta se deja como inactiva
                $estadoUsuario = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Inactivo');
                $usuarioResult->setEstado($estadoUsuario);
                $em->flush();

                // Borrar suscripción Mailchimp
                $mc = new MCAPI($this->container->getParameter('mailchimp_apikey'));
                $mcInfo = $mc->listMemberInfo( $this->container->getParameter('mailchimp_list_id'), $usuarioResult->getMail() );
                $mcId = 0;

                if (!$mc->errorCode){
                    if(!empty($mcInfo['success'])){
                        if(isset($mcInfo['data'])){ // tiene que estar en la lista para considerarse "suscrito"??
                            $mcId = $mcInfo['data'][0]['id'];
                        }
                    }
                }
                if($mcId > 0)
                    $mc->listUnsubscribe( $this->container->getParameter('mailchimp_list_id'), $mcId, true, false );      

                // Enviamos correo a administrador con la razón del cierre de la cuenta
                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('usuario.edicion.borrar.mail.asunto');
                $mail['usuario'] = $usuarioResult;
                $mail['fechaRegistro'] = $usuarioResult->getFechaRegistro()->format('d-m-Y');
                $mail['motivo'] = $request->request->get('motivo');

                $paths = array();
                $paths['logo'] = 'assets/images/mails/logo_mails.png';

                $message = $this->get('fn')->enviarMail($mail['asunto'], 'cuenta.usuarios@loogares.com', 'noreply@loogares.com', $mail, $paths, 'LoogaresUsuarioBundle:Mails:mail_borrar_cuenta.html.twig', $this->get('templating'));
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
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }

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
                    $usuariosConElMismoSlug = $ur->findBySlug($usuario->getNombre().'-'.$usuario->getApellido());
                    if(sizeOf($usuariosConElMismoSlug) > 0){
                        $usuariosConElMismoSlug = "-".sizeOf($usuariosConElMismoSlug);
                    }else{
                        $usuariosConElMismoSlug = false;
                    }
                    
                    $slug = $fn->generarSlug($usuario->getNombre().'-'.$usuario->getApellido().$usuariosConElMismoSlug);

                    $repetidos = $ur->getUsuarioSlugRepetido($slug);
                    if($repetidos > 0)
                        $slug = $slug.'-'.++$repetidos;                    
                    $usuario->setSlug($slug);
                    $usuario->setImagenFull("default.gif");
                    $usuario->setFechaRegistro(new \DateTime());

                    // Password codificado en SHA2 (por ahora MD5 por compatibilidad)
                    $usuario->setSha1password(1);
                    $usuario->setPassword(sha1($usuario->getPassword()));               

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

        // Usuario inicia sesión automáticamente
        $token = new UsernamePasswordToken($usuarioResult, $usuarioResult->getPassword(),'main', $usuarioResult->getRoles());
        $this->container->get('security.context')->setToken($token);


        $this->get('session')->setFlash('usuario_flash', 'usuario.flash.confirmar_usuario.exito');
        return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($usuarioResult))));
    }

    public function olvidarPasswordAction(Request $request) {
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }

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
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }

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
                    $usuario->setPassword(sha1($request->request->get('nuevo')));
                    $usuario->setSha1password(1);
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
        $error=null;
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $formErrors = array();
        // Usuario loggeado es redirigido a su perfil
        if($this->get('security.context')->isGranted('ROLE_USER')){
            
            return $this->redirect($this->generateUrl('showUsuario', array('param' => $ur->getIdOrSlug($this->get('security.context')->getToken()->getUser()))));
        }
        $request = $this->getRequest();
        $session = $request->getSession();
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        if($error != null && $error->getMessage() == 'Bad credentials')
            $formErrors['completo'] = 'usuario.errors.login.completo';
        else if($error != null && $error->getMessage() == 'The presented password is invalid.')
            $formErrors['password'] = 'usuario.errors.login.password';
        else if($error != null && $error->getMessage() == 'The presented password cannot be empty.')
            $formErrors['emptyPassword'] = 'usuario.errors.login.emptyPassword';
        else if($error != null && $error->getMessage() == 'User account is disabled.')
            $formErrors['noActivo'] = 'usuario.errors.login.noActivo';
        
        return $this->render('LoogaresUsuarioBundle:Usuarios:login.html.twig', array(
            'last_mail' => $session->get(SecurityContext::LAST_USERNAME),
            'errors' => $formErrors,
            'locale' => $this->get('session')->getLocale()
        ));
    }

    public function loginCheckAction() {
        //echo $this->getRequest()->request->get('_password');
    }

    public function loginCheckFacebookAction() {
        
    }

    public function totalAccionesPendientesAction($accion) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        $cantidad = $ur->getTotalAccionesUsuario($accion, $this->get('security.context')->getToken()->getUser());

        return new Response($cantidad);
    }

    public function forzarDatosAction(Request $request) {
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING); 
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $tnr = $em->getRepository('LoogaresMailBundle:TipoNotificacion');

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u WHERE u.mostrar_lugar = 2 or u.mostrar_lugar = 3 order by u.nombre asc");
        $ciudades = $q->getResult();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Pais u WHERE u.mostrar_lugar = 2 or u.mostrar_lugar = 3 order by u.nombre asc");
        $paises = $q->getResult();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Comuna u order by u.nombre asc");
        $comunas = $q->getResult();

        if($this->get('security.context')->isGranted('ROLE_USER')) {
            $usuario = $this->get('security.context')->getToken()->getUser();
            $formErrors = array();

            // Revisamos los errores inmediatamente para indicarle al usuario qué debe ingresar
            if($usuario->getNombre() == '')
                $formErrors['nombre'] = "usuario.errors.validacion.nombre.blanco";
            if($usuario->getApellido() == '')
                $formErrors['apellido'] = "usuario.errors.validacion.apellido.blanco";
            /*if($usuario->getComuna() == null)
                $formErrors['comuna'] = "usuario.errors.validacion.comuna.blanco";*/

            if($request->getMethod() == 'POST') {
                if($request->request->get('nombre')){
                    $usuario->setNombre($request->request->get('nombre'));
                    $usuario->setApellido($request->request->get('apellido'));
                }

                if(isset($_POST['pais']) && $request->request->get('pais') != 'elige'){
                    $pr = $em->getRepository("LoogaresExtraBundle:Pais");
                    $pais = $pr->findOneBySlug($request->request->get('pais'));
                    $usuario->setPais($pais);
                }

                if(isset($_POST['comuna']) && $request->request->get('comuna') != 'elige'){
                    $ccr = $em->getRepository("LoogaresExtraBundle:Comuna");
                    $comuna = $ccr->findOneBySlug($request->request->get('comuna'));
                    $usuario->setComuna($comuna);
                }

                if(isset($_POST['ciudad']) && $request->request->get('ciudad') != 'elige'){
                    $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
                    $ciudad = $cr->findOneBySlug($request->request->get('ciudad'));
                    $usuario->setCiudad($ciudad);

                    // Array que se agrega a $merge_vars, en el caso de estar suscrito a newsletter
                    if($ciudad->getId() == 1 || $ciudad->getId() == 6) {
                        $groupings = array(
                            array(
                                'id' => 41,
                                'groups' => $ciudad->getNombre()
                            )
                        );

                        if($ciudad->getId() == 1) {
                            $tipoNotificacion = $tnr->find(1);
                        }
                        else {
                            $tipoNotificacion = $tnr->find(3);
                        }
                    }
                    else if($ciudad->getSlug() == 'valparaiso' || $ciudad->getSlug() == 'vina-del-mar' || $ciudad->getSlug() == 'concon') {
                        $groupings = array(
                            array(
                                'id' => 41,
                                'groups' => 'Valparaíso - Viña del Mar'
                            )
                        );
                        $tipoNotificacion = $tnr->find(2);
                    }
                    else {
                        $groupings = array(
                            array(
                                'id' => 41,
                                'groups' => 'Otras Ciudades'
                            )
                        );
                        $tipoNotificacion = $tnr->find(4);
                    }
                }

                // Según ciudad especificada, agregamos a lista Mailchimp

                $mc = new MCAPI($this->container->getParameter('mailchimp_apikey'));
                $mcInfo = $mc->listMemberInfo( $this->container->getParameter('mailchimp_list_id'), $usuario->getMail() );
                $mcId = 0;

                if (!$mc->errorCode){
                    if(!empty($mcInfo['success'])){
                        if(isset($mcInfo['data'])){ // tiene que estar en la lista para considerarse "suscrito"??
                            $mcId = $mcInfo['data'][0]['id'];
                        }
                    }
                }
                // Se agrega usuario a lista de correos de Mailchimp
                $merge_vars = array(
                    'EMAIL' => $usuario->getMail(),
                    'FNAME' => $usuario->getNombre(),
                    'LNAME' => $usuario->getApellido(),
                    'USER' => $usuario->getSlug(),
                    'IDUSER' => $usuario->getId()
                );

                if(isset($groupings)) {
                    $merge_vars['GROUPINGS'] = $groupings;
                }
                else {
                    $merge_vars['GROUPINGS'] = array(
                        array(
                            'id' => 41,
                            'groups' => 'Otras Ciudades'
                        )
                    );
                    $tipoNotificacion = $tnr->find(4);
                }

                // Verificar suscripción Mailchimp
                if($mcId == 0) {
                    // Nueva suscripción
                    $mc->listSubscribe($this->container->getParameter('mailchimp_list_id'), $usuario->getMail(), $merge_vars, 'html', false, true, true );
                }
                else {
                    // Usuario suscrito. Se actualizan datos
                    $mc->listUpdateMember($this->container->getParameter('mailchimp_list_id'), $mcId, $merge_vars, 'html', true);
                }

                // Agregamos notificación
                if(isset($tipoNotificacion)) {
                    $notificacion = new Notificacion();
                    $notificacion->setActiva(true);
                    $notificacion->setTipoNotificacion($tipoNotificacion);
                    $notificacion->setUsuario($usuario);
                    $em->persist($notificacion);
                }

                $em->flush();
                return new Response('gud gud', 200);
            }

            return $this->render('LoogaresUsuarioBundle:Usuarios:datos_obligatorios.html.twig', array(
                'errors' => $formErrors,
                'ciudades' => $ciudades,
                'paises' => $paises,
                'comunas' => $comunas
            ));
        }
        else {
            return new Response('');
        }        
    }



}
