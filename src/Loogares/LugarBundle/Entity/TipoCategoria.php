<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * Loogares\LugarBundle\Entity\TipoCategoria
 */
class TipoCategoria implements Translatable
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
     * @var string $prioridad_web
     */
    private $prioridad_web;

    /**
    *  Variable que referencia el locale
    */
    private $locale;


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
     * @param string $prioridadWeb
     */
    public function setPrioridadWeb($prioridadWeb)
    {
        $this->prioridad_web = $prioridadWeb;
    }

    /**
     * Get prioridad_web
     *
     * @return string 
     */
    public function getPrioridadWeb()
    {
        return $this->prioridad_web;
    }

    /**
    *  Método que setea el locale de la entidad
    */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    /**
     * @var Loogares\LugarBundle\Entity\Categoria
     */
    private $categorias;

    public function __construct()
    {
        $this->categorias = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add categorias
     *
     * @param Loogares\LugarBundle\Entity\Categoria $categorias
     */
    public function addCategoria(\Loogares\LugarBundle\Entity\Categoria $categorias)
    {
        $this->categorias[] = $categorias;
    }

    /**
     * Get categorias
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCategorias()
    {
        return $this->categorias;
    }
}