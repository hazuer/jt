<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

define( '_VALID_MOS', 1 );
session_start();
date_default_timezone_set('America/Mexico_City');


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
		$message  = 'Error al guardar la infomación del paquete';
		$data['id_location'] = $_POST['id_location'];
		$phone               = $_POST['phone'];
		$receiver            = $_POST['receiver'];
		$data['id_status']   = $_POST['id_status'];
		$data['note']        = $_POST['note'];
		$id_contact          = $_POST['id_contact'];

		$action              = $_POST['action'];
		try {
			$sqlContactCheck = "SELECT COUNT(phone) tContact FROM cat_contact 
			WHERE phone IN ('$phone') AND contact_name IN('$receiver') AND id_location IN(".$data['id_location'].")";
			$rstContactCheck = $db->select($sqlContactCheck);
			$tContact = $rstContactCheck[0]['tContact'];
			if($tContact==0){
				$contact['id_location']       = $data['id_location'];
				$contact['phone']             = $phone;
				$contact['contact_name']      = $receiver;
				$contact['id_contact_type']   = 1; //SMS
				$contact['id_contact_status'] = 1;
				$contact['id_contact']  = null;
				$id_contact = $db->insert('cat_contact',$contact);
			}

			$data['id_contact']  = $id_contact;

			switch ($action) {
				case 'update':
					if($data['id_status'] == 4 || $data['id_status'] == 5){
						$data['d_date']     = date("Y-m-d H:i:s");
						$data['d_user_id']  = $_SESSION["uId"];
					}
					$id       = $_POST['id_package'];
					$success  = 'true';
					$dataJson = $db->update('package',$data," `id_package` = $id");
					$message  = 'Actualizado';
				break;
				case 'new':

					$data['id_package']  = null;
					$data['folio']       = $_POST['folio'];
					$data['c_date']      = date("Y-m-d H:i:s");
					$data['c_user_id']   = $_SESSION["uId"];
					$data['tracking']    = $_POST['tracking'];
					$sqlCheck = "SELECT COUNT(tracking) total FROM package WHERE tracking IN ('".$data['tracking']."')";
					$rstCheck = $db->select($sqlCheck);
					$total = $rstCheck[0]['total'];
					if($total==0){
						//TODO: check if folio was delivered or canceled| reconsider
						$success  = 'true';
						$dataJson = $db->insert('package',$data);
						$message  = 'Registrado';
					}else{
						$success  = 'false';
						$dataJson = [];
						$message  = 'El código ya esta registrado';
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
		$message  = 'Error al guardar el folio';

		$id_location      = $_POST['id_location'];
		$data['folio']    = $_POST['mfNumFolio'];
		try {
			$success  = 'true';
			$dataJson = $db->update('folio',$data," `id_location` = $id_location");
			$message  = 'Actualizado';
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
		$message  = 'Error al obtener la información de contactos';

		$phone       = $_POST['phone'];
		$id_location = $_POST['id_location'];
		try {
			$success  = 'true';
			$sqlContact = "SELECT id_contact,contact_name,phone FROM cat_contact WHERE phone LIKE '%$phone%' AND id_location IN($id_location) AND id_contact_status IN(1) ORDER BY contact_name ASC LIMIT 10";
			$dataJson = $db->select($sqlContact);
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

	case 'saveContact':
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error al guardar el contacto';

		 $data['id_location']       = $_POST['id_location'];
		 $data['phone']             = $_POST['mCPhone'];
		 $data['contact_name']      = $_POST['mCName'];
		 $data['id_contact_type']   = $_POST['mCContactType'];
		 $data['id_contact_status'] = $_POST['mCEstatus'];
		try {
			$data['id_contact']  = null;
			$success  = 'true';
			$dataJson = $db->insert('cat_contact',$data);
			$message  = 'Registrado';
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
		$message  = 'Error listar los envios para sms';
		$id_location   = $_POST['id_location'];
		$IdContactType = $_POST['IdContactType'];
		$idStatus      = $_POST['idStatus'];
		$sql="SELECT 
		cc.phone,
		(SELECT cct2.contact_name FROM cat_contact cct2 WHERE cct2.phone=cc.phone AND cct2.id_location IN($id_location) LIMIT 1) main_name,
		COUNT(p.tracking) AS total_p,
		GROUP_CONCAT(p.tracking) AS trackings,
		GROUP_CONCAT(p.id_package) AS ids 
		FROM package p 
		INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
		INNER JOIN cat_contact_type cct ON cct.id_contact_type = cc.id_contact_type 
		WHERE 
		p.id_location IN ($id_location) 
		AND p.id_status IN (1) 
		AND cct.id_contact_type IN (1) 
		GROUP BY cc.phone,main_name
		ORDER BY cc.phone ASC";
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
		$message  = 'Error al enviar los mensajes';

		$id_location   = $_POST['id_location'];
		$idContactType = $_POST['idContactType'];
		$smsMessage    = $_POST['message'];

		$data['id_notification'] = null;
		$data['id_location']     = $id_location;
		$data['n_user_id']       = $_SESSION["uId"];
		$data['message']         = $smsMessage;
		$data['id_contact_type'] = $idContactType;

		$arrayNotification   = json_decode($_POST['arrayNotification'], true);

			$account_sid   = "ACf6823c76da7644c216809dfe186f1f83";
			$auth_token    = "655bbef60ff32b4ac59a1a354f15432d";
			$twilio_number = "+18019013730";

			$totalSms    = COUNT($arrayNotification);
			$smsEnviados = 0;
			
			foreach ($arrayNotification as $item) {
				$phone = $item['phone'];

				$client = new Client($account_sid, $auth_token);
				#################################
				#$response = (object) ['sid' => true];
				#################################
				try {
					$response = $client->messages->create(
						'+52'.$phone,
						array(
							'from' => $twilio_number,
							'body' => $smsMessage
						)
					);

					if ($response->sid) {
						$data['sid']   = $response->sid;
						$statusPackage = 2; // SMS Enviado
						$smsEnviados++;
					}
				} catch (Exception $e) {
					$data['sid']   = $e->getMessage();
					$statusPackage = 6; //Error al enviar SMS
				}

				//Recorrer los ids y guardar su notificacion y actualizar su estatus
				$listIds = explode(",", $item['ids']);
				foreach ($listIds as $id_package) {
					$data['id_package']  = $id_package;
					$data['n_date']  = date("Y-m-d H:i:s");
					$db->insert('notification',$data);
					$upData['id_status'] = $statusPackage;
					$db->update('package',$upData," `id_package` IN($id_package)");
				}
			}

			$result = [
				'success'  => 'true',
				'dataJson' => [],
				'message'  => "Se han enviado $smsEnviados mensajes de un total de $totalSms"
			];

		echo json_encode($result);
	break;

	case 'releasePackage':
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error liberar el paquete';
		$id_location = $_POST['id_location'];
		$tracking    = $_POST['tracking'];
		$jsonPakage = $_POST['listPackageRelease'];
		try {

			$sql="SELECT id_status
		   	FROM package
		   	WHERE tracking IN ('$tracking')
			AND id_location IN ($id_location)
			LIMIT 1";
			$checkRelease = $db->select($sql);
			if(count($checkRelease)==0){
				$success  = 'false';
				$message  = 'Paquete no encontrado';
			}else{
				$idEstatus = $checkRelease[0]['id_status'];
				switch ($idEstatus) {
					case 1:
					case 6:
						$success  = 'false';
						$message  = 'No es posible liberar un paquete sin contactar al destinatario';
						break;
					case 4:
					case 5:
						$success  = 'false';
						$message  = 'El paquete ya no esta disponible';
						break;
					case 3:
						$success  = 'false';
						$message  = 'El paquete ya fue entregado';
						break;
					case 2:
					case 7:
						$success  = 'true';
						$message  = 'Paquete Liberado';

						$data['id_status']  = 3; //Liberado
						$data['d_date']     = date("Y-m-d H:i:s");
						$data['d_user_id']  = $_SESSION["uId"];
						$rst = $db->update('package',$data," `tracking` = '$tracking'");
						$listPackageRelease   = json_decode($jsonPakage, true);
						$inList = implode(", ", $listPackageRelease);
						$sql ="SELECT DISTINCT 
						p.tracking,
						cc.phone,
						cc.contact_name receiver,
						p.folio 
						FROM package p 
						INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
						WHERE tracking IN($inList) AND p.id_location IN($id_location) 
						AND id_status IN (3)";
						$records = $db->select($sql);
						$dataJson = $records;
						break;
				}
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
}