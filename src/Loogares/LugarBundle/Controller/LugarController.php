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
use Loogares\LugarBundle\Entity\ReportarLugar;
use Loogares\LugarBundle\Entity\ReportarImagen;
use Loogares\LugarBundle\Entity\ReportarRecomendacion;

use Loogares\UsuarioBundle\Entity\Recomendacion;
use Loogares\UsuarioBundle\Entity\Tag;
use Loogares\UsuarioBundle\Entity\TagRecomendacion;
use Loogares\UsuarioBundle\Entity\Dueno;

use Loogares\AdminBundle\Entity\TempLugar;
use Loogares\AdminBundle\Entity\TempCategoriaLugar;
use Loogares\AdminBundle\Entity\TempCaracteristicaLugar;
use Loogares\AdminBundle\Entity\TempHorario;
use Loogares\AdminBundle\Entity\TempSubcategoriaLugar;



class LugarController extends Controller{

    public function lugarAction($slug, Request $request, $usuarioSlug = false){
                $fn = $this->get('fn');
                $_GET['pagina'] = (!isset($_GET['pagina']))?1:$_GET['pagina'];
                $_GET['orden'] = (!isset($_GET['orden']))?'ultimas':$_GET['orden'];
                $paginaActual = (isset($_GET['pagina']))?$_GET['pagina']:1;
                $resultadosPorPagina = (!isset($_GET['resultados']))?10:$_GET['resultados'];
                $offset = ($paginaActual == 1)?0:floor(($paginaActual-1)*$resultadosPorPagina);
                $router = $this->get('router');
                $precioPromedio = 0;
                $estrellasPromedio = 0;
                $sacarRecomendacionPedida = false;

                $em = $this->getDoctrine()->getEntityManager();
                $qb = $em->createQueryBuilder();
                $lr = $em->getRepository('LoogaresLugarBundle:Lugar');
                
                $lugarResult = $lr->getLugares($slug);

                //Id del Lugar
                if(!isset($lugarResult[0])){
                    return $this->render(':erroresHTTP:404.html.twig');   
                }

                $visitas = $lugarResult[0]->getVisitas();
                $visitas++;
                $lugarResult[0]->setVisitas($visitas);
                $em->persist($lugarResult[0]);
                $em->flush();

                $idLugar = $lugarResult[0]->getId();
                $idUsuario = ($this->get('security.context')->isGranted('ROLE_USER')) ? $this->get('security.context')->getToken()->getUser()->getId() : 0;
                $codigoArea = $lugarResult[0]->getComuna()->getCiudad()->getPais()->getCodigoArea();

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
                                       WHERE u.lugar = ?1 and u.estado != ?2");
                $q->setParameter(1, $idLugar);
                $q->setParameter(2, 3);
                $totalRecomendacionesResult = $q->getSingleScalarResult();

                //Query para sacar el Total de las recomendaciones
                $q = $em->createQuery("SELECT u.id 
                                       FROM Loogares\UsuarioBundle\Entity\Recomendacion u 
                                       WHERE u.usuario = ?1 and u.lugar = ?2 and u.estado != ?3");
                $q->setParameter(1, $idUsuario);                      
                $q->setParameter(2, $idLugar);
                $q->setParameter(3, 3);
                $yaRecomendoResult = $q->getResult();

                if($usuarioSlug != false){
                    $ur = $em->getRepository('LoogaresUsuarioBundle:Usuario');
                    if(preg_match('/\w/', $usuarioSlug)){
                        $usuario = $ur->findOneBySlug($usuarioSlug);
                    }else{
                        $usuario = $ur->findOneById($usuarioSlug);
                    }

                    if($usuario){ 
                        $idRecomendacionPedida = $usuario->getId();

                        //Sacar la recomendacion del usuario slugeado
                        $recomendacionPedidaResult = $this->getDoctrine()->getConnection()->fetchAll("SELECT recomendacion.*, group_concat(DISTINCT tag.tag) as tags, count(DISTINCT util.id) AS utiles, usuarios.slug, usuarios.imagen_full, usuarios.nombre, usuarios.apellido, usuarios.id as userId,
                            (select min(id) from util where util.usuario_id = $idRecomendacionPedida and util.recomendacion_id = recomendacion.id) as apretoUtil
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
                                                                                 AND recomendacion.usuario_id = $idRecomendacionPedida
                                                                                 AND recomendacion.estado_id != 3
                                                                                 GROUP BY recomendacion.id
                                                                                 LIMIT 1");
                            //Explotamos los tags, BOOM
                            for($i = 0; $i < sizeOf($recomendacionPedidaResult); $i++){
                                $recomendacionPedidaResult[$i]['tags'] = explode(',', $recomendacionPedidaResult[$i]['tags']); 
                            }

                            $resultadosPorPagina++;
                    }else{
                        $usuarioSlug = false;
                    }
                    
                }


                //Definicion del orden para la siguiente consulta
                if($_GET['orden'] == 'ultimas'){
                        $orderBy = "ORDER BY recomendacion.fecha_creacion DESC";
                }else if($_GET['orden'] == 'mas-utiles'){
                        $orderBy = "ORDER BY utiles DESC";
                }else if($_GET['orden'] == 'mejor-evaluadas'){
                        $orderBy = "ORDER BY recomendacion.estrellas desc, recomendacion.fecha_creacion DESC";
                }

                //Query para las recomendaciones a mostrar
                $recomendacionesResult = $this->getDoctrine()->getConnection()->fetchAll("SELECT recomendacion.*, group_concat(DISTINCT tag.tag) as tags, count(DISTINCT util.id) AS utiles, usuarios.slug, usuarios.imagen_full, usuarios.nombre, usuarios.apellido, usuarios.id as userId,
                    (select min(id) from util where util.usuario_id = $idUsuario and util.recomendacion_id = recomendacion.id) as apretoUtil
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
                                                                         AND recomendacion.estado_id != 3
                                                                         $sacarRecomendacionPedida
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
                if($usuarioSlug != false){
                    $data->recomendacionPedida = $recomendacionPedidaResult[0];
                }
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
                $data->yaRecomendo = $yaRecomendoResult;
                $data->mostrarPrecio = $fn->mostrarPrecio($lugarResult[0]);
                //Offset de comentarios mostrados, "mostrando 1 a 10 de 20"
                $data->mostrandoComentariosDe = $_GET['pagina'] * ($_GET['pagina'] != 1)?(10 + 1):1;
                $data->totalFotos = $totalFotosResult;
                $data->recomendacionesPorPagina = $resultadosPorPagina;
                $tp = $lr->getTagsPopulares($idLugar);
                $data->tagsPopulares = $lr->getTagsPopulares($idLugar);

                $params = array(
                    'slug' => $data->getSlug()
                );

                $paginacion = $fn->paginacion( $data->totalRecomendaciones, $resultadosPorPagina, '_lugar', $params, $router );

                //Render ALL THE VIEWS
                return $this->render('LoogaresLugarBundle:Lugares:lugar.html.twig', array('lugar' => $data, 'query' => $_GET, 'paginacion' => $paginacion));            
    }
    
    public function agregarAction(Request $request, $slug = null){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $errors = array();
        $formErrors = array();
        $lugaresRevisados = array();
        $camposExtraErrors = false;
        $esEdicionDeUsuario = false;
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

                $lugarManipulado->setUsuario($this->get('security.context')->getToken()->getUser());

                $comuna = $lr->getComunas($_POST['comuna']);  
                $sector = $lr->getSectores($_POST['sector']);


                $estado = $lr->getEstado(1);
                $tipo_lugar = $lr->getTipoLugar('lugar');

                $lugarManipulado->setComuna($comuna[0]);
                $lugarManipulado->setSector($sector[0]);


                $lugarManipulado->setEstado($estado);
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
                    if($slug && $rolAdmin == false){
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
                        if($rolAdmin == false){
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
                        if($rolAdmin == false){
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
                    if($rolAdmin == false){
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

                if(isset($_POST['texto']) && $_POST['texto'] != ''){
                    //CURL MAGIC
                    if(isset($_POST['recomienda-precio'])){
                        $precio = $_POST['precio'];
                    }else{
                        $precio = '';
                    }

                    if(isset($_POST['recomienda-estrellas'])){
                        $estrellas = $_POST['recomendacion-estrellas'];
                    }else{
                        $estrellas = '';
                    }
                    
                    //set POST variables
                    $fields_string = '';
                    $url = "http://".$_SERVER['SERVER_NAME'].$this->generateUrl('_recomendacion', array('slug' => $lugarManipulado->getSlug()));
                    $fields = array(
                        'texto'=> urlencode($_POST['texto']),
                        'tags'=> urlencode($_POST['tags']),
                        'estrellas'=> urlencode($estrellas),
                        'precio' => urlencode($precio),
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
                    $result = curl_exec($ch);

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
                    //Edicion o Agregar Success
                    
                    if($esEdicionDeUsuario == true){
                        $this->get('session')->setFlash('lugar_flash','Wena campeon, edito el lugar.');
                    }else{
                        $this->get('session')->setFlash('lugar_flash','Wena campeon, agrego el lugar.');
                    }
                    return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugarManipulado->getSlug())));
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
            $anchoDefault = 610;
            $altoDefault = 500;
            $ancho = $sizeArray[0];
            $alto = $sizeArray[1];        

            // Primer caso: sólo ancho mayor que default
            if($ancho > $anchoDefault && $alto <= $altoDefault) {
                $dimensiones['ancho'] = $anchoDefault;
            }

            // Segundo caso: ancho mayor que default, pero alto mayor que default y ancho
            else if($ancho > $anchoDefault && $alto > $altoDefault && $alto > $ancho) {
                $dimensiones['alto'] = $altoDefault;
            }

            // Tercer caso: sólo alto mayor que default
            else if($alto > $altoDefault && $ancho <= $anchoDefault ) {
                $dimensiones['alto'] = $altoDefault;
            }

            // Cuarto caso: alto mayor que default, pero ancho mayor que default y alto
            else if($alto > $altoDefault && $ancho > $anchoDefault && $ancho > $alto) {
                $dimensiones['ancho'] = $anchoDefault;
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
        ));
    }

    /* RECOMENDACIONES */
    public function recomendacionesAction($slug, Request $request, $curlSuperVar = false){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $tr = $em->getRepository("LoogaresUsuarioBundle:Tag");
        $trr = $em->getRepository("LoogaresUsuarioBundle:TagRecomendacion");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $fn = $this->get('fn');
        $lugar = $lr->findOneBySlug($slug);

        // Flag que determina si la recomendación es nueva o no
        $nueva = true;

        if(isset($_POST['editando']) && $_POST['editando'] == 1){
            $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u WHERE u.usuario = ?1 and u.lugar = ?2");
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

        if ($request->getMethod() == 'POST') {
            $newTagRecomendacion = array();
            $tag = array();
            $recomendacion->setTexto($_POST['texto']);
            $recomendacion->setEstrellas($_POST['recomienda-estrellas']);
            $estado = $lr->getEstado(1);
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
            $em->flush();
            $lr->actualizarPromedios($lugar->getSlug());

            // Se envía mail al lugar
            if($lugar->getMail() != null && $lugar->getMail() != '') {
                // Buscamos dueño del lugar
                $owner = $em->getRepository("LoogaresUsuarioBundle:Usuario")->getDuenoLugar($recomendacion->getLugar()->getId());

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
                $message = \Swift_Message::newInstance()
                        ->setSubject($mail['asunto'])
                        ->setFrom('noreply@loogares.com')
                        ->setTo($lugar->getMail());
                
                $assets = array();
                $assets['logo'] = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $assets['estrella_llena'] =  $message->embed(\Swift_Image::fromPath('assets/images/extras/estrella_llena_recomendacion.png'));
                $assets['estrella_media'] =  $message->embed(\Swift_Image::fromPath('assets/images/extras/estrella_media_recomendacion.png'));
                $assets['estrella_vacia'] =  $message->embed(\Swift_Image::fromPath('assets/images/extras/estrella_vacia_recomendacion.png'));                               
                $assets['usuario'] = $message->embed(\Swift_Image::fromPath('assets/images/usuarios/'.$recomendacion->getUsuario()->getImagenFull()));

                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_lugar.html.twig', array('mail' => $mail, 'assets' => $assets)), 'text/html');
                $this->get('mailer')->send($message);
            }

            if(isset($_POST['curlSuperVar']) && $_POST['curlSuperVar'] == 1){
                return new Response('',200);
            }else{
                //SET FLASH AND REDIRECTTT
                $this->get('session')->setFlash('lugar_flash','Wena campeon, recomendo el lugar.');
                return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
            }

            
        }
    }

    public function accionRecomendacionAction($slug, $borrar){
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);
        
        if($borrar == true){
            $q = $em->createQuery("SELECT u FROM Loogares\UsuarioBundle\Entity\Recomendacion u WHERE u.usuario = ?1 and u.lugar = ?2");
            $q->setParameter(1, $this->get('security.context')->getToken()->getUser()->getId());
            $q->setParameter(2, $lugar->getId());
            $recomendacionResult = $q->getSingleResult();

            $estado = $lr->getEstado(3);
            $recomendacionResult->setEstado($estado);

            $em->flush();
            $this->get('session')->setFlash('lugar_flash','Wena campeon, borro su recomendacion :(.');
            return $this->redirect($this->generateUrl('_lugar', array('slug' => $lugar->getSlug())));
        }
    }

    public function enviarLugarAction(Request $request, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $formErrors = array();

        $lugar = $lr->findOneBySlug($slug);

        if($lugar == null)
            throw $this->createNotFoundException('El lugar con slug '.$slug. ' no existe.');

        // Si el request es POST, se procesa el envío del mail
        if ($request->getMethod() == 'POST') {

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
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                    $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_enviar.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                    $this->get('mailer')->send($message);  
                }

                // Mensaje de éxito en el envío
                $this->get('session')->setFlash('envio-mail','lugar.flash.compartir.mail');
                    
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
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                    $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_enviar.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                    $this->get('mailer')->send($message);  
                }

                // Mensaje de éxito en el envío
                $this->get('session')->setFlash('envio-mail','recomendacion.flash.compartir.mail');
                    
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
            throw $this->createNotFoundException('No puedes reportar una imagen agregada por ti.');   
        }
        else {
            $reportes = $lr->getReportesImagenesUsuarioLugar($imagen->getId(), $this->get('security.context')->getToken()->getUser(), 1);
            if(sizeof($reportes) > 0)
                throw $this->createNotFoundException('Ya has reportado esta imagen anteriormente, y aún está en revisión. Una vez finalizado este proceso, podrás reportar la imagen nuevamente.');   
        }

        $reporte = new ReportarImagen();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') { 
            $form->bindRequest($request);

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
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);               

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('reportar-imagen','reportes.flash');
                    
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
            throw $this->createNotFoundException('No puedes reportar una recomendación hecha por ti.');   
        }
        else {
            $reportes = $rr->getReportesRecomendacionUsuario($recomendacion->getId(), $this->get('security.context')->getToken()->getUser(), 1);
            if(sizeof($reportes) > 0)
                throw $this->createNotFoundException('Ya has reportado esta recomendación anteriormente, y aún está en revisión. Una vez finalizado este proceso, podrás reportar la recomendación nuevamente.');   
        }

        $reporte = new ReportarRecomendacion();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') { 
            $form->bindRequest($request);

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
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);               

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('reportar-recomendacion','reportes.flash');
                    
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
        if(sizeof($reportes) > 0)
            throw $this->createNotFoundException('Nos has notificado anteriormente que este lugar cerró. Aún estamos corroborando datos al respecto.');

        $reporte = new ReportarLugar();

        $form = $this->createFormBuilder($reporte)
                         ->add('reporte', 'textarea')
                         ->getForm();

        if ($request->getMethod() == 'POST') { 
            $form->bindRequest($request);

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
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message);               

                 // Mensaje de éxito del reporte
                $this->get('session')->setFlash('reportar-lugar','reportes.flash');
                    
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
                    $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                    $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                    $this->get('mailer')->send($message);

                    $this->get('session')->setFlash('nuevo-dueno','dueno.reclamar.flash.nuevo');
                }                
            }
            // Request proviene desde la URL (a mano)
            else {
                $this->get('session')->setFlash('existe-dueno','dueno.reclamar.flash.existe');   
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
                $logo = $message->embed(\Swift_Image::fromPath('assets/images/extras/logo_mails.jpg'));
                $message->setBody($this->renderView('LoogaresLugarBundle:Mails:mail_reporte.html.twig', array('mail' => $mail, 'logo' => $logo)), 'text/html');
                $this->get('mailer')->send($message); 

                // Mensaje de éxito en el envío
                $this->get('session')->setFlash('nuevo-dueno','dueno.reclamar.flash.nuevo');   
                    
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
}
