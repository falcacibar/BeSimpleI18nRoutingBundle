<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Loogares\LugarBundle\Entity\Lugar;
use Loogares\LugarBundle\Entity\CategoriaLugar;
use Loogares\LugarBundle\Entity\CaracteristicaLugar;
use Loogares\Lugarbundle\Entity\Horario;
use Loogares\Lugarbundle\Entity\SubcategoriaLugar;
use Loogares\Lugarbundle\Entity\ImagenLugar;


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
                $fn = $this->get('fn');
                $_GET['pagina'] = (!isset($_GET['pagina']))?1:$_GET['pagina'];
                $_GET['orden'] = (!isset($_GET['orden']))?'ultimas':$_GET['orden'];
                $offset = ($_GET['pagina'] - 1) * 10;
                $resultadosPorPagina = (!isset($_GET['resultados']))?10:$_GET['resultados'];

                $em = $this->getDoctrine()->getEntityManager();
                $qb = $em->createQueryBuilder();
                $lr = $em->getRepository('LoogaresLugarBundle:Lugar');
                
                $lugarResult = $lr->getLugares($slug);

                //Id del Lugar
                $idLugar = $lugarResult[0]->getId();

                $codigoArea = $lugarResult[0]->getComuna()->getCiudad()->getPais()->getCodigoArea();

                //Ultima foto del Lugar
                $q = $em->createQuery("SELECT u
                                       FROM Loogares\LugarBundle\Entity\ImagenLugar u
                                       WHERE u.lugar = ?1
                                       ORDER BY u.fecha_modificacion");
                $q->setMaxResults(1)
                  ->setParameter(1, $idLugar);
                $imagenLugarResult = $q->getResult();

                //Total Fotos Lugar
                $q = $em->createQuery("SELECT count(u.id)
                                       FROM Loogares\LugarBundle\Entity\ImagenLugar u
                                       WHERE u.lugar = ?1");
                $q->setParameter(1, $idLugar);
                $totalFotosResult = $q->getSingleScalarResult();

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
                if($_GET['orden'] == 'ultimas'){
                        $orderBy = "ORDER BY recomendacion.fecha_creacion DESC";
                }else if($_GET['orden'] == 'mas-utiles'){
                        $orderBy = "ORDER BY utiles DESC";
                }else if($_GET['orden'] == 'mejor-evaluadas'){
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
                                                                         LIMIT $resultadosPorPagina
                                                                         OFFSET $offset");

                //Explotamos los tags, BOOM
                for($i = 0; $i < sizeOf($recomendacionesResult); $i++){
                        $recomendacionesResult[$i]['tags'] = explode(',', $recomendacionesResult[$i]['tags']);
                }
                $telefonos = array();
                //Array con telefonos del lugar
                if($lugarResult[0]->getTelefono1() != null || $lugarResult[0]->getTelefono1() != '') {
                    $telefonos[] = str_replace($codigoArea, '', $lugarResult[0]->getTelefono1());
                }
                if($lugarResult[0]->getTelefono2() != null || $lugarResult[0]->getTelefono2() != '') {
                    $telefonos[] = str_replace($codigoArea, '', $lugarResult[0]->getTelefono2());
                }
                if($lugarResult[0]->getTelefono3() != null || $lugarResult[0]->getTelefono3() != '') {
                    $telefonos[] = str_replace($codigoArea, '', $lugarResult[0]->getTelefono3());
                }

                //Sacamos los HTTP
                $lugarResult[0]->setSitioWeb($fn->stripHTTP($lugarResult[0]->getSitioWeb()));
                $lugarResult[0]->setTwitter($fn->stripHTTP($lugarResult[0]->getTwitter()));
                $lugarResult[0]->setFacebook($fn->stripHTTP($lugarResult[0]->getFacebook()));

                /*
                *  Armado de Datos para pasar a Twig
                */
                $data = $lugarResult[0];
                $data->horarios = $fn->generarHorario($lugarResult[0]->getHorario());
                //Armando los datos a pasar, solo pasamos un objeto con todo lo que necesitamos
                $data->telefonos = $telefonos;
                //Imagen a mostrar
                $data->imagen_full = (isset($imagenLugarResult[0]))?$imagenLugarResult[0]->getImagenFull():'Sin-Foto-Lugar.gif';
                $data->primero = (isset($primeroRecomendarResult[0]))?$primeroRecomendarResult[0]:'asd';
                $data->recomendaciones = $recomendacionesResult;
                //Total de Pagina que debemos mostrar/generar
                $data->totalPaginas = ($totalRecomendacionesResult >$resultadosPorPagina )?floor($totalRecomendacionesResult / $resultadosPorPagina):1;
                $data->totalRecomendaciones = $totalRecomendacionesResult;
                //Offset de comentarios mostrados, "mostrando 1 a 10 de 20"
                $data->mostrandoComentariosDe = $_GET['pagina'] * ($_GET['pagina'] != 1)?(10 + 1):1;
                $data->totalFotos = $totalFotosResult;
                $data->recomendacionesPorPagina = $resultadosPorPagina;
                $data->tagsPopulares = $lr->getTagsPopulares($idLugar);
                //Render ALL THE VIEWS
                return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => $data, 'query' => $_GET));            
    }
    
    public function agregarAction(Request $request, $slug = null){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $errors = array();
        $camposExtraErrors = false;
        $formErrors = array();

        if($slug){
            $lugar = $lr->findOneBySlug($slug);    
        }else{
            $lugar = new Lugar();
        }

        $form = $this->createFormBuilder($lugar)
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

                $comuna = $lr->getComunas($_POST['comuna']);  
                $sector = $lr->getSectores($_POST['sector']);

                $estado = $lr->getEstado(1);
                $tipo_lugar = $lr->getTipoLugar('lugar');

                $lugar->setComuna($comuna[0]);

                $lugar->setEstado($estado[0]);
                $lugar->setTipoLugar($tipo_lugar[0]);

                //Sacamos los HTTP
                $lugar->setSitioWeb($fn->stripHTTP($lugar->getSitioWeb()));
                $lugar->setTwitter($fn->stripHTTP($lugar->getTwitter()));
                $lugar->setFacebook($fn->stripHTTP($lugar->getFacebook()));

                $lugaresConElMismoNombre = $lr->getLugaresPorNombre($lugar->getNombre());
                
                if($slug == null){
                    $lugar->setFechaAgregado(new \DateTime());
                }

                if(sizeOf($lugaresConElMismoNombre) != 0 && $slug == null){
                    $lugarSlug = $fn->generarSlug($lugar->getNombre()) . "-" . $_POST['ciudad'].(sizeOf($lugaresConElMismoNombre)+1);
                }else{
                    $lugarSlug = $fn->generarSlug($lugar->getNombre()) . "-" . $_POST['ciudad'];
                }

                $lugar->setSlug($lugarSlug);
                
                $em->persist($lugar);

                $lr->cleanUp($lugar->getId());

                foreach($_POST['categoria'] as $postCategoria){
                    $categoriaLugar[] = new CategoriaLugar();
                    $size = sizeOf($categoriaLugar) - 1;
                    if($postCategoria != "elige"){
                        $categoria = $lr->getCategorias($postCategoria);
                        if($categoria){
                            $categoriaLugar[$size]->setCategoria($categoria[0]);
                            $categoriaLugar[$size]->setLugar($lugar);
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
                        $caracteristicaLugar[] = new CaracteristicaLugar();
                        $size = sizeOf($caracteristicaLugar) - 1;
                        $caracteristica = $lr->getCaracteristicaPorNombre($postCaracteristica);
                        if($caracteristica){
                            $caracteristicaLugar[$size]->setLugar($lugar);
                            $caracteristicaLugar[$size]->setCaracteristica($caracteristica[0]);
                            $em->persist($caracteristicaLugar[$size]);
                        }
                    }
                }

                if(isset($_POST['subcategoria']) && is_array($_POST['subcategoria'])){
                    foreach($_POST['subcategoria'] as $postSubCategoria){
                        $subCategoriaLugar[] = new SubcategoriaLugar();
                        $size = sizeOf($subCategoriaLugar) - 1;
                        $subCategoria = $lr->getSubCategoriaPorNombre($postSubCategoria);
                        if($subCategoria){
                            $subCategoriaLugar[$size]->setLugar($lugar);
                            $subCategoriaLugar[$size]->setSubCategoria($subCategoria[0]);
                            $em->persist($subCategoriaLugar[$size]);
                        }
                    }
                }

                $dias = array('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo');

                foreach($dias as $key => $value){
                    $horario[] = new Horario();
                    $size = sizeOf($horario) - 1;
                    if(isset($_POST['horario-'.$value])){
                        $postHorario = $_POST['horario-'.$value];
                        if($postHorario[0] != 'cerrado' || $postHorario[1] != 'cerrado'){
                            $horario[$size]->setLugar($lugar);
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

                $this->get('session')->setFlash('nuevo-lugar','This is a random message, sup.');
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
                
                return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => $data));
            }
        }

        $tipoCategorias = $lr->getTipoCategorias();
        $paises = $lr->getPaises();
        $categorias = $lr->getCategorias();
        $ciudades = $lr->getCiudades();
        $comunas = $lr->getComunas();
        $sectores = $lr->getSectores();
        $caracteristicas = $lr->getCaracteristicas();
        $subCategorias = $lr->getSubCategorias();

        $categoriaSelect = "<select class='categoria required' title='Recuerda asignarle una categoría al lugar' name='categoria[]'><option value='elige'>Elige una categoria principal</option>";
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

        $paisSelect = "<select class='pais required' title='' name='ciudad' id='ciudad'>";
        $ciudadSelect = "<select class='ciudad required' title='' name='ciudad' id='ciudad'>";
        $comunaSelect = "<select class='comuna required' title='Localiza la comuna del lugar' name='comuna' id='comuna'><option value='elige'>Elige una comuna</option>";
        $sectorSelect = "<select class='sector' name='sector' id='sector'><option value='elige'>¿Está en un sector popular? Elígelo aquí</option>";
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
        $data['categorias'] = $categorias;
        $data['tipoCategoria'] = $tipoCategorias;
        $data['subCategorias'] = $subCategorias;
        $data['ciudad'] = $ciudades;
        $data['pais'] = $paises;
        $data['caracteristicas'] = $caracteristicas;
        $data['categoriaSelect'] = $categoriaSelect;
        $data['ciudadSelect'] = $ciudadSelect;
        $data['comunaSelect'] = $comunaSelect;
        $data['sectorSelect'] = $sectorSelect;
        $data['ciudadActual'] = $lr->getCiudadById('1');

        //Sacar +56 de los telefonos
        $lugar->tel1 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono1());
        $lugar->tel2 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono2());
        $lugar->tel3 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono3());
         
        return $this->render('LoogaresLugarBundle:Lugares:agregar.html.twig', array(
            'data' => $data,
            'lugar' => $lugar,
            'form' => $form->createView(),
            'errors' => $errors,
        ));
    }
    

    public function editarAction($slug){
        return $this->render('LoogaresLugarBundle:Lugares:agregar.html.twig');
    }

    public function agregarFotoAction(Request $request, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $formErrors = array();

        $lugar = $lr->findOneBySlug($slug);

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

                // Verificación de selección de al menos una foto
                $imagenes = array();

                if($imgLugar->firstImg != null)
                    $imagenes[] = $imgLugar->firstImg;

                if($imgLugar->secondImg != null)
                    $imagenes[] = $imgLugar->secondImg;

                if($imgLugar->thirdImg != null)
                    $imagenes[] = $imgLugar->thirdImg;

                if(sizeof($imagenes) == 0) {
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
                                        ->findOneByNombre('Aprobado');
                        }
                        else {
                             $estadoImagen = $em->getRepository("LoogaresExtraBundle:Estado")
                                        ->findOneByNombre('Por revisar');
                        }
                        $newImagen->setEstado($estadoImagen);
                        $newImagen->setFechaCreacion(new \DateTime());
                        $newImagen->setImagenFull('.jpg');
                        $newImagen->firstImg = $imagen;

                        $em->persist($newImagen);
                        $em->flush();

                        $newImagen->setFechaCreacion(new \DateTime());;

                        $em->flush();
                        $imgs[] = $newImagen;

                        $newImagen = null;
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

                    $em->flush();
                }
            }

            // Redirección a galería de fotos (FICHA POR AHORA)
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $slug)));
        }
    }
}
