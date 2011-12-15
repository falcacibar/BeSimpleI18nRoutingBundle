<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;
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
    
    public function agregarAction(Request $request){

        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = new Lugar();

        $form = $this->createFormBuilder($lugar)
             ->add('nombre', 'text')
             ->add('calle', 'text')
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
             ->add('profesional', 'text')
             ->add('agno_construccion', 'text')
             ->add('materiales', 'text')
             ->add('_token', 'csrf')
             ->getForm();
        
        $camposExtraErrores = false;
        if ($request->getMethod() == 'POST') {
            if($_POST['comuna'] == 'elige'){
                $camposExtraErrores[] = "Porfavor Elige una Comuna";
            }

            if($_POST['ciudad'] == 'elige'){
                $camposExtraErrores[] = "Porfavor Elige una Ciudad";
            }

            $form->bindRequest($request);

            if($form->isValid() && $camposExtraErrores == false){
                // Setear Ciudad, COmuna, Usuario, Estado (NULL)
                $comuna = $lr->getComunas($_POST['comuna']);    
                $sector = $lr->getSectores($_POST['sector']);
                $estado = $lr->getEstado('probando');
                $usuario = $this->get('security.context')->getToken()->getUser();
                $tipo_lugar = $lr->getTipoLugar('que-visitar');

                $lugar->setComuna($comuna[0]);
                $lugar->setSector($sector[0]);
                $lugar->setUsuario($usuario);
                $lugar->setEstado($estado[0]);
                $lugar->setTipoLugar($tipo_lugar[0]);
                $lugar->setPrecio(0);
                $lugar->setSlug('aaa');
                $lugar->setDuenoId(0);
                $lugar->setMail('asd@asd.com');
                $lugar->setVisitas(0);
                $lugar->setUtiles(0);
                $lugar->setPrecioInicial(0);
                $lugar->setPrioridadWeb(0);
                $lugar->setTotalRecomendaciones(0);
                $lugar->setFechaAgregado(new \DateTime());
                $lugar->setFechaUltimaRecomendacion(new \DateTime());
                $lugar->setMapx('1');
                $lugar->setMapy('1');
                $lugar->setEstrellas('0');

                $em->persist($lugar);
                $em->flush();
                $data['nombre'] = $lugar->getNombre();
                return $this->render('LoogaresLugarBundle:Lugares:mensaje_lugar.html.twig', array('lugar' => $data));   
            }
        }

        $tipoCategorias = $lr->getTipoCategorias();
        $categorias = $lr->getCategorias();
        $ciudades = $lr->getCiudades();
        $comunas = $lr->getComunas();
        $sectores = $lr->getSectores();

        $categoriaSelect = "<select class='categoria' name='categoria[]'><option value='elegir'>Elige una Categoria</option>";
        foreach($tipoCategorias as $tipoCategoria){
            $tipoCategoriaNombre = $tipoCategoria->getNombre();

            $categoriaSelect .= "<optgroup label='$tipoCategoriaNombre'>";
            foreach($categorias as $categoria){
                $categoriaId = $categoria->getId();
                $categoriaSlug = $categoria->getSlug();
                $categoriaNombre = $categoria->getNombre();
                if($categoria->getTipoCategoria()->getId() == $tipoCategoria->getId()){
                    $categoriaSelect .= "<option value='$categoriaSlug'>$categoriaNombre</option>";
                }
            }
            $categoriaSelect .= "</optgroup>";
        }
        $categoriaSelect .= "</select>";


        $ciudadSelect = "<select class='ciudad' name='ciudad' id='ciudad'>";
        $comunaSelect = "<select class='comuna' name='comuna' id='comuna'><option value='elige'>Elige una Comuna</option>";
        $sectorSelect = "<select class='sector' name='sector' id='sector'><option value='elige'>Elige un Sector</option>";
        foreach($ciudades as $ciudad){
            $ciudadSlug = $ciudad->getSlug();
            $ciudadNombre = $ciudad->getNombre();
            $ciudadId = $ciudad->getId();
            $ciudadSelect .= "<option ".(($ciudadId == 1)?"selected":"")." value='$ciudadSlug'>$ciudadNombre</option>";

            $comunaSelect .= "<optgroup label='$ciudadNombre'>";
            foreach($comunas as $comuna){
                $comunaSlug= $comuna->getSlug();
                $comunaNombre = $comuna->getNombre();
                if($comuna->getCiudad()->getId() == $ciudadId){
                    $comunaSelect .= "<option value='$comunaSlug'>$comunaNombre</option>";
                     
                }
            }
            $comunaSelect .= "</optgroup>";

            $sectorSelect .= "<optgroup label='$ciudadNombre'>";
            foreach($sectores as $sector){
                $sectorId = $sector->getId();
                $sectorNombre = $sector->getNombre();
                $sectorSlug = $sector->getSlug();
                if($sector->getCiudad()->getId() == $ciudadId){
                    $sectorSelect .= "<option value='$sectorSlug'>$sectorNombre</option>";
                     
                }
            }
            $sectorSelect .= "</optgroup>";
        }
        $ciudadSelect .= "</select>";
        $comunaSelect .= "</select>";
        $sectorSelect .= "</select>";

        $data['categoriaSelect'] = $categoriaSelect;
        $data['ciudadSelect'] = $ciudadSelect;
        $data['comunaSelect'] = $comunaSelect;
        $data['sectorSelect'] = $sectorSelect;
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
        $data['categorias'] = $lr->getCategorias();

        return $this->render('LoogaresLugarBundle:Lugares:agregar.html.twig', array(
            'data' => $data,
            'form' => $form->createView(),
            'camposExtraErrores' => $camposExtraErrores
        ));
    }
}
