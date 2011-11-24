<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\ReportarRecomendacion
 */
class ReportarRecomendacion
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var text $reporte
     */
    private $reporte;

    /**
     * @var datetime $fecha
     */
    private $fecha;

    /**
     * @var Loogares\UsuarioBundle\Entity\Recomendacion
     */
    private $recomendacion;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;


    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reporte
     *
     * @param text $reporte
     */
    public function setReporte($reporte)
    {
        $this->reporte = $reporte;
    }

    /**
     * Get reporte
     *
     * @return text 
     */
    public function getReporte()
    {
        return $this->reporte;
    }

    /**
     * Set fecha
     *
     * @param datetime $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * Get fecha
     *
     * @return datetime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set recomendacion
     *
     * @param Loogares\UsuarioBundle\Entity\Recomendacion $recomendacion
     */
    public function setRecomendacion(\Loogares\UsuarioBundle\Entity\Recomendacion $recomendacion)
    {
        $this->recomendacion = $recomendacion;
    }

    /**
     * Get recomendacion
     *
     * @return Loogares\UsuarioBundle\Entity\Recomendacion 
     */
    public function getRecomendacion()
    {
        return $this->recomendacion;
    }

    /**
     * Set usuario
     *
     * @param Loogares\UsuarioBundle\Entity\Usuario $usuario
     */
    public function setUsuario(\Loogares\UsuarioBundle\Entity\Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Get usuario
     *
     * @return Loogares\UsuarioBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}