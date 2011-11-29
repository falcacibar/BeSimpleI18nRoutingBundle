<?php

namespace Loogares\ExtraBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\ExtraBundle\Entity\Comuna
 */
class Comuna
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
     * @var smallint $mostrar_lugar
     */
    private $mostrar_lugar;


    /**
     * Set mostrar_lugar
     *
     * @param smallint $mostrarLugar
     */
    public function setMostrarLugar($mostrarLugar)
    {
        $this->mostrar_lugar = $mostrarLugar;
    }

    /**
     * Get mostrar_lugar
     *
     * @return smallint 
     */
    public function getMostrarLugar()
    {
        return $this->mostrar_lugar;
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
}