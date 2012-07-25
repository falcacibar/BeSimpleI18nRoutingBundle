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
     * @var datetime $fecha_termino
     */
    private $fecha_termino;

    /**
     * @var Loogares\BlogBundle\Entity\Concurso
     */
    private $concursos;

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

    public function __construct()
    {
        $this->concursos = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add concursos
     *
     * @param Loogares\BlogBundle\Entity\Concurso $concursos
     */
    public function addConcurso(\Loogares\BlogBundle\Entity\Concurso $concursos)
    {
        $this->concursos[] = $concursos;
    }

    /**
     * Get concursos
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getConcursos()
    {
        return $this->concursos;
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