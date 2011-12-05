<?php
error_reporting(E_ALL);ini_set('display_errors','1');

// Datos de configuración
$config = array(
    'webhost' => 'www.loogares.com',
    'webroot' => '/dev',
    'baselink' => '/',
    'dbhost' => '69.164.205.225',
    'dbuser' => 'loogaresuser',
    'dbpass' => 'L0og4r3s',
    'dbname' => 'loogaresdb',
    'dbprefix' => '',
    'clave' => 'loogares',
    'sitio' => 'Loogares.com | Comparte y Vive la ciudad',
    'googlemaps_apikey' => 'ABQIAAAAzlORVreqGpkqQqQvjPC0pBTqGAcz-UBEJ7apA_tMhekG0smvBRSy_IPIzC_OjDTx3IC5QK-ZXxFlmg',
    'facebook_appid' => '121287677927865',  // http://www.facebook.com/developers/apps.php?app_id=121287677927865
    'facebook_appsecret' => '432f1b6d45956f5695bf74daefdc3748',
    'mailchimp_apikey' => '1b0ea3e9b81c3d8fe0c5ae5f022805b2-us2',
    'mailchimp_list_id' => 'ab5bd16845',
    'adsense' => false,
    'social-widgets' => false
);

$rootPath = dirname(__FILE__);
/* incluye librerías en el path */
set_include_path(
    get_include_path() .
    PATH_SEPARATOR . $rootPath .
    PATH_SEPARATOR . $rootPath . '/lib' .
    PATH_SEPARATOR . $rootPath . '/fn'
    );
date_default_timezone_set('America/Santiago');

/* atajos para la ubicación de directorio base */
define('SERVER','http://'.$_SERVER['HTTP_HOST']);
define('WEBROOT',$config['webroot']);
define('BASELINK',$config['baselink']);
define('ADMIN',WEBROOT.'/mago');
define('CLAVE',$config['clave']);

/* base de datos */
require_once 'ez_sql_core.php';
require_once 'ez_sql_mysql.php';
require_once 'jtMySQL.php';
$db = new jtMySQL(
    $config['dbuser'],
    $config['dbpass'],
    $config['dbname'],
    $config['dbhost']
    );

/* Inicializa jtHead : clase para organizar los elementos script, title, link, etc del header de la página */
require_once 'jtHead.php';
$head = new jtHead();

//$head->meta("Content-Type","text/html; charset=latin-1",'http-equiv');
define('USAR_UTF8DECODE',true);
$head->meta("Content-Type","text/html; charset=utf-8",'http-equiv');

$head->cssInclude(WEBROOT.'/css/reset.css');
$head->jQuerySrc = WEBROOT.'/js/jquery-1.4.2.min.js';
$head->jQueryValidateSrc = WEBROOT.'/js/jquery.validate.min.js';

$head->jQueryValidateParams =
"    jQuery.validator.messages.required = 'Este campo es obligatorio. Por favor, complétalo.';
    jQuery.validator.messages.email = 'La dirección de E-mail está mal escrita. Por favor, corrígela';
    jQuery.validator.messages.number = 'Debes escribir un número';";


/* mailchimp api
 * 1b0ea3e9b81c3d8fe0c5ae5f022805b2-us2
 * 83eaf5240fcd168ae5875ca071aa35aa-us2
 */