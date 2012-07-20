<?php

namespace Loogares\CampanaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\CampanaBundle\Entity\DescuentosUsuarios
 */
class DescuentosUsuarios
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $codigo
     */
    private $codigo;

    /**
     * @var integer $canjeado
     */
    private $canjeado;

    /**
     * @var Loogares\CampanaBundle\Entity\Descuento
     */
    private $descuento;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;


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
     * Set codigo
     *
     * @param string $codigo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set canjeado
     *
     * @param integer $canjeado
     */
    public function setCanjeado($canjeado)
    {
        $this->canjeado = $canjeado;
    }

    /**
     * Get canjeado
     *
     * @return integer 
     */
    public function getCanjeado()
    {
        return $this->canjeado;
    }

    /**
     * Set descuento
     *
     * @param Loogares\CampanaBundle\Entity\Descuento $descuento
     */
    public function setDescuento(\Loogares\CampanaBundle\Entity\Descuento $descuento)
    {
        $this->descuento = $descuento;
    }

    /**
     * Get descuento
     *
     * @return Loogares\CampanaBundle\Entity\Descuento 
     */
    public function getDescuento()
    {
        return $this->descuento;
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
}