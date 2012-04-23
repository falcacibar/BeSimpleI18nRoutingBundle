<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

use Loogares\LugarBundle\Entity\Promocion;
use Loogares\LugarBundle\Entity\PedidoLugar;

use Loogares\BlogBundle\Entity\Posts;
use Loogares\BlogBundle\Entity\Categoria;
use Loogares\BlogBundle\Entity\EstadoConcurso;
use Loogares\UsuarioBundle\Entity\LoogarenoEstrella;

class AdminController extends Controller
{
    public function indexAction(){
        
        return $this->render('LoogaresAdminBundle:Admin:index.html.twig');
    }

    public function seleccionarPaisAction(Request $request){
        if($request->getMethod() == 'POST'){
            return $this->redirect($this->generateUrl('LoogaresAdminBundle_administrarLugares', array(
                'ciudad' => $_POST['ciudad']
            )));
        }

        $em = $this->getDoctrine()->getEntityManager();

        $q = $em->createQuery('SELECT u from Loogares\ExtraBundle\Entity\Pais u where u.mostrar_lugar = 1 or u.mostrar_lugar = 3');
        $paisesResult = $q->getResult();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u where u.mostrar_lugar = 1 or u.mostrar_lugar = 3");
        $ciudadesResult = $q->getResult();

        return $this->render('LoogaresAdminBundle:Admin:seleccionPais.html.twig', array(
            'paises' => $paisesResult,
            'ciudades' => $ciudadesResult
        ));
    }   

    public function administrarLugaresAction($ciudad){
        $em = $this->getDoctrine()->getEntityManager();

        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $ciudad = $cr->findOneBySlug($ciudad);
        $idCiudad = $ciudad;
    
        //Total Lugares por $ciudad
        $q = $em->createQuery("SELECT count(u) 
                                FROM Loogares\LugarBundle\Entity\Lugar u 
                                LEFT JOIN u.comuna c
                                where c.ciudad = ?1");
        $q->setParameter(1, $idCiudad);
        $totalLugaresResult = $q->getSingleScalarResult();

        //Total Lugares por $ciudad reportados
        $q = $em->createQuery("SELECT count(u) 
                                FROM Loogares\LugarBundle\Entity\Lugar u 
                                LEFT JOIN u.comuna c
                                where c.ciudad = ?1
                                and u.estado = ?2");
        $q->setParameter(1, $idCiudad);
        $q->setParameter(2, 5);
        $totalLugaresReportadosResult = $q->getSingleScalarResult();

        //Total Lugares por $ciudad sin revisar
        $q = $em->createQuery("SELECT count(u) 
                                FROM Loogares\LugarBundle\Entity\Lugar u 
                                LEFT JOIN u.comuna c
                                where c.ciudad = ?1
                                and u.estado = ?2");
        $q->setParameter(1, $idCiudad);
        $q->setParameter(2, 1);
        $totalLugaresPorRevisarResult = $q->getSingleScalarResult();

        //Total Lugares por $ciudad sin revisar
        $q = $em->createQuery("SELECT count(u) 
                                FROM Loogares\LugarBundle\Entity\Lugar u 
                                LEFT JOIN u.comuna c
                                where c.ciudad = ?1
                                and u.estado = ?2");
        $q->setParameter(1, $idCiudad);
        $q->setParameter(2, 3);
        $totalLugaresEliminadosResult = $q->getSingleScalarResult();

        //Total Lugares por $ciudad sin revisar
        $q = $em->createQuery("SELECT count(u) 
                                FROM Loogares\LugarBundle\Entity\Lugar u 
                                LEFT JOIN u.comuna c
                                where c.ciudad = ?1
                                and u.estado = ?2");
        $q->setParameter(1, $idCiudad);
        $q->setParameter(2, 4);
        $totalLugaresCerradosResult = $q->getSingleScalarResult();

        //Total de lugares con revision por $ciudad
        $q = $em->createQuery("SELECT count(distinct tl.lugar)
                             FROM Loogares\AdminBundle\Entity\TempLugar tl
                             LEFT JOIN tl.comuna c
                             WHERE tl.estado = ?1 and c.ciudad = ?2");
        $q->setParameter(1, 1);
        $q->setParameter(2, $idCiudad);
        $totalLugaresConRevisionResult = $q->getSingleScalarResult();
        
        //Total de fotos por $ciudad
        $q = $em->createQuery("SELECT count(il)
                             FROM Loogares\LugarBundle\Entity\ImagenLugar il
                             LEFT JOIN il.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1");
        $q->setParameter(1, $idCiudad);
        $totalFotosResult = $q->getSingleScalarResult();

        //Total de fotos reportadas por $ciudad
        $q = $em->createQuery("SELECT count(il)
                             FROM Loogares\LugarBundle\Entity\ImagenLugar il
                             LEFT JOIN il.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and il.estado = ?2");
        $q->setParameter(1, $idCiudad);
        $q->setParameter(2, 5);
        $totalFotosReportadasResult = $q->getSingleScalarResult();

        //Total de fotos por revisar por $ciudad
        $q = $em->createQuery("SELECT count(il)
                             FROM Loogares\LugarBundle\Entity\ImagenLugar il
                             LEFT JOIN il.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and il.estado = ?2");
        $q->setParameter(1, $idCiudad);
        $q->setParameter(2, 1);
        $totalFotosPorRevisarResult = $q->getSingleScalarResult();

        //Total de fotos eliminadas por $ciudad
        $q = $em->createQuery("SELECT count(il)
                             FROM Loogares\LugarBundle\Entity\ImagenLugar il
                             LEFT JOIN il.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and il.estado = ?2");
        $q->setParameter(1, $idCiudad);
        $q->setParameter(2, 3);
        $totalFotosEliminadasResult = $q->getSingleScalarResult();

        //Total recomendaciones por $ciudad
        $q = $em->createQuery("SELECT count(r)
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             LEFT JOIN r.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1");
        $q->setParameter(1, $idCiudad);
        $totalRecomendacionesResult = $q->getSingleScalarResult();

        //Total recomendaciones aprobadas por $ciudad
        $q = $em->createQuery("SELECT count(r)
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             LEFT JOIN r.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and r.estado = 2");
        $q->setParameter(1, $idCiudad);
        $totalRecomendacionesAprobadasResult = $q->getSingleScalarResult();
    
        //Total recomendaciones reportadas por $ciudad
        $q = $em->createQuery("SELECT count(r)
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             LEFT JOIN r.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and r.estado = 5");
        $q->setParameter(1, $idCiudad);
        $totalRecomendacionesReportadasResult = $q->getSingleScalarResult();

        //Total recomendaciones eliminadas por $ciudad
        $q = $em->createQuery("SELECT count(r)
                             FROM Loogares\UsuarioBundle\Entity\Recomendacion r
                             LEFT JOIN r.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and r.estado = 3");
        $q->setParameter(1, $idCiudad);
        $totalRecomendacionesEliminadasResult = $q->getSingleScalarResult();

        //Total pedidos por $ciudad
        $q = $em->createQuery("SELECT count(pl)
                             FROM Loogares\LugarBundle\Entity\PedidoLugar pl
                             LEFT JOIN pl.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1");
        $q->setParameter(1, $idCiudad);
        $totalPedidosResult = $q->getSingleScalarResult();

        //Total posts por $ciudad
        $q = $em->createQuery("SELECT count(p)
                             FROM Loogares\BlogBundle\Entity\Posts p
                             LEFT JOIN p.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1");
        $q->setParameter(1, $idCiudad);
        $totalPostsResult = $q->getSingleScalarResult();

        //Total posts publicados por $ciudad
        $q = $em->createQuery("SELECT count(p)
                             FROM Loogares\BlogBundle\Entity\Posts p
                             LEFT JOIN p.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and p.blog_estado = 2");
        $q->setParameter(1, $idCiudad);
        $totalPostsPublicadosResult = $q->getSingleScalarResult();

        //Total posts borradores por $ciudad
        $q = $em->createQuery("SELECT count(p)
                             FROM Loogares\BlogBundle\Entity\Posts p
                             LEFT JOIN p.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and p.blog_estado = 1");
        $q->setParameter(1, $idCiudad);
        $totalPostsBorradoresResult = $q->getSingleScalarResult();

        //Total posts por $ciudad
        $q = $em->createQuery("SELECT count(p)
                             FROM Loogares\BlogBundle\Entity\Posts p
                             LEFT JOIN p.lugar l
                             LEFT JOIN l.comuna c
                             WHERE c.ciudad = ?1 and p.blog_estado = 3");
        $q->setParameter(1, $idCiudad);
        $totalPostsAgendadosResult = $q->getSingleScalarResult();


        return $this->render('LoogaresAdminBundle:Admin:administrarLugares.html.twig', array(
            'totalLugares' => $totalLugaresResult,
            'totalLugaresPorRevisar' => $totalLugaresPorRevisarResult,
            'totalLugaresReportados' => $totalLugaresReportadosResult,
            'totalLugaresConRevision' => $totalLugaresConRevisionResult,
            'totalLugaresEliminados' => $totalLugaresEliminadosResult,
            'totalLugaresCerrados' => $totalLugaresCerradosResult,
            'totalFotos' => $totalFotosResult,
            'totalFotosReportadas' => $totalFotosReportadasResult,
            'totalFotosPorRevisar' => $totalFotosPorRevisarResult,
            'totalFotosEliminadas' => $totalFotosEliminadasResult,
            'totalRecomendaciones' => $totalRecomendacionesResult,
            'totalRecomendacionesAprobadas' => $totalRecomendacionesAprobadasResult,
            'totalRecomendacionesReportadas' => $totalRecomendacionesReportadasResult,
            'totalRecomendacionesEliminadas' => $totalRecomendacionesEliminadasResult,
            'totalPedidos' => $totalPedidosResult,
            'totalPosts' => $totalPostsResult,
            'totalPostsPublicados' => $totalPostsPublicadosResult,
            'totalPostsBorradores' => $totalPostsBorradoresResult,
            'totalPostsAgendados' => $totalPostsAgendadosResult,
            'ciudad' => $ciudad
        ));
    }

    public function listadoLugaresAction(Request $request, $ciudad){
        $router = $this->get('router');
        $fn = $this->get('fn');
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $ciudad = $cr->findOneBySlug($ciudad);
        $ciudadNombre = $ciudad->getNombre();
        $ciudadId = $ciudad->getId();
        $ciudadSlug = $ciudad->getSlug();
        $order = false;
        $like = false;
        $where = "WHERE ciudad.id = $ciudadId";

        $filters = array(
            'pusuario' => 'usuarios.slug',
            'pcategoria' => 'categorias',
            'psubcategoria' => 'subcategorias',
            'pcalle' => 'calle',
            'pcomuna' => 'comuna.nombre',
            'psector' => 'sector.nombre',
            'pestrellas' => 'estrellas',
            'putiles' => 'utiles', 
            'pprecio' => 'precio',
            'pcaracteristica' => 'caracteristicas',
            'pwww' => 'sitio_web',
            'pfacebook' => 'facebook',
            'ptwitter' => 'twitter',
            'pmail' => 'lugares.mail',
            'pestado' => 'estado',
            'precomendaciones' => 'recomendaciones',
            'pimagenes' => 'imagenes'
        );

        $listadoFilters = array(
            'id' => 'lugares.id', 
            'nombre' => 'lugares.nombre',
            'usuario' => 'usuarios.slug',
            'categoria' => 'categorias',
            'subcategoria' => 'subcategorias',
            'calle' => 'lugares.calle',
            'detalle' => 'lugares.detalle',
            'comuna' => 'comuna.nombre',
            'sector' => 'sector.nombre',
            'estrellas' => 'lugares.estrellas',
            'utiles' => 'utiles',
            'precio' => 'lugares.precio',
            'ranking' => '',
            'caracteristica' => 'caracteristicas',
            'www' => 'lugares.sitio_web',
            'facebook' => 'lugares.facebook',
            'twitter' => 'lugares.twitter',
            'mail' => 'lugares.mail',
            'estado' => 'estado',
            'recomendaciones' => 'recomendaciones',
            'imagenes' => 'imagenes'
        );

        if(isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
            if(preg_match('/[A-Za-z]+/', $buscar) == false){
                $where .= " and lugares.id = '$buscar'";
            }else{
                $where .= " and lugares.nombre LIKE '%$buscar%'";
            }
        }

        if(isset($_GET['fecha-desde']) && isset($_GET['fecha-hasta'])){
            $hasta = preg_replace('/-/', '/',$_GET['fecha-hasta']);
            $hasta = explode('/', $hasta);
            $hasta = $hasta[2] . "/" . $hasta[1] . "/" . $hasta[0];

            $desde = preg_replace('/-/', '/',$_GET['fecha-desde']);
            $desde = explode('/', $desde);
            $desde = $desde[2] . "/" . $desde[1] . "/" . $desde[0];

            $where .= " and fecha_agregado between '$desde' and '$hasta'";
        }

        foreach($_GET as $column => $filter){
            if(!$like && isset($filters[$column])){
                    $like = "HAVING ".$filters[$column]." LIKE '%$filter%'";
            }
             if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order = "ORDER BY ".$listadoFilters[$column]." $filter";
                }else{
                    $order .= ", $listadoFilters[$column] $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*30);

        $ih8doctrine = $this->getDoctrine()->getConnection()
        ->fetchAll("SELECT straight_join SQL_CALC_FOUND_ROWS 
                    lugares.*, 
                    usuarios.slug as usuarioSlug, 
                    comuna.nombre as comunaNombre, 
                    (select sector.nombre from sector where lugares.sector_id = sector.id) as sectorNombre,
                    (select estado.nombre from estado where estado.id = lugares.estado_id) as estado,
                    cast(AVG(recomendacion.estrellas) as signed) as estrellas, 
                    count(distinct recomendacion.id) as recomendaciones,
                    (select count(util.id) from util where util.recomendacion_id = recomendacion.id) as utiles, 
                    (select count(imagenes_lugar.id) from imagenes_lugar where lugares.id = imagenes_lugar.lugar_id) as imagenes, 
                    group_concat(distinct categorias.nombre) as categorias, 
                    group_concat(distinct subcategoria.nombre) as subcategorias, 
                    group_concat(distinct caracteristica.nombre) as caracteristicas FROM lugares left join usuarios on usuarios.id = lugares.usuario_id 

                    left join comuna 
                    on comuna.id = lugares.comuna_id 

                    left join ciudad 
                    on comuna.ciudad_id = ciudad.id 

                    left join recomendacion 
                    on recomendacion.lugar_id = lugares.id 

                    left join categoria_lugar 
                    on categoria_lugar.lugar_id = lugares.id 

                    left join categorias 
                    on categorias.id = categoria_lugar.categoria_id 

                    left join subcategoria_lugar 
                    on subcategoria_lugar.lugar_id = lugares.id 

                    left join subcategoria 
                    on subcategoria.categoria_id = categorias.id and subcategoria.id = subcategoria_lugar.subcategoria_id 
                    left join caracteristica_lugar 
                    on caracteristica_lugar.lugar_id = lugares.id 

                    left join caracteristica 
                    on caracteristica.id = caracteristica_lugar.caracteristica_id 

                    $where
                    GROUP BY lugares.id
                    $like
                    $order
                    LIMIT 30
                    OFFSET $offset");

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        for($i = 0; $i < sizeOf($ih8doctrine); $i++){
            $ih8doctrine[$i]['caracteristicas'] = explode(',', $ih8doctrine[$i]['caracteristicas']);
            $ih8doctrine[$i]['categorias'] = explode(',', $ih8doctrine[$i]['categorias']);
            $ih8doctrine[$i]['subcategorias'] = explode(',', $ih8doctrine[$i]['subcategorias']);
        }

        $params = array(
            'ciudad' => $ciudadSlug
        );

        $options = array(
            'izq' => 5,
            'der' => 5
        );

        $paginacion = $fn->paginacion($resultSetSize[0]['rows'], 30, 'LoogaresAdminBundle_listadoLugares', $params, $router, $options);

        return $this->render('LoogaresAdminBundle:Admin:listadoLugares.html.twig', array(
            'lugares' => $ih8doctrine, 
            'filters' => $filters,
            'query' => $_GET,
            'paginacion' => $paginacion,
            'ciudad' => $ciudad,
            'type' => 'listadoLugares-lugares'
        ));
    }

    public function listadoRevisionAction($ciudad){
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository('LoogaresExtraBundle:Ciudad');
        $ciudad = $cr->findOneBySlug($ciudad);
        $ciudadNombre = $ciudad->getNombre();
        $ciudadId = $ciudad->getId();
        $ciudadSlug = $ciudad->getSlug();

        $ih8doctrine = $this->getDoctrine()->getConnection()
        ->fetchAll("select distinct lugares.*, sector.nombre as sector, ciudad.nombre as ciudad, comuna.nombre as comuna, count(lugares.id) as revisiones

                    from lugares

                    left join temp_lugares
                    on lugares.id = temp_lugares.lugar_id

                    left join comuna
                    on lugares.comuna_id = comuna.id

                    left join sector
                    on lugares.sector_id = sector.id

                    left join ciudad
                    on comuna.ciudad_id = ciudad.id

                    where lugares.id = temp_lugares.lugar_id and temp_lugares.estado_id = 1
                    and ciudad.id = $ciudadId
                    group by lugares.id");

        return $this->render('LoogaresAdminBundle:Admin:listadoRevision.html.twig',array(
            'lugares' => $ih8doctrine,
            'ciudad' => $ciudad,
            'type' => 'listado-lugares-revision'
        ));        
    }

    public function revisionLugaresAction($slug, $ciudad){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);
        $tempLugares = $lr->getLugaresPorRevisar($lugar->getId(), 1);
        return $this->render('LoogaresAdminBundle:Admin:revisionLugar.html.twig', array(
            'lugares' => $tempLugares,
            'ciudad' => $ciudad
        ));
    }

    public function accionLugarAction($ciudad, $id, $cerrar = false, $borrar = false, $habilitar = false, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");

        if($request->getMethod() == 'POST'){
            $vars = $_POST['id'];
            if($_POST['accion'] == 'aprobar'){
                $habilitar = true;
            }else if($_POST['accion'] == 'eliminar'){
                $borrar = true;
            }else if($_POST['accion'] == 'cerrar'){
                $cerrar = true;
            }
        }else{
            $vars = $id;
        }

        if(is_array($vars)){
            $itemsABorrar = $vars;
        }else{
            $itemsABorrar[] = $vars;
        }

        foreach($itemsABorrar as $item){  
            $lugar = $lr->find($item);
            $mail = array();
            $mail['lugar'] = $lugar;
            $mail['usuario'] = $lugar->getUsuario();

            if($borrar == true){
                $estado = $lr->getEstado(3);
                // Lugar rechazado
                if($lugar->getEstado()->getId() == 1) {
                    $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.lugar.rechazar.asunto');
                    $mail['tipo'] = "rechazar";
                }
                // Lugar eliminado
                else {
                    $mail['asunto'] = $lugar->getNombre().' '.$this->get('translator')->trans('admin.notificaciones.lugar.borrar.asunto');                
                    $mail['tipo'] = "borrar";
                }               

            }else if($cerrar == true){
                $estado = $lr->getEstado(4);                
                $mail['asunto'] = $lugar->getNombre().' '.$this->get('translator')->trans('admin.notificaciones.lugar.cerrar.asunto');
                $mail['tipo'] = "cerrar";                

            }else if($habilitar == true){
                $estado = $lr->getEstado(2);
                $mail['asunto'] = $lugar->getNombre().' '.$this->get('translator')->trans('admin.notificaciones.lugar.aprobar.asunto');
                $mail['tipo'] = "aprobar";    
            }

            // Se envía mail a usuario que agregó el lugar
            $paths = array();
            $paths['logo'] = 'assets/images/mails/logo_mails.png';

            $message = $this->get('fn')->enviarMail($mail['asunto'], $mail['usuario']->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresAdminBundle:Mails:mail_accion_lugar.html.twig', $this->get('templating'));
            $this->get('mailer')->send($message);

            $lugar->setEstado($estado);
            
            $ar->actualizarActividadReciente($lugar->getId(), 'Loogares\LugarBundle\Entity\Lugar');

            $em->persist($lugar);
        }

        $em->flush();

        $args = array(
            'ciudad' => $ciudad
        );

        $args = array_merge($args, $_GET);
        unset($args['id']);

        return $this->redirect($this->generateUrl('LoogaresAdminBundle_listadoLugares', $args));
    }

    public function listadoUsuariosAction(Request $request) {
        $where = null;
        $like = null;
        $order = null;
        $offset = 0;
        $fn = $this->get('fn');

        $filters = array(
            'pnombre' => 'usuarios.nombre',
            'papellido' => 'usuarios.apellido',
            'pslug' => 'usuarios.slug',
            'pmail' => 'usuarios.mail',
            'psexo' => 'usuarios.sexo',
            'pfecha_nacimiento' => 'usuarios.fecha_nacimiento',
            'pestado' => 'estado.nombre',
            'ptipo_usuario' => 'tipo_usuario.nombre',
            'pwww' => 'usuarios.web',
            'ptwitter' => 'usuarios.twitter',
            'pfacebook' => 'usuarios.facebook',
            'pcomuna' => 'comuna.nombre'
        );

        $listadoFilters = array(
            'id' => 'usuarios.id', 
            'nombre' => 'usuarios.nombre',
            'apellido' => 'usuarios.apellido',
            'mail' => 'usuarios.mail',
            'slug' => 'usuarios.slug',
            'sexo' => 'usuarios.sexo',
            'fecha_nacimiento' => 'usuarios.fecha_nacimiento',
            'estado' => 'estado.nombre',
            'tipo_usuario' => 'tipo_usuario.nombre',
            'imagenes' => 'imagenes',
            'recomendaciones' => 'recomendaciones',
            'lugares' => 'lugares',
            'utiles' => 'utiles',
            'fecha_registro' => 'usuarios.fecha_registro',
            'comuna' => 'comuna.nombre'
        );

        if(isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
            if(preg_match('/[A-Za-z]+/', $buscar) == false){
                $where = "WHERE usuarios.id = '$buscar'";
            }else{
                $where = "WHERE usuarios.nombre LIKE '%$buscar%' or usuarios.apellido LIKE '%$buscar%' or usuarios.slug LIKE '%$buscar%'";
            }
        }

        if(isset($_GET['fecha-desde']) && isset($_GET['fecha-hasta']) && isset($_GET['tipo-fecha'])){
            $hasta = preg_replace('/-/', '/',$_GET['fecha-hasta']);
            $hasta = explode('/', $hasta);
            $hasta = $hasta[2] . "/" . $hasta[1] . "/" . $hasta[0];

            $desde = preg_replace('/-/', '/',$_GET['fecha-desde']);
            $desde = explode('/', $desde);
            $desde = $desde[2] . "/" . $desde[1] . "/" . $desde[0];

            $tipo = $_GET['tipo-fecha'];

            if(!$where){
                $where = "WHERE usuarios.$tipo between '$desde' and '$hasta'";
            }else{
                $where = " and usuarios.$tipo between '$desde' and '$hasta'";
            }
        }

        foreach($_GET as $column => $filter){
            if(!$like && isset($filters[$column])){
                    $like = "HAVING ".$filters[$column]." LIKE '%$filter%'";
            }
             if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order = "ORDER BY ".$listadoFilters[$column]." $filter";
                }else{
                    $order .= ", $listadoFilters[$column] $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*30);

        $em = $this->getDoctrine()->getEntityManager();
        $usuarios = $this->getDoctrine()->getConnection()
        ->fetchAll("select SQL_CALC_FOUND_ROWS usuarios.*, 
                    (select count(distinct imagenes_lugar.id) from imagenes_lugar where usuarios.id = imagenes_lugar.usuario_id and imagenes_lugar.estado_id != 5) as imagenes,
                    (select count(distinct lugares.id) from lugares where usuarios.id = lugares.usuario_id) as lugares,
                    (select count(distinct recomendacion.id) from recomendacion where usuarios.id = recomendacion.usuario_id) as recomendaciones,
                    (select count(distinct util.id) from util where usuarios.id = util.usuario_id) as utiles,
                    estado.nombre as estadoNombre,
                    comuna.nombre as comunaNombre,
                    tipo_usuario.descripcion as tipoUsuarioNombre
                                         
                    from usuarios
                                        
                    left join estado
                    on estado.id = usuarios.estado_id

                    left join comuna
                    on comuna.id = usuarios.comuna_id

                    left join tipo_usuario
                    on usuarios.tipo_usuario_id = tipo_usuario.id   

                    $where
                    GROUP BY usuarios.id
                    $like
                    $order
                    LIMIT 30
                    OFFSET $offset");

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        $params = array();

        $options = array(
            'izq' => 5,
            'der' => 5
        );

        $paginacion = $fn->paginacion($resultSetSize[0]['rows'], 30, 'LoogaresAdminBundle_listadoUsuarios', $params, $this->get('router'), $options);

        return $this->render('LoogaresAdminBundle:Admin:listadoUsuarios.html.twig', array(
            'usuarios' => $usuarios,
            'query' => $_GET,
            'paginacion' => $paginacion
        ));
    }

    public function accionUsuarioAction($activar, $desactivar, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        if($request->getMethod() == 'POST'){
            $vars = $_POST['id'];
            if($_POST['accion'] == 'activar'){
                $activar = true;
            }else if($_POST['accion'] == 'desactivar'){
                $desactivar = true;
            }
        }else{
            $vars = $_GET['id'];
        }

        if(is_array($vars)){
            $itemsAEditar = $vars;
        }else{
            $itemsAEditar[] = $vars;
        }

        foreach($itemsAEditar as $item){    
            $usuario = $ur->findOneById($item);
            if($activar == true){
                $estado = $lr->getEstado(7);
            }else if($desactivar == true){
                $estado = $lr->getEstado(8);
            }
            
            $usuario->setEstado($estado[0]);
            $em->persist($usuario);
        }

        $em->flush();

        $args = $_GET;
        unset($args['id']);

        return $this->redirect($this->generateUrl('LoogaresAdminBundle_listadoUsuarios', $args));
    }

    public function fotosLugarAction($ciudad, $slug){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $ciudad = $cr->findOneBySlug($ciudad);
        $idCiudad = $ciudad->getId();

        if($slug == 'todos'){
            $nombre = "Todos";
            $where = null;
            $slug = 'todos';
            $where = "where comuna.ciudad_id = " . $idCiudad;
        }else{
            $lugar = $lr->findOneBySlug($slug);
            $where = "where il.lugar_id = " . $lugar->getId();
            $nombre = $lugar->getNombre();
        }
        
        $order = null;
        $like = null;
        $offset = 0;
        $fn = $this->get('fn');

        $filters = array(
            'pusuario' => 'usuario',
            'pfecha_creacion' => 'fecha_creacion',
            'pestado' => 'estado',
            'plugar' => 'lugar'
        );

        $listadoFilters = array(
            'lugar' => 'lugar',
            'usuario' => 'usuario',
            'fecha_creacion' => 'fecha_creacion',
            'estado' => 'estado'
        );

        if(isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
            if(preg_match('/[A-Za-z]+/', $buscar) == false){
                if($where != null){
                    $where .= " and lugares.id = '$buscar'";
                }else{
                    $where .= "WHERE lugares.id = '$buscar'";
                }
            }else if($slug == 'todos'){
                if($where != null){
                    $where .= " and lugares.nombre like '%$buscar%'";
                }else{
                    $where = "WHERE lugares.nombre like '%$buscar%'";
                }
            }
        }

        if(isset($_GET['fecha-desde']) && isset($_GET['fecha-hasta'])){
            $hasta = preg_replace('/-/', '/',$_GET['fecha-hasta']);
            $hasta = explode('/', $hasta);
            $hasta = $hasta[2] . "/" . $hasta[1] . "/" . $hasta[0];

            $desde = preg_replace('/-/', '/',$_GET['fecha-desde']);
            $desde = explode('/', $desde);
            $desde = $desde[2] . "/" . $desde[1] . "/" . $desde[0];
            if($where != null){
                $where .= " and fecha_creacion between '$desde' and '$hasta'";
            }else{
               $where .= "WHERE fecha_creacion between '$desde' and '$hasta'"; 
            }
        }

        foreach($_GET as $column => $filter){
            if(!$like && isset($filters[$column])){
                $like = "HAVING ".$filters[$column]." LIKE '%$filter%'";
            }
             if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order .= "ORDER BY ".$listadoFilters[$column]." $filter";
                }else{
                    $order .= ", $listadoFilters[$column] $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*30);

        $fotos = $this->getDoctrine()->getConnection()
        ->fetchAll("select SQL_CALC_FOUND_ROWS il.*,
                    lugares.nombre as lugar,
                    (select lugares.nombre from lugares where lugares.id = il.lugar_id) as lugar,
                    (select lugares.id from lugares where lugares.id = il.lugar_id) as idLugar,
                    (select estado.nombre from estado where il.estado_id = estado.id) as estado,
                    (select usuarios.slug from usuarios where usuarios.id = il.usuario_id) as usuario


                    from imagenes_lugar as il

                    left join lugares
                    on lugares.id = il.lugar_id

                    left join comuna
                    on lugares.comuna_id = comuna.id

                    $where
                    GROUP BY il.id
                    $like
                    $order
                    LIMIT 30
                    OFFSET $offset");

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        $params = array(
            'slug'=> $slug,
            'ciudad' => $ciudad->getSlug()
        );

        $paginacion = $fn->paginacion($resultSetSize[0]['rows'], 30, 'LoogaresAdminBundle_fotosLugar', $params, $this->get('router'));

        return $this->render('LoogaresAdminBundle:Admin:fotosLugar.html.twig', array(
            'fotos' => $fotos,
            'ciudad' => $ciudad,
            'lugar' => $nombre,
            'slug' => $slug,
            'query' => $_GET,
            'paginacion' => $paginacion,
            'type' => 'listado-fotos'
        ));
    }

    public function accionFotosAction($ciudad, $slug, $borrar = false, $aprobar = false, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");

        if($request->getMethod() == 'POST'){
            $vars = $_POST['id'];
            if($_POST['accion'] == 'aprobar'){
                $aprobar = true;
            }else if($_POST['accion'] == 'borrar'){
                $borrar = true;
            }
        }else{
            $vars = $_GET['id'];
        }

        if(is_array($vars)){
            $itemsABorrar = $vars;
        }else{
            $itemsABorrar[] = $vars;
        }

        foreach($itemsABorrar as $item){    
            $imagen = $ilr->findOneById($item);
            $mail = array();
            $mail['imagen'] = $imagen;
            $mail['usuario'] = $imagen->getUsuario();
            if($borrar == true){
                $estado = $lr->getEstado(3);
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.imagen.borrar.asunto', array('%lugar%' => $imagen->getLugar()->getNombre()));                
                $mail['tipo'] = "borrar";

            }else if($aprobar == true){
                $estado = $lr->getEstado(2);
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.imagen.aprobar.asunto', array('%lugar%' => $imagen->getLugar()->getNombre()));
                $mail['tipo'] = "aprobar";
            }

            $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo($mail['usuario']->getMail());
            $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
            $message->setBody($this->renderView('LoogaresAdminBundle:Mails:mail_accion_foto.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
            $this->get('mailer')->send($message);
            
            $imagen->setEstado($estado);
            $em->persist($imagen);
        }

        $em->flush();

        //$this->get('session')->setFlash('accionFoto','Se ejecuto la accion, <a href="">Imagen '.$imagen->getId().'</a>.');

        $args = array(
            'ciudad' => $ciudad,
            'slug' => $slug
        );
        $args = array_merge($args, $_GET);
        unset($args['id']);

        return $this->redirect($this->generateUrl('LoogaresAdminBundle_fotosLugar', $args));
    }

    public function listadoRecomendacionesAction($ciudad){
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $idCiudad = $cr->findOneBySlug($ciudad);

        $where = "WHERE ciudad.id = ".$idCiudad->getId();
        $order = null;
        $like = null;
        $offset = 0;
        $fn = $this->get('fn');

        $filters = array(
            'pusuario' => 'usuarios.slug',
            'plugar' => 'lugares.nombre',
            'pestrellas' => 'estrellas',
            'pprecio' => 'precio',
            'putil' => 'util',
            'pestado' => 'estado'
        );

        $listadoFilters = array(
            'fecha_creacion' => 'fecha_creacion',
            'estrellas' => 'estrellas',
            'precio' => 'precio',
            'utiles' => 'util',
            'usuario' => 'usuarios.slug',
            'lugar' => 'lugares.nombre',
            'id' => 'r.id'
        );

        if(isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
            if(preg_match('/[A-Za-z]+/', $buscar) == false){
                    $where .= " and r.id = '$buscar'";
            }else{
                    $where .= " and lugares.nombre like '%$buscar%'";
            }
        }

        if(isset($_GET['fecha-desde']) && isset($_GET['fecha-hasta'])){
            $hasta = preg_replace('/-/', '/',$_GET['fecha-hasta']);
            $hasta = explode('/', $hasta);
            $hasta = $hasta[2] . "/" . $hasta[1] . "/" . $hasta[0];

            $desde = preg_replace('/-/', '/',$_GET['fecha-desde']);
            $desde = explode('/', $desde);
            $desde = $desde[2] . "/" . $desde[1] . "/" . $desde[0];
            if($where != null){
                $where .= " and fecha_creacion between '$desde' and '$hasta'";
            }else{
               $where .= "WHERE fecha_creacion between '$desde' and '$hasta'"; 
            }
        }

        foreach($_GET as $column => $filter){
            if(!$like && isset($filters[$column])){
                $like = "HAVING ".$filters[$column]." LIKE '%$filter%'";
            }
             if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order .= "ORDER BY ".$listadoFilters[$column]." $filter";
                }else{
                    $order .= ", $listadoFilters[$column] $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*30);        
                
        $recomendacionesResult = $this->getDoctrine()->getConnection()
        ->fetchAll("SELECT STRAIGHT_JOIN SQL_CALC_FOUND_ROWS r.id, r.fecha_creacion, r.estrellas, r.precio, LEFT(r.texto, 140) as texto, 
                    lugares.nombre as lugarNombre, lugares.slug as lugarSlug,
                    usuarios.nombre as usuarioNombre, usuarios.sexo as usuarioSexo, usuarios.apellido as usuarioApellido, usuarios.slug as usuarioSlug, 
                    count(util.id) as util,
                    (select estado.nombre from estado where r.estado_id = estado.id) as estado,
                    GROUP_CONCAT(DISTINCT tag.tag) as tags

                    FROM recomendacion as r

                    LEFT JOIN lugares
                    on lugares.id = r.lugar_id

                    LEFT JOIN comuna
                    on lugares.comuna_id = comuna.id

                    LEFT JOIN ciudad
                    on ciudad.id = comuna.ciudad_id

                    LEFT JOIN usuarios
                    on usuarios.id = r.usuario_id

                    LEFT JOIN util
                    on util.recomendacion_id = r.id

                    LEFT JOIN tag_recomendacion
                    ON tag_recomendacion.recomendacion_id = r.id

                    LEFT JOIN tag
                    on tag_recomendacion.tag_id = tag.id

                    $where
                    GROUP BY r.id
                    $like
                    $order
                    LIMIT 30
                    OFFSET $offset");

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        //Explotamos los tags, BOOM
        for($i = 0; $i < sizeOf($recomendacionesResult); $i++){
            $recomendacionesResult[$i]['tags'] = explode(',', $recomendacionesResult[$i]['tags']);
        }


        $params = array(
            'ciudad' => $ciudad
        );

        $options = array(
            'izq' => 5,
            'der' => 5
        );

        $paginacion = $fn->paginacion($resultSetSize[0]['rows'], 30, 'LoogaresAdminBundle_listadoRecomendaciones', $params, $this->get('router'), $options);

        return $this->render('LoogaresAdminBundle:Admin:listadoRecomendaciones.html.twig', array(
            'recomendaciones' => $recomendacionesResult,
            'ciudad' => $idCiudad,
            'query' => $_GET,
            'paginacion' => $paginacion,
            'type' => 'listado-recomendaciones'
        ));
    }

    public function accionRecomendacionesAction($ciudad, $id, $estrella = false, $habilitar = false, $borrar = false, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $arr = $em->getRepository("LoogaresExtraBundle:ActividadReciente");
        $aur = $em->getRepository("LoogaresUsuarioBundle:AccionUsuario");

        $args = array(
            'ciudad' => $ciudad
        );

        $args = array_merge($args, $_GET);
        unset($args['id']);

        if($request->getMethod() == 'POST'){
            $vars = $_POST['id'];
            if($_POST['accion'] == 'aprobar'){
                $habilitar = true;
            }else if($_POST['accion'] == 'eliminar'){
                $borrar = true;
            }
        }else{
            $vars = $id;
        }

        if(is_array($vars)){
            $itemsABorrar = $vars;
        }else{
            $itemsABorrar[] = $vars;
        }

        if($estrella == true){
            $recomendacion = $rr->findOneById($itemsABorrar);
            $recomendacionEstrella = new LoogarenoEstrella();
            $recomendacionEstrella->setRecomendacion($recomendacion);
            $recomendacionEstrella->setDetalle('');
            $recomendacionEstrella->setFecha(new \DateTime());
            $em->persist($recomendacionEstrella);
            $em->flush();

            return $this->redirect($this->generateUrl('LoogaresAdminBundle_listadoRecomendaciones', $args));
        }
        
        foreach($itemsABorrar as $item){    
            $recomendacion = $rr->findOneById($item);

            $mail = array();
            $mail['recomendacion'] = $recomendacion;
            $mail['usuario'] = $recomendacion->getUsuario();
            if($borrar == true){
                $estado = $lr->getEstado(3);
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.recomendacion.borrar.asunto', array('%lugar%' => $recomendacion->getLugar()->getNombre()));                
                $mail['tipo'] = "borrar";

                $recomendacion->setEstado($estado);
                $em->persist($recomendacion);
                $em->flush();

                $aur->borrarAccionesUsuario($recomendacion->getLugar()->getId(), $recomendacion->getUsuario()->getId());
                $arr->actualizarActividadReciente($recomendacion->getId(), 'Loogares\UsuarioBundle\Entity\Recomendacion');
            }else if($habilitar == true){
                $estado = $lr->getEstado(2); 
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.recomendacion.aprobar.asunto', array('%lugar%' => $recomendacion->getLugar()->getNombre()));                
                $mail['tipo'] = "aprobar";      

                $recomendacion->setEstado($estado);
                $em->persist($recomendacion);
                $em->flush();             
            }

            $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo($mail['usuario']->getMail());
            $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
            $message->setBody($this->renderView('LoogaresAdminBundle:Mails:mail_accion_recomendacion.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
            $this->get('mailer')->send($message);

            $arr->actualizarActividadReciente($recomendacion->getId(), 'Loogares\UsuarioBundle\Entity\Recomendacion');

            //Cambiamos la fecha de la ultima recomendacion en el lugar
            $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u
                                   WHERE u.lugar = ?1 AND u.estado != 3 ORDER BY u.id DESC");
            $q->setParameter(1, $recomendacion->getLugar()->getId());
            $q->setMaxResults(1);
            $ultimaRecomendacion = $q->getOneOrNullResult();

            if($ultimaRecomendacion){
              $fechaUltimaRecomendacion = $ultimaRecomendacion->getFechaCreacion();
            }else{
              $fechaUltimaRecomendacion = null;
            }
            
            $recomendacion->getLugar()->setFechaUltimaRecomendacion($fechaUltimaRecomendacion);
            $em->persist($recomendacion->getLugar());
            $em->flush();
        }

        $lr->actualizarPromedios($recomendacion->getLugar()->getSlug());

        return $this->redirect($this->generateUrl('LoogaresAdminBundle_listadoRecomendaciones', $args));
    }

    public function editarRecomendacionAction($id, $ciudad, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $arr = $em->getRepository("LoogaresExtraBundle:ActividadReciente");
        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $aur = $em->getRepository("LoogaresUsuarioBundle:AccionUsuario");
        
        $fn = $this->get('fn');
        $lugarAntiguo = null;
        $ciudad = $cr->findOneBySlug($ciudad);

        if($request->getMethod() == 'POST'){
            $recomendacion = $rr->findOneById($id);

            $recomendacion->setTexto($_POST['texto']);
            $recomendacion->setEstrellas($_POST['estrellas']);
            $recomendacion->setFechaUltimaModificacion(new \DateTime());

            if(isset($_POST['precio'])){
                $recomendacion->setPrecio($_POST['precio']);
            }

            if(isset($_POST['lugar_id'])){
                $_POST['lugar_id'] = preg_replace('/\(/', '', $_POST['lugar_id']);
                $_POST['lugar_id'] = preg_replace('/\)/', '', $_POST['lugar_id']);
                
                $lugar = $lr->findOneById($_POST['lugar_id']);
                $lugarAntiguo = $recomendacion->getLugar();
                $recomendacion->setLugar($lugar);

                // Mail al usuario que agregó la recomendación notificando que se movió su recomendación.
                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.recomendacion.mover.asunto', array('%old_lugar%' => $lugarAntiguo->getNombre(), '%new_lugar%' => $recomendacion->getLugar()->getNombre()));
                $mail['recomendacion'] = $recomendacion;
                $mail['old_lugar'] = $lugarAntiguo;
                $mail['usuario'] = $recomendacion->getUsuario();
                $mail['tipo'] = "mover";
                $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo($mail['usuario']->getMail());
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                $message->setBody($this->renderView('LoogaresAdminBundle:Mails:mail_accion_recomendacion.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);   
            }

            $em->persist($recomendacion);
            $em->flush();

            //Si hay un lugar antiguo, actualizamos los totales del lugar antiguo tambien y cambiamos la accion del usuario
            if($lugarAntiguo != null){
                $lr->actualizarPromedios($lugar->getSlug());
                $lr->actualizarPromedios($lugarAntiguo->getSlug());
                $aur->actualizarAcccionesUsuario($lugarAntiguo->getId(), $lugar->getId(), $recomendacion->getUsuario()->getId());
                $arr->actualizarActividadReciente($recomendacion->getId(), 'Loogares\UsuarioBundle\Entity\Recomendacion');

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

                $q->setParameter(1, $lugarAntiguo->getId());
                $ultimaRecomendacion = $q->getOneOrNullResult();

                if($ultimaRecomendacion){
                  $fechaUltimaRecomendacion = $ultimaRecomendacion->getFechaCreacion();
                }else{
                  $fechaUltimaRecomendacion = null;
                }

                $lugarAntiguo->setFechaUltimaRecomendacion($fechaUltimaRecomendacion);

                $em->persist($lugar);
                $em->persist($lugarAntiguo);
                $em->flush();
            }
        }

        $recomendacionResult = $this->getDoctrine()->getConnection()
        ->fetchAll("SELECT STRAIGHT_JOIN SQL_CALC_FOUND_ROWS r.id, r.fecha_creacion, r.estrellas, r.precio, r.texto as texto, 
                    lugares.nombre as lugarNombre, lugares.slug as lugarSlug, lugares.id as lugarId,
                    usuarios.nombre as usuarioNombre, usuarios.sexo as usuarioSexo, usuarios.apellido as usuarioApellido, usuarios.slug as usuarioSlug, 
                    count(util.id) as util,
                    (select estado.nombre from estado where r.estado_id = estado.id) as estado,
                    GROUP_CONCAT(DISTINCT tag.tag) as tags

                    FROM recomendacion as r

                    LEFT JOIN lugares
                    on lugares.id = r.lugar_id

                    LEFT JOIN comuna
                    on lugares.comuna_id = comuna.id

                    LEFT JOIN ciudad
                    on ciudad.id = comuna.ciudad_id

                    LEFT JOIN usuarios
                    on usuarios.id = r.usuario_id

                    LEFT JOIN util
                    on util.recomendacion_id = r.id

                    LEFT JOIN tag_recomendacion
                    ON tag_recomendacion.recomendacion_id = r.id

                    LEFT JOIN tag
                    on tag_recomendacion.tag_id = tag.id

                    WHERE r.id = $id");

        $lugar = $lr->findOneById($recomendacionResult[0]['lugarId']);

        return $this->render('LoogaresAdminBundle:Admin:editarRecomendacion.html.twig', array(
            'recomendacion' => $recomendacionResult[0],
            'mostrarPrecio' => $fn->mostrarPrecio($lugar),
            'ciudad' => $ciudad,
            'type' => 'listado-recomendaciones'
        ));
    }

    public function editarFotoAction($id, $slug, $ciudad, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $imagen = $ilr->findOneById($id);
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $ciudad = $cr->findOneBySlug($ciudad);
        $idCiudad = $ciudad->getId();

        if($request->getMethod() == 'POST'){
            $imagen->setTituloEnlace($_POST['titulo_enlace']);
            $imagen->setFechaModificacion(new \DateTime());

            // Verificamos si es URL
            $match = preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $imagen->getTituloEnlace());
            if($match > 0)
                $imagen->setEsEnlace(1);
            else
                $imagen->setEsEnlace(0);

            if(isset($_POST['lugar_id']) && $_POST['lugar_id'] != ''){
                $_POST['lugar_id'] = preg_replace('/\(/', '', $_POST['lugar_id']);
                $_POST['lugar_id'] = preg_replace('/\)/', '', $_POST['lugar_id']);

                $lugar = $lr->findOneById($_POST['lugar_id']);
                $lugarAntiguo = $imagen->getLugar();
                $imagen->setLugar($lugar);
                $slug = $lugar->getSlug();

                // Mail al usuario que agregó la foto notificando que se movió su foto.
                $mail = array();
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.imagen.mover.asunto', array('%old_lugar%' => $lugarAntiguo->getNombre(), '%new_lugar%' => $imagen->getLugar()->getNombre()));
                $mail['imagen'] = $imagen;
                $mail['old_lugar'] = $lugarAntiguo;
                $mail['usuario'] = $imagen->getUsuario();
                $mail['tipo'] = "mover";
                $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo($mail['usuario']->getMail());
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/mails/logo_mails.png'));
                $message->setBody($this->renderView('LoogaresAdminBundle:Mails:mail_accion_foto.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);                
            }
            $em->persist($imagen);
            $em->flush();

            return $this->render('LoogaresAdminBundle:Admin:editarFoto.html.twig', array(
                'ciudad' => $ciudad,
                'slug' => $slug,
                'foto' => $imagen,
                'id' => $id
            ));

        }

        return $this->render('LoogaresAdminBundle:Admin:editarFoto.html.twig', array(
            'foto' => $imagen,
            'ciudad' => $ciudad,
            'slug' => $slug,
            'query' => $_GET
        ));
    }

    public function pedidosLugaresAction($ciudad, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $sp = $em->getRepository("LoogaresLugarBundle:ServicioPedido");
        $fn = $this->get('fn');

        if($slug == 'todos'){
            $nombre = "Todos";
            $where = null;
            $slug = 'todos';
        }else{
            $lugar = $lr->findOneBySlug($slug);
            $where = "where pl.lugar_id = " . $lugar->getId();
            $nombre = $lugar->getNombre();
        }

        $ciudad = $cr->findOneBySlug($ciudad);
        $idCiudad = $ciudad->getId();
        
        $order = null;
        $like = null;
        $offset = 0;       

        $filters = array(
            'pservicio' => 'servicio',
            'ptipo' => 'tipo',
            'plugar' => 'lugar',
            'pprioridad' => 'prioridad',
            'ppromocion' => 'tiene_promocion'
        );

        $listadoFilters = array(
            'lugar' => 'lugar',
            'servicio' => 'servicio',
            'tipo' => 'tipo',
            'idLugar' => 'idLugar',
            'prioridad' => 'prioridad',
            'promocion' => 'tiene_promocion'
        );

        if(isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
            if(preg_match('/[A-Za-z]+/', $buscar) == false){
                if($where != null){
                    $where .= " and lugares.id = '$buscar'";
                }else{
                    $where .= "WHERE lugares.id = '$buscar'";
                }
            }else if($slug == 'todos'){
                if($where != null){
                    $where .= " and lugares.nombre like '%$buscar%'";
                }else{
                    $where = "WHERE lugares.nombre like '%$buscar%'";
                }
            }
        }

        foreach($_GET as $column => $filter){
            if(!$like && isset($filters[$column])){
                $like = "HAVING ".$filters[$column]." LIKE '%$filter%'";
            }
            elseif ($like && isset($filters[$column])) {
                $like .= " AND ".$filters[$column]." LIKE '%$filter%'";
            }

            if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order .= "ORDER BY ".$listadoFilters[$column]." $filter";
                }else{
                    $order .= ", $listadoFilters[$column] $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*30);
        $pedidos = $this->getDoctrine()->getConnection()
        ->fetchAll("SELECT SQL_CALC_FOUND_ROWS pl.*,
                    lugares.nombre AS lugar,
                    lugares.id AS idLugar,
                    sp.nombre AS servicio,
                    tp.nombre AS tipo,
                    sp.link_base as link

                    FROM pedidos_lugar AS pl

                    INNER JOIN lugares
                    ON lugares.id = pl.lugar_id

                    INNER JOIN comuna
                    ON lugares.comuna_id = comuna.id

                    INNER JOIN servicios_pedido sp
                    ON sp.id = pl.servicio_pedido_id

                    INNER JOIN tipo_pedido tp
                    ON tp.id = pl.tipo_pedido_id

                    $where
                    AND comuna.ciudad_id = $idCiudad
                    GROUP BY pl.id
                    $like
                    $order
                    LIMIT 30
                    OFFSET $offset");

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        // Obtenemos todos los servicios disponibles
        $servicios = $sp->findAll();

        $params = array(
            'slug'=> $slug,
            'ciudad' => $ciudad->getSlug()
        );
            
        $paginacion = $fn->paginacion($resultSetSize[0]['rows'], 30, 'LoogaresAdminBundle_pedidosLugar', $params, $this->get('router'));

        return $this->render('LoogaresAdminBundle:Admin:listadoPedidos.html.twig', array(
            'pedidos' => $pedidos,
            'ciudad' => $ciudad,
            'lugar' => $nombre,
            'slug' => $slug,
            'query' => $_GET,
            'servicios' => $servicios,
            'paginacion' => $paginacion,
            'type' => 'listado-pedidos'
        ));
    }

    public function agregarPedidoAction(Request $request, $ciudad, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $spr = $em->getRepository("LoogaresLugarBundle:ServicioPedido");
        $tpr = $em->getRepository("LoogaresLugarBundle:TipoPedido");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $servicios = $spr->findAll();
        $tipos = $tpr->findAll();

        if($request->getMethod() == 'POST') {
            $pedido = new PedidoLugar();
            $pedido->setLugar($lr->find($request->request->get('lugar_id')));
            $pedido->setServicioPedido($spr->find($request->request->get('servicio')));
            $pedido->setTipoPedido($tpr->find($request->request->get('tipo')));
            $pedido->setPrioridad($request->request->get('prioridad'));
            $pedido->setReferral($request->request->get('referral'));
            
            if($request->request->get('habilitar_promocion')) {
                // Agregamos la promoción
                $promocion = new Promocion();
                $promocion->setPedidoLugar($pedido);
                $promocion->setTitulo($request->request->get('titulo'));
                $promocion->setDias($request->request->get('dias'));
                $promocion->setDescripcion($request->request->get('descripcion'));
                $em->persist($promocion);

                $pedido->setTienePromocion(true);
                $pedido->setPromocion($promocion);
            }
            else {
                $pedido->setTienePromocion(false);
            }

            $em->persist($pedido);
            $em->flush();
            return $this->redirect($this->generateUrl('LoogaresAdminBundle_pedidosLugar', array(
                'ciudad' => $ciudad,
                'slug' => $pedido->getLugar()->getSlug()
            )));
        }

        return $this->render('LoogaresAdminBundle:Admin:agregarPedido.html.twig', array(
            'servicios' => $servicios,
            'tipos' => $tipos,
            'ciudad' => $ciudad,
            'slug' => $slug
        ));
    }

    public function editarPedidoAction(Request $request, $ciudad, $slug, $id) {
        $em = $this->getDoctrine()->getEntityManager();
        $plr = $em->getRepository("LoogaresLugarBundle:PedidoLugar");
        $spr = $em->getRepository("LoogaresLugarBundle:ServicioPedido");
        $tpr = $em->getRepository("LoogaresLugarBundle:TipoPedido");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $pedido = $plr->find($id);
        $servicios = $spr->findAll();
        $tipos = $tpr->findAll();

        if($request->getMethod() == 'POST') {
            $pedido->setServicioPedido($spr->find($request->request->get('servicio')));
            $pedido->setTipoPedido($tpr->find($request->request->get('tipo')));
            $pedido->setPrioridad($request->request->get('prioridad'));
            $pedido->setReferral($request->request->get('referral'));

            // Manejo de las promociones
            if($pedido->getPromocion() != null) {
                $promocion = $pedido->getPromocion();
                $promocion->setTitulo($request->request->get('titulo'));
                $promocion->setDias($request->request->get('dias'));
                $promocion->setDescripcion($request->request->get('descripcion'));
            }
            else {
                if($request->request->get('habilitar_promocion')) {
                    // Agregamos la promoción
                    $promocion = new Promocion();
                    $promocion->setPedidoLugar($pedido);
                    $promocion->setTitulo($request->request->get('titulo'));
                    $promocion->setDias($request->request->get('dias'));
                    $promocion->setDescripcion($request->request->get('descripcion'));
                    $em->persist($promocion);

                    $pedido->setTienePromocion(true);
                    $pedido->setPromocion($promocion);
                }
            }

            $em->flush();
        }
        return $this->render('LoogaresAdminBundle:Admin:editarPedido.html.twig', array(
            'pedido' => $pedido,
            'servicios' => $servicios,
            'tipos' => $tipos,
            'ciudad' => $ciudad,
            'slug' => $slug
        ));
    }

    public function accionPedidosAction(Request $request, $ciudad, $slug, $borrar = false) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $plr = $em->getRepository("LoogaresLugarBundle:PedidoLugar");

        if($request->getMethod() == 'POST'){
            $vars = $_POST['id'];
            if($request->request->get('accion') == 'borrar'){
                $borrar = true;
            }
        }else{
            $vars = $request->query->get('id');
        }

        if(is_array($vars)){
            $items = $vars;
        }else{
            $items[] = $vars;
        }
        foreach($items as $item){    
            $pedido = $plr->find($item);

            if($borrar == true){
                if($pedido->getPromocion() != null) {
                    $em->remove($pedido->getPromocion());
                }
                $em->remove($pedido);
            }
        }

        $em->flush();

        if(sizeof($items) == 1)
            $this->get('session')->setFlash('accionPedido','Pedido eliminado');    
        else if(sizeof($items) > 1)
            $this->get('session')->setFlash('accionPedido','Pedidos eliminados');       

        $args = array(
            'ciudad' => $ciudad,
            'slug' => $slug
        );
        $args = array_merge($args, $_GET);
        unset($args['id']);

        return $this->redirect($this->generateUrl('LoogaresAdminBundle_pedidosLugar', $args));    
    }

    public function listadoBlogPostsAction($ciudad){
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $fn = $this->get('fn');
        $ciudad = $cr->findOneBySlug($ciudad);
        $idCiudad = $ciudad->getId();

        $where = "WHERE p.ciudad_id = ".$idCiudad;
        $like = null;
        $order = null;
        $offset = 0;
        $fn = $this->get('fn');

        $filters = array(
            'pusuario' => 'usuarios.slug',
            'plugar' => 'lugares.nombre',
            'pestado' => 'blogEstadoNombre',
            'pcategoria' => 'categoriaSlug'
        );

        $listadoFilters = array(
            'id' => 'p.id',
            'usuario' => 'usuarioSlug',
            'titulo' => 'p.titulo',
            'categoria' => 'categoria.nombre',
            'premios' => 'p.numero_premios',
            'fecha_publicacion' => 'p.fecha_publicacion',
            'estado' => 'estado.nombre'
        );

        if(isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
            if(preg_match('/[A-Za-z]+/', $buscar) == false){
                    $where .= " and p.id = '$buscar'";
            }else{
                    $where .= " and p.titulo like '%$buscar%'";
            }
        }


        if(isset($_GET['fecha-desde']) && isset($_GET['fecha-hasta'])){
            $hasta = preg_replace('/-/', '/',$_GET['fecha-hasta']);
            $hasta = explode('/', $hasta);
            $hasta = $hasta[2] . "/" . $hasta[1] . "/" . $hasta[0];

            $desde = preg_replace('/-/', '/',$_GET['fecha-desde']);
            $desde = explode('/', $desde);
            $desde = $desde[2] . "/" . $desde[1] . "/" . $desde[0];
            if($where != null){
                $where .= " and fecha between '$desde' and '$hasta'";
            }else{
               $where .= "WHERE fecha between '$desde' and '$hasta'"; 
            }
        }

        foreach($_GET as $column => $filter){
            if(!$like && isset($filters[$column])){
                $like = "HAVING ".$filters[$column]." LIKE '%$filter%'";
            }
             if($filter == 'asc' || $filter == 'desc'){
                if(!$order){
                    $order .= "ORDER BY ".$listadoFilters[$column]." $filter";
                }else{
                    $order .= ", $listadoFilters[$column] $filter";
                }
                $filters[$column] = ($filter == 'asc')?'desc':'asc';
            }
        }

        $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
        $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*30);
        $posts = $this->getDoctrine()->getConnection()
        ->fetchAll("SELECT SQL_CALC_FOUND_ROWS p.*,
                    usuarios.nombre as usuarioNombre,
                    usuarios.apellido as usuarioApellido,
                    usuarios.slug as usuarioSlug,
                    blog_estado.nombre as blogEstadoNombre,
                    blog_categoria.nombre as categoriaNombre,
                    blog_categoria.slug as categoriaSlug
                    FROM blog_posts AS p

                    LEFT JOIN lugares
                    ON lugares.id = p.lugar_id

                    LEFT JOIN usuarios
                    ON usuarios.id = p.usuario_id

                    LEFT JOIN blog_estado
                    ON blog_estado.id = p.blog_estado_id

                    LEFT JOIN blog_categoria
                    ON blog_categoria.id = p.blog_categoria_id

                    $where
                    GROUP BY p.id
                    $like
                    $order
                    LIMIT 30
                    OFFSET $offset");

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        $params = array(
            'ciudad' => $ciudad->getSlug()
        );
            
        $paginacion = $fn->paginacion($resultSetSize[0]['rows'], 30, 'LoogaresAdminBundle_listadoBlogPosts', $params, $this->get('router'));

        return $this->render('LoogaresAdminBundle:Admin:listadoBlogPosts.html.twig', array(
            'ciudad' => $ciudad,
            'query' => $_GET,
            'posts' => $posts,
            'paginacion' => $paginacion
        ));
    }

    public function accionPostsAction($ciudad, $id, $borrador = false, $publicar = false, $borrar = false, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $ber = $em->getRepository("LoogaresBlogBundle:Estado");

        if($request->getMethod() == 'POST'){
            $vars = $_POST['id'];
            if($_POST['accion'] == 'publicar'){
                $publicar = true;
            }else if($_POST['accion'] == 'eliminar'){
                $borrar = true;
            }else if($_POST['accion'] == 'borrador'){
                $borrador = true;
            }
        }else{
            $vars = $id;
        }

        if(is_array($vars)){
            $itemsABorrar = $vars;
        }else{
            $itemsABorrar[] = $vars;
        }
        
        foreach($itemsABorrar as $item){    
            $post = $pr->findOneById($item);

            if($borrar == true){
                $estado = $ber->findOneByNombre('Post Eliminado');
            }else if($publicar == true){
                $estado = $ber->findOneByNombre('Post Publicado');                               
            }else if($borrador == true){
                $estado = $ber->findOneByNombre('Post Borrador');  
            }
            $post->setBlogEstado($estado);
            $em->persist($post);
        }

        $em->flush();

        $args = array(
            'ciudad' => $ciudad
        );

        $args = array_merge($args, $_GET);
        unset($args['id']);
        return $this->redirect($this->generateUrl('LoogaresAdminBundle_listadoBlogPosts', $args));
    }

    public function vistaBlogPostsAction($ciudad, Request $request, $post = null){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $bcr = $em->getRepository("LoogaresBlogBundle:Categoria");
        $ber = $em->getRepository("LoogaresBlogBundle:Estado");
        $becr = $em->getRepository("LoogaresBlogBundle:EstadoConcurso");
        $pr = $em->getRepository("LoogaresBlogBundle:Posts");
        $fn = $this->get('fn');
        $imagenes = array();
        $fechaPublicacion = null;
        $fechaTermino = null;
        $lugar = null;

        $ciudad = $cr->findOneBySlug($ciudad);
        
        if($post != null){ //Si slug tiene data, entonces el post existe, estamos editando, yada yada.
            $post = $pr->findOneBySlug($post);
        }else{
            $post = new Posts();
        }

        $form = $this->createFormBuilder($post)
             ->add('vimagen')
             ->add('vimagen_home')
             ->add('vimagen_detalle')
             ->getForm();

        if($request->getMethod() == 'POST'){
            //Agregamos el Post, parsing time.
            $form->bindRequest($request);

            if($request->get('lugar_id') != ''){
                $lugar = preg_replace('/\(/', '', $request->get('lugar_id'));
                $lugar = preg_replace('/\)/', '', $lugar);
                $lugar = $lr->findOneById($lugar);
                $post->setLugar($lugar);
            }

            $usuario = preg_replace('/\(/', '', $request->get('usuario_id'));
            $usuario = preg_replace('/\)/', '', $usuario);
            $usuario = $ur->findOneById($usuario);
            $post->setUsuario($usuario);

            if($request->get('fecha_publicacion') != ''){
                $fechaPublicacion = new \DateTime( $request->get('fecha_publicacion') );
            }

            if($request->get('fecha_termino') != ''){
                $fechaTermino = new \DateTime( $request->get('fecha_termino') );
            }

            if($request->get('nuevo_estado') != ''){
                $nuevoEstadoConcurso = new EstadoConcurso();
                $nuevoEstadoConcurso->setNombre($request->get('nuevo_estado'));
                $nuevoEstadoConcurso->setSlug($fn->generarSlug($request->get('nuevo_estado')));
                $nuevoEstadoConcurso->setClase($request->get('nuevo_estado_clase'));
                $em->persist($nuevoEstadoConcurso);
                $em->flush();
                $estadoConcurso = $nuevoEstadoConcurso;
            }else{
                $estadoConcurso = $becr->findOneById(preg_match('/Selecciona/', $request->get('estado_concurso'))?5:$request->get('estado_concurso'));
            }

            if($request->get('nueva_categoria') != ''){
                $nuevaCategoria = new Categoria();
                $nuevaCategoria->setNombre($request->get('nueva_categoria'));
                $nuevaCategoria->setSlug($fn->generarSlug($request->get('nueva_categoria')));
                $nuevaCategoria->setClase($request->get('nueva_categoria_clase'));
                $nuevaCategoria->setHex($request->get('nueva_categoria_hex'));
                $em->persist($nuevaCategoria);
                $em->flush();
                $categoria  = $nuevaCategoria;
            }else{
                $categoria = $bcr->findOneById($request->get('categoria'));
            }

            if ($form->isValid()) {
                $post->setCiudad($cr->findOneBySlug($request->get('ciudad')));
                $post->setTitulo($request->get('titulo')); 
                $post->setSlug($request->get('slug'));
                $post->setBlogEstadoConcurso($estadoConcurso);
                $post->setBlogCategoria($categoria);
                $post->setContenido($request->get('contenido'));
                $post->setDetalles($request->get('detalle'));
                $post->setNumeroPremios($request->get('numero_premios'));
                $post->setGanadores($request->get('ganadores'));
                $post->setCondiciones($request->get('condiciones'));
                $post->setBlogEstado($ber->findOneById($request->get('estado')));
                $post->setFecha(new \DateTime());
                $post->setFechaPublicacion($fechaPublicacion);
                $post->setFechaTermino($fechaTermino);
                $post->setTituloHome($request->get('titulo_home'));
                $post->setDescripcionHome($request->get('descripcion_home'));
                $post->setDestacadoHome($request->get('destacado_home'));
                $post->setPosicionHome($request->get('posicion_home'));
                $post->setPreview($request->get('preview'));

                if($post->vimagen !== null){
                    $post->setImagen($post->getSlug().'.jpg');
                }

                if($post->vimagen_detalle !== null){
                    $post->setImagenDetalle($post->getSlug().'_detalle.jpg');
                }

                if($post->vimagen_home !== null){
                    $post->setImagenDetalle($post->getSlug().'_home.jpg');
                }

                $em->persist($post);
                $em->flush();
            }            
        }

        return $this->render('LoogaresAdminBundle:Admin:agregarBlogPosts.html.twig', array(
            'ciudad' => $ciudad,
            'estados' => $ber->findAll(),
            'categorias' => $bcr->findAll(),
            'estados_concurso' => $becr->findAll(),
            'form' => $form->createView(),
            'post' => $post
        ));
    }

    public function testMailAction() {
    //    return $this->render('LoogaresAdminBundle:Mails:test_mail_accion_foto.html.twig');
    //    return $this->render('LoogaresAdminBundle:Mails:test_mail_accion_lugar.html.twig');
    //    return $this->render('LoogaresAdminBundle:Mails:test_mail_accion_recomendacion.html.twig');
    //    return $this->render('LoogaresLugarBundle:Mails:test_mail_enviar.html.twig');
          return $this->render('LoogaresLugarBundle:Mails:test_mail_lugar.html.twig');
    //    return $this->render('LoogaresLugarBundle:Mails:test_mail_recomendar.html.twig');
    //    return $this->render('LoogaresLugarBundle:Mails:test_mail_reporte.html.twig');
    //    return $this->render('LoogaresUsuarioBundle:Mails:test_mail_olvidar_password.html.twig');
    //    return $this->render('LoogaresUsuarioBundle:Usuarios:test_mail_borrar_cuenta.html.twig');
    //    return $this->render('LoogaresUsuarioBundle:Usuarios:test_mail_registro.html.twig');
    }

}
