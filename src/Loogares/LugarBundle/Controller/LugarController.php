<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Loogares\LugarBundle\Entity\Lugar;
use Loogares\LugarBundle\Entity\CategoriaLugar;
use Loogares\LugarBundle\Entity\CaracteristicaLugar;
use Loogares\LugarBundle\Entity\Horario;
use Loogares\LugarBundle\Entity\SubcategoriaLugar;
use Loogares\LugarBundle\Entity\ImagenLugar;
use Loogares\LugarBundle\Entity\ReportarLugar;
use Loogares\LugarBundle\Entity\ReportarImagen;
use Loogares\LugarBundle\Entity\ReportarRecomendacion;

use Loogares\UsuarioBundle\Entity\Recomendacion;
use Loogares\UsuarioBundle\Entity\Tag;
use Loogares\UsuarioBundle\Entity\TagRecomendacion;
use Loogares\UsuarioBundle\Entity\Dueno;
use Loogares\UsuarioBundle\Entity\AccionUsuario;

use Loogares\AdminBundle\Entity\TempLugar;
use Loogares\AdminBundle\Entity\TempCategoriaLugar;
use Loogares\AdminBundle\Entity\TempCaracteristicaLugar;
use Loogares\AdminBundle\Entity\TempHorario;
use Loogares\AdminBundle\Entity\TempSubcategoriaLugar;

use Loogares\ExtraBundle\Entity\ActividadReciente;

class LugarController extends Controller{

    public function lugarAction($slug, Request $request, $usuarioSlug = false){
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository('LoogaresLugarBundle:Lugar');

        $lugarResult[0] = $lr->findOneBySlug($slug);

        if(!$lugarResult[0] || $lugarResult[0]->getEstado()->getId() == 3){
          throw $this->createNotFoundException('');
        }

        $paises = array(
            'cl' => 'chile',
            'ar' => 'argentina',
            'www' => 'www'
        );

        $url = explode('.', $request->getHost());

        //Seteo para que funcione en localhost!
        if(isset($paises[$url[0]])){
            $paisSlug = $lugarResult[0]->getComuna()->getCiudad()->getPais()->getSlug();
            if($paisSlug != $paises[$url[0]]){
                return $this->redirect("http://".array_search($paisSlug, $paises).".loogares.com".$_SERVER['REQUEST_URI'], 301);
            }
        }

        $fn = $this->get('fn');
        $precioPromedio = 0;
        $estrellasPromedio = 0;

        if($lugarResult[0]->getEstado()->getId() == 4){
          $this->get('session')->setFlash('cerrado_flash', 'Este lugar está cerrado. De reabrirse, quitaremos este mensaje. En caso contrario borraremos este lugar después de un tiempo.');
        }else if($lugarResult[0]->getEstado()->getId() == 1){
          $this->get('session')->setFlash('lugar_flash', 'Este lugar se encuentra en Revisión.');
        }

        $visitas = $lugarResult[0]->getVisitas();
        $visitas++;
        $lugarResult[0]->setVisitas($visitas);
        $em->persist($lugarResult[0]);
        $em->flush();

        $idLugar = $lugarResult[0]->getId();
        $idUsuario = ($this->get('security.context')->isGranted('ROLE_USER')) ? $this->get('security.context')->getToken()->getUser()->getId() : 0;

        //Ultima foto del Lugar
        $q = $em->createQuery("SELECT u
                               FROM Loogares\LugarBundle\Entity\ImagenLugar u
                               WHERE u.lugar = ?1
                               AND u.estado != ?2
                               ORDER BY u.fecha_creacion DESC, u.id DESC");
        $q->setMaxResults(1)
          ->setParameter(1, $idLugar)
          ->setParameter(2, 3);
        $imagenLugarResult = $q->getResult();

        //Total Fotos Lugar
        $q = $em->createQuery("SELECT count(u.id)
                               FROM Loogares\LugarBundle\Entity\ImagenLugar u
                               WHERE u.lugar = ?1 AND u.estado != ?2");
        $q->setParameter(1, $idLugar);
        $q->setParameter(2, 3);
        $totalFotosResult = $q->getSingleScalarResult();

        //Query para sacar si ya recomendo
        $q = $em->createQuery("SELECT u.id
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion u
                               WHERE u.usuario = ?1 and u.lugar = ?2 and u.estado != ?3");
        $q->setParameter(1, $idUsuario);
        $q->setParameter(2, $idLugar);
        $q->setParameter(3, 3);

        $yaRecomendoResult = $q->getResult();

        $mostrarPrecio = $fn->mostrarPrecio($lugarResult[0]);

        if($mostrarPrecio){
            //Precio Promedio
            $q = $em->createQuery("SELECT SUM(r.precio) as precioSum, count(r.id) as precioTotal
                                   FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                                   WHERE r.lugar = ?1 and r.estado != 3");
            $q->setParameter(1, $lugarResult[0]);
            $preciosRecomendaciones = $q->getResult();

            $precioPromedio = ($lugarResult[0]->getPrecioInicial() + $preciosRecomendaciones[0]['precioSum']) / ($preciosRecomendaciones[0]['precioTotal'] + 1);
            $lugarResult[0]->setPrecio(round($precioPromedio));
        }

        $totalAcciones = $lr->getTotalAccionesLugar($lugarResult[0]->getId());

        if($this->get('security.context')->isGranted('ROLE_USER')) {
            $accionesUsuario = $lr->getAccionesUsuario($lugarResult[0]->getId(), $this->get('security.context')->getToken()->getUser()->getId());

            // Verificamos si el usuario puede o no realizar acciones según sus acciones actuales
            for($i = 0; $i < sizeof($accionesUsuario); $i++) {
                $accionesUsuario[$i]['puede'] = 1;

                // Si el usuario ya estuvo, no puede desmarcar esta opción
                if($accionesUsuario[$i]['id'] == 3 && $accionesUsuario[$i]['hecho'] == 1)
                    $accionesUsuario[$i]['puede'] = 0;
                else if($accionesUsuario[$i]['id'] == 5 && $accionesUsuario[$i]['hecho'] == 1)
                    $accionesUsuario[$i]['puede'] = 0;
            }

            // Si el usuario ya estuvo o quiere volver, no puede querer ir
            if($accionesUsuario[2]['hecho'] == 1 || $accionesUsuario[1]['hecho'] == 1) {
                $accionesUsuario[0]['puede'] = 0;
            }
        } else {
            $accionesUsuario = $lr->getAccionesUsuario($lugarResult[0]->getId());
             for($i = 0; $i < sizeof($accionesUsuario); $i++) {
                $accionesUsuario[$i]['puede'] = 0;
            }
        }

        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $q  = $em->createQuery("SELECT count(r) FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.lugar = ?1
                               AND r.estado != ?2 ");
        $q->setParameters(array(1 => $lugarResult[0], 2 => 3));

        $lugarResult[0]->recomendaciones = $q->getSingleScalarResult();

        // Revisamos si el lugar tiene pedidos asociados
        $reservas = $lr->getPedidosLugar($lugarResult[0], 1);
        $pedidos = $lr->getPedidosLugar($lugarResult[0], 2);

        $lugarResult[0]->tel1 = preg_replace('/^\+[0-9]{2}\s/', '', $lugarResult[0]->getTelefono1());
        $lugarResult[0]->tel2 = preg_replace('/^\+[0-9]{2}\s/', '', $lugarResult[0]->getTelefono2());
        $lugarResult[0]->tel3 = preg_replace('/^\+[0-9]{2}\s/', '', $lugarResult[0]->getTelefono3());

        //Sacamos los HTTP
        $lugarResult[0]->setSitioWeb($fn->stripHTTP($lugarResult[0]->getSitioWeb()));
        $lugarResult[0]->setTwitter($fn->stripHTTP($lugarResult[0]->getTwitter()));
        $lugarResult[0]->setFacebook($fn->stripHTTP($lugarResult[0]->getFacebook()));

        $conr = $em->getRepository("LoogaresBlogBundle:Concurso");
        //Concursos vigentes
        $concursos = $conr->getConcursosVigentes($lugarResult[0]->getComuna()->getCiudad()->getId());

        /*
        *  Armado de Datos para pasar a Twig
        */
        $data = $lugarResult[0];

        if(isset($recomendacionPedidaResult[0])){
            $data->recomendacionPedida = $recomendacionPedidaResult[0];
        }

        $data->horarios = $fn->generarHorario($lugarResult[0]->getHorario());
        $data->imagen_full = (isset($imagenLugarResult[0]))?$imagenLugarResult[0]->getImagenFull():'default.gif';
        $data->yaRecomendo = $yaRecomendoResult;
        $data->mostrarPrecio = $mostrarPrecio;
        $data->totalFotos = $totalFotosResult;
        $data->tagsPopulares = $lr->getTagsPopulares($idLugar);
        $data->totalAcciones = $totalAcciones;
        $data->accionesUsuario = $accionesUsuario;
        $data->reservas = $reservas;
        $data->pedidos = $pedidos;
        $data->concursos = $concursos;
        $data->usuarioSlug = $usuarioSlug;

        //Render ALL THE VIEWS
       return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => $data));
    }


    public function lugarRecomendacionAction (Request $request, $slug, $usuarioSlug = false) {
        $em    = $this->getDoctrine()->getEntityManager();
        $lr    = $em->getRepository("LoogaresLugarBundle:Lugar");

        // Lugar con slug
        $lugar = $lr->findOneBySlug($slug);

        if(!$lugar) {
            return $this->createNotFoundException();
        }

        $idLugar    = $lugar->getId();

        if($this->get('security.context')->isGranted('ROLE_USER')) {
            $idUsuario  = $this->get('security.context')->getToken()->getUser()->getId();
        } else {
            $idUsuario  = 0;
        }

        //Ultima foto del Lugar
        $q = $em->createQuery("SELECT u
                               FROM Loogares\LugarBundle\Entity\ImagenLugar u
                               WHERE u.lugar = ?1
                               AND u.estado != ?2
                               ORDER BY u.fecha_creacion DESC, u.id DESC");
        $q->setMaxResults(1)
          ->setParameter(1, $idLugar)
          ->setParameter(2, 3);
        $imagenLugar = $q->getResult();

        $lugar->imagen_full  = ($imagenLugar)  ? $imagenLugar[0]->getImagenFull() : 'default.gif';

        //Query para sacar si ya recomendo
        $q = $em->createQuery("SELECT u.id
                               FROM Loogares\UsuarioBundle\Entity\Recomendacion u
                               WHERE u.usuario = ?1 and u.lugar = ?2 and u.estado != 3")
                ->setParameters(array(
                                    1 => $idUsuario,
                                    2 => $idLugar
                ));

        $lugar->yaRecomendo = $q->getResult();

        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $q  = $em->createQuery("SELECT count(r) FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.lugar = ?1
                               AND r.estado != ?2 ");
        $q->setParameters(array(1 => $lugar, 2 => 3));

        $lugar->recomendaciones = $q->getSingleScalarResult();

        if($idUsuario) {
            $accionesUsuario = $lr->getAccionesUsuario($idLugar, $idUsuario);

            // Verificamos si el usuario puede o no realizar acciones según sus acciones actuales
            for($i = 0; $i < sizeof($accionesUsuario); $i++) {
                if( $accionesUsuario[$i]['hecho'] == 1 &&
                    ($accionesUsuario[$i]['id'] == 3 || $accionesUsuario[$i]['id'] == 5)
                ) {
                    $accionesUsuario[$i]['puede'] = 0;
                } else {
                    $accionesUsuario[$i]['puede'] = 1;
                }
            }

            // Si el usuario ya estuvo o quiere volver, no puede querer ir
            if($accionesUsuario[2]['hecho'] == 1 || $accionesUsuario[1]['hecho'] == 1) {
                $accionesUsuario[0]['puede'] = 0;
            }
        } else {
            $accionesUsuario = $lr->getAccionesUsuario($lugar->getId());

            for($i = 0; $i < sizeof($accionesUsuario); $i++) {
                $accionesUsuario[$i]['puede'] = 0;
            }
        }

        $lugar->accionesUsuario     = $accionesUsuario;
        $lugar->totalAcciones       = $lr->getTotalAccionesLugar($idLugar);
        $lugar->usuarioSlug         = $usuarioSlug;

        return $this->render('LoogaresLugarBundle:Lugares:lugar_recomendaciones.html.twig', array(
                'lugar'    => $lugar,
                'slug'     => $slug
            )
        );
    }

    public function listadoRecomendacionesAction($slug, $usuarioSlug = null, $enLugar = false){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);

        $recomendacionPedida = null;
        $exclusionRecomendacionPedida = null;
        $_GET['pagina'] = (!isset($_GET['pagina']))?1:$_GET['pagina'];
        $_GET['orden'] = (!isset($_GET['orden']))?'ultimas':$_GET['orden'];
        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $resultadosPorPagina = ($enLugar)?5:((isset($_GET['resultados']))?$_GET['resultados']:20);
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*$resultadosPorPagina);
        $params = array('slug' => $lugar->getSlug());
        $routePath = ($enLugar) ? '_lugar' : '_lugarRecomendacion';
        $fn = $this->get('fn');

        if($_GET['orden'] == 'ultimas'){
                $orderBy = "ORDER BY r.fecha_creacion DESC";
        }else if($_GET['orden'] == 'mas-utiles'){
                $orderBy = "ORDER BY r.utiles DESC";
        }else if($_GET['orden'] == 'mejor-evaluadas'){
                $orderBy = "ORDER BY r.estrellas desc, r.fecha_creacion DESC";
        }

        if($usuarioSlug) {
            //Recomendacion pedida
            $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
            $usuario = $ur->findOneByIdOrSlug($usuarioSlug);

            $q = $em->createQuery("SELECT r FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                                   WHERE r.lugar = ?1
                                   AND r.usuario = ?2
                                   AND r.estado != ?3");

            $q->setParameter(1, $lugar);
            $q->setParameter(2, $usuario);
            $q->setParameter(3, 3);
            $q->setMaxResults(1);

            $recomendacionPedida = $q->getSingleResult();
            $exclusionRecomendacionPedida = ' AND r.id != '.$recomendacionPedida->getId();
        }

        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $q = $em->createQuery("SELECT count(r) FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.lugar = ?1
                               AND r.estado != ?2 $exclusionRecomendacionPedida");
        $q->setParameter(1, $lugar);
        $q->setParameter(2, 3);
        $totalRecomendaciones = $q->getSingleScalarResult();

        $q = $em->createQuery("SELECT r FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.lugar = ?1
                               AND r.estado != ?2 $exclusionRecomendacionPedida $orderBy");
        $q->setParameter(1, $lugar);
        $q->setParameter(2, 3);

        $q->setMaxResults($resultadosPorPagina);
        $q->setFirstResult($offset);

        $recomendaciones = $q->getResult();

        if($recomendacionPedida) $recomendaciones[] = &$recomendacionPedida;

        foreach($recomendaciones as $key => $recomendacion){
            $q = $em->createQuery("SELECT min(u.id) FROM Loogares\UsuarioBundle\Entity\Util u
                                   WHERE u.recomendacion = ?1 and u.usuario = ?2");
            $q->setParameter(1, $recomendacion->getId());
            $q->setParameter(2, $this->get('security.context')->getToken()->getUser());
            $q->setMaxResults(1);
            $recomendaciones[$key]->apretoUtil = $q->getSingleScalarResult();

            $q = $em->createQuery("SELECT t from Loogares\UsuarioBundle\Entity\Tag t
                                   JOIN t.tag_recomendacion tr
                                   WHERE tr.recomendacion = ?1");

            $q->setParameter(1, $recomendacion->getId());
            $tags = $q->getResult();

            $tagsBuffer = array();
            foreach($tags as $tag){ $tagsBuffer[] = $tag->getTag(); }
            $recomendaciones[$key]->tags = join(', ', $tagsBuffer );
        }

        $paginacion = $fn->paginacion($totalRecomendaciones, $resultadosPorPagina, $routePath, $params, $this->get('router') );

        return $this->render('LoogaresLugarBundle:Lugares:listado_recomendaciones.html.twig', array(
            'lugar' => $lugar,
            'recomendaciones' => $recomendaciones,
            'query' => $_GET,
            'paginacion' => $paginacion,
            'enLugar' => $enLugar ,
            'routePath' => $routePath
        ));
    }

    public function lugarMapaAction(Request $request, $slug = null) {
        if(is_null($slug)) {
            return $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $lugar = $lr->findOneBySlug($slug);
        $idLugar = $lugar->getId();

        // Imagen principal
        $q = $em->createQuery("SELECT       i
                               FROM         Loogares\LugarBundle\Entity\ImagenLugar i
                               WHERE        i.lugar = ?1
                                            AND i.estado != 3
                               ORDER BY     i.fecha_creacion DESC, i.id DESC")
                ->setMaxResults(1)
                ->setParameter(1, $idLugar);

        try {
            $imagenLugar = $q->getSingleResult();
        } catch(\Doctrine\Orm\NoResultException $e) {
            $imagenLugar = null;
        }

        unset($q);

        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $q  = $em->createQuery("SELECT count(r) FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                               WHERE r.lugar = ?1
                               AND r.estado != ?2 ");
        $q->setParameters(array(1 => $lugar, 2 => 3));

        $lugar->recomendaciones = $q->getSingleScalarResult();

        $lugar->imagen_full  = ($imagenLugar)  ? $imagenLugar->getImagenFull() : null;

        unset($lr, $rr, $em);
        return $this->render('LoogaresLugarBundle:Lugares:lugar_mapa.html.twig', array(
                'lugar' => $lugar
        ));
    }

    public function agregarAction(Request $request, $slug = null){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $errors = array();
        $formErrors = array();
        $lugaresRevisados = array();
        $camposExtraErrors = false;
        $esEdicionDeUsuario = false;
        $nuevoLugar = false;
        $rolAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        if($slug && $rolAdmin == false){
            $lugarManipulado = new TempLugar();
            $esEdicionDeUsuario = true;
            $lugar = $lr->findOneBySlug($slug);
        }else if($slug && $rolAdmin == true){
            $tlr = $em->getRepository("LoogaresAdminBundle:TempLugar");
            $lugarManipulado = $lr->findOneBySlug($slug);
            $lugaresRevisados = $lr->getLugaresPorRevisar($lugarManipulado->getId(), 1);
        }else{
            $nuevoLugar = true;
            $lugarManipulado = new Lugar();
        }

        if($slug && $rolAdmin == false){ //Proceso de parseo de datos de lugar existente, SOLO LECTURA/OUTPUT
            //Sacar +56 de los telefonos
            $lugar->tel1 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono1());
            $lugar->tel2 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono2());
            $lugar->tel3 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono3());
        }else if($slug && $rolAdmin == true){
            $lugarManipulado->tel1 = preg_replace('/^\+[0-9]{2}\s/', '', $lugarManipulado->getTelefono1());
            $lugarManipulado->tel2 = preg_replace('/^\+[0-9]{2}\s/', '', $lugarManipulado->getTelefono2());
            $lugarManipulado->tel3 = preg_replace('/^\+[0-9]{2}\s/', '', $lugarManipulado->getTelefono3());
        }else{ //Proceso de parseo de datos de lugar existente, SOLO LECTURA/OUTPUT
            //Sacar +56 de los telefonos
            $lugarManipulado->tel1 = '';
            $lugarManipulado->tel2 = '';
            $lugarManipulado->tel3 = '';
        }

        $form = $this->createFormBuilder($lugarManipulado)
            ->add('nombre', 'text')
            ->add('calle', 'text')
            ->add('slug', 'hidden')
            ->add('numero', 'text')
            ->add('descripcion', 'text')
            ->add('detalle', 'text')
            ->add('telefono1', 'text')
            ->add('telefono2', 'text')
            ->add('telefono3', 'text')
            ->add('sitio_web', 'text')
            ->add('facebook', 'text')
            ->add('twitter', 'text')
            ->add('mail', 'text')
            ->add('mapx', 'text')
            ->add('mapy', 'text')
            ->add('precio', 'hidden')
            ->add('profesional', 'text')
            ->add('agno_construccion', 'text')
            ->add('materiales', 'text')
            ->add('_token', 'csrf')
            ->getForm();

        if ($request->getMethod() == 'POST') {

            $form->bindRequest($request);

            if($form->isValid() && $camposExtraErrors == false){
                $fn = $this->get('fn');

                if($esEdicionDeUsuario == true){
                  $lugarManipulado->setLugar($lugar);
                }

                if($nuevoLugar == true || $esEdicionDeUsuario == true){
                  $lugarManipulado->setUsuario($this->get('security.context')->getToken()->getUser());
                }

                $comuna = $lr->getComunas($_POST['comuna'], $_POST['ciudad']);

                $sector = $lr->getSectores($_POST['sector']);

                if(isset($sector[0])){
                    $lugarManipulado->setSector($sector[0]);
                }

                if(isset($_POST['precio'])){
                    $lugarManipulado->setPrecioInicial($_POST['precio']);
                    $lugarManipulado->setPrecio($_POST['precio']);
                }else{
                    $lugarManipulado->setPrecioInicial(0);
                    $lugarManipulado->setPrecio(0);
                }

                if($rolAdmin == false){
                    $estado = $lr->getEstado(1);
                    $lugarManipulado->setEstado($estado);
                }else if($nuevoLugar == true && $rolAdmin == true){
                    $estado = $lr->getEstado(2);
                    $lugarManipulado->setEstado($estado);
                }

                $tipo_lugar = $lr->getTipoLugar('lugar');
                $lugarManipulado->setTipoLugar($tipo_lugar[0]);
                $lugarManipulado->setComuna($comuna[0]);

                //Sacamos los HTTP
                $lugarManipulado->setSitioWeb($fn->stripHTTP($lugarManipulado->getSitioWeb()));
                $lugarManipulado->setTwitter($fn->stripHTTP($lugarManipulado->getTwitter()));
                $lugarManipulado->setFacebook($fn->stripHTTP($lugarManipulado->getFacebook()));

                $q = $em->createQuery("SELECT l FROM Loogares\LugarBundle\Entity\Lugar l
                                       JOIN l.comuna c
                                       JOIN c.ciudad ci
                                       WHERE l.nombre = ?1 AND ci.slug = ?2");
                $q->setParameter(1, $lugarManipulado->getNombre());
                $q->setParameter(2, $_POST['ciudad']);
                $lugaresConElMismoNombre = $q->getResult();

                if($nuevoLugar == true || $esEdicionDeUsuario == true){
                  $lugarManipulado->setFechaAgregado(new \DateTime());
                }

                if(sizeOf($lugaresConElMismoNombre) != 0 && $slug == null){
                    $n = sizeOf($lugaresConElMismoNombre) + 1;
                    $lugarSlug = $fn->generarSlug($lugarManipulado->getNombre()) . "-" . $_POST['ciudad'] . "-".$n;
                    $lugarManipulado->setSlug($lugarSlug);
                }else if(sizeOf($lugaresConElMismoNombre) == 0){
                    $lugarSlug = $fn->generarSlug($lugarManipulado->getNombre()) . "-" . $_POST['ciudad'];
                    $lugarManipulado->setSlug($lugarSlug);
                }

                $em->persist($lugarManipulado);

                $lr->cleanUp($lugarManipulado->getId());

                foreach($_POST['categoria'] as $postCategoria){
                    if($slug != null && $rolAdmin == false){
                        $categoriaLugar[] = new TempCategoriaLugar();
                    }else{
                        $categoriaLugar[] = new CategoriaLugar();
                    }
                    $size = sizeOf($categoriaLugar) - 1;
                    if($postCategoria != "elige"){
                        $categoria = $lr->getCategorias($postCategoria);
                        if($categoria){
                            $categoriaLugar[$size]->setCategoria($categoria[0]);
                            $categoriaLugar[$size]->setLugar($lugarManipulado);
                            if($_POST['categoria'][0] == $postCategoria){
                                $categoriaLugar[$size]->setPrincipal(1);
                            }else{
                                $categoriaLugar[$size]->setPrincipal(0);
                            }
                            $em->persist($categoriaLugar[$size]);
                        }
                    }
                }

                if(isset($_POST['caracteristica']) && is_array($_POST['caracteristica'])){
                    foreach($_POST['caracteristica'] as $postCaracteristica){
                        if($slug != null && $rolAdmin == false){
                            $caracteristicaLugar[] = new TempCaracteristicaLugar();
                        }else{
                            $caracteristicaLugar[] = new CaracteristicaLugar();
                        }
                        $size = sizeOf($caracteristicaLugar) - 1;
                        $caracteristica = $lr->getCaracteristicaPorNombre($postCaracteristica);
                        if($caracteristica){
                            $caracteristicaLugar[$size]->setLugar($lugarManipulado);
                            $caracteristicaLugar[$size]->setCaracteristica($caracteristica[0]);
                            $em->persist($caracteristicaLugar[$size]);
                        }
                    }
                }

                if(isset($_POST['subcategoria']) && is_array($_POST['subcategoria'])){
                    foreach($_POST['subcategoria'] as $postSubCategoria){
                        if($slug != null && $rolAdmin == false){
                            $subCategoriaLugar[] = new TempSubcategoriaLugar();
                        }else{
                            $subCategoriaLugar[] = new SubcategoriaLugar();
                        }
                        $size = sizeOf($subCategoriaLugar) - 1;
                        $subCategoria = $lr->getSubCategoriaPorNombre($postSubCategoria);
                        if($subCategoria){
                            $subCategoriaLugar[$size]->setLugar($lugarManipulado);
                            $subCategoriaLugar[$size]->setSubCategoria($subCategoria[0]);
                            $em->persist($subCategoriaLugar[$size]);
                        }
                    }
                }

                $dias = array('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo');

                foreach($dias as $key => $value){
                    if($slug != null && $rolAdmin == false){
                        $horario[] = new TempHorario();
                    }else{
                        $horario[] = new Horario();
                    }
                    $size = sizeOf($horario) - 1;
                    if(isset($_POST['horario-'.$value])){
                        $postHorario = $_POST['horario-'.$value];
                        if($postHorario[0] != 'cerrado' || $postHorario[1] != 'cerrado'){
                            $horario[$size]->setLugar($lugarManipulado);
                            $horario[$size]->setDia($key);
                            if($postHorario[0] != 'cerrado' && $postHorario[1] != 'cerrado'){
                                $horario[$size]->setAperturaAm($postHorario[0]);
                                $horario[$size]->setCierreAm($postHorario[1]);
                            }

                            if(isset($postHorario[2]) && $postHorario[2]!= 'cerrado' && isset($postHorario[3]) && $postHorario[3] != 'cerrado'){
                                $horario[$size]->setAperturaPm($postHorario[2]);
                                $horario[$size]->setCierrePm($postHorario[3]);
                            }

                            $em->persist($horario[$size]);
                        }
                    }
                }

                $em->flush();

                if(!$esEdicionDeUsuario && !$rolAdmin) {
                    // Si el lugar es nuevo y no es agregado por administrador, se agrega a actividad reciente
                    $actividad = new ActividadReciente();
                    $actividad->setEntidad('Loogares\LugarBundle\Entity\Lugar');
                    $actividad->setEntidadId($lugarManipulado->getId());
                    $actividad->setFecha($lugarManipulado->getFechaAgregado());
                    $actividad->setUsuario($lugarManipulado->getUsuario());
                    $actividad->setCiudad($lugarManipulado->getComuna()->getCiudad());

                    $tipoActividad = $em->getRepository('LoogaresExtraBundle:TipoActividadReciente')
                                        ->findOneByNombre('agregar');
                    $estadoActividad = $em->getRepository("LoogaresExtraBundle:Estado")
                                          ->findOneByNombre('Aprobado');
                    $actividad->setTipoActividadReciente($tipoActividad);
                    $actividad->setEstado($estadoActividad);

                    $em->persist($actividad);
                    $em->flush();
                }

                if( isset($_POST['texto']) && $_POST['texto'] != '' && !preg_match('/^¡Este es tu espacio!/', $_POST['texto'])){
                    //CURL MAGIC

                    if(isset($_POST['recomienda-precio'])){
                        $precio = $_POST['recomienda-precio'];
                    }else{
                        $precio = '';
                    }


                    if(isset($_POST['recomienda-estrellas'])){
                        $estrellas = $_POST['recomienda-estrellas'];
                    }else{
                        $estrellas = '';
                    }

                    //set POST variables
                    $fields_string = '';
                    $url = "http://".$_SERVER['SERVER_NAME'].$this->generateUrl('_recomienda', array('slug' => $lugarManipulado->getSlug()));
                    $fields = array(
                        'texto'=> urlencode($_POST['texto']),
                        'tags'=> urlencode($_POST['tags']),
                        'recomienda-estrellas'=> urlencode($estrellas),
                        'recomienda-precio' => urlencode($precio),
                        'usuario' => $this->get('security.context')->getToken()->getUser()->getId(),
                        'curlSuperVar' => 1
                    );

                    //url-ify the data for the POST
                    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                    $fields_string = rtrim($fields_string,'&');

                    //open connection
                    $ch = curl_init();

                    //set the url, number of POST vars, POST data
                    curl_setopt($ch,CURLOPT_URL, $url);
                    curl_setopt($ch,CURLOPT_POST,6);
                    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

                    //execute post
                    curl_exec($ch);

                    curl_close($ch);
                }

                if($rolAdmin == 1){
                    /**************************


                    (       )(  ___  )\__   __/( \      (  ____ \
                    | () () || (   ) |   ) (   | (      | (    \/
                    | || || || (___) |   | |   | |      | (_____
                    | |(_)| ||  ___  |   | |   | |      (_____  )
                    | |   | || (   ) |   | |   | |            ) |
                    | )   ( || )   ( |___) (___| (____/\/\____) |
                    |/     \||/     \|\_______/(_______/\_______)



                    *************************/

                    foreach($lugaresRevisados as $key => $lugar){
                        $estado = $lr->getEstado(9);
                        $lugar->setEstado($estado);
                        $em->persist($lugar);
                        $em->flush();
                    }

                    return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugarManipulado->getSlug())));
                }else{
                    if($esEdicionDeUsuario == true){
                        $this->get('session')->setFlash('lugar_flash', $this->get('translator')->trans('lugar.flash.recomendacion.edicion_revision', array('%nombre%' => $this->get('security.context')->getToken()->getUser()->getNombre(), '%apellido%' => $this->get('security.context')->getToken()->getUser()->getApellido())));
                        return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
                    }else{
                        $this->get('session')->setFlash('lugar_flash', $this->get('translator')->trans('lugar.flash.recomendacion.agregar_revision', array('%nombre%' => $this->get('security.context')->getToken()->getUser()->getNombre(), '%apellido%' => $this->get('security.context')->getToken()->getUser()->getApellido())));
                        return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugarManipulado->getSlug())));
                    }
                }
                //Agregar, solo, nada maish.
                return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => ''));
            }
        }

        $data['horarios'] = '<option value="cerrado">Cerrado</option>
                            <option value="06:00">06:00</option>
                            <option value="06:30">06:30</option>
                            <option value="07:00">07:00</option>
                            <option value="07:30">07:30</option>
                            <option value="08:00">08:00</option>
                            <option value="08:30">08:30</option>
                            <option value="09:00">09:00</option>
                            <option value="09:30">09:30</option>
                            <option value="10:00">10:00</option>
                            <option value="10:30">10:30</option>
                            <option value="11:00">11:00</option>
                            <option value="11:30">11:30</option>
                            <option value="12:00">12:00</option>
                            <option value="12:30">12:30</option>
                            <option value="13:00">13:00</option>
                            <option value="13:30">13:30</option>
                            <option value="14:00">14:00</option>
                            <option value="14:30">14:30</option>
                            <option value="15:00">15:00</option>
                            <option value="15:30">15:30</option>
                            <option value="16:00">16:00</option>
                            <option value="16:30">16:30</option>
                            <option value="17:00">17:00</option>
                            <option value="17:30">17:30</option>
                            <option value="18:00">18:00</option>
                            <option value="18:30">18:30</option>
                            <option value="19:00">19:00</option>
                            <option value="19:30">19:30</option>
                            <option value="20:00">20:00</option>
                            <option value="20:30">20:30</option>
                            <option value="21:00">21:00</option>
                            <option value="21:30">21:30</option>
                            <option value="22:00">22:00</option>
                            <option value="22:30">22:30</option>
                            <option value="23:00">23:00</option>
                            <option value="23:30">23:30</option>
                            <option value="00:00">00:00</option>
                            <option value="00:30">00:30</option>
                            <option value="01:00">01:00</option>
                            <option value="01:30">01:30</option>
                            <option value="02:00">02:00</option>
                            <option value="02:30">02:30</option>
                            <option value="03:00">03:00</option>
                            <option value="03:30">03:30</option>
                            <option value="04:00">04:00</option>
                            <option value="04:30">04:30</option>
                            <option value="05:00">05:00</option>
                            <option value="05:30">05:30</option>';


        //Errores
        foreach($this->get('validator')->validate( $form ) as $formError){
            $formErrors[] = $formError->getMessage();
        }

        if(is_array($camposExtraErrors) && is_array($formErrors)){
            $errors = array_merge($formErrors, $camposExtraErrors);
        }

        if($rolAdmin != true){
            $data['categorias'] = $lr->getCategorias();
        }else{
            $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\Categoria u ORDER BY u.nombre ASC");
            $data['categorias'] = $q->getResult();
        }

        $data['tipoCategoria'] = $lr->getTipoCategorias();
        $q = $em->createQuery("SELECT DISTINCT u.nombre from Loogares\LugarBundle\Entity\SubCategoria u ORDER BY u.nombre asc");
        $data['subCategorias'] = $q->getResult();
        $data['caracteristicas'] = $lr->getCaracteristicas();
        $data['ciudad'] = $lr->getCiudades();
        $data['pais'] = $lr->getPaises();
        $data['comuna'] = $lr->getComunas();
        $data['sector'] = $lr->getSectores();

        $ciudad = $this->get('session')->get('ciudad');
        $data['ciudadActual'] = $lr->getCiudadById($ciudad['id']);

        return $this->render('LoogaresLugarBundle:Lugares:agregar.html.twig', array(
            'data' => $data,
            'lugar' => (isset($lugar))?$lugar:$lugarManipulado,
            'lugaresRevisados' => $lugaresRevisados,
            'form' => $form->createView(),
            'errors' => $errors,
        ));
    }

    public function agregarFotoAction(Request $request, $slug) {
        foreach($_POST as $key => $value){
            $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
        }
        foreach($_GET as $key => $value){
            $_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $formErrors = array();

        $lugar = $lr->findOneBySlug($slug);
        $usuario = $this->get('security.context')->getToken()->getUser();

        // Primer paso de agregar fotos
        if(!$request->request->get('info')) {

            $imgLugar = new ImagenLugar();

            $form = $this->createFormBuilder($imgLugar)
                         ->add('firstImg')
                         ->add('secondImg')
                         ->add('thirdImg')
                         ->getForm();

            // Si el request es POST, se procesan nuevas fotos
            if ($request->getMethod() == 'POST') {

                $form->bindRequest($request);

                $imagenes = array();

                // Imágenes subidas desde archivo
                if($imgLugar->firstImg != null)
                    $imagenes[] = $imgLugar->firstImg;

                if($imgLugar->secondImg != null)
                    $imagenes[] = $imgLugar->secondImg;

                if($imgLugar->thirdImg != null)
                    $imagenes[] = $imgLugar->thirdImg;

                $urls = $request->request->get('urls');

                // Imágenes subidas desde URL. Se guardan en carpeta assets/images/temp de forma temporal
                foreach($urls as $url) {
                    if($url != '') {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_POST, 0);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                        $result = curl_exec($ch);
                        curl_close($ch);

                        $u = explode('.',$url);
                        $ext = array_pop($u);
                        $fn = time().rand(1, 10000).'.jpg';//.$ext;
                        //try {
                        if(file_put_contents('assets/images/temp/'.$fn, $result)) {
                            if(getimagesize('assets/images/temp/'.$fn)) {
                                $imagen = new UploadedFile('assets/images/temp/'.$fn, $fn);
                                $imagen->url = $url;
                                $imagenes[] = $imagen;
                                echo "fileputcontents";
                            }
                            else {
                                $formErrors['no-imagen'] = "Ocurrió un error con la carga de una o más imágenes. Inténtalo de nuevo, o prueba con otras.";
                                unlink('assets/images/temp/'.$fn);
                            }
                        }
                        else {
                            $formErrors['no-imagen'] = "Ocurrió un error con la carga de una o más imágenes. Inténtalo de nuevo, o prueba con otras.";
                            unlink('assets/images/temp/'.$fn);
                        }
                        /*}
                        catch(\ErrorException $e) {
                            $formErrors['val'] = "No vale como imagen!";
                            echo "hola";
                        } */

                    }
                }

                if(sizeof($imagenes) == 0 && sizeOf($formErrors) == 0) {
                    $formErrors['valida'] = "No tienes seleccionado ningún archivo. Por favor, elige uno.";
                }

                if ($form->isValid() && sizeof($formErrors) == 0) {

                    //Array que nos permitirá obtener las imágenes en el siguiente paso
                    $imgs = array();

                    foreach($imagenes as $imagen) {
                        $newImagen = new ImagenLugar();
                        $newImagen->setUsuario($this->get('security.context')->getToken()->getUser());
                        $newImagen->setLugar($lugar);
                        if($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                        $estadoImagen = $em->getRepository("LoogaresExtraBundle:Estado")
                                        ->findOneByNombre('Por revisar');
                        }
                        else {
                             $estadoImagen = $em->getRepository("LoogaresExtraBundle:Estado")
                                        ->findOneByNombre('Por revisar');
                        }
                        $newImagen->setEstado($estadoImagen);
                        $newImagen->setFechaCreacion(new \DateTime());
                        $newImagen->setImagenFull('.jpg');

                        $newImagen->firstImg = $imagen;
                        if(isset($imagen->url)) {
                            $newImagen->setTituloEnlace($imagen->url);
                            $newImagen->setEsEnlace(1);
                        }

                        $em->persist($newImagen);
                        $em->flush();

                        $newImagen->setFechaCreacion(new \DateTime());

                        // Agregamos evento a la actividad reciente del usuario
                        if($this->get('security.context')->isGranted('ROLE_ADMIN') == false){
                          $actividad = new ActividadReciente();
                          $actividad->setEntidad('Loogares\LugarBundle\Entity\ImagenLugar');
                          $actividad->setEntidadId($newImagen->getId());
                          $actividad->setFecha($newImagen->getFechaCreacion());
                          $actividad->setUsuario($newImagen->getUsuario());
                          $actividad->setCiudad($lugar->getComuna()->getCiudad());

                          $tipoActividad = $em->getRepository('LoogaresExtraBundle:TipoActividadReciente')
                                              ->findOneByNombre('agregar');
                          $estadoActividad = $em->getRepository("LoogaresExtraBundle:Estado")
                                                ->findOneByNombre('Aprobado');
                          $actividad->setTipoActividadReciente($tipoActividad);
                          $actividad->setEstado($estadoActividad);

                          $em->persist($actividad);

                          if(!isset($imagen->url))
                              $imgs[] = $newImagen;

                          $newImagen = null;
                        }
                        $em->flush();
                    }

                    if(sizeof($imgs) == 0 && sizeof($imagenes) > 0) {
                        // Se agregaron sólo fotos por URL
                        $this->get('session')->setFlash('lugar_flash',$this->get('translator')->trans('lugar.flash.foto.agregar', array('%nombre%' => $usuario->getNombre(), '%apellido%' => $usuario->getApellido())));
                        return $this->redirect($this->generateUrl('_lugar', array('slug' => $slug)));
                    }

                    // Generación de vista para agregar descripción a foto
                    return $this->render('LoogaresLugarBundle:Lugares:agregar_info_foto.html.twig', array(
                        'lugar' => $lugar,
                        'imagenes' => $imgs,
                    ));
                }
            }

            //Errores
            foreach($this->get('validator')->validate( $form ) as $formError){
                $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
            }

            return $this->render('LoogaresLugarBundle:Lugares:agregar_foto.html.twig', array(
                'lugar' => $lugar,
                'form' => $form->createView(),
                'errors' => $formErrors,
            ));
        }

        // Segundo paso de agregar fotos
        else {
            $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");

            // Si el request es POST, se procesan descripciones de fotos
            if ($request->getMethod() == 'POST') {

                $infoImgs = $request->request->get('imagenes');

                // A cada imagen le asociamos la descripción/URL correspondiente
                foreach($infoImgs as $key => $info) {
                    $imagen = $ilr->find($key);

                    $imagen->setTituloEnlace($info);

                    // Verificamos si es URL
                    $match = preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $info);
                    if($match > 0)
                        $imagen->setEsEnlace(1);
                    else
                        $imagen->setEsEnlace(0);

                    $em->flush();
                }
            }

            $this->get('session')->setFlash('lugar_flash', $this->get('translator')->trans('lugar.flash.foto.agregar', array('%nombre%' => $usuario->getNombre(), '%apellido%' => $usuario->getApellido())));
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $slug)));
        }
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

            foreach($_POST as $key => $value){
                $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
            }

            if ($form->isValid()) {
                $imagen->setFechaModificacion(new \DateTime());

                // Verificamos si es URL
                $match = preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $imagen->getTituloEnlace());
                if($match > 0)
                    $imagen->setEsEnlace(1);
                else
                    $imagen->setEsEnlace(0);
                $em->flush();

                $tipo = 'descripcion';
                return $this->redirect($this->generateUrl('_fotoGaleria', array('id' => $id, 'slug' => $slug)));
                return $this->render('LoogaresLugarBundle:Lugares:editar_foto.html.twig', array(
                    'imagen' => $imagen,
                    'form' => $form->createView(),
                    'tipo' => $tipo,
                ));
            }
        }


        if ($this->getRequest()->isXmlHttpRequest()){
            $tipo = 'form';
            return $this->render('LoogaresLugarBundle:Lugares:editar_foto.html.twig', array(
                'imagen' => $imagen,
                'form' => $form->createView(),
                'tipo' => $tipo,
            ));
        }else{
            return $this->fotoGaleriaAction($slug, $id, $editar = true);
            return $this->redirect($this->generateUrl('_fotoGaleria', array('id' => $id, 'slug' => $slug)));
        }


    }

    public function eliminarFotoAction(Request $request, $slug, $id) {
        $em = $this->getDoctrine()->getEntityManager();
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");

        $imagen = $ilr->find($id);
        $lugar = $imagen->getLugar();

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
        $ar->actualizarActividadReciente($imagen->getId(), 'Loogares\LugarBundle\Entity\ImagenLugar');
        $em->flush();

        if($request->query->get('redirect') == 'usuario') {
            // Mensaje de éxito de la eliminación
            $this->get('session')->setFlash('usuario_flash','lugar.flash.foto.borrar');

            // Redirección a vista de fotos del usuario
            return $this->redirect($this->generateUrl('fotosLugaresUsuario', array('param' => $ur->getIdOrSlug($imagen->getUsuario()))));
        }

        // Mensaje de éxito de la eliminación
        $this->get('session')->setFlash('imagen_flash','lugar.flash.foto.borrar');

        // Redirección a galería de fotos del lugar
        return $this->redirect($this->generateUrl('_galeria', array('slug' => $slug)));
    }

    public function galeriaAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);
        $imagen = $lr->getImagenLugarMasReciente($lugar);

        if(!$imagen){
          $this->get('session')->setFlash('error_flash', 'No existen imagenes para este Lugar.');
          return $this->redirect($this->generateUrl('_lugar', array('slug' => $slug)));
        }else{
          $id = $imagen[0]->getId();
          return $this->forward('LoogaresLugarBundle:Lugar:fotoGaleria', array('slug' => $slug, 'id' => $id));
        }
    }

    public function fotoGaleriaAction($slug, $id, $editar = false) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $lugar = $lr->findOneBySlug($slug);
        $imagen = $ilr->find($id);

        $vecinas = $lr->getFotosVecinas($id, $lugar->getId());

        if($imagen->getLugar()->getSlug() != $slug) {
            throw $this->createNotFoundException('La foto especificada no corresponde al lugar '.$lugar->getNombre());
        }

        $imagen->loggeadoCorrecto = $this->get('security.context')->getToken()->getUser() == $imagen->getUsuario();

        // Array con output de dimensiones de imagen
        $dimensiones = array();
        $dimensiones['ancho'] = "auto";
        $dimensiones['alto'] = "auto";

        // Dimensiones de la imagen para manejar bien el output
        try {
            $sizeArray = getimagesize('assets/images/lugares/'.$imagen->getImagenFull());
            $anchoDefault = 600;
            $altoDefault = 500;
            $ancho = $sizeArray[0];
            $alto = $sizeArray[1];

            // Primer caso: sólo ancho mayor que default
            if($ancho > $anchoDefault && $alto <= $altoDefault) {
                $dimensiones['ancho'] = $anchoDefault;
                $dimensiones['alto'] = ($anchoDefault * $alto) / $ancho;
            }

            // Segundo caso: ancho mayor que default, pero alto mayor que default y ancho
            else if($ancho > $anchoDefault && $alto > $altoDefault && $alto > $ancho) {
                $dimensiones['alto'] = $altoDefault;
                $dimensiones['ancho'] = ($altoDefault * $ancho) / $alto;
            }

            // Tercer caso: sólo alto mayor que default
            else if($alto > $altoDefault && $ancho <= $anchoDefault ) {
                $dimensiones['alto'] = $altoDefault;
                $dimensiones['ancho'] = ($altoDefault * $ancho) / $alto;
            }

            // Cuarto caso: alto mayor que default, pero ancho mayor que default y alto
            else if($alto > $altoDefault && $ancho > $anchoDefault && $ancho > $alto) {
                $dimensiones['ancho'] = $anchoDefault;
                $dimensiones['alto'] = ($anchoDefault * $alto) / $ancho;
            }

            //ES MUY GRANDEEEEE!!!
            else if($alto > $altoDefault && $ancho > $anchoDefault){
                $dimensiones['ancho'] = $anchoDefault;
                $dimensiones['alto'] = ($anchoDefault * $alto) / $ancho;
            }
        }
        catch(\Exception $e) {

        }

        $reportar = 1;
        if(!$imagen->loggeadoCorrecto && $this->get('security.context')->isGranted('ROLE_USER')) {
            $reportes = $lr->getReportesImagenesUsuarioLugar($imagen->getId(), $this->get('security.context')->getToken()->getUser(), 1);
            if(sizeof($reportes) > 0)
                $reportar = 0;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->render('LoogaresLugarBundle:Lugares:contenido_galeria.html.twig', array(
                'lugar' => $lugar,
                'imagen' => $imagen,
                'vecinas' => $vecinas,
                'dimensiones' => $dimensiones,
                'reportar' => $reportar,
            ));
        }

        return $this->render('LoogaresLugarBundle:Lugares:foto_galeria.html.twig', array(
            'lugar' => $lugar,
            'imagen' => $imagen,
            'vecinas' => $vecinas,
            'dimensiones' => $dimensiones,
            'reportar' => $reportar,
            'editar' => $editar,
            'edicion' => ($this->getRequest()->query->get('edicion')) ? true : false,
        ));
    }

    /* RECOMENDACIONES */
    public function recomendacionesAction($slug, Request $request, $curlSuperVar = false){
        $yaRecomendo    = array();
        $em             = $this->getDoctrine()->getEntityManager();
        $session        = $this->get('session');

        if(false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $session->set('recomendacionPendiente', $_POST);
            $session->set('alIngresarIrA', $request->getRequestUri());

            return $this->redirect($this->generateUrl('registroUsuario'));
        }

        if(!is_null($rec = $session->get('recomendacionPendiente'))) {
            $_POST = &$rec;
            $request->server->set('REQUEST_METHOD', 'POST');
            $session->remove('recomendacionPendiente');
        }

        foreach($_POST as $key => $value){
          $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
        }

        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $tr = $em->getRepository("LoogaresUsuarioBundle:Tag");
        $trr = $em->getRepository("LoogaresUsuarioBundle:TagRecomendacion");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $fn = $this->get('fn');
        $lugar = $lr->findOneBySlug($slug);


        // Flag que determina si la recomendación es nueva o no
        $nueva = true;

        if(!isset($_POST['curlSuperVar'])){
            //Retrasamos el proceso 2 segundos para que no se gatille antes que el resto del agregar lugar
            sleep(2);
          //Revisamos si el usuario tiene ya una recomendacion en este lugar
          $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u where u.usuario = ?1 and u.lugar = ?2 and u.estado = ?3");
          $q->setParameter(2, $lugar->getId())
            ->setParameter(1, $this->get('security.context')->getToken()->getUser()->getId())
            ->setParameter(3, 2);
          $yaRecomendo = $q->getResult();
        }

        if(isset($_POST['editando']) && $_POST['editando'] == 1){
            $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u WHERE u.usuario = ?1 and u.lugar = ?2 and u.estado = 2");
            $q->setParameter(1, $this->get('security.context')->getToken()->getUser()->getId());
            $q->setParameter(2, $lugar->getId());
            $recomendacion = $q->getSingleResult();

            $q = $em->createQuery("DELETE Loogares\UsuarioBundle\Entity\TagRecomendacion u WHERE u.recomendacion = ?1");
            $q->setParameter(1,$recomendacion->getId());
            $q->getResult();

            $nueva = false;
        }else{
            $recomendacion = new Recomendacion();
        }

        //Monkey patching por la recomendacion doble
        if(sizeOf($yaRecomendo) == 0 || $nueva == false || $curlSuperVar == 1 && $request->getMethod() == 'POST'){
            $newTagRecomendacion = array();
            $tag = array();
            $recomendacion->setTexto($_POST['texto']);

            $recomendacion->setEstrellas($_POST['recomienda-estrellas']);
            $estado = $lr->getEstado(2);
            $recomendacion->setEstado($estado);

            if(isset($_POST['recomienda-precio'])){
                $recomendacion->setPrecio($_POST['recomienda-precio']);
            }

            $recomendacion->setLugar($lugar);
            if(isset($_POST['usuario'])){
                $recomendacion->setUsuario($ur->findOneById($_POST['usuario']));
            }else{
                $recomendacion->setUsuario($this->get('security.context')->getToken()->getUser());
            }

            $recomendacion->setFechaCreacion(new \DateTime());
            $recomendacion->setFechaUltimaModificacion(new \DateTime());
            $lugar->setFechaUltimaRecomendacion($recomendacion->getFechaCreacion());


            // Sacamos la recomendación justo anterior a ésta última (para enviar mail)
            $ultimaRecomendacion = $rr->getUltimaRecomendacion($lugar->getId());

            $em->persist($recomendacion);

            if($_POST['tags'] != ''){
                $tags = explode(',', $_POST['tags']);
                for($i=0;$i<sizeOf($tags);$i++){
                    $tags[$i] = trim($tags[$i]);
                }
                $tags = array_unique($tags);

                foreach($tags as $key => $value){
                    $tag[] = $tr->findOneByTag($value);
                    if(!$tag[sizeOf($tag)-1]){
                        $tag[] = new Tag();
                        $tag[sizeOf($tag)-1]->setTag($value);
                        $tag[sizeOf($tag)-1]->setSlug($fn->generarSlug($value));

                        $em->persist($tag[sizeOf($tag)-1]);
                    }

                    $newTagRecomendacion[] = new TagRecomendacion();
                    $newTagRecomendacion[sizeOf($newTagRecomendacion)-1]->setTag($tag[sizeOf($tag)-1]);
                    $newTagRecomendacion[sizeOf($newTagRecomendacion)-1]->setRecomendacion($recomendacion);

                    $em->persist($newTagRecomendacion[sizeOf($newTagRecomendacion)-1]);
                }
            }

            // Se marca la acción 'Ya estuve' del usuario en el lugar
            $ar = $em->getRepository("LoogaresUsuarioBundle:Accion");
            $accionResult = $lr->getAccionUsuarioLugar($lugar, $recomendacion->getUsuario(), "estuve_alla");

            if(!is_object($accionResult)){
                $accionObj = new AccionUsuario();
                $accionObj->setUsuario($recomendacion->getUsuario());
                $accionObj->setAccion($ar->findOneById($accionResult));
                $accionObj->setLugar($lugar);
                $accionObj->setFecha(new \DateTime());
                $em->persist($accionObj);

                // Verificamos estado de 'Quiero ir'
                $quieroIr = $lr->getAccionUsuarioLugar($lugar, $recomendacion->getUsuario(), 'quiero_ir');
                if(is_object($quieroIr)) {
                    $em->remove($quieroIr);
                }
            }

            // Verificamos estado de 'Por Recomendar'
            $porRecomendar = $lr->getAccionUsuarioLugar($lugar, $recomendacion->getUsuario(), 'recomendar_despues');
            if(is_object($porRecomendar)) {
                $em->remove($porRecomendar);
            }

            $em->flush();
            $lr->actualizarPromedios($lugar->getSlug());

            //Agregamos a la actividad reciente
            $actividad = new ActividadReciente();
            $actividad->setEntidad('Loogares\UsuarioBundle\Entity\Recomendacion');
            $actividad->setEntidadId($recomendacion->getId());
            $actividad->setFecha($recomendacion->getFechaCreacion());
            $actividad->setUsuario($recomendacion->getUsuario());
            $actividad->setCiudad($lugar->getComuna()->getCiudad());
            $estadoActividad = $em->getRepository("LoogaresExtraBundle:Estado")
                                  ->findOneByNombre('Aprobado');
            $actividad->setEstado($estadoActividad);

            if($nueva){
                $tipoActividad = $em->getRepository('LoogaresExtraBundle:TipoActividadReciente')
                                    ->findOneByNombre('agregar');
                $actividad->setTipoActividadReciente($tipoActividad);
            }else{
                $tipoActividad = $em->getRepository('LoogaresExtraBundle:TipoActividadReciente')
                                    ->findOneByNombre('editar');
                $actividad->setTipoActividadReciente($tipoActividad);
            }

            $em->persist($actividad);
            $em->flush();

            // Revisamos si el usuario tenía concursos pendientes al momento de recomendar
            $conr = $em->getRepository('LoogaresBlogBundle:Concurso');
            $concursosPendientes = $conr->getConcursosPendientesUsuario($this->get('security.context')->getToken()->getUser(), $lugar->getComuna()->getCiudad());
            foreach($concursosPendientes as $concurso) {
                $concurso->setPendiente(false);
            }
            $em->flush();


            // Se envía mail al lugar
            if($lugar->getMail() != null && $lugar->getMail() != '' && !isset($_POST['editando'])) {
                try{
                    $owner = $lugar->getDueno();
                    $owner->getMail(); //Gatillamos el error aproposito!
                }catch (\Exception $e){
                    $owner = null;
                }

                $mailParam = '';
                if($owner != null)
                    $mailParam = md5($owner->getMail());

                // Extraemos preview de la recomendación
                $preview = '';
                if(strlen($recomendacion->getTexto()) > 300) {
                    $preview = substr($recomendacion->getTexto(),0,300).'...';
                }
                else {
                    $preview = $recomendacion->getTexto();
                }

                // Cálculo de las estrellas de la recomendación
                $estrellas = array();

                $numEstrellas = $recomendacion->getEstrellas() * 2;
                $estrellas['llenas'] = (int)($numEstrellas/2);
                $estrellas['medias'] = 0;
                if($numEstrellas%2 != 0)
                    $estrellas['medias'] = 1;

                $estrellas['vacias'] = 5 - $estrellas['llenas'] - $estrellas['medias'];

                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('lugar.notificaciones.nueva_recomendacion.mail.asunto', array('%lugar%' => $recomendacion->getLugar()->getNombre()));
                $mail['recomendacion'] = $recomendacion;
                $mail['preview'] = $preview;
                $mail['estrellas'] = $estrellas;
                $mail['mailParam'] = $mailParam;
                $mail['usuario'] = $recomendacion->getUsuario();
                $mail['tipo'] = "nueva-recomendacion";

                $paths = array();
                $paths['logo'] = 'assets/images/mails/logo_mails.png';
                $paths['boton'] = 'assets/images/mails/recibir_propuesta.png';
                if($estrellas['llenas'] > 0)
                    $paths['estrella_llena'] =  'assets/images/extras/estrella_llena_recomendacion.png';
                if($estrellas['medias'] > 0)
                    $paths['estrella_media'] =  'assets/images/extras/estrella_media_recomendacion.png';
                if($estrellas['vacias'] > 0)
                    $paths['estrella_vacia'] =  'assets/images/extras/estrella_vacia_recomendacion.png';

                if(!file_exists('assets/images/usuarios/'.$recomendacion->getUsuario()->getImagenFull())){
                    if(!file_exists('assets/images/usuarios/default.gif')) {
                        $this->get('imagine.controller')->filter('assets/images/usuarios/default.gif', "small_usuario");
                    }
                    $paths['usuario'] = 'assets/images/usuarios/default.gif';
                }else{
                    $this->get('imagine.controller')->filter('assets/images/usuarios/'.$recomendacion->getUsuario()->getImagenFull(), "small_usuario");
                    $paths['usuario'] = 'assets/media/cache/small_usuario/assets/images/usuarios/'.$recomendacion->getUsuario()->getImagenFull();
                }

                $mails = preg_split('/,/', $lugar->getMail());
                foreach($mails as $key => $value){
                  $mails[$key] = trim($value);
                }

                $message = $this->get('fn')->enviarMail($mail['asunto'], $mails, 'noreply@loogares.com', $mail, $paths, 'LoogaresLugarBundle:Mails:mail_lugar.html.twig', $this->get('templating'));
                $this->get('mailer')->send($message);
            }

            if(isset($_POST['curlSuperVar']) && $_POST['curlSuperVar'] == 1){
                return new Response('',200);
            }else{
                //Enviamos mail al usuario que recomendó justo antes del actual, si es el caso
                $recomendacionAnterior = $ultimaRecomendacion;
                $usuario = $recomendacion->getUsuario();
                if($recomendacionAnterior != null) {
                    // Existe una recomendación justo anterior
                    $usuarioAnterior = $recomendacionAnterior->getUsuario();
                    $nombreUsuario = ($usuario->getNombre() == '' && $usuario->getApellido() == '') ? $usuario->getSlug() : $usuario->getNombre().' '.$usuario->getApellido();
                if(!isset($_POST['editando'])){
                    $mail = array();
                    $mail['asunto'] = $this->get('translator')->trans('lugar.notificaciones.despues_recomendacion.mail.asunto', array('%usuario%' => $nombreUsuario,'%lugar%' => $recomendacion->getLugar()->getNombre()));
                    $mail['recomendacion'] = $recomendacion;
                    $mail['usuario'] = $usuario;
                    $mail['usuarioAnterior'] = $usuarioAnterior;
                    $mail['tipo'] = "despues-recomendacion";

                    $paths = array();
                    $paths['logo'] = 'assets/images/mails/logo_mails.png';

                    $message = $this->get('fn')->enviarMail($mail['asunto'], $usuarioAnterior->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresLugarBundle:Mails:mail_recomendar.html.twig', $this->get('templating'));
                    $this->get('mailer')->send($message);
                }
            }

            $this->get('session')->setFlash('flash_recomendacion', $_POST['texto']);
            $this->get('session')->setFlash('flash_recomendacion_estrellas', $_POST['recomienda-estrellas']);

            $flash = $this->get('translator')->trans('lugar.flash.recomendacion.agregar', array('%nombre%' => $usuario->getNombre(), '%apellido%' => $usuario->getApellido()));
            if (sizeOf($concursosPendientes) == 1) {
                $post = $concursosPendientes[0]->getConcurso()->getPost();
                $path = $this->get('router')->generate('post', array('ciudad' => $post->getCiudad()->getSlug(), 'slug' => $post->getSlug()));
                $flash .= "<br>".$this->get('translator')->trans('lugar.flash.recomendacion.concurso_singular', array('%link%' => '<a href="'. $path .'">'.$post->getTitulo().'</a>'));
            }
            else if (sizeOf($concursosPendientes) > 1) {
                $flash .= "<br>".$this->get('translator')->trans('lugar.flash.recomendacion.concurso_plural');
            }

            //SET FLASH AND REDIRECTTT
            $this->get('session')->setFlash('lugar_flash', $flash);
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }
        }
    }

    public function accionRecomendacionAction($slug, $borrar){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");
        $aur = $em->getRepository("LoogaresUsuarioBundle:AccionUsuario");

        $lugar = $lr->findOneBySlug($slug);

        if($borrar == true){
            $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u WHERE u.usuario = ?1 and u.lugar = ?2 and u.estado != 3 order by u.id desc");
            $q->setParameter(1, $this->get('security.context')->getToken()->getUser()->getId());
            $q->setParameter(2, $lugar->getId());
            $recomendacionResult = $q->getResult();

            $estado = $lr->getEstado(3);

            foreach($recomendacionResult as $recomendacion){
                $recomendacion->setEstado($estado);
            }
            $em->flush();

            $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u WHERE u.lugar = ?1 and u.estado != 3 ORDER BY u.id desc");
            $q->setMaxResults(1);
            $q->setParameter(1, $lugar->getId());
            $ultimaRecomendacion = $q->getOneOrNullResult();

            if($ultimaRecomendacion){
              $fechaUltimaRecomendacion = $ultimaRecomendacion->getFechaCreacion();
            }else{
              $fechaUltimaRecomendacion = null;
            }

            $lugar->setFechaUltimaRecomendacion($fechaUltimaRecomendacion);

            $em->persist($lugar);
            $em->flush();

            $aur->borrarAccionesUsuario($lugar->getId(), $recomendacion->getUsuario()->getId());
            $ar->actualizarActividadReciente($recomendacionResult[0]->getId(), 'Loogares\UsuarioBundle\Entity\Recomendacion');
            $lr->actualizarPromedios($lugar->getSlug());

            $this->get('session')->setFlash('lugar_flash','Acabas de borrar tu recomendación, prueba escribiendo una nueva.');
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
        }
    }

    public function enviarLugarAction(Request $request, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $formErrors = array();

        $lugar = $lr->findOneBySlug($slug);

        if($lugar == null)
            throw $this->createNotFoundException('El lugar '.$slug. ' no existe.');

        // Si el request es POST, se procesa el envío del mail
        if ($request->getMethod() == 'POST') {
            foreach($_POST as $key => $value){
                $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
            }

            if(!$this->get('security.context')->isGranted('ROLE_USER')) {
                if($request->request->get('nombre') == '')
                $formErrors['nombre'] = "lugar.errors.enviar.nombre";
                if($request->request->get('mail') == '')
                $formErrors['mail'] = "lugar.errors.enviar.mail";
            }

            if($request->request->get('mails') == '')
                $formErrors['mails'] = "lugar.errors.enviar.mails";

            if (sizeof($formErrors) == 0) {
                $usuario = array();
                if(!$this->get('security.context')->isGranted('ROLE_USER')) {
                    $usuario['nombre'] = $request->request->get('nombre');
                    $usuario['mail'] = $request->request->get('mail');
                }
                else {
                    $usuario['nombre'] = $this->get('security.context')->getToken()->getUser()->getNombre().' '.$this->get('security.context')->getToken()->getUser()->getApellido();
                    $usuario['mail'] = $this->get('security.context')->getToken()->getUser()->getMail();
                }

                // Se envía el mail a los destinatarios
                $destinatarios = explode(',',$request->request->get('mails'));
                foreach($destinatarios as $e){
                    $e = trim($e);

                    // Verificar si es un e-mail correcto
                    $mail = array();
                    $mail['asunto'] = $usuario['nombre'].' '.$this->get('translator')->trans('compartir.lugar.mail.asunto');
                    $mail['lugar'] = $lugar;
                    $mail['usuario'] = $usuario;
                    $mail['destinatario'] = $e;
                    $mail['texto'] = $request->request->get('cuerpo');
                    $mail['tipo'] = "lugar";
                    $message = \Swift_Message::newInstance()
                            ->setSubject($mail['asunto'])
                            ->setFrom($usuario['mail'])
                            ->setTo($e);
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                    $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_enviar.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                    $this->get('mailer')->send($message);
                }

                // Mensaje de éxito en el envío
                $this->get('session')->setFlash('lugar_flash','lugar.flash.compartir.mail');

                // Redirección a vista de ficha del lugar
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }
        }

        $tipo = 'lugar';

        return $this->render('LoogaresLugarBundle:Lugares:enviar.html.twig', array(
            'lugar' => $lugar,
            'errors' => $formErrors,
            'tipo' => $tipo,
        ));
    }

    public function enviarRecomendacionAction(Request $request, $slug, $usuarioSlug) {
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

        // Si el request es POST, se procesa el envío del mail
        if ($request->getMethod() == 'POST') {
            foreach($_POST as $key => $value){
                $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
            }

            if(!$this->get('security.context')->isGranted('ROLE_USER')) {
                if($request->request->get('nombre') == '')
                $formErrors['nombre'] = "lugar.errors.enviar.nombre";
                if($request->request->get('mail') == '')
                $formErrors['mail'] = "lugar.errors.enviar.mail";
            }

            if($request->request->get('mails') == '')
                $formErrors['mails'] = "lugar.errors.enviar.mails";

            if (sizeof($formErrors) == 0) {
                $usuario = array();
                if(!$this->get('security.context')->isGranted('ROLE_USER')) {
                    $usuario['nombre'] = $request->request->get('nombre');
                    $usuario['mail'] = $request->request->get('mail');
                }
                else {
                    $usuario['nombre'] = $this->get('security.context')->getToken()->getUser()->getNombre().' '.$this->get('security.context')->getToken()->getUser()->getApellido();
                    $usuario['mail'] = $this->get('security.context')->getToken()->getUser()->getMail();
                }

                // Se envía el mail a los destinatarios
                $destinatarios = explode(',',$request->request->get('mails'));
                foreach($destinatarios as $e){
                    $e = trim($e);

                    // Verificar si es un e-mail correcto
                    $mail = array();
                    $mail['asunto'] = $usuario['nombre'].' '.$this->get('translator')->trans('compartir.recomendacion.mail.asunto');
                    $mail['recomendacion'] = $recomendacion;
                    $mail['lugar'] = $recomendacion->getLugar();
                    $mail['usuario'] = $usuario;
                    $mail['destinatario'] = $e;
                    $mail['texto'] = $request->request->get('cuerpo');
                    $mail['tipo'] = 'recomendacion';
                    $message = \Swift_Message::newInstance()
                            ->setSubject($mail['asunto'])
                            ->setFrom($usuario['mail'])
                            ->setTo($e);
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                    $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_enviar.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                    $this->get('mailer')->send($message);
                }

                // Mensaje de éxito en el envío
                $this->get('session')->setFlash('lugar_flash','recomendacion.flash.compartir.mail');

                // Redirección a vista de ficha del lugar
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }
        }

        $tipo = 'recomendacion';

        return $this->render('LoogaresLugarBundle:Lugares:enviar.html.twig', array(
            'recomendacion' => $recomendacion,
            'lugar' => $lugar,
            'errors' => $formErrors,
            'tipo' => $tipo,
        ));
    }

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
            $this->get('session')->setFlash('lugar_flash','No puedes reportar una imagen agregada por ti.');
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $slug)));
        }
        else {

            $reportes = $lr->getReportesImagenesUsuarioLugar($imagen->getId(), $this->get('security.context')->getToken()->getUser(), 1);
            if(sizeof($reportes) > 0) {
                $this->get('session')->setFlash('error_flash', 'Ya has reportado esta imagen anteriormente, y aún está en revisión. <br/>Una vez finalizado este proceso, podrás reportar la imagen nuevamente.');
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $slug)));
            }
        }

        $reporte = new ReportarImagen();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            foreach($_POST as $key => $value){
                $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
            }

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
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('imagen_flash','reportes.flash');

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
          $this->get('session')->setFlash('error_flash', 'No puedes reportar una recomendación hecha por ti.');
          return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
        }else{
            $reportes = $rr->getReportesRecomendacionUsuario($recomendacion->getId(), $this->get('security.context')->getToken()->getUser(), 1);
            if(sizeof($reportes) > 0){
              $this->get('session')->setFlash('error_flash', 'Ya has reportado esta recomendación anteriormente, y aún está en revisión. <br/>Una vez finalizado este proceso, podrás reportar la recomendación nuevamente.');
              return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }
        }

        $reporte = new ReportarRecomendacion();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            foreach($_POST as $key => $value){
                $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
            }

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
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('lugar_flash','reportes.flash');

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

        if(sizeof($reportes) > 0){
            $this->get('session')->setFlash('lugar_flash','Nos has notificado anteriormente que este lugar cerró. Aún estamos corroborando datos al respecto.');
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
        }else{
            $reporte = new ReportarLugar();

            $form = $this->createFormBuilder($reporte)
                             ->add('reporte', 'textarea')
                             ->getForm();

            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);

                foreach($_POST as $key => $value){
                    $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
                }

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
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                    $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                    $this->get('mailer')->send($message);

                     // Mensaje de éxito del reporte
                    $this->get('session')->setFlash('lugar_flash','reportes.flash');

                    // Redirección a ficha del lugar
                    return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
                }
                else {
                    foreach($this->get('validator')->validate( $form ) as $formError){
                        $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
                    }
                }

            }
        }
        return $this->render('LoogaresLugarBundle:Lugares:reporte.html.twig', array(
            'lugar' => $lugar,
            'form' => $form->createView(),
            'errors' => $formErrors,
        ));
    }

    public function reclamarLugarAction(Request $request, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $formErrors = array();

        $lugar = $lr->findOneBySlug($slug);

        if($lugar == null)
            throw $this->createNotFoundException('El lugar con slug '.$slug. ' no existe.');

        // Si lugar ya tiene dueño, se redirecciona a ficha
        if($lugar->getDuenoId() > 0) {
            foreach($_POST as $key => $value){
                $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
            }
            // Request proviene desde E-mail del dueño
            if($request->query->get('mail')) {
                // Buscamos el dueño del lugar, y enviamos mail a administradores con datos de lugar
                $owner = $em->getRepository("LoogaresUsuarioBundle:Usuario")->getDuenoLugar($lugar->getId());

                // Comprobamos que el parámetro mail corresponde con el del dueño (seguridad)
                if($request->query->get('mail') == md5($owner->getMail())) {
                    $mail = array();
                    $mail['asunto'] = $this->get('translator')->trans('dueno.reclamar.mail.asunto').' '.$lugar->getNombre();
                    $mail['owner'] = $owner;
                    $mail['tipo'] = "owner";
                    $message = \Swift_Message::newInstance()
                            ->setSubject($mail['asunto'])
                            ->setFrom('noreply@loogares.com')
                            ->setTo('duenos.local@loogares.com');
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                    $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                    $this->get('mailer')->send($message);

                    $this->get('session')->setFlash('lugar_flash','dueno.reclamar.flash.nuevo');
                }
            }
            // Request proviene desde la URL (a mano)
            else {
                $this->get('session')->setFlash('lugar_flash','dueno.reclamar.flash.existe');
            }

            // Redirección a vista de ficha del lugar
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
        }

        $owner = new Dueno();
        $form = $this->createFormBuilder($owner)
                     ->add('nombre', 'text')
                     ->add('apellido', 'text')
                     ->add('mail', 'text')
                     ->add('telefono', 'text')
                     ->add('texto', 'textarea')
                     ->getForm();

        // Si el request es POST, se procesa el envío del mail
        if ($request->getMethod() == 'POST') {
            foreach($_POST as $key => $value){
                $_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
            }

            $form->bindRequest($request);

            if ($form->isValid()) {
                // Dueño llenó formulario. Se crea un nuevo registro (en estado de revisión)
                $estadoDueno = $em->getRepository("LoogaresExtraBundle:Estado")
                                    ->findOneByNombre('Por revisar');
                $owner->setEstado($estadoDueno);
                $owner->setLugar($lugar);
                $owner->setFecha(new \DateTime());

                // Actualizamos el registro del lugar
                $lugar->setDuenoId(1);

                $em->persist($owner);
                $em->flush();

                // Se envía mail a administradores informando del asunto
                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('dueno.reclamar.mail.asunto').' '.$lugar->getNombre();
                $mail['owner'] = $owner;
                $mail['tipo'] = "owner";
                $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo('duenos.local@loogares.com');
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);

                // Mensaje de éxito en el envío
                $this->get('session')->setFlash('lugar_flash','dueno.reclamar.flash.nuevo');

                // Redirección a vista de ficha del lugar
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }
            else {
                foreach($this->get('validator')->validate( $form ) as $formError){
                    $formErrors[substr($formError->getPropertyPath(), 5)] = $formError->getMessage();
                }
            }
        }

        return $this->render('LoogaresLugarBundle:Lugares:reclamar_lugar.html.twig', array(
            'lugar' => $lugar,
            'form' => $form->createView(),
            'errors' => $formErrors
        ));
    }

    public function pedidosLugarAction($slug, $tipo) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);

        $tipoPedido = 1;
        if($tipo == 'pedidos')
            $tipoPedido = 2;

        $pedidos = $lr->getPedidosLugar($lugar, $tipoPedido);

        return $this->render('LoogaresLugarBundle:Ajax:pedidos_popup.html.twig', array(
            'lugar' => $lugar,
            'pedidos' => $pedidos,
        ));
    }

    public function moduloDescuentosAction($ciudad, $pagina) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $promociones = $lr->getPedidosRandom($ciudad);

        // Sort Random
        shuffle($promociones);
        $promocionesRandom = array();
        if(count($promociones) > 0) {
            $promocionesRandom[] = $promociones[0];
            if(count($promociones) > 1)
                $promocionesRandom[] = $promociones[1];
            if(count($promociones) >= 2)
                $promocionesRandom[] = $promociones[2];
        }

        $template = '';
        if($pagina == 'lugar') {
            $template = 'LoogaresLugarBundle:Lugares:promocion_pedidos.html.twig';
        }
        else if($pagina == 'home') {
            $template = 'LoogaresExtraBundle:Default:promocion_pedidos.html.twig';
        }
        return $this->render($template, array(
            'promociones' => $promocionesRandom,
        ));
    }

    public function reporteLocalAction(Request $request, $slug, $id) {
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresBlogBundle:Concurso");
        $dr = $em->getRepository("LoogaresUsuarioBundle:Dueno");
        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $formErrors = array();

        $lugar = $lr->findOneBySlug($slug);
        $dueno = $lugar->getDueno();

        if($request->getMethod() == 'POST') {
            if(!$request->request->get('reporte')) {
                if($request->request->get('password') == '') {
                    $formErrors['blanco'] = "Debes especificar un password";
                }
                else if(sha1($request->request->get('password')) != $lugar->getDueno()->getPassword()) {
                    $formErrors['password'] = "El password no es válido";
                }

                if(sizeOf($formErrors) == 0) {
                    // Está todo bien, redireccionamos a reporte correspondiente
                    $concurso = $cr->find($id);

                    // Obtenemos ganadores si existen
                    $ganadores = $cr->getGanadoresConcurso($concurso);

                    // Asociamos a cada ganador si el usuario ha recomendado con anterioridad o no
                    foreach($ganadores as $ganador) {
                        $usuario = $ganador->getParticipante()->getUsuario();
                        $lugar = $ganador->getParticipante()->getConcurso()->getPost()->getLugar();
                        $recomendacion = $rr->findOneBy(array('usuario' => $usuario->getId(), 'lugar' => $lugar->getId()));
                        if(!$recomendacion) {
                            $ganador->recomendo = false;
                        }
                        else {
                            $ganador->recomendo = true;
                            $ganador->recomendacion = $recomendacion;
                        }
                    }
                    $concurso->ganadores = $ganadores;

                    return $this->render('LoogaresLugarBundle:Lugares:reporte_local.html.twig', array(
                        'concurso' => $concurso,
                        'dueno' => $dueno
                    ));
                }
            }
            else {

            }
        }

        return $this->render('LoogaresLugarBundle:Lugares:reporte_autenticacion.html.twig', array(
            'lugar' => $lugar,
            'id' => $id,
            'errors' => $formErrors
        ));
    }
}
