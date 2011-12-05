<?php
class fichaImagenLugar{

    function __construct($id,$idlugar=null){
        global  $db;
        $this->db = $db;
        $this->Id = $id;
        $this->Id_Lugar = $idlugar;
        $this->info = $this->db->get_row("SELECT * FROM ImagenesLugar WHERE id='$id'");
        $this->existe_lugar = $this->comprobar_lugar_existe();
        if($this->comprobar_lugar_existe()){
            $this->lugar = $this->obtener_info_lugar();
            $this->ciudad_lugar = $this->obtener_nombre_ciudad();
            $this->url_lugar = $this->generar_url_lugar();
            $this->link_lugar = $this->generar_url_lugar(true);
            //$this->src_base = $this->generar_src_base();
            //$this->src_legacy = $this->generar_src_base(true);
            $this->src = $this->generar_tag(false,46);
            $this->tag = $this->generar_tag(true,46);
            $this->url = $this->generar_link(false); // su ubicación dentro de la galería
            $this->link = $this->generar_link(true,46);
        }
    }

    private function comprobar_lugar_existe(){
        if(isset($this->info->Id)){
            $l = $this->db->get_row("SELECT * FROM Lugares WHERE Id={$this->info->Id_Lugar}");
            if(isset($l->Id))
                return true;
        }
        return false;
    }

    private function obtener_info_lugar(){
        if(isset($this->info->Id_Lugar))
            return $this->db->get_row("SELECT * FROM Lugares WHERE Id={$this->info->Id_Lugar}");
        if(isset($this->Id_Lugar))
            return $this->db->get_row("SELECT * FROM Lugares WHERE Id={$this->Id_Lugar}");
        return null;
    }/*
    private function generar_src_base($legacy=false){ // src sin la info de tamaño
        if($legacy===false)
            return WEBROOT.'/images/lugares/';
        $slug = str_replace( array('á','é','í','ó','ú','ü',' ','Ñ','ñ','Á','É','Í','Ó','Ú'), array('a','e','i','o','u','u','-','N','n','A','E','I','O','U'), $this->lugar->Nombre );
        return 'http://www.loogares.com/images/lugares/' . $slug . '-' . $this->lugar->Id . '-' . $this->Id;
    }*/
    private function obtener_nombre_ciudad(){
        return $this->db->get_var("SELECT Nombre FROm Ciudad WHERE Id='{$this->lugar->Ciudad}'");
    }

    public function generar_tag($html=false,$tam=46){
        $src = WEBROOT.'/images/lugares/Sin-Foto-Lugar.gif';
        $host = 'http://' . $_SERVER['HTTP_HOST'] .WEBROOT ;
        if(isset($this->Id) && isset($this->info->Id)){
            if($tam=='large' && !empty($this->info->large)){
                $src = $host.'/images/lugares/'.$this->info->large ;
            } else {
                if(is_numeric($tam) && $tam<=150 && !empty($this->info->ico141)){
                    $src = $host.'/images/lugares/'.$this->info->ico141;
                }
                if(is_numeric($tam) && $tam<=50 && !empty($this->info->ico46)){
                    $src = $host.'/images/lugares/'.$this->info->ico46 ;
                }
            }
        }
        if($html===false)
            return $src;
        $ms = (is_numeric($tam)) ? str_replace('{{}}',$tam,' width="{{}}" height="{{}}" ') : null ;
        $title = ($this->comprobar_lugar_existe()) ? $this->lugar->Nombre.' | '.$this->ciudad_lugar : null ;
        return '<img src="'.$src.'" border="0" '.$ms.' class="borde-imagen" alt="'.$title.'" title="'.$title.'" />';
    }
    public function generar_link($img=false,$tam=46){
        $url = (isset($this->Id)) ? WEBROOT.BASELINK.'lugar/'.$this->lugar->Slug.'/galeria/'.$this->Id : null ;
        if($img===false)
            return $url;
        $link = '<a href="'.$url.'" title="Galería de fotos de '.$this->lugar->Nombre.'">' . $this->generar_tag(true,$tam) . '</a>';
        return $link;
    }
    private function generar_url_lugar($html=false){
        $href = null;
        if(isset($this->lugar->Id))
            $href = WEBROOT.BASELINK.'lugar/'.$this->lugar->Slug;
        if($html===false)
            return $href;
        if(!isset($this->lugar->Id))
            return null;
        return '<a href="'.$href.'" title="'.$this->lugar->Nombre.'">'.$this->lugar->Nombre.'</a>';
    }

}