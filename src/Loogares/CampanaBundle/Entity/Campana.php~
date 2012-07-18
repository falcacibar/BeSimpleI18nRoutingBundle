<?php

namespace Loogares\CampanaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\CampanaBundle\Entity\Campana
 */
class Campana
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $nombre
     */
    private $nombre;

    /**
     * @var datetime $fecha_inicio
     */
    private $fecha_inicio;

    /**
     * @var Loogares\CampanaBundle\Entity\Descuento
     */
    private $descuento;

    /**
     * @var Loogares\ExtraBundle\Entity\Estado
     */
    private $estado;

    /**
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;


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
     * Set nombre
     *
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set fecha_inicio
     *
     * @param datetime $fechaInicio
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fecha_inicio = $fechaInicio;
    }

    /**
     * Get fecha_inicio
     *
     * @return datetime 
     */
    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    /**
     * Set descuento
     *
     * @param Loogares\CampanaBundle\Entity\Descuento $descuento
     */
    public function setDescuento(\Loogares\CampanaBundle\Entity\Descuento $descuento)
    {
        $this->descuento = $descuento;
    }

    /**
     * Get descuento
     *
     * @return Loogares\CampanaBundle\Entity\Descuento 
     */
    public function getDescuento()
    {
        return $this->descuento;
    }

    /**
     * Set estado
     *
     * @param Loogares\ExtraBundle\Entity\Estado $estado
     */
    public function setEstado(\Loogares\ExtraBundle\Entity\Estado $estado)
    {
        $this->estado = $estado;
    }

    /**
     * Get estado
     *
     * @return Loogares\ExtraBundle\Entity\Estado 
     */
    public function getEstado()
    {
        return $this->estado;
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
}