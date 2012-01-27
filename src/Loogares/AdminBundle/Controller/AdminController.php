<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


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

        $q = $em->createQuery('SELECT u from Loogares\ExtraBundle\Entity\Pais u where u.mostrar_lugar = 1');
        $paisesResult = $q->getResult();

        $q = $em->createQuery("SELECT u FROM Loogares\ExtraBundle\Entity\Ciudad u where u.mostrar_lugar = 1");
        $ciudadesResult = $q->getResult();

        return $this->render('LoogaresAdminBundle:Admin:seleccionPais.html.twig', array(
            'paises' => $paisesResult,
            'ciudades' => $ciudadesResult
        ));
    }   

    public function administrarLugaresAction($ciudad){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $tlr = $em->getRepository("LoogaresAdminBundle:TempLugar");
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");

        return $this->render('LoogaresAdminBundle:Admin:administrarLugares.html.twig', array(
            'totalLugares' => $lr->getTotalLugaresPorCiudad($ciudad),
            'totalPorRevision' => $tlr->getTotalLugaresARevisarPorCiudad($ciudad),
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
            'pusuario' => 'usuarioMail',
            'pcategoria' => 'categorias',
            'psubcategoria' => 'subcategorias',
            'pcalle' => 'calle',
            'pcomuna' => 'comunaNombre',
            'psector' => 'sectorNombre',
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
            'usuario' => 'usuarioMail',
            'categoria' => 'categorias',
            'subcategoria' => 'subcategorias',
            'calle' => 'lugares.calle',
            'detalle' => 'lugares.detalle',
            'comuna' => 'comunaNombre',
            'sector' => 'sectorNombre',
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

            if(!$where){
                $where .= " and fecha_agregado between '$desde' and '$hasta'";
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
            'ciudad' => $ciudadSlug,
            'ciudadNombre' => $ciudadNombre
        ));
    }

    public function listadoRevisionAction($ciudad){
        $em = $this->getDoctrine()->getEntityManager();

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
                    on sector.ciudad_id = ciudad.id

                    where lugares.id = temp_lugares.lugar_id and temp_lugares.estado_id = 1
                    group by lugares.id");

        return $this->render('LoogaresAdminBundle:Admin:listadoRevision.html.twig',array(
            'lugares' => $ih8doctrine,
            'ciudad' => $ciudad
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

    public function accionLugarAction($ciudad, $cerrar = false, $borrar = false, $habilitar = false, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

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
            $vars = $_GET['id'];
        }

        if(is_array($vars)){
            $itemsABorrar = $vars;
        }else{
            $itemsABorrar[] = $vars;
        }

        foreach($itemsABorrar as $item){    
            $lugar = $lr->findOneById($item);
            $mail = array();
            $mail['lugar'] = $lugar;
            $mail['usuario'] = $lugar->getUsuario();

            if($borrar == true){
                $estado = $lr->getEstado(3);
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.lugar.borrar.asunto').' '.$lugar->getNombre();                
                $mail['tipo'] = "borrar";

            }else if($cerrar == true){
                $estado = $lr->getEstado(4);                
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.lugar.cerrar.asunto').' '.$lugar->getNombre();
                $mail['tipo'] = "cerrar";                

            }else if($habilitar == true){
                $estado = $lr->getEstado(2);
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.lugar.aprobar.asunto').' '.$lugar->getNombre();
                $mail['tipo'] = "aprobar";    
            }

            // Se envía mail a usuario que agregó el lugar
            $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo($mail['usuario']->getMail());
            $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
            $message->setBody($this->renderView('LoogaresAdminBundle:Mails:mail_accion_lugar.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
            $this->get('mailer')->send($message);

            $lugar->setEstado($estado);
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
            'pestado' => 'estadoNombre',
            'ptipo_usuario' => 'tipoUsuarioNombre',
            'pwww' => 'usuarios.web',
            'ptwitter' => 'usuarios.twitter',
            'pfacebook' => 'usuarios.facebook'
        );

        $listadoFilters = array(
            'id' => 'usuarios.id', 
            'nombre' => 'usuarios.nombre',
            'apellido' => 'usuarios.apellido',
            'mail' => 'usuarios.mail',
            'slug' => 'usuarios.slug',
            'sexo' => 'usuarios.sexo',
            'fecha_nacimiento' => 'usuarios.fecha_nacimiento',
            'estado' => 'estadoNombre',
            'tipo_usuario' => 'tipoUsuarioNombre',
            'imagenes' => 'imagenes',
            'recomendaciones' => 'recomendaciones',
            'lugares' => 'lugares',
            'utiles' => 'utiles',
            'fecha_registro' => 'usuarios.fecha_registro'
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
                    (select count(distinct imagenes_lugar.id) from imagenes_lugar where usuarios.id = imagenes_lugar.usuario_id and imagenes_lugar.estado_id = 5) as imagenes,
                    (select count(distinct lugares.id) from lugares where usuarios.id = lugares.usuario_id) as lugares,
                    (select count(distinct recomendacion.id) from recomendacion where usuarios.id = recomendacion.usuario_id) as recomendaciones,
                    (select count(distinct util.id) from util where usuarios.id = util.usuario_id) as utiles,
                    estado.nombre as estadoNombre,
                    tipo_usuario.descripcion as tipoUsuarioNombre
                                         
                    from usuarios
                                        
                    left join estado
                    on estado.id = usuarios.estado_id

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
        if($slug == 'todos'){
            $nombre = "Todos";
            $where = null;
            $slug = 'todos';
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

                    $where
                    GROUP BY il.id
                    $like
                    $order
                    LIMIT 30
                    OFFSET $offset");

        $resultSetSize  = $this->getDoctrine()->getConnection()->fetchAll("SELECT FOUND_ROWS() as rows;");

        $params = array(
            'slug'=> $slug,
            'ciudad' => $ciudad
        );

        $paginacion = $fn->paginacion($resultSetSize[0]['rows'], 30, 'LoogaresAdminBundle_fotosLugar', $params, $this->get('router')    );

        return $this->render('LoogaresAdminBundle:Admin:fotosLugar.html.twig', array(
            'fotos' => $fotos,
            'ciudad' => $ciudad,
            'lugar' => $nombre,
            'slug' => $slug,
            'query' => $_GET,
            'paginacion' => $paginacion
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
                $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.imagen.aprobar.asunto', array('%lugar%' => $imagen->sgetLugar()->getNombre()));
                $mail['tipo'] = "aprobar";
            }

            $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo($mail['usuario']->getMail());
            $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
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

    public function editarFotoAction($id, $slug, $ciudad, Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $imagen = $ilr->findOneById($id);

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
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresAdminBundle:Mails:mail_accion_foto.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);                
            }
            $em->persist($imagen);
            $em->flush();

            return $this->redirect($this->generateUrl('LoogaresAdminBundle_editarFoto', array(
                'ciudad' => $ciudad,
                'slug' => $slug,
                'imagen' => $imagen,
                'id' => $id
            )));
        }

        return $this->render('LoogaresAdminBundle:Admin:editarFoto.html.twig', array(
            'foto' => $imagen,
            'ciudad' => $ciudad,
            'slug' => $slug,
            'query' => $_GET
        ));
    }

    public function testMailAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $imagen = $ilr->find(17553);
        $lugar = $lr->find(3480);
        $mail = array();
        $mail['asunto'] = $this->get('translator')->trans('admin.notificaciones.imagen.borrar.asunto', array('%lugar%' => $imagen->getLugar()->getNombre()));
        $mail['imagen'] = $imagen;
        //$mail['lugar_new'] = $lugar;
        $mail['usuario'] = $imagen->getUsuario();
        $mail['tipo'] = "borrar";

        return $this->render('LoogaresAdminBundle:Mails:mail_accion_foto.html.twig', array('mail' => $mail));
    }

}
