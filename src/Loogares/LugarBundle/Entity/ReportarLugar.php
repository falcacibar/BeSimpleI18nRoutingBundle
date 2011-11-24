<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\ReportarLugar
 */
class ReportarLugar
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
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;

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
     * Set lugar
     *
     * @param Loogares\LugarBundle\Entity\Lugar $lugar
     */
    public function setLugar(\Loogares\LugarBundle\Entity\Lugar $lugar)
    {
        $this->lugar = $lugar;
    }

    /**
     * Get lugar
     *
     * @return Loogares\LugarBundle\Entity\Lugar 
     */
    public function getLugar()
    {
        return $this->lugar;
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