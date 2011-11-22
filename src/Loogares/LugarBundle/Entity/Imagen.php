<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\Imagen
 */
class Imagen
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $titulo
     */
    private $titulo;

    /**
     * @var integer $enlace
     */
    private $enlace;

    /**
     * @var datetime $fecha_creacion
     */
    private $fecha_creacion;

    /**
     * @var datetime $fecha_modificacion
     */
    private $fecha_modificacion;

    /**
     * @var string $imagen_full
     */
    private $imagen_full;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $caracteristica;

    /**
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;

    /**
     * @var Loogares\LugarBundle\Entity\EstadoImagen
     */
    private $estado_imagen;


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
     * Set titulo
     *
     * @param string $titulo
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    /**
     * Get titulo
     *
     * @return string 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set enlace
     *
     * @param integer $enlace
     */
    public function setEnlace($enlace)
    {
        $this->enlace = $enlace;
    }

    /**
     * Get enlace
     *
     * @return integer 
     */
    public function getEnlace()
    {
        return $this->enlace;
    }

    /**
     * Set fecha_creacion
     *
     * @param datetime $fechaCreacion
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fecha_creacion = $fechaCreacion;
    }

    /**
     * Get fecha_creacion
     *
     * @return datetime 
     */
    public function getFechaCreacion()
    {
        return $this->fecha_creacion;
    }

    /**
     * Set fecha_modificacion
     *
     * @param datetime $fechaModificacion
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fecha_modificacion = $fechaModificacion;
    }

    /**
     * Get fecha_modificacion
     *
     * @return datetime 
     */
    public function getFechaModificacion()
    {
        return $this->fecha_modificacion;
    }

    /**
     * Set imagen_full
     *
     * @param string $imagenFull
     */
    public function setImagenFull($imagenFull)
    {
        $this->imagen_full = $imagenFull;
    }

    /**
     * Get imagen_full
     *
     * @return string 
     */
    public function getImagenFull()
    {
        return $this->imagen_full;
    }

    /**
     * Set caracteristica
     *
     * @param Loogares\UsuarioBundle\Entity\Usuario $caracteristica
     */
    public function setCaracteristica(\Loogares\UsuarioBundle\Entity\Usuario $caracteristica)
    {
        $this->caracteristica = $caracteristica;
    }

    /**
     * Get caracteristica
     *
     * @return Loogares\UsuarioBundle\Entity\Usuario 
     */
    public function getCaracteristica()
    {
        return $this->caracteristica;
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

    /**
     * Set estado_imagen
     *
     * @param Loogares\LugarBundle\Entity\EstadoImagen $estadoImagen
     */
    public function setEstadoImagen(\Loogares\LugarBundle\Entity\EstadoImagen $estadoImagen)
    {
        $this->estado_imagen = $estadoImagen;
    }

    /**
     * Get estado_imagen
     *
     * @return Loogares\LugarBundle\Entity\EstadoImagen 
     */
    public function getEstadoImagen()
    {
        return $this->estado_imagen;
    }
}