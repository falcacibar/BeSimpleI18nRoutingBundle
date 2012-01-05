<?php

namespace Loogares\LugarBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\ImagenLugar
 */
class ImagenLugar
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
     * Propiedad virtual para referenciar primera imagen 
     */
    public $firstImg;

    /**
     * Propiedad virtual para referenciar segunda imagen 
     */
    public $secondImg;

    /**
     * Propiedad virtual para referenciar tercera imagen
     */
    public $thirdImg;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;

    /**
     * @var Loogares\ExtraBundle\Entity\Estado
     */
    private $estado;


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
     * Set estado
     *
     * @param Loogares\ExtraBundle\Entity\Estado $estado
     */
    public function setEstado(\Loogares\ExtraBundle\Entity\Estado $estado)
    {
        $this->estado = $estado;
    }

    /**
     * Get estado
     *
     * @return Loogares\ExtraBundle\Entity\Estado 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
    * Funciones que permiten manejar de mejor forma la imagen de usuario
    */
    public function getAbsolutePath()
    {
        return null === $this->imagen_full ? null : $this->getUploadRootDir().'/'.$this->imagen_full;
    }

    public function getWebPath()
    {
        return null === $this->imagen_full ? null : $this->getUploadDir().'/'.$this->imagen_full;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'assets/images/lugares';
    }

    /**
     * @ORM\preUpdate
     */
    public function preUpload()
    {
        if ($this->firstImg !== null) {
            // Se genera nombre de la imagen (slugLugar-idLugar-id.jpg)
            $filename = $this->getLugar()->getSlug().'-'.$this->getLugar()->getId().'-'.$this->id;
            $this->setImagenFull($filename.'.jpg');//.$this->file->guessExtension());
        }
    }

    /**
     * @ORM\postUpdate
     */
    public function upload()
    {
        if ($this->firstImg  === null) {
            return;
        }
        //echo $this->getImagenFull().$this->id.'.jpg';
        //$this->setImagenFull($this->getImagenFull().$this->id.'.jpg');//.$this->file->guessExtension());
        $this->firstImg->move($this->getUploadRootDir(), $this->imagen_full);
        unset($this->firstImg);
    }

    /**
     * @ORM\postRemove
     */
    public function removeUpload()
    {
        if ($firstImg = $this->getAbsolutePath()) {
            unlink($firstImg);
        }
    }

    
}