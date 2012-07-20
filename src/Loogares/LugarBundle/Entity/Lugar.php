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
     * @var string $telefono1
     */
    private $telefono1;

    /**
     * @var string $telefono2
     */
    private $telefono2;

    /**
     * @var string $telefono3
     */
    private $telefono3;

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
     * @var Loogares\LugarBundle\Entity\ImagenLugar
     */
    private $imagenes_lugar;

    /**
     * @var Loogares\LugarBundle\Entity\CategoriaLugar
     */
    private $categoria_lugar;

    /**
     * @var Loogares\LugarBundle\Entity\SubcategoriaLugar
     */
    private $subcategoria_lugar;

    /**
     * @var Loogares\LugarBundle\Entity\CaracteristicaLugar
     */
    private $caracteristica_lugar;

    /**
     * @var Loogares\UsuarioBundle\Entity\Recomendacion
     */
    private $recomendacion;

    /**
     * @var Loogares\LugarBundle\Entity\Horario
     */
    private $horario;

    /**
     * @var Loogares\AdminBundle\Entity\TempLugar
     */
    private $temp_lugares;

    /**
     * @var Loogares\LugarBundle\Entity\VideosLugar
     */
    private $videos_lugar;

    /**
     * @var Loogares\ExtraBundle\Entity\Comuna
     */
    private $comuna;

    /**
     * @var Loogares\ExtraBundle\Entity\Sector
     */
    private $sector;

    /**
     * @var Loogares\LugarBundle\Entity\TipoLugar
     */
    private $tipo_lugar;

    /**
     * @var Loogares\ExtraBundle\Entity\Estado
     */
    private $estado;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;

    public function __construct()
    {
        $this->imagenes_lugar = new \Doctrine\Common\Collections\ArrayCollection();
    $this->categoria_lugar = new \Doctrine\Common\Collections\ArrayCollection();
    $this->subcategoria_lugar = new \Doctrine\Common\Collections\ArrayCollection();
    $this->caracteristica_lugar = new \Doctrine\Common\Collections\ArrayCollection();
    $this->recomendacion = new \Doctrine\Common\Collections\ArrayCollection();
    $this->horario = new \Doctrine\Common\Collections\ArrayCollection();
    $this->temp_lugares = new \Doctrine\Common\Collections\ArrayCollection();
    $this->videos_lugar = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set telefono1
     *
     * @param string $telefono1
     */
    public function setTelefono1($telefono1)
    {
        $this->telefono1 = $telefono1;
    }

    /**
     * Get telefono1
     *
     * @return string 
     */
    public function getTelefono1()
    {
        return $this->telefono1;
    }

    /**
     * Set telefono2
     *
     * @param string $telefono2
     */
    public function setTelefono2($telefono2)
    {
        $this->telefono2 = $telefono2;
    }

    /**
     * Get telefono2
     *
     * @return string 
     */
    public function getTelefono2()
    {
        return $this->telefono2;
    }

    /**
     * Set telefono3
     *
     * @param string $telefono3
     */
    public function setTelefono3($telefono3)
    {
        $this->telefono3 = $telefono3;
    }

    /**
     * Get telefono3
     *
     * @return string 
     */
    public function getTelefono3()
    {
        return $this->telefono3;
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
     * Add imagenes_lugar
     *
     * @param Loogares\LugarBundle\Entity\ImagenLugar $imagenesLugar
     */
    public function addImagenLugar(\Loogares\LugarBundle\Entity\ImagenLugar $imagenesLugar)
    {
        $this->imagenes_lugar[] = $imagenesLugar;
    }

    /**
     * Get imagenes_lugar
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getImagenesLugar()
    {
        return $this->imagenes_lugar;

    }

    public function getImagenesActivasLugar()
    {
        $imagenes = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->imagenes_lugar as $img) {
            if($img->getEstado()->getId() != 3) {
                $imagenes[] = $img;
            }
        }
        return $imagenes;
    }

    public function getCategoriaPrincipal()
    {
        foreach($this->categoria_lugar as $categoria_lugar) {
            if($categoria_lugar->getPrincipal() == 1) {
               $categoria = $categoria_lugar->getCategoria();
            }
        }
        return $categoria;
    }

    public function getRecomendacionesActivas()
    {
        $recomendaciones = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->recomendacion as $recomendacion) {
            if($recomendacion->getEstado()->getId() != 3){
                $recomendaciones[] = $recomendacion;
            }
        }
        return $recomendaciones;
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
     * Add caracteristica_lugar
     *
     * @param Loogares\LugarBundle\Entity\CaracteristicaLugar $caracteristicaLugar
     */
    public function addCaracteristicaLugar(\Loogares\LugarBundle\Entity\CaracteristicaLugar $caracteristicaLugar)
    {
        $this->caracteristica_lugar[] = $caracteristicaLugar;
    }

    /**
     * Get caracteristica_lugar
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCaracteristicaLugar()
    {
        return $this->caracteristica_lugar;
    }

    /**
     * Add recomendacion
     *
     * @param Loogares\UsuarioBundle\Entity\Recomendacion $recomendacion
     */
    public function addRecomendacion(\Loogares\UsuarioBundle\Entity\Recomendacion $recomendacion)
    {
        $this->recomendacion[] = $recomendacion;
    }

    /**
     * Get recomendacion
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRecomendacion()
    {
        return $this->recomendacion;
    }

    /**
     * Add horario
     *
     * @param Loogares\LugarBundle\Entity\Horario $horario
     */
    public function addHorario(\Loogares\LugarBundle\Entity\Horario $horario)
    {
        $this->horario[] = $horario;
    }

    /**
     * Get horario
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getHorario()
    {
        return $this->horario;
    }

    /**
     * Add temp_lugares
     *
     * @param Loogares\AdminBundle\Entity\TempLugar $tempLugares
     */
    public function addTempLugar(\Loogares\AdminBundle\Entity\TempLugar $tempLugares)
    {
        $this->temp_lugares[] = $tempLugares;
    }

    /**
     * Get temp_lugares
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTempLugares()
    {
        return $this->temp_lugares;
    }

    /**
     * Add videos_lugar
     *
     * @param Loogares\LugarBundle\Entity\VideosLugar $videosLugar
     */
    public function addVideosLugar(\Loogares\LugarBundle\Entity\VideosLugar $videosLugar)
    {
        $this->videos_lugar[] = $videosLugar;
    }

    /**
     * Get videos_lugar
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getVideosLugar()
    {
        return $this->videos_lugar;
    }

    /**
     * Set comuna
     *
     * @param Loogares\ExtraBundle\Entity\Comuna $comuna
     */
    public function setComuna(\Loogares\ExtraBundle\Entity\Comuna $comuna)
    {
        $this->comuna = $comuna;
    }

    /**
     * Get comuna
     *
     * @return Loogares\ExtraBundle\Entity\Comuna 
     */
    public function getComuna()
    {
        return $this->comuna;
    }

    /**
     * Set sector
     *
     * @param Loogares\ExtraBundle\Entity\Sector $sector
     */
    public function setSector(\Loogares\ExtraBundle\Entity\Sector $sector)
    {
        $this->sector = $sector;
    }

    /**
     * Get sector
     *
     * @return Loogares\ExtraBundle\Entity\Sector 
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * Set tipo_lugar
     *
     * @param Loogares\LugarBundle\Entity\TipoLugar $tipoLugar
     */
    public function setTipoLugar(\Loogares\LugarBundle\Entity\TipoLugar $tipoLugar)
    {
        $this->tipo_lugar = $tipoLugar;
    }

    /**
     * Get tipo_lugar
     *
     * @return Loogares\LugarBundle\Entity\TipoLugar 
     */
    public function getTipoLugar()
    {
        return $this->tipo_lugar;
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
     * @var Loogares\UsuarioBundle\Entity\Dueno
     */
    private $dueno;


    /**
     * Set dueno
     *
     * @param Loogares\UsuarioBundle\Entity\Dueno $dueno
     */
    public function setDueno(\Loogares\UsuarioBundle\Entity\Dueno $dueno)
    {
        $this->dueno = $dueno;
    }

    /**
     * Get dueno
     *
     * @return Loogares\UsuarioBundle\Entity\Dueno 
     */
    public function getDueno()
    {
        return $this->dueno;
    }
}