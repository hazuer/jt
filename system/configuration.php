<?php
defined('_VALID_MOS') or die('Restricted access');

$userRoot = '';
$docRoot  = $_SERVER['SERVER_NAME'];
#define('BASE_URL','https://'.$docRoot."/".$userRoot);
define('BASE_URL','https://'.$docRoot);
#var_dump(BASE_URL);

define('PAGE_TITLE','J&T Express');

//database configuration
define('HOST','localhost');
define('USERNAME','root');
define('PASSWD','');
define('DBNAME','jt_local');
define('PORT','3306');
define('SOCKET','null');

?>