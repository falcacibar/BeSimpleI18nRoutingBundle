<?php

namespace Loogares\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\AdminBundle\Entity\TempTipoLugar
 */
class TempTipoLugar
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
     * @var smallint $prioridad_web
     */
    private $prioridad_web;


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
     * Set prioridad_web
     *
     * @param smallint $prioridadWeb
     */
    public function setPrioridadWeb($prioridadWeb)
    {
        $this->prioridad_web = $prioridadWeb;
    }

    /**
     * Get prioridad_web
     *
     * @return smallint 
     */
    public function getPrioridadWeb()
    {
        return $this->prioridad_web;
    }
}