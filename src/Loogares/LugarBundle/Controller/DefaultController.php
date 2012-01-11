<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Loogares\LugarBundle\Entity\ImagenLugar;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('LoogaresLugarBundle:Lugares:ajax.html.twig');
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
}
