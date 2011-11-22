<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\Lugar
 */
class Lugar
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
     * @var integer $usuario_id
     */
    private $usuario_id;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $calle
     */
    private $calle;

    /**
     * @var string $numero
     */
    private $numero;

    /**
     * @var text $detalle
     */
    private $detalle;

    /**
     * @var text $descripcion
     */
    private $descripcion;

    /**
     * @var integer $comuna_id
     */
    private $comuna_id;

    /**
     * @var integer $sector_id
     */
    private $sector_id;

    /**
     * @var integer $tipo_lugar_id
     */
    private $tipo_lugar_id;

    /**
     * @var integer $estado_lugar_id
     */
    private $estado_lugar_id;

    /**
     * @var integer $dueno_id
     */
    private $dueno_id;

    /**
     * @var float $mapx
     */
    private $mapx;

    /**
     * @var float $mapy
     */
    private $mapy;

    /**
     * @var string $profesional
     */
    private $profesional;

    /**
     * @var string $agno_construccion
     */
    private $agno_construccion;

    /**
     * @var string $materiales
     */
    private $materiales;

    /**
     * @var string $sitio_web
     */
    private $sitio_web;

    /**
     * @var string $facebook
     */
    private $facebook;

    /**
     * @var string $twitter
     */
    private $twitter;

    /**
     * @var string $mail
     */
    private $mail;

    /**
     * @var float $estrellas
     */
    private $estrellas;

    /**
     * @var integer $visitas
     */
    private $visitas;

    /**
     * @var integer $utiles
     */
    private $utiles;

    /**
     * @var date $fecha_agregado
     */
    private $fecha_agregado;

    /**
     * @var datetime $fecha_ultima_recomendacion
     */
    private $fecha_ultima_recomendacion;

    /**
     * @var integer $total_recomendaciones
     */
    private $total_recomendaciones;

    /**
     * @var integer $precio
     */
    private $precio;

    /**
     * @var integer $precio_inicial
     */
    private $precio_inicial;

    /**
     * @var integer $prioridad_web
     */
    private $prioridad_web;

    /**
     * @var Loogares\LugarBundle\Entity\EstadoLugar
     */
    private $estado_lugar;


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
     * Set usuario_id
     *
     * @param integer $usuarioId
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuario_id = $usuarioId;
    }

    /**
     * Get usuario_id
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuario_id;
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
     * Set calle
     *
     * @param string $calle
     */
    public function setCalle($calle)
    {
        $this->calle = $calle;
    }

    /**
     * Get calle
     *
     * @return string 
     */
    public function getCalle()
    {
        return $this->calle;
    }

    /**
     * Set numero
     *
     * @param string $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set detalle
     *
     * @param text $detalle
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
    }

    /**
     * Get detalle
     *
     * @return text 
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set descripcion
     *
     * @param text $descripcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    /**
     * Get descripcion
     *
     * @return text 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set comuna_id
     *
     * @param integer $comunaId
     */
    public function setComunaId($comunaId)
    {
        $this->comuna_id = $comunaId;
    }

    /**
     * Get comuna_id
     *
     * @return integer 
     */
    public function getComunaId()
    {
        return $this->comuna_id;
    }

    /**
     * Set sector_id
     *
     * @param integer $sectorId
     */
    public function setSectorId($sectorId)
    {
        $this->sector_id = $sectorId;
    }

    /**
     * Get sector_id
     *
     * @return integer 
     */
    public function getSectorId()
    {
        return $this->sector_id;
    }

    /**
     * Set tipo_lugar_id
     *
     * @param integer $tipoLugarId
     */
    public function setTipoLugarId($tipoLugarId)
    {
        $this->tipo_lugar_id = $tipoLugarId;
    }

    /**
     * Get tipo_lugar_id
     *
     * @return integer 
     */
    public function getTipoLugarId()
    {
        return $this->tipo_lugar_id;
    }

    /**
     * Set estado_lugar_id
     *
     * @param integer $estadoLugarId
     */
    public function setEstadoLugarId($estadoLugarId)
    {
        $this->estado_lugar_id = $estadoLugarId;
    }

    /**
     * Get estado_lugar_id
     *
     * @return integer 
     */
    public function getEstadoLugarId()
    {
        return $this->estado_lugar_id;
    }

    /**
     * Set dueno_id
     *
     * @param integer $duenoId
     */
    public function setDuenoId($duenoId)
    {
        $this->dueno_id = $duenoId;
    }

    /**
     * Get dueno_id
     *
     * @return integer 
     */
    public function getDuenoId()
    {
        return $this->dueno_id;
    }

    /**
     * Set mapx
     *
     * @param float $mapx
     */
    public function setMapx($mapx)
    {
        $this->mapx = $mapx;
    }

    /**
     * Get mapx
     *
     * @return float 
     */
    public function getMapx()
    {
        return $this->mapx;
    }

    /**
     * Set mapy
     *
     * @param float $mapy
     */
    public function setMapy($mapy)
    {
        $this->mapy = $mapy;
    }

    /**
     * Get mapy
     *
     * @return float 
     */
    public function getMapy()
    {
        return $this->mapy;
    }

    /**
     * Set profesional
     *
     * @param string $profesional
     */
    public function setProfesional($profesional)
    {
        $this->profesional = $profesional;
    }

    /**
     * Get profesional
     *
     * @return string 
     */
    public function getProfesional()
    {
        return $this->profesional;
    }

    /**
     * Set agno_construccion
     *
     * @param string $agnoConstruccion
     */
    public function setAgnoConstruccion($agnoConstruccion)
    {
        $this->agno_construccion = $agnoConstruccion;
    }

    /**
     * Get agno_construccion
     *
     * @return string 
     */
    public function getAgnoConstruccion()
    {
        return $this->agno_construccion;
    }

    /**
     * Set materiales
     *
     * @param string $materiales
     */
    public function setMateriales($materiales)
    {
        $this->materiales = $materiales;
    }

    /**
     * Get materiales
     *
     * @return string 
     */
    public function getMateriales()
    {
        return $this->materiales;
    }

    /**
     * Set sitio_web
     *
     * @param string $sitioWeb
     */
    public function setSitioWeb($sitioWeb)
    {
        $this->sitio_web = $sitioWeb;
    }

    /**
     * Get sitio_web
     *
     * @return string 
     */
    public function getSitioWeb()
    {
        return $this->sitio_web;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * Get facebook
     *
     * @return string 
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Get twitter
     *
     * @return string 
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set mail
     *
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
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
     * Set visitas
     *
     * @param integer $visitas
     */
    public function setVisitas($visitas)
    {
        $this->visitas = $visitas;
    }

    /**
     * Get visitas
     *
     * @return integer 
     */
    public function getVisitas()
    {
        return $this->visitas;
    }

    /**
     * Set utiles
     *
     * @param integer $utiles
     */
    public function setUtiles($utiles)
    {
        $this->utiles = $utiles;
    }

    /**
     * Get utiles
     *
     * @return integer 
     */
    public function getUtiles()
    {
        return $this->utiles;
    }

    /**
     * Set fecha_agregado
     *
     * @param date $fechaAgregado
     */
    public function setFechaAgregado($fechaAgregado)
    {
        $this->fecha_agregado = $fechaAgregado;
    }

    /**
     * Get fecha_agregado
     *
     * @return date 
     */
    public function getFechaAgregado()
    {
        return $this->fecha_agregado;
    }

    /**
     * Set fecha_ultima_recomendacion
     *
     * @param datetime $fechaUltimaRecomendacion
     */
    public function setFechaUltimaRecomendacion($fechaUltimaRecomendacion)
    {
        $this->fecha_ultima_recomendacion = $fechaUltimaRecomendacion;
    }

    /**
     * Get fecha_ultima_recomendacion
     *
     * @return datetime 
     */
    public function getFechaUltimaRecomendacion()
    {
        return $this->fecha_ultima_recomendacion;
    }

    /**
     * Set total_recomendaciones
     *
     * @param integer $totalRecomendaciones
     */
    public function setTotalRecomendaciones($totalRecomendaciones)
    {
        $this->total_recomendaciones = $totalRecomendaciones;
    }

    /**
     * Get total_recomendaciones
     *
     * @return integer 
     */
    public function getTotalRecomendaciones()
    {
        return $this->total_recomendaciones;
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
     * Set precio_inicial
     *
     * @param integer $precioInicial
     */
    public function setPrecioInicial($precioInicial)
    {
        $this->precio_inicial = $precioInicial;
    }

    /**
     * Get precio_inicial
     *
     * @return integer 
     */
    public function getPrecioInicial()
    {
        return $this->precio_inicial;
    }

    /**
     * Set prioridad_web
     *
     * @param integer $prioridadWeb
     */
    public function setPrioridadWeb($prioridadWeb)
    {
        $this->prioridad_web = $prioridadWeb;
    }

    /**
     * Get prioridad_web
     *
     * @return integer 
     */
    public function getPrioridadWeb()
    {
        return $this->prioridad_web;
    }

    /**
     * Set estado_lugar
     *
     * @param Loogares\LugarBundle\Entity\EstadoLugar $estadoLugar
     */
    public function setEstadoLugar(\Loogares\LugarBundle\Entity\EstadoLugar $estadoLugar)
    {
        $this->estado_lugar = $estadoLugar;
    }

    /**
     * Get estado_lugar
     *
     * @return Loogares\LugarBundle\Entity\EstadoLugar 
     */
    public function getEstadoLugar()
    {
        return $this->estado_lugar;
    }
}