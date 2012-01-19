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
        $order = false;
        $like = false;
        $where = null;
        $router = $this->get('router');
        $fn = $this->get('fn');
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

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
            'pestado' => 'estado'
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
            'estado' => 'estado'
        );

        if(isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
            if(preg_match('/[A-Za-z]+/', $buscar) == false){
                $where = "WHERE lugares.id = '$buscar'";
            }else{
                $where = "WHERE lugares.nombre LIKE '%$buscar%'";
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
                $where = "WHERE fecha_agregado between '$desde' and '$hasta'";
            }else{
                $where = " and fecha_agregado between '$desde' and '$hasta'";
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
        ->fetchAll("SELECT SQL_CALC_FOUND_ROWS lugares.*, 
                    usuarios.mail as usuarioMail,
                    comuna.nombre as comunaNombre,
                    sector.nombre as sectorNombre,
                    ciudad.nombre as ciudadNombre,
                    estado.nombre as estado,
                    cast(AVG(recomendacion.estrellas) as signed) as estrellas,
                    count(distinct util.id) as utiles,
                    group_concat(distinct categorias.nombre) as categorias,
                    group_concat(distinct subcategoria.nombre) as subcategorias,
                    group_concat(distinct caracteristica.nombre) as caracteristicas

                    FROM lugares

                    left join usuarios
                    on usuarios.id = lugares.usuario_id

                    left join comuna
                    on comuna.id = lugares.comuna_id

                    left join sector
                    on sector.id = lugares.sector_id

                    left join ciudad
                    on ciudad.id = comuna.ciudad_id

                    left join recomendacion
                    on recomendacion.lugar_id = lugares.id

                    left join util
                    on util.recomendacion_id = recomendacion.id

                    left join categoria_lugar
                    on categoria_lugar.lugar_id = lugares.id

                    left join categorias
                    on categorias.id = categoria_lugar.categoria_id

                    left join estado
                    on estado.id = lugares.estado_id

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
            'ciudad' => $ciudad
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
            'ciudad' => $ciudad
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
            if($borrar == true){
                $estado = $lr->getEstado(3);
            }else if($cerrar == true){
                $estado = $lr->getEstado(4);
            }else if($habilitar == true){
                $estado = $lr->getEstado(2);
            }
            
            $lugar->setEstado($estado[0]);
            $em->persist($lugar);
        }

        $em->flush();

        return $this->redirect($this->generateUrl('LoogaresAdminBundle_listadoLugares', array(
            'ciudad' => $ciudad
        )));
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

        $lugaresFilters = array();

        $recomendacionesFilter = array(
            'recomendaciones' => 'recomendaciones',
            'utiles' => 'utiles'
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

        return $this->redirect($this->generateUrl('LoogaresAdminBundle_listadoUsuarios'));
    }


}
