<?php

namespace Loogares\CommonBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Loogares\LugarBundle\Entity\TipoCategoria;

define('CommonDBDataListaCategorias', 0);

class CommonBundle extends Bundle
{
		static public function DBData($name)
		{
			global $kernel;

			$doctrine = $kernel->getContainer('doctrine');

			switch($name) {
				case CommonDBDataListaCategorias:
					$em = $doctrine->getEntityManager();
			        $tlr = $em->getRepository("LoogaresLugarBundle:TipoCategoria");
			        $q = $em->createQuery("SELECT u FROM Loogares\LugarBundle\Entity\TipoCategoria u ORDER BY u.prioridad_web asc");
			        $tipoCategoria = $q->getResult();
			        $ciudad = $this->get('session')->get('ciudad');
			        $idCiudad = $ciudad['id'];
			        $data = array();

			        foreach($tipoCategoria as $key => $value){
			            $id = $value->getId();
			            $buff = $this->getDoctrine()
			            ->getConnection()->fetchAll("SELECT count(categorias.id) as total, categorias.nombre aso
			                                         LEFT JOIN categoria_lugar
			                                         ON categoria_lugar.lugar_id = lugares.id

			                                         JOIN categorias
			                                         ON categorias.id = categoria_lugar.categoria_id

			                                         LEFT JOIN tipo_categoria
			                                         ON tipo_categoria.id = categorias.tipo_categoria_id

			                                         WHERE tipo_categoria.id = $id AND comuna.ciudad_id = $idCiudad AND lugares.estado_id != 3

			                                         GROUP BY categorias.id
			                                         ORDER BY tipo_categoria.id, categorias.nombre asc");
			            $data[$value->getSlug()]['tipo'] = $tipoCategoria[$key];
			            $data[$value->getSlug()]['categorias'] = $buff;
			        };

			        return $data;
				break;
				default;
					return false;
			}
		}
}
