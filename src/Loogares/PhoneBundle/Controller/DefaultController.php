<?php

namespace Loogares\PhoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function listadoCategoriasAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $tlr = $em->getRepository("LoogaresLugarBundle:TipoCategoria");    
        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\TipoCategoria u ORDER BY u.prioridad_web asc");
        $tipoCategoria = $q->getResult();
        $ciudad = $this->get('session')->get('ciudad');
        $idCiudad = $ciudad['id'];
        
        foreach($tipoCategoria as $key => $value){
            $id = $value->getId();
            $buff = $this->getDoctrine()
            ->getConnection()->fetchAll("SELECT count(categorias.id) as total, categorias.nombre as categoria_nombre, categorias.slug as categoria_slug, tipo_categoria.nombre
                                         FROM lugares

                                         JOIN comuna
                                         ON comuna.id = lugares.comuna_id

                                         LEFT JOIN categoria_lugar
                                         ON categoria_lugar.lugar_id = lugares.id

                                         JOIN categorias
                                         ON categorias.id = categoria_lugar.categoria_id

                                         LEFT JOIN tipo_categoria
                                         ON tipo_categoria.id = categorias.tipo_categoria_id

                                         WHERE tipo_categoria.id = $id AND comuna.ciudad_id = $idCiudad AND lugares.estado_id != 3

                                         GROUP BY categorias.id
                                         ORDER BY tipo_categoria.id, categorias.nombre asc");
            $data[]['tipo'] = $value->getNombre();
            $data[sizeOf($data)-1]['categorias'] = $buff;
        }

        $json = json_encode($data);

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));
    }

    public function lugaresPorCategoriaAction($categoria = null){
        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresLugarBundle:Categoria");
        $offset = 0;
        $data = array();

        if($categoria == null){
            $q = $em->createQuery("SELECT cl, (l.estrellas*6 + l.utiles + l.total_recomendaciones*2) as ranking 
                                   FROM Loogares\LugarBundle\Entity\CategoriaLugar cl
                                   JOIN cl.lugar l GROUP BY l.id ORDER BY ranking DESC");
        }else{
            $categoria = $cr->findBySlug($categoria);
            $q = $em->createQuery("SELECT cl, (l.estrellas*6 + l.utiles + l.total_recomendaciones*2) as ranking 
                                   FROM Loogares\LugarBundle\Entity\CategoriaLugar cl 
                                   JOIN cl.lugar l WHERE cl.categoria = ?1 GROUP BY l.id ORDER BY ranking DESC");
            $q->setParameter(1, $categoria);
        }
        $q->setMaxResults(20);
        $q->setFirstResult($offset);
        $categoriaLugar = $q->getResult();

        for($i=0;$i<20;$i++){
            $data[]['nombre'] = $categoriaLugar[$i][0]->getLugar()->getNombre();
            $data[sizeOf($data)-1]['slug'] = $categoriaLugar[$i][0]->getLugar()->getSlug();
            $data[sizeOf($data)-1]['estrellas'] = $categoriaLugar[$i][0]->getLugar()->getEstrellas();
            $data[sizeOf($data)-1]['calle'] = $categoriaLugar[$i][0]->getLugar()->getCalle();
            $data[sizeOf($data)-1]['numero'] = $categoriaLugar[$i][0]->getLugar()->getNumero();
            $imagenes = $categoriaLugar[$i][0]->getLugar()->getImagenesActivasLugar();
            $data[sizeOf($data)-1]['imagen'] = $imagenes[sizeOf($imagenes)-1]->getImagenFull();
            $data[sizeOf($data)-1]['totalRecomendaciones'] = $categoriaLugar[$i][0]->getLugar()->getTotalRecomendaciones();
        }   

        $json = json_encode($data);

        return $this->render('LoogaresPhoneBundle:Default:json.html.twig', array('json' => $json));  
    }
}
