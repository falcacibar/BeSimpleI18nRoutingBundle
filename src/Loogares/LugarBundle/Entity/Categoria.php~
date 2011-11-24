<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\Categoria
 */
class Categoria
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
     * @var Loogares\LugarBundle\Entity\TipoCategoria
     */
    private $tipo_categoria;


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
}