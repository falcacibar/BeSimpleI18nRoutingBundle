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
            'pmail' => 'lugares.mail'
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
            'mail' => 'lugares.mail'
        );

        if(isset($_GET['fecha-desde']) && isset($_GET['fecha-hasta'])){
            $hasta = preg_replace('/-/', '/',$_GET['fecha-hasta']);
            $hasta = explode('/', $hasta);
            $hasta = $hasta[2] . "/" . $hasta[1] . "/" . $hasta[0];

            $desde = preg_replace('/-/', '/',$_GET['fecha-desde']);
            $desde = explode('/', $desde);
            $desde = $desde[2] . "/" . $desde[1] . "/" . $desde[0];

            $where = "WHERE fecha_agregado between '$desde' and '$hasta'";
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

    public function usuariosAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        $usuarios = $ur->getUsuariosAdmin();

        return $this->render('LoogaresAdminBundle:Admin:usuarios.html.twig', array(
            'usuarios' => $usuarios,
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
}
