<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define( '_VALID_MOS', 1 );
session_start();

require_once('../system/configuration.php');
require_once('../system/DB.php');
$db = new DB(HOST,USERNAME,PASSWD,DBNAME,PORT,SOCKET);

require '../vendor/autoload.php';
use Twilio\Rest\Client;

header('Content-Type: application/json; charset=utf-8');

switch ($_POST['option']) {

	case 'changeLocation':
		$id_location           = $_POST['id_location'];
		$_SESSION['uLocation'] = $id_location;
		$result = [
			'success'  => 'true',
			'dataJson' => $id_location,
			'message'  => 'ok'
		];
		echo json_encode($result);
	break;

	case 'getFolio':
		$id_location = $_POST['id_location'];
		$type = $_POST['type'];
		$newOrCurrent = ($type=='new')? 1: 0;
		$sqlMax="SELECT MAX(folio) + $newOrCurrent AS nuevo_folio FROM folio WHERE id_location IN ($id_location)";
		$records = $db->select($sqlMax);
		$folio = $records[0]['nuevo_folio'];
		$upQr['folio']  = $folio;
		$db->update('folio',$upQr," `id_location` = $id_location");
		$result = [
			'success' => 'true',
			'folio'   => $folio,
			'message' => 'ok'
		];
		echo json_encode($result);
	break;

	case 'savePackage':
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error to process request';

		$data['phone']       = $_POST['phone'];
		$data['receiver']    = $_POST['receiver'];
		$data['id_status']   = $_POST['id_status'];

		$action              = $_POST['action'];
		try {
			switch ($action) {
				case 'update':
					$id        = $_POST['id_package'];
					$success  = 'true';
					$dataJson = $db->update('package',$data," `id_package` = $id");
					$message  = 'Upgraded';
				break;
				case 'new':
					$data['id_package']  = null;
					$data['id_location'] = $_POST['id_location'];
					$data['folio']       = $_POST['folio'];
					$data['c_date']      = $_POST['c_date'];
					$data['c_user_id']   = $_SESSION["uId"];
					$data['tracking']    = $_POST['tracking'];
					$sqlCheck = "SELECT COUNT(tracking) total FROM package WHERE tracking IN ('".$data['tracking']."')";
					$rstCheck = $db->select($sqlCheck);
					$total = $rstCheck[0]['total'];
					if($total==0){
						//TODO: check if folio was delivered or canceled| reconsider
						$success  = 'true';
						$dataJson = $db->insert('package',$data);
						$message  = 'Registered';
					}else{
						$success  = 'false';
						$dataJson = [];
						$message  = 'Tracking already exist';
					}

				break;
			}

			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message
			];

		} catch (Exception $e) {
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message.": ".$e->getMessage()
			];
		}
		echo json_encode($result);

	break;

	case 'saveFolio':
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error to process request';

		$id_location      = $_POST['id_location'];
		$data['folio']    = $_POST['mfNumFolio'];
		try {
			$success  = 'true';
			$dataJson = $db->update('folio',$data," `id_location` = $id_location");
			$message  = 'Upgraded';
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message
			];
		} catch (Exception $e) {
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message.": ".$e->getMessage()
			];
		}
		echo json_encode($result);
	break;

	case 'getContact':
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error to process request';

		$phone       = $_POST['phone'];
		$id_location = $_POST['id_location'];
		try {
			$success  = 'true';
			$sqlContact = "SELECT contact_name,phone FROM cat_contact WHERE phone LIKE '%$phone%' AND id_location IN($id_location) AND id_contact_status IN(1) ORDER BY contact_name ASC LIMIT 10";
			$dataJson = $db->select($sqlContact);
			$message  = 'Conactact';
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message
			];
		} catch (Exception $e) {
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message.": ".$e->getMessage()
			];
		}
		echo json_encode($result);
	break;

	case 'saveContact':
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error to process request';

		 $data['id_location']       = $_POST['id_location'];
		 $data['phone']             = $_POST['mCPhone'];
		 $data['contact_name']      = $_POST['mCName'];
		 $data['id_contact_type']   = $_POST['mCContactType'];
		 $data['id_contact_status'] = $_POST['mCEstatus'];
		try {
			//$id        = $_POST['id_contact']; #TODO
			$data['id_contact']  = null;
			$success  = 'true';
			$dataJson = $db->insert('cat_contact',$data);
			$message  = 'Added';
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message
			];
		} catch (Exception $e) {
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message.": ".$e->getMessage()
			];
		}
		echo json_encode($result);
	break;

	case 'getPackageNewSms':
		try {
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$id_location   = $_POST['id_location'];
		$IdContactType = $_POST['IdContactType'];
		$idStatus      = $_POST['idStatus'];
		$sql="SELECT 
				p.phone,
				(SELECT cc.contact_name FROM cat_contact cc WHERE cc.phone = c.phone LIMIT 1) AS contact_name,
				COUNT(p.tracking) AS total_p,
				GROUP_CONCAT(p.tracking) AS trackings,
				GROUP_CONCAT(p.id_package) AS ids 
			FROM package p 
			INNER JOIN cat_contact c ON c.phone = p.phone 
			INNER JOIN cat_contact_type ct ON ct.id_contact_type = c.id_contact_type 
			WHERE 
				p.id_location IN (1) 
				AND p.id_status IN (1) 
				AND ct.id_contact_type IN (1) 
				GROUP BY p.phone, c.phone 
				ORDER BY p.phone ASC";
				$success  = 'true';
				$dataJson = $db->select($sql);
				$message  = 'ok';
				$result = [
					'success'  => $success,
					'dataJson' => $dataJson,
					'message'  => $message
				];
		} catch (Exception $e) {
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message.": ".$e->getMessage()
			];
		}
		echo json_encode($result);
	break;

	case 'sendMessages':
		$result   = [];
		$success  = 'false';
		$dataJson = [];

		$id_location   = $_POST['id_location'];
		$idContactType = $_POST['idContactType'];
		$message       = $_POST['message'];

		$data['id_notification'] = null;
		$data['id_location']     = $id_location;
		//$data['n_date']          = date("Y-m-d H:i:s");
		$data['n_user_id']       = $_SESSION["uId"];

		$data['message']         = $message;
		$data['id_contact_type'] = $idContactType;

		$arrayNotification   = json_decode($_POST['arrayNotification'], true);
		try {
			// Your Account SID and Auth Token from twilio.com/console
			// To set up environmental variables, see http://twil.io/secure
			$account_sid = "ACf6823c76da7644c216809dfe186f1f83";
			$auth_token = "e5d89fe6304319829b2cc2afa69a6ac6";
			// In production, these should be environment variables. E.g.:
			// $auth_token = $_ENV["TWILIO_AUTH_TOKEN"]

			// A Twilio number you own with SMS capabilities
			$twilio_number = "+18019013730";

			foreach ($arrayNotification as $item) {
				$phone = $item['phone'];
				$data['phone']    = $phone;
				$data['name']      = $item['name'];
				$data['trackings'] = $item['trackings'];

				// Imprimir los valores o realizar cualquier otra operación
				//echo "Phone: ".$phone; //.", name:".$name.", trackings:".$trackings.", Ids: ".$ids. PHP_EOL;
				$client = new Client($account_sid, $auth_token);
				$response = $client->messages->create(
					'+52'.$phone,
					array(
						'from' => $twilio_number,
						'body' => $message
					)
				);

				$data['sid']   = 'error al enviar el mensaje';
				$statusPackage = 6; //Error al enviar SMS
				if ($response->sid) {
					// El mensaje se envió correctamente
					//$message_sid = $response->sid; // Obtener el SID del mensaje
					$data['sid'] = $response->sid;
					$statusPackage=2; // En Proceso (SMS)
				}

				//insert in notifications
				$db->insert('notification',$data);
				$ids = $item['ids'];

				$upData['id_status'] = $statusPackage;
				//update status in package
				$db->update('package',$upData," `id_package` IN($ids)");
			}

			$success  = 'true';
			$dataJson = ['enviados'];
			$message  = 'ok';
			$result = [
				'success'  => $success,
				'dataJson' => [],
				'message'  => $message
			];
		} catch (Exception $e) {
			$result = [
				'success'  => $success,
				'dataJson' => $dataJson,
				'message'  => $message.": ".$e->getMessage()
			];
		}
		echo json_encode($result);
	break;
}