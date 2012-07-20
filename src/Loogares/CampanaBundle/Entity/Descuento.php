<?php

namespace Loogares\CampanaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\CampanaBundle\Entity\Descuento
 */
class Descuento
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
     * @var datetime $fecha_termino
     */
    private $fecha_termino;

    /**
     * @var integer $cantidad
     */
    private $cantidad;


    /**
     * Set fecha_termino
     *
     * @param datetime $fechaTermino
     */
    public function setFechaTermino($fechaTermino)
    {
        $this->fecha_termino = $fechaTermino;
    }

    /**
     * Get fecha_termino
     *
     * @return datetime 
     */
    public function getFechaTermino()
    {
        return $this->fecha_termino;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }
    /**
     * @var string $condiciones
     */
    private $condiciones;


    /**
     * Set condiciones
     *
     * @param string $condiciones
     */
    public function setCondiciones($condiciones)
    {
        $this->condiciones = $condiciones;
    }

    /**
     * Get condiciones
     *
     * @return string 
     */
    public function getCondiciones()
    {
        return $this->condiciones;
    }
    /**
     * @var Loogares\CampanaBundle\Entity\Campana
     */
    private $campana;


    /**
     * Set campana
     *
     * @param Loogares\CampanaBundle\Entity\Campana $campana
     */
    public function setCampana(\Loogares\CampanaBundle\Entity\Campana $campana)
    {
        $this->campana = $campana;
    }

    /**
     * Get campana
     *
     * @return Loogares\CampanaBundle\Entity\Campana 
     */
    public function getCampana()
    {
        return $this->campana;
    }
}