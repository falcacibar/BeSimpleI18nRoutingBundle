<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Loogares\LugarBundle\Entity\Lugar;

class LugarController extends Controller
{

    public function listadoAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $q = $em->createQuery('SELECT u FROM Loogares\LugarBundle\Entity\Lugar u');
        $q->setFirstResult(rand(1, 6000));
        $q->setMaxResults(10);
        $result = $q->getResult();

        $repo = $this->getDoctrine()
                     ->getRepository('LoogaresLugarBundle:Lugar');
        
        return $this->render('LoogaresLugarBundle:Lugares:listado.html.twig', array('lugares' => $result));
    }    

    public function ajaxTestAction(){
        return $this->render('LoogaresLugarBundle:Lugares:ajax.html.twig');    
    }

    public function lugarAction($slug){
                $paginaActual = (!isset($_GET['pagina']))?1:$_GET['pagina'];
                $orden = (!isset($_GET['orden']))?'ultimas':$_GET['orden'];
                $offset = ($paginaActual - 1) * 10;

                $em = $this->getDoctrine()->getEntityManager();
                $qb = $em->createQueryBuilder();

                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\Lugar u 
                                       WHERE u.slug = ?1");
                $q->setParameter(1, $slug);
                $lugarResult = $q->getResult();

                //Id del Lugar
                $idLugar = $lugarResult[0]->getId();

                //Ultima foto del Lugar
                $q = $em->createQuery("SELECT u
                                       FROM Loogares\LugarBundle\Entity\ImagenLugar u
                                       WHERE u.lugar = ?1
                                       ORDER BY u.fecha_modificacion");
                $q->setMaxResults(1)
                  ->setParameter(1, $idLugar);
                $imagenLugarResult = $q->getResult();

                //Query para categorias del lugar
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\CategoriaLugar u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $categoriasResult = $q->getResult();

                //Query para categorias del lugar
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\SubcategoriaLugar u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $subCategoriaResult = $q->getResult();

                //Caracteristicas del lugar
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\CaracteristicaLugar u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $caracteristicaLugarResult = $q->getResult();

                //Query para horarios del lugar
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\LugarBundle\Entity\Horario u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $horarioResult = $q->getResult();

                //Query para sacar la primera recomendacion
                $q = $em->createQuery("SELECT u 
                                       FROM Loogares\UsuarioBundle\Entity\Recomendacion u 
                                       WHERE u.lugar = ?1 
                                       ORDER BY u.id ASC");
                $q->setParameter(1, $idLugar)
                  ->setMaxResults(1);
                $primeroRecomendarResult = $q->getResult();

                //Query para sacar el Total de las recomendaciones
                $q = $em->createQuery("SELECT count(u.id) 
                                       FROM Loogares\UsuarioBundle\Entity\Recomendacion u 
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $totalRecomendacionesResult = $q->getSingleScalarResult();

                //Definicion del orden para la siguiente consulta
                if($orden == 'ultimas'){
                        $orderBy = "ORDER BY recomendacion.fecha_creacion DESC";
                }else if($orden == 'mas-utiles'){
                        $orderBy = "ORDER BY utiles DESC";
                }else if($orden == 'mejor-evaluadas'){
                        $orderBy = "ORDER BY recomendacion.estrellas desc, recomendacion.fecha_creacion DESC";
                }

                
                //Query para las recomendaciones a mostrar
                $recomendacionesResult = $this->getDoctrine()->getConnection()->fetchAll("SELECT recomendacion.*, group_concat(DISTINCT tag.tag) as tags, count(DISTINCT util.id) AS utiles, usuarios.*
                                                                         FROM recomendacion
                                                                         LEFT JOIN util
                                                                         ON util.recomendacion_id = recomendacion.id
                                                                         LEFT JOIN tag_recomendacion
                                                                         ON tag_recomendacion.recomendacion_id = recomendacion.id
                                                                         LEFT JOIN tag
                                                                         ON tag_recomendacion.tag_id = tag.id
                                                                         LEFT JOIN usuarios
                                                                         ON recomendacion.usuario_id = usuarios.id
                                                                         WHERE recomendacion.lugar_id = $idLugar
                                                                         GROUP BY recomendacion.id 
                                                                         $orderBy
                                                                         LIMIT 10
                                                                         OFFSET $offset");


                //Explotamos los tags, BOOM
                for($i = 0; $i < sizeOf($recomendacionesResult); $i++){
                        $recomendacionesResult[$i]['tags'] = explode(',', $recomendacionesResult[$i]['tags']);
                }
                $telefonos = array();
                //Array con telefonos del lugar
                if($lugarResult[0]->getTelefono1() != null || $lugarResult[0]->getTelefono1() != '') {
                    $telefonos[] = $lugarResult[0]->getTelefono1();
                }
                if($lugarResult[0]->getTelefono2() != null || $lugarResult[0]->getTelefono2() != '') {
                    $telefonos[] = $lugarResult[0]->getTelefono2();
                }
                if($lugarResult[0]->getTelefono3() != null || $lugarResult[0]->getTelefono3() != '') {
                    $telefonos[] = $lugarResult[0]->getTelefono3();
                }

                /*
                *  Armado de Datos para pasar a Twig
                */
                $data = $lugarResult[0];

                //Armamos un array con todas las categorias
                foreach($categoriasResult as $cat){
                        $data->categorias[] = $cat->getCategoria()->getNombre();
                }

                $data->subcategorias = array();
                foreach($subCategoriaResult as $subcat){
                    foreach($data->categorias as $cat){
                        if($cat == $subcat->getSubCategoria()->getCategoria()->getNombre()){
                            $data->subcategorias[]['categoria'] = $cat;
                            $data->subcategorias[sizeOf($data->subcategorias) - 1]['subcategorias'][] = $subcat->getSubCategoria()->getNombre();
                        }
                    }
                }

                //Armando los datos a pasar, solo pasamos un objeto con todo lo que necesitamos
                $data->horarios = $horarioResult;
                $data->telefonos = $telefonos;
                $data->caracteristicaslugar = $caracteristicaLugarResult;
                //Imagen a mostrar
                $data->imagen_full = $imagenLugarResult[0]->getImagenFull();
                $data->primero = (isset($primeroRecomendarResult[0]))?$primeroRecomendarResult[0]:'';
                $data->recomendaciones = $recomendacionesResult;
                //Total de Pagina que debemos mostrar/generar
                $data->totalPaginas = ($totalRecomendacionesResult > 10)?floor($totalRecomendacionesResult / 10):1;
                $data->totalRecomendaciones = $totalRecomendacionesResult;
                //Offset de comentarios mostrados, "mostrando 1 a 10 de 20"
                $data->mostrandoComentariosDe = $paginaActual * ($paginaActual != 1)?(10 + 1):1;
                $data->paginaActual = $paginaActual;
                $data->orden = $orden;

                //Render ALL THE VIEWS
                return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => $data));            
    }
    
    public function agregarAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $tipoCategorias = $lr->getTipoCategorias();
        $categorias = $lr->getCategorias();
        $ciudades = $lr->getCiudades();
        $comunas = $lr->getComunas();
        $sectores = $lr->getSectores();


        $categoriaSelect = "<select class='categoria' name='categoria'><option>Elige una Categoria</option>";
        foreach($tipoCategorias as $tipoCategoria){
            $tipoCategoriaNombre = $tipoCategoria->getNombre();

            $categoriaSelect .= "<optgroup label='$tipoCategoriaNombre'>";
            foreach($categorias as $categoria){
                $categoriaId = $categoria->getId();
                $categoriaNombre = $categoria->getNombre();
                if($categoria->getTipoCategoria()->getId() == $tipoCategoria->getId()){
                    $categoriaSelect .= "<option value='$categoriaId'>$categoriaNombre</option>";
                }
            }
            $categoriaSelect .= "</optgroup>";
        }
        $categoriaSelect .= "</select>";


        $ciudadSelect = "<select class='ciudad' name='ciudad'>";
        $comunaSelect = "<select class='comuna' name='comuna'><option>Elige una Comuna</option>";
        $sectorSelect = "<select class='sector' name='sector'><option>Elige un Sector</option>";
        foreach($ciudades as $ciudad){
            $ciudadId = $ciudad->getId();
            $ciudadNombre = $ciudad->getNombre();
            $ciudadSelect .= "<option ".(($ciudadId == 1)?"selected":"")." value='$ciudadId'>$ciudadNombre</option>";

            $comunaSelect .= "<optgroup label='$ciudadNombre'>";
            foreach($comunas as $comuna){
                $comunaId = $comuna->getId();
                $comunaNombre = $comuna->getNombre();
                if($comuna->getCiudad()->getId() == $ciudadId){
                    $comunaSelect .= "<option value='$comunaId'>$comunaNombre</option>";
                     
                }
            }
            $comunaSelect .= "</optgroup>";

            $sectorSelect .= "<optgroup label='$ciudadNombre'>";
            foreach($sectores as $sector){
                $sectorId = $sector->getId();
                $sectorNombre = $sector->getNombre();
                if($sector->getCiudad()->getId() == $ciudadId){
                    $sectorSelect .= "<option value='$sectorId'>$sectorNombre</option>";
                     
                }
            }
            $sectorSelect .= "</optgroup>";
        }
        $ciudadSelect .= "</select>";
        $comunaSelect .= "</select>";

        $data['categoriaSelect'] = $categoriaSelect;
        $data['ciudadSelect'] = $ciudadSelect;
        $data['comunaSelect'] = $comunaSelect;
        $data['sectorSelect'] = $sectorSelect;

        $data['categorias'] = $lr->getCategorias();
        #return new Response('dohohoho');
        return $this->render('LoogaresLugarBundle:Lugares:agregar.html.twig', array('data' => $data));
    }
}
