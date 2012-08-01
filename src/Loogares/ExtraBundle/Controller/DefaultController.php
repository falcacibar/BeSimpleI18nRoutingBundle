<?php

namespace Loogares\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;
use Loogares\LugarBundle\Entity\TipoCategoria;
use Loogares\BlogBundle\Entity\Concurso;
use Mailchimp\MCAPI;


class DefaultController extends Controller
{
    public function indexAction($name){
        return $this->render('LoogaresExtraBundle:Default:index.html.twig', array('name' => $name));
    }

    public function menuAction(){
    	$em = $this->getDoctrine()->getEntityManager();
        $tlr = $em->getRepository("LoogaresLugarBundle:TipoCategoria");
        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\TipoCategoria u ORDER BY u.prioridad_web asc");
        $tipoCategoria = $q->getResult();
        $ciudad = $this->get('session')->get('ciudad');
        $idCiudad = $ciudad['id'];
        $data = array();

        foreach($tipoCategoria as $key => $value){
            $id = $value->getId();
            $buff = $this->getDoctrine()
            ->getConnection()->fetchAll("SELECT count(categorias.id) as total, categorias.nombre as categoria_nombre, categorias.slug as categoria_slug, tipo_categoria.nombre, tipo_categoria.slug
                                         FROM lugares

                                         JOIN comuna
                                         ON comuna.id = lugares.comuna_id

                                         LEFT JOIN categoria_lugar
                                         ON categoria_lugar.lugar_id = lugares.id

                                         JOIN categorias
                                         ON categorias.id = categoria_lugar.categoria_id

                                         LEFT JOIN tipo_categoria
                                         ON tipo_categoria.id = categorias.tipo_categoria_id

                                         WHERE tipo_categoria.id = $id AND comuna.ciudad_id = $idCiudad AND lugares.estado_id != 3

                                         GROUP BY categorias.id
                                         ORDER BY tipo_categoria.id, categorias.nombre asc");
            $data[$value->getSlug()]['tipo'] = $tipoCategoria[$key];
            $data[$value->getSlug()]['categorias'] = $buff;
        }

    	return $this->render('::menu.html.twig', array('menu' => $data));
    }

    public function ciudadAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");

        $tipoCiudades = $cr->getCiudadesActivas();

        $data = $tipoCiudades;
        return $this->render('::ciudad.html.twig', array('ciudades' => $data));
    }

    public function localeAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $ciudad = $cr->findOneBySlug($slug);

        // Seteamos el locale correspondiente a la ciudad en la sesión
        $this->get('session')->setLocale($ciudad->getPais()->getLocale());

        $ciudadArray = array();
        $ciudadArray['id'] = $ciudad->getId();
        $ciudadArray['nombre'] = $ciudad->getNombre();
        $ciudadArray['slug'] = $ciudad->getSlug();

        $ciudadArray['pais']['id'] = $ciudad->getPais()->getId();
        $ciudadArray['pais']['nombre'] = $ciudad->getPais()->getNombre();
        $ciudadArray['pais']['slug'] = $ciudad->getPais()->getSlug();

        $root = "root_".preg_replace('/-/', '_', $slug);

        $this->get('session')->set('ciudad',$ciudadArray);

        // Redirección a vista de login
        return new Response('ok');
    }

    public function homepageAction($slug = null){
        $em = $this->getDoctrine()->getEntityManager();
        $fn = $this->get('fn');
        $ip = $fn->ip2int($_SERVER['REMOTE_ADDR']);

        //Comprobamos de donde es la IP
        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\ip2loc u WHERE u.range_to >= ?1");
        $q->setParameter(1, $ip);
        $q->setMaxResults(1);
        $ipPais = $q->getOneOrNullResult();

        $ciudadesHabilitadas = array(
            'santiago-de-chile' => 'santiago-de-chile',
            'buenos-aires' => 'buenos-aires',
            'valparaiso-vina-del-mar' => 'valparaiso-vina-del-mar'
        );

        //Ciudad antigua
        $ciudadSession = $this->get('session')->get('ciudad');

        if(!in_array($slug, $ciudadesHabilitadas)){
            if(preg_match('/Argentina|Peru/', $ipPais->getCountry())){
                return $this->redirect($this->generateUrl('locale', array('slug' => 'buenos-aires')));
            }
            return $this->redirect($this->generateUrl('locale', array('slug' => 'santiago-de-chile')));
        }

        $this->localeAction($slug);

        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $cr = $em->getRepository("LoogaresLugarBundle:Categoria");
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");
        $trr = $em->getRepository("LoogaresExtraBundle:TiempoRelativo");
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $conr = $em->getRepository("LoogaresBlogBundle:Concurso");

        //Ciudad Nueva
        $ciudadSession = $this->get('session')->get('ciudad');

        //Concursos vigentes
        $concursos = $conr->getConcursosVigentes($ciudadSession['id']);

        // Sort Random de concursos
        shuffle($concursos);

        //Slider del home
        //$sliderCampanas = $pr->getPostsDestacados($ciudadSession['id'], 3);

        //Recomendacion Estrella
        $q = $em->createQuery("SELECT u from Loogares\UsuarioBundle\Entity\LoogarenoEstrella u ORDER BY u.id desc");
        $q->setMaxResults(1);
        $estrellaResult = $q->getSingleResult();

        // Cantidad de recomendaciones escritas
        $totalRecomendaciones = $rr->getTotalRecomendaciones();

        $ciudad = $this->get('session')->get('ciudad');

        // Top Five de tres categorías
        $categorias = array();

        // Categoría Restaurantes
        $restaurantes = $cr->findOneBySlug('restaurantes');
        $restaurantes->top_five = $lr->getTopFivePorCategoria($restaurantes->getId(), $ciudad['id']);
        $categorias[] = $restaurantes;

        // Categoría Cafés
        $cafes = $cr->findOneBySlug('cafes-teterias');
        $cafes->top_five = $lr->getTopFivePorCategoria($cafes->getId(), $ciudad['id']);
        $categorias[] = $cafes;

        // Categoría Bares/Pubs
        $bares = $cr->findOneBySlug('bares-pubs');
        $bares->top_five = $lr->getTopFivePorCategoria($bares->getId(), $ciudad['id']);
        $categorias[] = $bares;

        // Recomendación del día
        $recomendacionDelDia = $rr->getRecomendacionDelDia($ciudad['id']);

        $previewRecDia = '';
        if(strlen($recomendacionDelDia->getTexto()) > 160) {
            $previewRecDia = substr($recomendacionDelDia->getTexto(),0,159).'...';
        }
        else {
            $previewRecDia = $recomendacionDelDia->getTexto();
        }

        $preview = '';
        // Actividad reciente por ciudad
        $actividad = $ar->getActividadReciente(5, $ciudad['id'], null, null, 0);
        for($i = 0; $i < sizeOf($actividad); $i++){
            $r = $em->getRepository($actividad[$i]->getEntidad());
            $entidad = $r->find($actividad[$i]->getEntidadId());

            if($actividad[$i]->getEntidad() == 'Loogares\UsuarioBundle\Entity\Recomendacion') {
                $preview = '';
                if(strlen($entidad->getTexto()) > 160) {
                    $preview = substr($entidad->getTexto(),0,160).'...';
                }
                else {
                    $preview = $entidad->getTexto();
                }
                $entidad->preview = $preview;
            }
            $actividad[$i]->relativeTime = $trr->tiempoRelativo($actividad[$i]->getFecha()->format('Y-m-d H:i:s'));
            $actividad[$i]->ent = $entidad;
        }

        // Últimos conectados
        $ultimosConectados = $ur->getUltimosConectados(0.02);

        $home = array();
        $home['totalRecomendaciones'] = $totalRecomendaciones;
        $home['totalRecomendaciones_format'] = number_format( $totalRecomendaciones , 0 , '' , '.' );
        $home['recDia'] = $recomendacionDelDia;
        $home['previewRecDia'] = $previewRecDia;
        $home['ultimosConectados'] = $ultimosConectados;
        $home['categorias'] = $categorias;
        $home['actividad'] = $actividad;
        $home['estrella'] = $estrellaResult->getRecomendacion();

        return $this->render('LoogaresExtraBundle:Default:home.html.twig', array(
            'home' => $home,
            'concursos' => $concursos
        ));
    }

    public function actividadAction(Request $request) {
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING);
        }

        $fn = $this->get('fn');
        $router = $this->get('router');
        $em = $this->getDoctrine()->getEntityManager();
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");
        $trr = $em->getRepository("LoogaresExtraBundle:TiempoRelativo");
        $ciudad = $this->get('session')->get('ciudad');

        $filtro = (!$request->query->get('filtro')) ? 'todo' : $request->query->get('filtro');
        $pagina = (!$request->query->get('pagina')) ? 1 : $request->query->get('pagina');
        $ppag = 20;
        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);

        // Actividad reciente por ciudad
        $actividad = $ar->getActividadReciente($ppag, $ciudad['id'], null, ($filtro != 'todo') ? $filtro : null, $offset);

        $totalActividad = $ar->getTotalActividad($ciudad['id'], null, ($filtro != 'todo') ? $filtro : null);

        foreach($actividad as $a) {
            $r = $em->getRepository($a->getEntidad());
            $entidad = $r->find($a->getEntidadId());
            if($a->getEntidad() == 'Loogares\UsuarioBundle\Entity\Recomendacion') {
                $preview = '';
                if(strlen($entidad->getTexto()) > 160) {
                    $preview = substr($entidad->getTexto(),0,160).'...';
                }
                else {
                    $preview = $entidad->getTexto();
                }
                $entidad->preview = $preview;
            }
            $a->relativeTime = $trr->tiempoRelativo($a->getFecha()->format('Y-m-d H:i:s'));
            $a->ent = $entidad;
        }

        $data = array();
        $data['lista'] = $actividad;
        $data['pagina'] = $pagina;
        $data['totalPaginas'] = ($totalActividad > $ppag) ? ceil($totalActividad / $ppag) : 1;
        $data['totalActividad'] = $totalActividad;
        $data['offset'] = $offset;
        $data['filtro'] = $filtro;

        $params = array(
            'filtro' => $filtro,
        );

        $paginacion = $fn->paginacion($totalActividad, $ppag, 'actividad', $params, $router );

        return $this->render('LoogaresExtraBundle:Default:actividad_extendida.html.twig', array(
            'actividad' => $data,
            'paginacion' => $paginacion,
        ));
    }

    public function staticAction($static){
        $path = null;
        $errors = null;

        if(isset($_SESSION['staticerrors'])){
            $errors = $_SESSION['staticerrors'];
            unset($_SESSION['staticerrors']);
        }

        $paginas = array(
            'codigos_de_conducta' => 'Static',
            'contacto' => 'Static',
            'copyright' => 'Static',
            'eres_el_dueno_de_un_lugar' => 'Static',
            'loogareno_estrella' => 'Static',
            'nuevo_sistema_concursos_y_be' => 'Static',
            'politicas_de_privacidad' => 'Static',
            'prensa' => 'Static',
            'publicidad' => 'Static',
            'que_es_loogares' => 'Static',
            'terminos_de_uso' => 'Static',
            'trabaja_con_nosotros' => 'Static',
            'concursos_local' => 'Static',
            'iphone_app' => 'Static'
        );

        foreach($paginas as $key => $value){
            if($key == $static){
                $path = $value;
                break;
            }
        }

        if($path == null){
            return $this->redirect($this->generateUrl('locale', array('slug' => 'santiago-de-chile')));
        }

        return $this->render('LoogaresExtraBundle:'.$path.':'.$static.'.html.twig', array(
            'static' => $static,
            'errors' => $errors
        ));
    }

    public function contactoMailAction(){
        $errors = array();
        $errorFlag = false;
        foreach($_POST as $key => $field){
            if($key == 'mail' && $field != '' && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $field)){
                $errors[$key] = array('mail' => $field);
            }else if($field == ''){
                $errors[$key] = array('error' => 'empty');
                $errorFlag = true;
            }else{
                $errors[$key] = array('ok' => $field);
            }
        }

        if($errorFlag == true){
            //No se puede pasar errores a un redirect sin alterar la URL asi que mandamos los errores en un obj session comun
            //Como lo hacian mis antepasados.
            $_SESSION['staticerrors'] = $errors;
            $this->get('session')->setFlash('error','Porfavor rellena todos los campos marcados con rojo y asegurate que el mail ingresado sea valido.');
            return $this->redirect($this->generateUrl('static', array(
                'static' => 'contacto'
            )));
        }

        $contacto['nombre'] = $_POST['nombre'];
        $contacto['asunto'] = $_POST['asunto'];
        $contacto['mail'] = $_POST['mail'];
        $contacto['mensaje'] = preg_split('/\n/', $_POST['mensaje']);
        // Se envía mail a administradores notificando reporte
        $mail = array();
        $mail['asunto'] = $_POST['asunto'];
        $mail['contacto'] = $contacto;
        $mail['tipo'] = "imagen";
        $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('static.mail.asunto.contacto').' - '.$mail['asunto'])
                ->setFrom($contacto['mail'])
                ->setTo('contacto@loogares.com');
        $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
        $message->setBody($this->renderView('LoogaresExtraBundle:Mails:mail_contacto.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
        $this->get('mailer')->send($message);

         // Mensaje de éxito del reporte
        $this->get('session')->setFlash('contacto_flash','¡Gracias por el contacto! En menos de 48 horas tendrás nuestra respuesta... y si no, que nos parta un rayo.');

        // Redirección a galería de fotos
        return $this->redirect($this->generateUrl('static', array('static' => 'contacto')));
    }

    public function publicidadMailAction(){
        $errors = array();
        $errorFlag = false;
        foreach($_POST as $key => $field){
            if($key == 'mail' && $field != '' && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $field)){
                $errors[$key] = array('mail' => $field);
            }else if($field == ''){
                $errors[$key] = array('error' => 'empty');
                $errorFlag = true;
            }else{
                $errors[$key] = array('ok' => $field);
            }
        }

        if($errorFlag == true){
            //No se puede pasar errores a un redirect sin alterar la URL asi que mandamos los errores en un obj session comun
            //Como lo hacian mis antepasados.
            $_SESSION['staticerrors'] = $errors;
            $this->get('session')->setFlash('error','Porfavor rellena todos los campos marcados con rojo y asegurate que el mail ingresado sea valido.');
            return $this->redirect($this->generateUrl('static', array(
                'static' => 'publicidad'
            )));
        }

        $contacto['nombre'] = $_POST['nombre'];
        $contacto['empresa'] = $_POST['empresa'];
        $contacto['mail'] = $_POST['mail'];
        $contacto['www'] = $_POST['www'];
        $contacto['tel'] = $_POST['tel'];
        $contacto['mensaje'] = preg_split('/\n/', $_POST['mensaje']);
        // Se envía mail a administradores notificando reporte
        $mail = array();
        $mail['asunto'] = 'Publicidad';
        $mail['contacto'] = $contacto;
        $mail['tipo'] = "imagen";
        $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('static.mail.asunto.publicidad').' - '.$contacto['empresa'])
                ->setFrom($contacto['mail'])
                ->setTo('contacto@loogares.com');
        $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
        $message->setBody($this->renderView('LoogaresExtraBundle:Mails:mail_publicidad.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
        $this->get('mailer')->send($message);

         // Mensaje de éxito del reporte
        $this->get('session')->setFlash('publicidad_flash','¡Gracias por el contacto! En menos de 48 horas tendrás nuestra respuesta... y si no, que nos parta un rayo.');

        // Redirección a galería de fotos
        return $this->redirect($this->generateUrl('static', array('static' => 'publicidad')));
    }

    public function beneficioExclusivoMailAction(Request $request) {
        // Almacenamos información en un array
        $datos = array();
        $datos['lugar'] = $request->request->get('lugar');
        $datos['nombre'] = $request->request->get('nombre');
        $datos['apellido'] = $request->request->get('apellido');
        $datos['mail'] = $request->request->get('mail');
        $datos['telefono'] = $request->request->get('telefono');
        $datos['comprobar'] = preg_split('/\n/', $request->request->get('comprobar'));

        // Enviar mail con información provista por el local
        $mail = array();
        $mail['datos'] = $datos;
        $mail['asunto'] = "Beneficio Exclusivo - Datos de Contacto";

        $paths = array();
        $paths['logo'] = 'assets/images/mails/logo_mails.png';

        $message = $this->get('fn')->enviarMail($mail['asunto'], 'ventas@loogares.com', $datos['mail'], $mail, $paths, 'LoogaresExtraBundle:Mails:mail_beneficios_exclusivos.html.twig', $this->get('templating'));
        $this->get('mailer')->send($message);

        $this->get('session')->setFlash('beneficios_flash','ventas.beneficio_exclusivo.flash.exito');

        // Redirección a página de beneficios
        return $this->redirect($this->generateUrl('static', array('static' => 'beneficio_exclusivo')));
    }

    public function sitemapAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $ciudades = $cr->getCiudadesActivas();

        foreach($ciudades as $c) {
            $c->categorias = $cr->getCategoriasPorCiudad($c->getId());
        }

        $lugares = $this->getDoctrine()
                        ->getConnection()->fetchAll("SELECT l.* FROM lugares l WHERE l.estado_id != 3");

        return $this->render('LoogaresExtraBundle:Default:sitemap.xml.twig', array(
            'ciudades' => $ciudades,
            'lugares' => $lugares
        ));
    }

    public function concursosAction(Request $request, $ciudad) {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $conr = $em->getRepository("LoogaresBlogBundle:Concurso");
        $fn = $this->get('fn');
        $router = $this->get('router');

        $ciudad = $cr->findOneBySlugActivo($ciudad);
        if($ciudad == null) {
            // Redireccionar a home de Santiago de Chile
            return $this->redirect($this->generateUrl('locale', array('slug' => 'santiago-de-chile')));
        }
        $ciudadArray = array();
        $ciudadArray['id'] = $ciudad->getId();
        $ciudadArray['nombre'] = $ciudad->getNombre();
        $ciudadArray['slug'] = $ciudad->getSlug();
        $ciudadArray['pais']['id'] = $ciudad->getPais()->getId();
        $ciudadArray['pais']['nombre'] = $ciudad->getPais()->getNombre();
        $ciudadArray['pais']['slug'] = $ciudad->getPais()->getSlug();

        $this->get('session')->setLocale($ciudad->getPais()->getLocale());
        $this->get('session')->set('ciudad',$ciudadArray);

        $pagina = (!$request->query->get('pagina')) ? 1 : $request->query->get('pagina');
        $ppag = 12;
        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);


        // Concursos vigentes
        $vigentes = $conr->getConcursosVigentes($ciudadArray['id']);

        // Sort Random concursos vigentes
        shuffle($vigentes);

        // Concursos cerrados
        $cerrados = $conr->getConcursosCerrados($ciudadArray['id'], $ppag, $offset);

        $totalCerrados = $conr->getTotalConcursosCerrados($ciudadArray['id']);

        $params = array(
            'ciudad' => $ciudadArray['slug'],
        );

        $paginacion = $fn->paginacion($totalCerrados, $ppag, 'concursos', $params, $router );

        return $this->render('LoogaresExtraBundle:Default:concursos.html.twig', array(
            'vigentes' => $vigentes,
            'cerrados' => $cerrados,
            'totalCerrados' => $totalCerrados,
            'paginacion' => $paginacion
        ));
    }

    public function mailConcursosAction($ciudad) {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $conr = $em->getRepository("LoogaresBlogBundle:Concurso");
        $fn = $this->get('fn');

        $ciudad = $cr->findOneBySlugActivo($ciudad);
        $ciudadArray = array();
        $ciudadArray['id'] = $ciudad->getId();
        $ciudadArray['nombre'] = $ciudad->getNombre();
        $ciudadArray['slug'] = $ciudad->getSlug();
        $ciudadArray['pais']['id'] = $ciudad->getPais()->getId();
        $ciudadArray['pais']['nombre'] = $ciudad->getPais()->getNombre();
        $ciudadArray['pais']['slug'] = $ciudad->getPais()->getSlug();

        $this->get('session')->setLocale($ciudad->getPais()->getLocale());
        $this->get('session')->set('ciudad',$ciudadArray);

        // Concursos vigentes
        $concursos = $conr->getConcursosVigentes($ciudadArray['id']);

        foreach($concursos as $concurso){
            if($concurso->getFechaInicio()->format('y-m-d') == date('y-m-d')){
                $fn->generarTemplateNuevoMail($concurso->getPost()->getImagen());
            }
        }

        $meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

        return $this->render('LoogaresExtraBundle:Mails:mail_concursos.html.twig', array(
            'concursos' => $concursos,
            'meses' => $meses
        ));
    }

    public function mailiPhoneAppAction() {
        return $this->render('LoogaresExtraBundle:Mails:mail_iphone_app.html.twig');
    }

    /*public function actualizarPostsAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $ecr = $em->getRepository("LoogaresBlogBundle:EstadoConcurso");
        $tcr = $em->getRepository("LoogaresBlogBundle:TipoConcurso");

        // Actualizamos notas con destacado_home = 2
        $q = $em->createQuery("SELECT p FROM Loogares\BlogBundle\Entity\Posts p
                               WHERE p.destacado_home = ?1");
        $q->setParameter(1, 2);
        $posts = $q->getResult();

        foreach($posts as $post) {
            $post->setDestacadoHome(1);
        }
        $em->flush();

        $q = $em->createQuery("SELECT p FROM Loogares\BlogBundle\Entity\Posts p
                               WHERE p.ganadores IS NOT NULL AND p.ganadores != '' ");
        $posts = $q->getResult();

        foreach($posts as $post) {
            // Creamos concursos para cada post antiguo
            $fechaInicio = $post->getFechaPublicacion();
            if($fechaInicio ==  null) {
                $fechaInicio = new \DateTime('10/10/2010');
            }
            $fechaTermino = $post->getFechaTermino();
            if($fechaTermino == null) {
                $fechaTermino = new \DateTime('10/10/2010');
            }
            $estadoConcurso = $ecr->find(3);
            $tipoConcurso = $tcr->find(1);
            $concurso = new Concurso();
            $concurso->setPost($post);
            $concurso->setEstadoConcurso($estadoConcurso);
            $concurso->setTipoConcurso($tipoConcurso);
            $concurso->setTitulo($post->getTitulo());
            $concurso->setDescripcion($post->getTitulo());
            $concurso->setFechaInicio($fechaInicio);
            $concurso->setFechaTermino($fechaTermino);
            $concurso->setNumeroPremios($post->getNumeroPremios());
            $em->persist($concurso);
        }
        $em->flush();

        return new Response(sizeOf($posts));
    }*/

    // Esto está acá como backup, por si alguna vez se necesita de nuevo (no es basura)
    /*public function mailchimpAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        echo "<pre>";
        $mc = new MCAPI($this->container->getParameter('mailchimp_apikey'));
        $usuarios = $ur->getUsuariosActivos();
        $i = 0;

        foreach($usuarios as $usuario) {
            if($usuario->getNewsletterActivo()) {
                $i++;
                // Se agrega usuario a lista de correos de Mailchimp

                $mcInfo = $mc->listMemberInfo( $this->container->getParameter('mailchimp_list_id'), $usuario->getMail() );
                $mcId = 0;

                if (!$mc->errorCode){
                    if(!empty($mcInfo['success'])){
                        if(isset($mcInfo['data'])){ // tiene que estar en la lista para considerarse "suscrito"??
                            $mcId = $mcInfo['data'][0]['id'];
                        }
                    }
                }

                $merge_vars = array(
                    'EMAIL' => $usuario->getMail(),
                    'FNAME' => $usuario->getNombre(),
                    'LNAME' => $usuario->getApellido(),
                    'USER' => $usuario->getSlug(),
                    'IDUSER' => $usuario->getId()
                );
                if($usuario->getCiudad() != null) {
                    if($usuario->getCiudad()->getId() == 1 || $usuario->getCiudad()->getId() == 6) {
                        $merge_vars['GROUPINGS'] = array(
                            array(
                                'id' => 41,
                                'groups' => $usuario->getCiudad()->getNombre()
                            )
                        );
                        if($usuario->getCiudad()->getId() == 1) {
                            echo "INSERT INTO notificaciones(tipo_notificacion_id,usuario_id,activa) VALUES (1,".$usuario->getId().",1)<br>";
                        }
                        else {
                            echo "INSERT INTO notificaciones(tipo_notificacion_id,usuario_id,activa) VALUES (3,".$usuario->getId().",1)<br>";
                        }
                    }
                    else if($usuario->getCiudad()->getSlug() == 'valparaiso' || $usuario->getCiudad()->getSlug() == 'vina-del-mar' || $usuario->getCiudad()->getSlug() == 'concon') {
                        $groupings = array(
                            array(
                                'id' => 41,
                                'groups' => 'Valparaíso - Viña del Mar'
                            )
                        );
                        echo "INSERT INTO notificaciones(tipo_notificacion_id,usuario_id,activa) VALUES (2,".$usuario->getId().",1)<br>";
                    }
                    else {
                        // Usamos Otras Ciudades
                        $merge_vars['GROUPINGS'] = array(
                            array(
                                'id' => 41,
                                'groups' => "Otras Ciudades"
                            )
                        );
                        echo "INSERT INTO notificaciones(tipo_notificacion_id,usuario_id,activa) VALUES (4,".$usuario->getId().",1)<br>";
                    }
                }
                else {
                    // No tiene ciudad definida. Usamos Santiago
                    $merge_vars['GROUPINGS'] = array(
                        array(
                            'id' => 41,
                            'groups' => "Santiago de Chile"
                        )
                    );
                    echo "INSERT INTO notificaciones(tipo_notificacion_id,usuario_id,activa) VALUES (1,".$usuario->getId().",1)<br>";
                }

                //print_r($merge_vars);

                // Verificar suscripción Mailchimp
                if($mcId == 0) {
                    // Nueva suscripción
                    $mc->listSubscribe($this->container->getParameter('mailchimp_list_id'), $usuario->getMail(), $merge_vars, 'html', false, true, true );
                }
                else {
                    // Usuario suscrito. Se actualizan datos
                    $mc->listUpdateMember($this->container->getParameter('mailchimp_list_id'), $mcId, $merge_vars, 'html', true);
                }
            }
        }


        echo $i;

        echo "</pre>";

        return new Response('');
    }*/
}
