<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * Loogares\LugarBundle\Entity\SubCategoria
 */
class SubCategoria implements Translatable
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
     * @var Loogares\LugarBundle\Entity\SubcategoriaLugar
     */
    private $subcategoria_lugar;

    /**
     * @var Loogares\LugarBundle\Entity\Categoria
     */
    private $categoria;

    /**
    *  Variable que referencia el locale
    */
    private $locale;

    public function __construct()
    {
        $this->subcategoria_lugar = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Add subcategoria_lugar
     *
     * @param Loogares\LugarBundle\Entity\SubcategoriaLugar $subcategoriaLugar
     */
    public function addSubcategoriaLugar(\Loogares\LugarBundle\Entity\SubcategoriaLugar $subcategoriaLugar)
    {
        $this->subcategoria_lugar[] = $subcategoriaLugar;
    }

    /**
     * Get subcategoria_lugar
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSubcategoriaLugar()
    {
        return $this->subcategoria_lugar;
    }

    /**
     * Set categoria
     *
     * @param Loogares\LugarBundle\Entity\Categoria $categoria
     */
    public function setCategoria(\Loogares\LugarBundle\Entity\Categoria $categoria)
    {
        $this->categoria = $categoria;
    }

    /**
     * Get categoria
     *
     * @return Loogares\LugarBundle\Entity\Categoria 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
    *  Método que setea el locale de la entidad
    */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}