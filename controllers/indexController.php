<?php
session_start();
# ini_set('display_errors',1);
# error_reporting(E_ALL);

define( '_VALID_MOS', 1 );

require_once('../system/configuration.php');
require_once('../system/DB.php');

$db = new DB(HOST,USERNAME,PASSWD,DBNAME,PORT,SOCKET);

switch ($_REQUEST['option']) {
	case 'login':
		if(empty($_POST['username']) || empty($_POST['password'])){
			header('Location: '.BASE_URL);
			die();
		}else{
			header('Content-Type: application/json; charset=utf-8');

			$result = ['success'=>'false'];
			try {
			    $u = $_POST['username'];
			    $p = $_POST['password'];
			    $sql ="SELECT * FROM users 
				WHERE 1 
				AND user = '$u' 
				AND password = md5('$p') 
				AND status IN(1)
				LIMIT 1";
				$user = $db->select($sql);
				if(isset($user[0]['id'])) {
					$_SESSION["uId"]       = $user[0]['id'];
					$_SESSION["uName"]     = $u;
					$_SESSION["uLocationDefault"] = $user[0]['id_location_default'];
					$_SESSION["uActive"]   = true;
					$_SESSION["uMarker"]   = 'black';
					$result                = ['success' => 'true'];
				}

				echo json_encode($result);
				die();
			} catch (Exception $e) {
				echo json_encode( 'Exception caught: ',  $e->getMessage(), "\n");
			}
		}
	break;

	case 'logoff':
		session_unset();
		session_destroy();
		header('Location: '.BASE_URL);
		die();
	break;

	default:
		header('Location: '.BASE_URL);
		die();
	break;
}

?>