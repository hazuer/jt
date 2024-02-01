<?php
defined('_VALID_MOS') or die('Restricted access');

$userRoot = '';
$docRoot  = $_SERVER['SERVER_NAME'];
#define('BASE_URL','https://'.$docRoot."/".$userRoot);
define('BASE_URL','https://'.$docRoot);
#var_dump(BASE_URL);

define('PAGE_TITLE','J&T Express');

//database configuration
define('HOST','127.0.0.1');
define('USERNAME','root');
define('PASSWD','');
define('DBNAME','u611824705_jt');
define('PORT','3306');
define('SOCKET','null');

?>