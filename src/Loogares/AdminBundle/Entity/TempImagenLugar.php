<?php

namespace Loogares\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\AdminBundle\Entity\TempImagenLugar
 */
class TempImagenLugar
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $es_enlace
     */
    private $es_enlace;

    /**
     * @var string $titulo_enlace
     */
    private $titulo_enlace;

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
    private $usuario;

    /**
     * @var Loogares\AdminBundle\Entity\TempLugar
     */
    private $lugar;

    /**
     * @var Loogares\ExtraBundle\Entity\Estado
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
     * Set es_enlace
     *
     * @param integer $esEnlace
     */
    public function setEsEnlace($esEnlace)
    {
        $this->es_enlace = $esEnlace;
    }

    /**
     * Get es_enlace
     *
     * @return integer 
     */
    public function getEsEnlace()
    {
        return $this->es_enlace;
    }

    /**
     * Set titulo_enlace
     *
     * @param string $tituloEnlace
     */
    public function setTituloEnlace($tituloEnlace)
    {
        $this->titulo_enlace = $tituloEnlace;
    }

    /**
     * Get titulo_enlace
     *
     * @return string 
     */
    public function getTituloEnlace()
    {
        return $this->titulo_enlace;
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
     * Set lugar
     *
     * @param Loogares\AdminBundle\Entity\TempLugar $lugar
     */
    public function setLugar(\Loogares\AdminBundle\Entity\TempLugar $lugar)
    {
        $this->lugar = $lugar;
    }

    /**
     * Get lugar
     *
     * @return Loogares\AdminBundle\Entity\TempLugar 
     */
    public function getLugar()
    {
        return $this->lugar;
    }

    /**
     * Set estado_imagen
     *
     * @param Loogares\ExtraBundle\Entity\Estado $estadoImagen
     */
    public function setEstadoImagen(\Loogares\ExtraBundle\Entity\Estado $estadoImagen)
    {
        $this->estado_imagen = $estadoImagen;
    }

    /**
     * Get estado_imagen
     *
     * @return Loogares\ExtraBundle\Entity\Estado 
     */
    public function getEstadoImagen()
    {
        return $this->estado_imagen;
    }
}