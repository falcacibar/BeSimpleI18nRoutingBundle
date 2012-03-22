<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * Loogares\LugarBundle\Entity\Categoria
 */
class Categoria implements Translatable
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
     * @var smallint $mostrar_precio
     */
    private $mostrar_precio;

    /**
     * @var integer $tipo_categoria_id
     */
    private $tipo_categoria_id;

    /**
     * @var Loogares\LugarBundle\Entity\CategoriaLugar
     */
    private $categoria_lugar;

    /**
     * @var Loogares\LugarBundle\Entity\TipoCategoria
     */
    private $tipo_categoria;

    /**
    *  Variable que referencia el locale
    */
    private $locale;

    public function __construct()
    {
        $this->categoria_lugar = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set mostrar_precio
     *
     * @param smallint $mostrarPrecio
     */
    public function setMostrarPrecio($mostrarPrecio)
    {
        $this->mostrar_precio = $mostrarPrecio;
    }

    /**
     * Get mostrar_precio
     *
     * @return smallint 
     */
    public function getMostrarPrecio()
    {
        return $this->mostrar_precio;
    }

    /**
     * Set tipo_categoria_id
     *
     * @param integer $tipoCategoriaId
     */
    public function setTipoCategoriaId($tipoCategoriaId)
    {
        $this->tipo_categoria_id = $tipoCategoriaId;
    }

    /**
     * Get tipo_categoria_id
     *
     * @return integer 
     */
    public function getTipoCategoriaId()
    {
        return $this->tipo_categoria_id;
    }

    /**
     * Add categoria_lugar
     *
     * @param Loogares\LugarBundle\Entity\CategoriaLugar $categoriaLugar
     */
    public function addCategoriaLugar(\Loogares\LugarBundle\Entity\CategoriaLugar $categoriaLugar)
    {
        $this->categoria_lugar[] = $categoriaLugar;
    }

    /**
     * Get categoria_lugar
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCategoriaLugar()
    {
        return $this->categoria_lugar;
    }

    /**
     * Set tipo_categoria
     *
     * @param Loogares\LugarBundle\Entity\TipoCategoria $tipoCategoria
     */
    public function setTipoCategoria(\Loogares\LugarBundle\Entity\TipoCategoria $tipoCategoria)
    {
        $this->tipo_categoria = $tipoCategoria;
    }

    /**
     * Get tipo_categoria
     *
     * @return Loogares\LugarBundle\Entity\TipoCategoria 
     */
    public function getTipoCategoria()
    {
        return $this->tipo_categoria;
    }

    /**
    *  Método que setea el locale de la entidad
    */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    /**
     * @var smallint $mostrar_categoria
     */
    private $mostrar_categoria;


    /**
     * Set mostrar_categoria
     *
     * @param smallint $mostrarCategoria
     */
    public function setMostrarCategoria($mostrarCategoria)
    {
        $this->mostrar_categoria = $mostrarCategoria;
    }

    /**
     * Get mostrar_categoria
     *
     * @return smallint 
     */
    public function getMostrarCategoria()
    {
        return $this->mostrar_categoria;
    }
}