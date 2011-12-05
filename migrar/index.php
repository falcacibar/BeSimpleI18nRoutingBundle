<!DOCTYPE html> 
<head>
<meta charset="utf-8">
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
<h1>Loogares Migration System</h1>
<h2>Migracion de DB actual a Modelo basado en ORM</h2>
<ul class="tabs">
    <li class="selected"><a href="#lugares">Lugares</a></li>
    <li><a href="#usuarios">Usuarios</a></li>
    <li><a href="#extras">Extras</a></li>
</ul>
<div id="list">
    <ol id="lugares">
        <li><a href="estado_lugar.php">estado_lugares</a></li>
        <li><a href="lugares.php">lugares</a></li>
        <li><a href="categorias.php">categorias</a></li>
        <li><a href="categoria_lugar.php">categoria_lugares</a></li>
        <li><a href="tipo_categoria.php">tipo_categoria</a></li>
        <li><a href="caracteristica.php">caracteristica</a></li>
        <li><a href="caracteristica_lugar.php">caracteristica_lugar</a></li>
        <li><a href="caracteristica_categoria.php">caracteristica_categoria</a></li>
        <li><a href="imagenes_lugar.php">imagenes_lugar</a></li>
        <li><a href="subcategoria.php">subcategoria</a></li>
        <li><a href="subcategoria_lugar.php">subcategoria_lugar</a></li>
        <li><a href="horarios.php">horario</a></li>
    </ol>
    <ol id="usuarios">
        <li><a href="usuarios.php">usuarios</a></li>
        <li><a href="recomendaciones.php">recomendacion</a></li>
        <li><a href="tag.php">tag</a></li>
        <li><a href="tag_recomendacion.php">tag_recomendacion</a></li>
        <li><a href="util.php">util</a></li>
    </ol>
    <ol id="extras">
        <li><a href="comunas.php">comuna</a></li>
        <li><a href="ciudad.php">ciudad</a></li>
        <li><a href="sector.php">sector</a></li>
    </ol>
</div>
</body>
</html>