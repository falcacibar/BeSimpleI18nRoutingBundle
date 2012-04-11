<?php

namespace Loogares\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Loogares\LugarBundle\Entity\TipoCategoria;
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

        //Ciudad Nueva
        $ciudadSession = $this->get('session')->get('ciudad');

        //Campañas del home
        $q = $em->createQuery("SELECT p FROM Loogares\BlogBundle\Entity\Posts p 
                               WHERE p.ciudad = ?1 AND (p.destacado_home = ?2 OR p.destacado_home = ?3) 
                               ORDER BY p.id DESC");
        $q->setMaxResults(3);
        $q->setParameter(1, $ciudadSession['id']);
        $q->setParameter(2, 1);
        $q->setParameter(3, 3);
        $campanas = $q->getResult();

        //Slider del home
        $q = $em->createQuery("SELECT p FROM Loogares\BlogBundle\Entity\Posts p 
                               WHERE p.ciudad = ?1 AND (p.destacado_home = ?2 OR p.destacado_home = ?3) 
                               ORDER BY p.id DESC");
        $q->setMaxResults(3);
        $q->setParameter(1, $ciudadSession['id']);
        $q->setParameter(2, 2);
        $q->setParameter(3, 3);
        $sliderCampanas = $q->getResult();


        //Recomendacion Estrella
        $q = $em->createQuery("SELECT u from Loogares\UsuarioBundle\Entity\LoogarenoEstrella u ORDER BY u.id desc");
        $q->setMaxResults(1);
        $estrellaResult = $q->getResult();

        $estrella['obj'] = $estrellaResult[0]->getRecomendacion();
        $estrella['truncated'] = substr($estrellaResult[0]->getRecomendacion()->getTexto(), 0, 180);

        // Cantidad de premios regalados (totales)
        $q = $em->createQuery("SELECT count(cu.id)
                               FROM Loogares\ExtraBundle\Entity\ConcursoUsuario cu
                               JOIN cu.usuario u
                               WHERE u.estado != ?1");
        $q->setParameter(1, 3);
        $totalPremios = $q->getSingleScalarResult();

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
        $home['totalPremios'] = $totalPremios;        
        $home['totalPremios_format'] = number_format( $totalPremios , 0 , '' , '.' );
        $home['totalRecomendaciones'] = $totalRecomendaciones;
        $home['totalRecomendaciones_format'] = number_format( $totalRecomendaciones , 0 , '' , '.' );
        $home['recDia'] = $recomendacionDelDia;
        $home['previewRecDia'] = $previewRecDia;
        $home['ultimosConectados'] = $ultimosConectados;
        $home['categorias'] = $categorias;
        $home['actividad'] = $actividad;
        $home['estrella'] = $estrella;

        return $this->render('LoogaresExtraBundle:Default:home.html.twig', array(
            'home' => $home,     
            'campanas' => $campanas,
            'sliderCampanas' => $sliderCampanas
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

        $paginas = array(
            'lunes_de_pelicula' => 'Notas',
            'beneficio_exclusivo' => 'Notas',
            'martes_de_amanda' => 'Notas',
            'miercoles_de_municipal' => 'Notas',
            'jueves_de_gam' => 'Notas',
            'sabor_platonico' => 'Notas',
            'sanduich' => 'Notas',
            'codigos_de_conducta' => 'Static',
            'contacto' => 'Static',
            'copyright' => 'Static',
            'eres_el_dueno_de_un_lugar' => 'Static',
            'loogareno_estrella' => 'Static',
            'politicas_de_privacidad' => 'Static',
            'prensa' => 'Static',
            'publicidad' => 'Static',
            'que_es_loogares' => 'Static',
            'terminos_de_uso' => 'Static',
            'trabaja_con_nosotros' => 'Static'
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
            'static' => $static
        ));
    }

    public function contactoMailAction(){
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
