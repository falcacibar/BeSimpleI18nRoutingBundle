<?php

namespace Loogares\ExtraBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\ExtraBundle\Entity\Sector
 */
class Sector
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
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $mapa
     */
    private $mapa;

    /**
     * @var integer $prioridad_sector
     */
    private $prioridad_sector;

    /**
     * @var smallint $habilitado
     */
    private $habilitado;

    /**
     * @var Loogares\ExtraBundle\Entity\Ciudad
     */
    private $pais;


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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set mapa
     *
     * @param string $mapa
     */
    public function setMapa($mapa)
    {
        $this->mapa = $mapa;
    }

    /**
     * Get mapa
     *
     * @return string 
     */
    public function getMapa()
    {
        return $this->mapa;
    }

    /**
     * Set prioridad_sector
     *
     * @param integer $prioridadSector
     */
    public function setPrioridadSector($prioridadSector)
    {
        $this->prioridad_sector = $prioridadSector;
    }

    /**
     * Get prioridad_sector
     *
     * @return integer 
     */
    public function getPrioridadSector()
    {
        return $this->prioridad_sector;
    }

    /**
     * Set habilitado
     *
     * @param smallint $habilitado
     */
    public function setHabilitado($habilitado)
    {
        $this->habilitado = $habilitado;
    }

    /**
     * Get habilitado
     *
     * @return smallint 
     */
    public function getHabilitado()
    {
        return $this->habilitado;
    }

    /**
     * Set pais
     *
     * @param Loogares\ExtraBundle\Entity\Ciudad $pais
     */
    public function setPais(\Loogares\ExtraBundle\Entity\Ciudad $pais)
    {
        $this->pais = $pais;
    }

    /**
     * Get pais
     *
     * @return Loogares\ExtraBundle\Entity\Ciudad 
     */
    public function getPais()
    {
        return $this->pais;
    }
    /**
     * @var Loogares\ExtraBundle\Entity\Ciudad
     */
    private $ciudad;


    /**
     * Set ciudad
     *
     * @param Loogares\ExtraBundle\Entity\Ciudad $ciudad
     */
    public function setCiudad(\Loogares\ExtraBundle\Entity\Ciudad $ciudad)
    {
        $this->ciudad = $ciudad;
    }

    /**
     * Get ciudad
     *
     * @return Loogares\ExtraBundle\Entity\Ciudad 
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }
    /**
     * @var integer $mostrar
     */
    private $mostrar;


    /**
     * Set mostrar
     *
     * @param integer $mostrar
     */
    public function setMostrar($mostrar)
    {
        $this->mostrar = $mostrar;
    }

    /**
     * Get mostrar
     *
     * @return integer 
     */
    public function getMostrar()
    {
        return $this->mostrar;
    }
    /**
     * @var integer $mostrar_lugar
     */
    private $mostrar_lugar;


    /**
     * Set mostrar_lugar
     *
     * @param integer $mostrarLugar
     */
    public function setMostrarLugar($mostrarLugar)
    {
        $this->mostrar_lugar = $mostrarLugar;
    }

    /**
     * Get mostrar_lugar
     *
     * @return integer 
     */
    public function getMostrarLugar()
    {
        return $this->mostrar_lugar;
    }
}