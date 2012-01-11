<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Loogares\LugarBundle\Entity\Lugar;
use Loogares\LugarBundle\Entity\CategoriaLugar;
use Loogares\LugarBundle\Entity\CaracteristicaLugar;
use Loogares\LugarBundle\Entity\Horario;
use Loogares\LugarBundle\Entity\SubcategoriaLugar;
use Loogares\LugarBundle\Entity\ImagenLugar;

use Loogares\AdminBundle\Entity\TempLugar;
use Loogares\AdminBundle\Entity\TempCategoriaLugar;
use Loogares\AdminBundle\Entity\TempCaracteristicaLugar;
use Loogares\AdminBundle\Entity\TempHorario;
use Loogares\AdminBundle\Entity\TempSubcategoriaLugar;

class LugarController extends Controller{

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
                                       AND u.estado != ?2
                                       ORDER BY u.fecha_modificacion");
                $q->setMaxResults(1)
                  ->setParameter(1, $idLugar)
                  ->setParameter(2, 3);
                $imagenLugarResult = $q->getResult();

                //Total Fotos Lugar
                $q = $em->createQuery("SELECT count(u.id)
                                       FROM Loogares\LugarBundle\Entity\ImagenLugar u
                                       WHERE u.lugar = ?1
                                       AND u.estado != ?2");
                $q->setParameter(1, $idLugar);
                $q->setParameter(2, 3);
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
    
    public function agregarAction(Request $request, $slug = null, $id = null, $isAdmin = null){

        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $errors = array();
        $formErrors = array();
        $lugaresRevisados = array();
        $camposExtraErrors = false;
        $esEdicion = false;

        if($slug && $isAdmin == null){
            $lugarManipulado = new TempLugar();
            $esEdicionDeUsuario = true;
            $lugar = $lr->findOneBySlug($slug);
        }else if($slug && $isAdmin){
            $tlr = $em->getRepository("LoogaresAdminBundle:TempLugar");
            $lugarManipulado = $lr->findOneBySlug($slug);
            $lugaresRevisados = $tlr->findByLugar($lugarManipulado->getId());
        }else{
            $lugarManipulado = new Lugar();
        }
       
        if($slug && $isAdmin == false){ //Proceso de parseo de datos de lugar existente, SOLO LECTURA/OUTPUT
            //Sacar +56 de los telefonos
            $lugar->tel1 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono1());
            $lugar->tel2 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono2());
            $lugar->tel3 = preg_replace('/^\+[0-9]{2}\s/', '', $lugar->getTelefono3());
        }else if($slug && $isAdmin == true){
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

                $lugarManipulado->setUsuario($this->get('security.context')->getToken()->getUser());

                $comuna = $lr->getComunas($_POST['comuna']);  
                $sector = $lr->getSectores($_POST['sector']);

                $estado = $lr->getEstado(1);
                $tipo_lugar = $lr->getTipoLugar('lugar');

                $lugarManipulado->setComuna($comuna[0]);
                $lugarManipulado->setSector($sector[0]);


                $lugarManipulado->setEstado($estado[0]);
                $lugarManipulado->setTipoLugar($tipo_lugar[0]);

                //Sacamos los HTTP
                $lugarManipulado->setSitioWeb($fn->stripHTTP($lugarManipulado->getSitioWeb()));
                $lugarManipulado->setTwitter($fn->stripHTTP($lugarManipulado->getTwitter()));
                $lugarManipulado->setFacebook($fn->stripHTTP($lugarManipulado->getFacebook()));

                $lugaresConElMismoNombre = $lr->getLugaresPorNombre($lugarManipulado->getNombre());
                
                $lugarManipulado->setFechaAgregado(new \DateTime());

                if(sizeOf($lugaresConElMismoNombre) != 0 && $slug == null){
                    $lugarSlug = $fn->generarSlug($lugarManipulado->getNombre()) . "-" . $_POST['ciudad'].(sizeOf($lugaresConElMismoNombre)+1);
                }else{
                    $lugarSlug = $fn->generarSlug($lugarManipulado->getNombre()) . "-" . $_POST['ciudad'];
                }

                $lugarManipulado->setSlug($lugarSlug);
                
                $em->persist($lugarManipulado);

                $lr->cleanUp($lugarManipulado->getId());

                foreach($_POST['categoria'] as $postCategoria){
                    if($esEdicionDeUsuario == true){
                        $categoriaLugar[] = new TempCategoriaLugar();
                    }else{
                        $categoriaLugar[] = new CategoriaLugar();
                    }
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
                        if($esEdicionDeUsuario == true){
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
                        if($esEdicionDeUsuario == true){
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
                    if($esEdicionDeUsuario == true){
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

                $this->get('session')->setFlash('nuevo-lugar','This is a random message, sup.');
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
                
                return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => $data));
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

        $data['categorias'] = $lr->getCategorias();
        $data['tipoCategoria'] = $lr->getTipoCategorias();
        $data['subCategorias'] = $lr->getSubCategorias();
        $data['caracteristicas'] = $lr->getCaracteristicas();
        $data['ciudad'] = $lr->getCiudades();
        $data['pais'] = $lr->getPaises();
        $data['comuna'] = $lr->getComunas();
        $data['sector'] = $lr->getSectores();
        $data['ciudadActual'] = $lr->getCiudadById('1');
         
        return $this->render('LoogaresLugarBundle:Lugares:agregar.html.twig', array(
            'data' => $data,
            'lugar' => (isset($lugar))?$lugar:$lugarManipulado,
            'lugaresRevisados' => $lugaresRevisados,
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
                        $fn = time().'.jpg';//.$ext;
                        //try {
                        if(file_put_contents('assets/images/temp/'.$fn, $result)) {
                            
                            if(getimagesize('assets/images/temp/'.$fn)) {
                                $imagen = new UploadedFile('assets/images/temp/'.$fn, $fn);
                                $imagenes[] = $imagen;
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

                        $newImagen->setFechaCreacion(new \DateTime());
                        $em->flush();

                        $imgs[] = $newImagen;

                        $newImagen = null;
                    }

                    // Imágenes agregadas desde URLs

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

            // Redirección a galería de fotos (FICHA POR AHORA)
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

            if ($form->isValid()) {
                $imagen->setFechaModificacion(new \DateTime());

                // Verificamos si es URL
                $match = preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $imagen->getTituloEnlace());
                if($match > 0)
                    $imagen->setEsEnlace(1);
                else
                    $imagen->setEsEnlace(0);
                $em->flush();

                // Mensaje de éxito en la edición
                $this->get('session')->setFlash('edicion-foto-lugar','¡Ese es el espíritu, '.$imagen->getUsuario()->getNombre().' '.$imagen->getUsuario()->getApellido().'! Si sigues subiendo fotos, cuando tengamos un hijo le pondremos tu nombre.');
                    
                // Redirección a vista de fotos del usuario
                return $this->redirect($this->generateUrl('fotosLugaresUsuario', array('param' => $ur->getIdOrSlug($imagen->getUsuario()))));
            }
        }

        return $this->render('LoogaresLugarBundle:Lugares:editar_foto.html.twig', array(
            'imagen' => $imagen,
            'form' => $form->createView(),
        ));
    }

    public function eliminarFotoAction(Request $request, $slug, $id) {
        $em = $this->getDoctrine()->getEntityManager();
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");

        $imagen = $ilr->find($id);

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

        $em->flush();
                   
        // Mensaje de éxito de la eliminación
        $this->get('session')->setFlash('eliminar-foto-lugar','Tu foto acaba de ser borrada. Agrega otra cuando quieras.');
                    
        // Redirección a vista de fotos del usuario
        return $this->redirect($this->generateUrl('fotosLugaresUsuario', array('param' => $ur->getIdOrSlug($imagen->getUsuario())))); 
           
    }

    public function galeriaAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);
        $id = $lr->getImagenLugarMasReciente($lugar)->getId();

        return $this->forward('LoogaresLugarBundle:Lugar:fotoGaleria', array('slug' => $slug, 'id' => $id));
    }

    public function fotoGaleriaAction($slug, $id) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");

        $lugar = $lr->findOneBySlug($slug);
        $imagen = $ilr->find($id);
        return $this->render('LoogaresLugarBundle:Lugares:foto_galeria.html.twig', array(
            'lugar' => $lugar,
            'imagen' => $imagen
        ));
    }

    public function comparacionLugarAction($slug){
        $em = $this->getDoctrine()->getEntityManager();
        $tlr = $em->getRepository("LoogaresAdminBundle:TempLugar");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugarTemp = $tlr->findOneBySlug($slug);
        $lugar = $lr->findOneBySlug($slug);

        return $this->render('LoogaresLugarBundle:Lugares:comparacionLugar.html.twig', array(
            'lugarTemp' => $lugarTemp,
            'lugar' => $lugar
        ));
    }
}
