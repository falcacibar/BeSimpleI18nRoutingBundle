<!DOCTYPE html> 
<head>
<meta charset="utf-8">
<title>Loogares Migration System</title>
</head>
<body>
<style>
    html{
        background: #333;
        font-family: Helvetica;
    }

    h1, h2{
        text-align: center;
        color: #3df;
        margin: 0;
        margin-top: 30px;
        text-shadow: 0 1px 0 #111;
        margin-bottom: 4px;
    }

    h2{
        color: #555;
        font-size: 12px;
        margin: 0;
    }

    #list{
        margin: 0 auto;
        width: 550px;
        padding: 1px 25px; 
        background: #f0f0f0;
        color: #333;
        box-shadow: 0 1px 3px #111;
        font-size: 14px;
        margin-top: -15px;
        border-top-left-radius: 6px;
        border-bottom-right-radius: 6px;
        border-bottom-left-radius: 6px;
        text-shadow: 0 1px 0 #ccc;
    }

    ol{
        display: none;
    }

    ol#lugares{
        display: block;
    }

    .title {
        text-decoration:none;
    }

    li a{
        color: #333;
        text-decoration: none;
    }

    ol li a:hover{
        border-bottom: 1px dashed #333;
        color: #3df;
    }

    ol li{
        padding: 4px 10px;
    }

    .tabs{
        width: 600px;
        display: block;
        padding-left: 0;
        text-align: right;
        margin: 15px auto;
        text-shadow: 0 1px 0 #aaa;
    }

    .tabs li{
        background: #555;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        display: inline-block;
        font-size: 12px;
        padding: 4px 6px;
    }

    .tabs li a:hover{
        color: #3df;
    }

    .tabs li.selected{
        background: #0099B8;
    }

    span.migrated {
        color: green;
    }
</style>
<script src="jquery-1.5.min.js"></script>
<script>
    $(function(){
        $('.tabs li a').click(function(e){
            $('.selected').removeClass('selected');
            active = $(this).attr('href');
            $(this).parent().addClass('selected');
            $('ol').not(active).fadeOut(function(){
               $(active).fadeIn();
            });
        })
    })
</script>
<a class="title" href=""><h1>Loogares Migration System</h1></a>
<h2>Migracion de DB actual a Modelo basado en ORM</h2>
<ul class="tabs">
    <li class="selected"><a href="#lugares">Lugares</a></li>
    <li><a href="#usuarios">Usuarios</a></li>
    <li><a href="#extras">Extras</a></li>
</ul>

<?php
include('check_migration.php');
?>

<div id="list">
    <ol id="lugares">
        <li>
            <?php echo ($migratedTables['estado'] == 1) ? "<span class='migrated'>estado</span>" : "<a href='estado.php'>estado</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['lugares'] == 1) ? "<span class='migrated'>lugares</span>" : "<a href='lugares.php'>lugares</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['categorias'] == 1) ? "<span class='migrated'>categorias</span>" : "<a href='categorias.php'>categorias</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['categoria_lugar'] == 1) ? "<span class='migrated'>categoria_lugares</span>" : "<a href='categoria_lugar.php'>categoria_lugares</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['tipo_categoria'] == 1) ? "<span class='migrated'>tipo_categoria</span>" : "<a href='tipo_categoria.php'>tipo_categoria</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['categoria_ciudad'] == 1) ? "<span class='migrated'>categoria_ciudad</span>" : "<a href='categoria_ciudad.php'>categoria_ciudad</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['caracteristica'] == 1) ? "<span class='migrated'>caracteristica</span>" : "<a href='caracteristica.php'>caracteristica</a>" ?> 
        </li>
        <li>
            <?php echo ($migratedTables['caracteristica_lugar'] == 1) ? "<span class='migrated'>caracteristica_lugar</span>" : "<a href='caracteristica_lugar.php'>caracteristica_lugar</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['caracteristica_categoria'] == 1) ? "<span class='migrated'>caracteristica_categoria</span>" : "<a href='caracteristica_categoria.php'>caracteristica_categoria</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['imagenes_lugar'] == 1) ? "<span class='migrated'>imagenes_lugar</span>" : "<a href='imagenes_lugar.php'>imagenes_lugar</a>" ?>
            
        </li>
        <li>
            <?php echo ($migratedTables['subcategoria'] == 1) ? "<span class='migrated'>subcategoria</span>" : "<a href='subcategoria.php'>subcategoria</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['subcategoria_lugar'] == 1) ? "<span class='migrated'>subcategoria_lugar</span>" : "<a href='subcategoria_lugar.php'>subcategoria_lugar</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['horario'] == 1) ? "<span class='migrated'>horario</span>" : "<a href='horarios.php'>horario</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['pedidos'] == 1) ? "<span class='migrated'>pedidos</span>" : "<a href='pedidos.php'>pedidos</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['servicios_pedido'] == 1) ? "<span class='migrated'>servicios_pedido</span>" : "<a href='servicios_pedido.php'>servicios_pedido</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['tipo_pedido'] == 1) ? "<span class='migrated'>tipo_pedido</span>" : "<a href='tipo_pedido.php'>tipo_pedido</a>" ?>     
        </li>
        <li>
            <?php echo ($migratedTables['actividad_reciente'] == 1) ? "<span class='migrated'>actividad_reciente</span>" : "<a href='actividad_reciente.php'>actividad_reciente</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['tipo_actividad_reciente'] == 1) ? "<span class='migrated'>tipo_actividad_reciente</span>" : "<a href='tipo_actividad_reciente.php'>tipo_actividad_reciente</a>" ?>            
        </li>
        <li>
            <?php echo ($migratedTables['tipo_lugar'] == 1) ? "<span class='migrated'>tipo_lugar</span>" : "<a href='tipo_lugar.php'>tipo_lugar</a>" ?>            
        </li>
    </ol>
    <ol id="usuarios">
        <li>
            <?php echo ($migratedTables['usuarios'] == 1) ? "<span class='migrated'>usuarios</span>" : "<a href='usuarios.php'>usuarios</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['tipo_usuario'] == 1) ? "<span class='migrated'>tipo_usuario</span>" : "<a href='tipo_usuario.php'>tipo_usuario</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['acciones'] == 1 && $migratedTables['acciones_usuario'] == 1) ? "<span class='migrated'>acciones</span>" : "<a href='acciones.php'>acciones</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['recomendaciones'] == 1) ? "<span class='migrated'>recomendaciones</span>" : "<a href='recomendaciones.php'>recomendaciones</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['tag'] == 1) ? "<span class='migrated'>tag</span>" : "<a href='tag.php'>tag</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['tag_recomendacion'] == 1) ? "<span class='migrated'>tag_recomendacion</span>" : "<a href='tag_recomendacion.php'>tag_recomendacion</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['util'] == 1) ? "<span class='migrated'>util</span>" : "<a href='util.php'>util</a>" ?>
           
        </li>       
    </ol>
    <ol id="extras">
        <li>
            <?php echo ($migratedTables['comuna'] == 1) ? "<span class='migrated'>comuna</span>" : "<a href='comunas.php'>comuna</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['ciudad'] == 1) ? "<span class='migrated'>ciudad</span>" : "<a href='ciudad.php'>ciudad</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['pais'] == 1) ? "<span class='migrated'>pais</span>" : "<a href='pais.php'>pais</a>" ?>
        </li>
        <li>
            <?php echo ($migratedTables['sector'] == 1) ? "<span class='migrated'>sector</span>" : "<a href='sector.php'>sector</a>" ?>
        </li>
    </ol>
</div>
</body>
</html>