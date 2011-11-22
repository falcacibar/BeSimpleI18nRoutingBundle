<?php

namespace Loogares\UsuarioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\UsuarioBundle\Entity\Recomendacion
 */
class Recomendacion
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var text $texto
     */
    private $texto;

    /**
     * @var float $estrellas
     */
    private $estrellas;

    /**
     * @var integer $precio
     */
    private $precio;

    /**
     * @var datetime $fecha_creacion
     */
    private $fecha_creacion;

    /**
     * @var datetime $fecha_ultima_modificacion
     */
    private $fecha_ultima_modificacion;

    /**
     * @var datetime $fecha_ultima_vez_destacada
     */
    private $fecha_ultima_vez_destacada;

    /**
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var Loogares\UsuarioBundle\Entity\EstadoRecomendacion
     */
    private $estado_recomendacion;


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
     * Set texto
     *
     * @param text $texto
     */
    public function setTexto($texto)
    {
        $this->texto = $texto;
    }

    /**
     * Get texto
     *
     * @return text 
     */
    public function getTexto()
    {
        return $this->texto;
    }

    /**
     * Set estrellas
     *
     * @param float $estrellas
     */
    public function setEstrellas($estrellas)
    {
        $this->estrellas = $estrellas;
    }

    /**
     * Get estrellas
     *
     * @return float 
     */
    public function getEstrellas()
    {
        return $this->estrellas;
    }

    /**
     * Set precio
     *
     * @param integer $precio
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }

    /**
     * Get precio
     *
     * @return integer 
     */
    public function getPrecio()
    {
        return $this->precio;
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
     * Set fecha_ultima_modificacion
     *
     * @param datetime $fechaUltimaModificacion
     */
    public function setFechaUltimaModificacion($fechaUltimaModificacion)
    {
        $this->fecha_ultima_modificacion = $fechaUltimaModificacion;
    }

    /**
     * Get fecha_ultima_modificacion
     *
     * @return datetime 
     */
    public function getFechaUltimaModificacion()
    {
        return $this->fecha_ultima_modificacion;
    }

    /**
     * Set fecha_ultima_vez_destacada
     *
     * @param datetime $fechaUltimaVezDestacada
     */
    public function setFechaUltimaVezDestacada($fechaUltimaVezDestacada)
    {
        $this->fecha_ultima_vez_destacada = $fechaUltimaVezDestacada;
    }

    /**
     * Get fecha_ultima_vez_destacada
     *
     * @return datetime 
     */
    public function getFechaUltimaVezDestacada()
    {
        return $this->fecha_ultima_vez_destacada;
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
     * Set usuario
     *
     * @param Loogares\UsuarioBundle\Entity\Usuario $usuario
     */
    public function setUsuario(\Loogares\UsuarioBundle\Entity\Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Get usuario
     *
     * @return Loogares\UsuarioBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set estado_recomendacion
     *
     * @param Loogares\UsuarioBundle\Entity\EstadoRecomendacion $estadoRecomendacion
     */
    public function setEstadoRecomendacion(\Loogares\UsuarioBundle\Entity\EstadoRecomendacion $estadoRecomendacion)
    {
        $this->estado_recomendacion = $estadoRecomendacion;
    }

    /**
     * Get estado_recomendacion
     *
     * @return Loogares\UsuarioBundle\Entity\EstadoRecomendacion 
     */
    public function getEstadoRecomendacion()
    {
        return $this->estado_recomendacion;
    }
}