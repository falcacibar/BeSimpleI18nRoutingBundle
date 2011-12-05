<?php
class fichaLugar{

    // db debe ya estar inicializado

    function __construct($id){
        global  $db;
        global $LOGGED;
        $this->db = $db;
        $this->info = $this->db->get_row("SELECT * FROM Lugares WHERE id='$id'");
        $this->ciudad = $this->obtener_ciudad();
        $this->sector = $this->obtener_sector();
        $this->comuna = $this->obtener_comuna();
        $this->es_lugar = $this->conocer_es_tipo(1);
        $this->es_atractivo = $this->conocer_es_tipo(2);
        $this->es_historia = $this->conocer_es_tipo(3);
        $this->es_descripcion = $this->conocer_es_tipo(4);
        $this->direccion = $this->obtener_direccion('ciudad');
        $this->direccion_comuna = $this->obtener_direccion('comuna');
        $this->telefonos = $this->obtener_telefonos();
        $this->horario = $this->generar_horario();
        $this->categorias = $this->obtener_categorias(true);
        $this->subcategorias = $this->obtener_subcategorias(null,true);
        $this->tipos_comida = $this->obtener_subcategorias(14);
        $this->tipos_musica = $this->obtener_subcategorias(30);
        $this->categoria_principal = $this->obtener_categoria_principal();
        $this->tags_populares = $this->formato_ficha($this->obtener_tags(5,'num DESC, RAND()'));
        $this->iconos = $this->iconos();
        $this->id_primer_usuario_recomienda = $this->obtener_primera_recomendacion('Id');
        $this->user_primer_usuario_recomienda = $this->obtener_primera_recomendacion('User');
        $this->num_recomendaciones = $this->obtener_num_recomendaciones();
        $this->donde_comer = $this->pertenece_categoria(3);
        $this->donde_dormir = $this->pertenece_categoria(6);
		$this->reserva = $this->obtener_reserva();
		$this->delivery = $this->obtener_delivery();
        $this->muestra_precio = $this->conocer_muestra_precio();
        $this->url = $this->generar_url(null);
        $this->link = $this->generar_url('html');
        $this->url_modificar_datos = (isset($LOGGED->Id))
            ? $this->url.'/editar' : WEBROOT.BASELINK.'usuario/registro' ;
        $this->url_reportar_lugar = (isset($LOGGED->Id))
            ? $this->url.'/reportar' : WEBROOT.BASELINK.'usuario/registro' ;
        $this->url_galeria = $this->url.'/galeria';
        $this->url_galeria_agregar = (isset($LOGGED->Id))
            ? $this->url_galeria.'/agregar' : WEBROOT.BASELINK.'usuario/registro' ;
        $this->puedo_recomendar = $this->conocer_usuario_puede_recomendar() ;
        $this->puedo_reportar = $this->conocer_usuario_puede_reportar() ;
        $this->id_imagen = $this->obtener_id_imagen_principal();
        $this->lugares_populares = $this->obtener_lugares_populares();
        $this->num_lugares_populares = count($this->lugares_populares);
        $this->num_imagenes_galeria = $this->num_imagenes_galeria();
        $this->imagenes_galeria = $this->obtener_imagenes_galeria(4);
        $this->url_sector = $this->generar_url_sector();
        $this->tag_puntuacion = '<div id="puntuacion-'.str_replace('.','',$this->info->Puntuacion).'" class="estrellas-sprite"></div>';
        $this->caracteristicas = $this->obtener_caracteristicas();
        $this->caracteristicas_iconos = $this->obtener_caracteristicas('icono');
        $this->caracteristicas_lista = $this->obtener_caracteristicas('no-icono');
    }

    private function obtener_lugares_populares(){
        return $this->db->get_results("SELECT Lugares.Id, Lugares.Nombre, Lugares.Slug
            FROM Lugares INNER JOIN LinksLugaresEspeciales ON (Lugares.Id = LinksLugaresEspeciales.Link)
            WHERE (LinksLugaresEspeciales.Id_Lugar ='{$this->info->Id}')
            ORDER BY LinksLugaresEspeciales.Id ASC;");
    }
    private function obtener_ciudad(){
        return $this->db->get_row("SELECT * FROM Ciudad WHERE Id='{$this->info->Ciudad}'");
    }
    private function obtener_sector(){
        return $this->db->get_row("SELECT * FROM Barrio WHERE Id='{$this->info->Barrio}'");
    }
    private function obtener_comuna(){
        return $this->db->get_row("SELECT * FROM Comuna WHERE Id='{$this->info->Comuna}'");
    }
    private function obtener_categoria_principal(){
        return $this->db->get_var("SELECT SubCategoria.Id_Categoria FROM SubCategoria
            INNER JOIN SubCat ON (SubCategoria.Id = SubCat.Id_SubCategoria)
            INNER JOIN Categoria ON (Categoria.Id = SubCategoria.Id_Categoria)
            WHERE (SubCat.Id_Lugar = {$this->info->Id} AND SubCat.Principal =1)");
    }
    private function generar_url_sector(){
        if(!$this->sector)
            return null;
        return WEBROOT.BASELINK.'buscar/'.$this->ciudad->slug.'/sector/'.$this->sector->slug;
    }

    private function conocer_usuario_puede_recomendar(){
        global $LOGGED;
        if(!isset($LOGGED->Id)) return true;
        //if($this->info->Id_Estado!=5) return false; // puede recomendar en lugares "en revisión"
        if($this->info->Id_Estado==7) return false; // puede recomendar en lugares "cerrados"
        if($this->db->get_var("SELECT COUNT(Id) FROM Recomendacion WHERE Id_Lugar='{$this->info->Id}' AND Usuario_Id='{$LOGGED->Id}' AND Id_Estado<6"))
            return false;
        return true;
    }
    private function conocer_usuario_puede_reportar(){
        global $LOGGED;
        if(!isset($LOGGED->Id)) return true;
        if($this->db->get_var("SELECT COUNT(Id) FROM ReportarLugar WHERE Id_Lugar='{$this->info->Id}' AND Usuario_Id='{$LOGGED->Id}'"))
            return false;
        return true;
    }
    private function conocer_muestra_precio(){
        if($this->donde_comer || $this->donde_dormir)
            return true;
        return false;
    }
    public function generar_url($formato){
        $link = WEBROOT.BASELINK.'lugar/'.$this->info->Slug;
        if($formato=='html')
            return '<a href="'.$link.'">'.$this->info->Nombre.'</a>';
        return $link;
    }
    private function conocer_es_tipo($idtipo){
        if($this->info->Id_Tipo==$idtipo) return true;
        return false;
    }
    private function obtener_direccion($alcance='ciudad'){
        if(!isset($this->calle)){
            $direccion = explode(' ',$this->info->Direccion);
            $this->numero = array_pop($direccion);
            $this->calle = implode(' ',$direccion);
        }
        $d = array();
        if(!empty($this->info->Direccion)) $d[] = $this->info->Direccion;
        if(!empty($this->info->Detalle)) $d[] = $this->info->Detalle;
        //$comuna = $this->db->get_row("SELECT * FROM Comuna WHERE Id='{$this->info->Comuna}'");
        $d[] = '<a href="'.WEBROOT.BASELINK.'buscar/'.$this->ciudad->slug.'/comuna/'.$this->comuna->slug.'">'.$this->comuna->Nombre.'</a>' ;
        if($alcance=='ciudad' && !empty($this->ciudad->Nombre)) {
            $d[] = '<a href="'.WEBROOT.BASELINK.'buscar/'.$this->ciudad->slug.'">'.$this->ciudad->Nombre.'</a>';
        }
        return $d;
    }
    private function obtener_telefonos(){
        $a = array();
        $t = $this->db->get_results("SELECT * FROM TelefonosLugar WHERE Id_Lugar='{$this->info->Id}' ORDER BY Id ASC");
        if(count($t)){
            foreach($t as $tt){
                $a[] = $tt->Telefono;
            }
        }
        return $a;
    }

	private function obtener_reserva(){
        $t = $this->db->get_var("SELECT Reserva FROM Lugares WHERE Id='{$this->info->Id}'");
        return $t;
    }
	
	private function obtener_delivery(){
        $t = $this->db->get_var("SELECT Delivery FROM Lugares WHERE Id='{$this->info->Id}'");
        return $t;
    }

    public function obtener_categorias($array=false){ // simple o html
        $cc = $this->db->get_results("SELECT SubCategoria.Id, SubCategoria.Nombre, SubCategoria.slug_subcat,SubCat.Id_Lugar
            FROM SubCategoria INNER JOIN SubCat ON (SubCategoria.Id = SubCat.Id_SubCategoria)
            WHERE (SubCat.Id_Lugar = '{$this->info->Id}') ORDER BY Principal DESC, SubCategoria.Nombre ASC");
        $cats = array();
        if(count($cc)){
            foreach($cc as $c){
                $href = WEBROOT.BASELINK.'buscar/'.$this->ciudad->slug.'/categoria/'.$c->slug_subcat;
                if($array===false)
                    $cats[] = '<a href="'.$href.'">'.$c->Nombre.'</a>';
                else
                    $cats[$c->Id] = $c->Nombre;
            }
        }
        return $cats;
    }
    public function obtener_subcategorias($categoria=null,$array=false){
        $where = (isset($categoria) && !empty($categoria)) ? 'AND SubSubCategoria.Id_SubCategoria ='.$categoria : null ;
        $cc = $this->db->get_results("SELECT SubSubCategoria.* FROM SubSubCat
            INNER JOIN SubSubCategoria ON (SubSubCat.Id_SubSubCategoria = SubSubCategoria.Id)
            WHERE (SubSubCat.Id_Lugar = {$this->info->Id} $where)");
        $cats = array();
        if(count($cc)){
            foreach($cc as $c){
                if($array===false)
                    $cats[] = '<a href="'.WEBROOT.BASELINK.'buscar/'.$this->ciudad->slug.'/subcategoria/'.$c->slug_subsubcat.'">'.$c->Nombre.'</a>';
                else
                    $cats[$c->Id] = $c->Nombre;
            }
        }
        return $cats;
    }
    public function formato_ficha($datos,$formato='lista'){
        if($formato=='lista')
            return implode(', ',$datos);
        return $datos;
    }
    public function obtener_tags($num=5,$order='Tag ASC',$array=false){
        $order = (isset($order) && !empty($order)) ? ' ORDER BY '.$order : null;
        $limit = (isset($num) && ($num>0)) ? ' LIMIT '.$num : null ;
        $tt = $this->db->get_results("SELECT Recomendacion.Id_Lugar, TagLugar.*, COUNT(TagLugarLink.Id) AS num FROM TagLugarLink
            INNER JOIN TagLugar ON (TagLugarLink.Id_TagLugar = TagLugar.Id)
            INNER JOIN Recomendacion ON (Recomendacion.Id = TagLugarLink.Id_Recomendacion)
            WHERE (Recomendacion.Id_Lugar ={$this->info->Id})
            GROUP BY TagLugar.Id $order $limit");
        $tags = array();
        if(count($tt)){
            foreach($tt as $t){
                if($array===false)
                    $tags[] = '<a href="'.WEBROOT.BASELINK.'buscar/'.$this->ciudad->slug.'/tag/'.$t->Id.'/'.$t->slug_tag.'">'.$t->Tag.'</a>';
                else
                    $tags[$t->Id] = $t->Tag;
            }
        }
        return $tags;
    }

    public function obtener_id_imagen_principal(){
        return $this->db->get_var("SELECT Id FROM ImagenesLugar WHERE Id_Lugar='{$this->info->Id}' AND Id_Estado<6 ORDER BY Fecha_Creacion DESC, Id DESC LIMIT 1");
    }
    public function num_imagenes_galeria(){
        return $this->db->get_var("SELECT COUNT(*) FROM ImagenesLugar WHERE Id_Lugar='{$this->info->Id}' AND Id_Estado<6")+0;
    }
    private function obtener_imagenes_galeria($n=999){
        if(isset($n) && is_numeric($n))
            $n = ' LIMIT '.$n;
        return $this->db->get_results("SELECT * FROM ImagenesLugar WHERE Id_Lugar='{$this->info->Id}' AND Id_Estado<6 ORDER BY Id DESC $n");
    }
    private function iconos(){
        $iconos = array();/*
        $icos = array(
            'Fumadores' => 'fumar',
            'No_Fumadores' => 'no-fumar',
            'Discapacitados' => 'discapacitado',
            'Wifi' => 'wifi'
        );
        $title['Discapacitados'] = 'Cuenta con facilidades para discapacitados, ya sea acceso y/o baños';

        foreach($icos as $k=>$v){
            $t = isset($title[$k]) ? $title[$k] : str_replace('_',' ',$k) ;
            if($this->info->$k==1)
                $iconos[] = '<span class="trigger-tipsy sprite-ico sprite-ico-'.$v.'" title="'.$t.'"></span>';
        }*/
        return implode(" ",$iconos);
    }
    private function obtener_primera_recomendacion($col='Id'){
        // exc luye usuarios eliminados
        $id = $this->db->get_var("SELECT Usuario_Id FROM Recomendacion INNER JOIN Usuario ON (Recomendacion.Usuario_Id=Usuario.Id)
            WHERE (Id_Lugar='{$this->info->Id}' AND User!='Administrador' AND Recomendacion.Id_Estado<6) ORDER BY Recomendacion.Fecha ASC LIMIT 1");
        if($col=='Id')
            return $id;
        return $this->db->get_var("SELECT User_Name FROM Usuario WHERE Id='$id'");
    }
    public function obtener_num_recomendaciones($where = null){
        $where = (isset($where) && !empty($where)) ? ' AND '.$where : null ;
        return $this->db->get_var("SELECT COUNT(*) FROM Recomendacion WHERE (Id_Lugar='{$this->info->Id}' AND Usuario_Id>1 AND Id_Estado<6 $where)");
    }
    private function pertenece_categoria($idcategoria){
        return $this->db->get_var("SELECT COUNT(SubCategoria.Id_Categoria) FROM SubCat
            INNER JOIN SubCategoria ON (SubCat.Id_SubCategoria = SubCategoria.Id)
            WHERE (SubCategoria.Id_Categoria = $idcategoria AND SubCat.Id_Lugar = {$this->info->Id} )");
    }
    public function obtener_datos_horario(){
        $hh = $this->db->get_results("SELECT * FROM Horario WHERE Id_Lugar='{$this->info->Id}' ORDER BY Id_Dia");
        $horario = array();
        if(count($hh)){
            foreach($hh as $h){
                $horario['hora1_'.$h->Id_Dia] = $h->Aper_M_L;
                $horario['hora2_'.$h->Id_Dia] = $h->Cierre_M_L;
                $horario['hora3_'.$h->Id_Dia] = $h->Aper_T_L;
                $horario['hora4_'.$h->Id_Dia] = $h->Cierre_T_L;
            }
        }
        return $horario;
    }
    private function generar_horario(){
        $hhs = $this->db->get_results("SELECT * FROM Horario WHERE Id_Lugar='{$this->info->Id}' ORDER BY Id_Dia");
        if(empty($hhs)) return null;
        $hh = array();
        for($i=0;$i<7;$i++){
            $temp = $this->db->get_row("SELECT * FROM Horario WHERE Id_Lugar={$this->info->Id} AND Id_Dia=$i");
            if(isset($temp->Id_Dia)){
                $hh[$i] = array(
                    'Id_Dia' => $i,
                    'Aper_M_L' => @$temp->Aper_M_L,
                    'Cierre_M_L' => @$temp->Cierre_M_L,
                    'Aper_T_L' => @$temp->Aper_T_L,
                    'Cierre_T_L' => @$temp->Cierre_T_L
                );
            } else {
                $hh[$i] = array(
                    'Id_Dia' => $i,
                    'Aper_M_L' => '',
                    'Cierre_M_L' => '',
                    'Aper_T_L' => '',
                    'Cierre_T_L' => ''
                );
            }
        }
        //echo '<pre>';print_r($hh);echo '</pre>';

        $dias = array('Lun','Mar','Mié','Jue','Vie','Sáb','Dom');
        $out = null;
        $horario = array();
        $dia = 0;
        if(count($hh)){
            $inicial = $hh[0];
            $final = $hh[0];
            $dia++;
            while($dia<7){
                if( $hh[$dia]['Aper_M_L'] != $inicial['Aper_M_L'] || $hh[$dia]['Cierre_M_L'] != $inicial['Cierre_M_L'] ||
                    @$hh[$dia]['Aper_T_L'] != @$inicial['Aper_T_L'] || $hh[$dia]['Cierre_T_L'] != $inicial['Cierre_T_L']) {
                    $out = $dias[$inicial['Id_Dia']];
                    if($final['Id_Dia'] != $inicial['Id_Dia']){
                        $out.= '-' . $dias[$final['Id_Dia']];
                    }
                    if(!empty($inicial['Aper_M_L'])){
                        $out.= ': ' . $inicial['Aper_M_L'] . ' - ' . $inicial['Cierre_M_L'];
                        if(!empty($inicial['Cierre_T_L'])){
                            $out.= ' / ' . $inicial['Aper_T_L'] . ' - ' . $inicial['Cierre_T_L'];
                        }
                    } else {
                        $out.= ': Cerrado' ;
                    }
                    $horario[] = $out;
                    $out=null;
                    $inicial = $hh[$dia];
                    $final = $hh[$dia];
                } else {
                    $final = $hh[$dia];
                }
                $dia++;
            }
            $out.= $dias[$inicial['Id_Dia']];
            if($final['Id_Dia'] != $inicial['Id_Dia']){
                $out.= '-' . $dias[$final['Id_Dia']];
            }
            if(!empty($inicial['Aper_M_L'])){
                $out.= ': ' . $inicial['Aper_M_L'] . ' - ' . $inicial['Cierre_M_L'];
                if(!empty($inicial['Aper_T_L'])){
                    $out.= ' / ' . $inicial['Aper_T_L'] . ' - ' . $inicial['Cierre_T_L'];
                }
            } else {
                $out.= ': Cerrado';
            }
            $horario[] = $out;
            $out=null;
        }
        return $horario;
    }
    private function obtener_caracteristicas($tipo='todos'){
        $where = null;
        if($tipo=='icono') $where = ' AND LENGTH(Icono)>0 ';
        if($tipo=='no-icono') $where = ' AND (Icono IS NULL OR LENGTH(Icono)<1) ';
		if($this->info->Reserva == '1') $where .= ' AND Caracteristicas.Id <> 18';
		if($this->info->Delivery == '1') $where .= ' AND Caracteristicas.Id <> 17';
        $out = array();
        $cars = $this->db->get_results("
            SELECT Caracteristicas.*
            FROM CaracteristicasLugar
            INNER JOIN Caracteristicas ON (Id_Caracteristica = Caracteristicas.Id)
            WHERE (Id_Lugar = {$this->info->Id} $where)
            ORDER BY Orden, Nombre ASC");
		
        if(count($cars)){
            foreach($cars as $car){
                $ttip = (!empty($car->Tooltip)) ? ' title="'.$car->Tooltip.'" ' : null ;
                $class = (!empty($car->Tooltip)) ? ' class="trigger-tipsy" ' : null ;
                $qmark = (!empty($car->Tooltip)) ? ' <span '.$ttip.$class.'>(?)</span> ' : null ;
                $out[] = ($tipo=='icono')
                    ? '<img src="'.WEBROOT.'/images/caracteristicas/'.$car->Icono.'" '.$ttip.$class.' />'
                    : '<b>'.$car->Nombre.'</b>'.$qmark.': Sí';
            }
        }
        return $out;
    }


    static function link_lugar($idlugar){
        // devuelve nombre del lugar, con link si está aprobado y bold si está rechazado
        global $db;
        $lugar = $db->get_row("SELECT Id,Nombre,Id_Estado,Slug FROM Lugares WHERE Id=$idlugar");
        $n = (empty($lugar->Nombre)) ? 'Lugar no existe' : $lugar->Nombre;
        $link = WEBROOT.BASELINK.'lugar/'.$lugar->Slug;
        return ($lugar->Id_Estado==6)
            ?   '<b>'.$n.'</b>' : '<a href="'.$link.'">'.$n.'</a>' ;
    }
    static function generar_url_lugar($idlugar){
        // devuelve nombre del lugar, con link si está aprobado y bold si está rechazado
        global $db;
        $lugar = $db->get_row("SELECT Id,Nombre,Slug FROM Lugares WHERE Id='$idlugar'");
        if(!isset($lugar->Id) || empty($lugar->Nombre))
            return '000/No+existe';
        return WEBROOT.BASELINK.'lugar/'.$lugar->Slug;
    }
    
}