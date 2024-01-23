<?php
define( '_VALID_MOS', 1 );
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../system/configuration.php');
require_once('../system/DB.php');
$db = new DB(HOST,USERNAME,PASSWD,DBNAME,PORT,SOCKET);

header('Content-Type: application/json; charset=utf-8');

switch ($_POST['option']) {

	case 'registerEvent':
		if(!isset($_POST['id_event'])){
			header('Location: '.BASE_URL);
			die();
		}

		$result      = [];
		$action      = $_POST['action'];

		$id          = $_POST['id_event'];
		$name_event  = $_POST['name_event'];
		$place_event = $_POST['place_event'];
		$total_users = $_POST['total_users'];
		$start_date  = $_POST['start_date'];
		$end_date    = $_POST['end_date'];
		$status      = $_POST['status'];

		#$cuser_id        = $_SESSION["uId"];

		$data    = [
			'name_event'  => $name_event,
			'place_event' => $place_event,
			'total_users' => $total_users,
			'start_date'  => date("Y-m-d 00:00:00", strtotime($start_date)),
			'end_date'    => date("Y-m-d 11:59:59", strtotime($end_date)),
			'status'      => $status
		];

		try {
			$success  = 'false';
			$dataJson = [];
			$message  = 'Error to process request';
			switch ($action) {
				case 'update':
					#$data['mdate']    = date('Y-m-d H:i:s');
					#$data['muser_id'] = $cuser_id;
					$success  = 'true';
					$dataJson = $db->update('event',$data," `id_event` = $id");
					$message  = 'Upgraded Event';
				break;
				case 'new':
					$data['id_event']       = null;
					$data['cdate']    = date('Y-m-d H:i:s');
					#$data['cuser_id'] = $cuser_id;
					$success  = 'true';
					$dataJson = $db->insert('event',$data);
					$message  = 'Registered Event';
				break;
			}

			$result = [
				'success'  => $success,
				'dataJson' => json_encode($dataJson),
				'message'  => $message
			];

			echo json_encode($result);
		} catch (Exception $e) {
			$result = [
				'success'  => 'fasle',
				'dataJson' => [],
				'message'  => 'Exception caught: '.$e->getMessage()
			];
		}
	break;

	case 'registerUser':
		if(!isset($_POST['id_event_user'])){
			header('Location: '.BASE_URL);
			die();
		}

		$result   = [];
		$action   = $_POST['action'];

		$id       = $_POST['id_event_user'];
		$id_event = $_POST['id_event'];
		$type_ibo      = $_POST['type_ibo'];
		$ibo      = $_POST['ibo'];
		$name      = $_POST['name'];
		$email    = $_POST['email'];
		$phone    = $_POST['phone'];
		$status   = $_POST['status'];
		//$base_url   = $_POST['base_url'];

		$data    = [
			'id_event' => $id_event,
			'type_ibo'      => $type_ibo,
			'ibo'      => $ibo,
			'name'     => $name,
			'email'    => $email,
			'phone'    => $phone,
			'status'   => $status
		];

		try {
			$success  = 'false';
			$dataJson = [];
			$message  = 'Error to process request';
			switch ($action) {
				case 'update':
					$success  = 'true';
					$db->update('event_user',$data," `id_event_user` = $id");
					$message  = 'Upgraded User';
					if($status==1){
						$dtsUp = $db->select("SELECT id_event_user,
						id_event,
						type_ibo,
						ibo,
						name,
						email,
						phone,
						cdate,
						status,
						qr_path fullUrlCode,
						qr_info FROM event_user WHERE id_event_user=$id");
						$dtsUp[0]['typeNotifications'] = ['SMS','EMAIL'];
						$notification       = notificationEvent(WS_URL,TOKEN,$dtsUp[0]);
						$rstNot             = $notification->data;
						$dataJson['rstNot'] = $rstNot->message;
					}

				break;
				case 'new':
					$data['id_event_user'] = null;
					$data['cdate']    = date('Y-m-d H:i:s');
					$success  = 'true';
					$message  = 'Registered User';
					$id_event_user = $db->insert('event_user',$data);
					$rstQr = createQr($_POST, $id_event_user);
					if($rstQr['success']){
						$upQr['qr_path']  = $rstQr['qr_path'];
						$upQr['qr_info']  = $rstQr['qr_info'];
						$db->update('event_user',$upQr," `id_event_user` = $id_event_user");
						$data['typeNotifications'] = ['SMS','EMAIL'];
						$data['fullUrlCode']       = $upQr['qr_path'];
						$data['id_event_user'] = $id_event_user;
						$notification       = notificationEvent(WS_URL,TOKEN,$data);
						$rstNot             = $notification->data;
						$dataJson['rstNot'] = $rstNot->message;
					}
				break;
			}

			$result = [
				'success'  => $success,
				'dataJson' => json_encode($dataJson),
				'message'  => $message
			];

			echo json_encode($result);
		} catch (Exception $e) {
			$result = [
				'success'  => 'fasle',
				'dataJson' => [],
				'message'  => 'Exception caught: '.$e->getMessage()
			];
		}
	break;


	case 'resend':
		if(!isset($_POST['id_event_user'])){
			header('Location: '.BASE_URL);
			die();
		}

		$result   = [];
		$id       = $_POST['id_event_user'];

		try {
			$success  = 'true';
			$message  = 'Notifications sent';
			$data=$db->select("SELECT id_event_user,
			id_event,
			ibo,
			name,
			email,
			phone,
			cdate,
			status,
			qr_path fullUrlCode,
			qr_info FROM event_user WHERE id_event_user=$id");
			$data[0]['typeNotifications'] = ['SMS','EMAIL'];

			$notification       = notificationEvent(WS_URL,TOKEN,$data[0]);
			$rstNot             = $notification->data;
			$dataJson['rstNot'] = $rstNot->message;
			echo json_encode([
				'success'  => $success,
				'dataJson' => json_encode($dataJson),
				'message'  => $message
			]);
		} catch (Exception $e) {
			$result = [
				'success'  => 'fasle',
				'dataJson' => [],
				'message'  => 'Exception caught: '.$e->getMessage()
			];
		}
	break;


	case 'saveRecordsQr':
		$result   = [];
		try {
			$decodedText = base64_decode($_POST['decodedText']);

			$records = explode("|",$decodedText);
			$data['id_event_record'] = null;
			$data['id_event_user'] = $records[1];
			$data['id_event'] = $records[2];
			$data['ibo'] = $records[3];
			$data['cdate']    = date('Y-m-d H:i:s');

			$id_event_scan  = $_POST['id_event_scan'];
			if($id_event_scan!=$data['id_event']){
				$result = [
					'success'  => 'false',
					'dataJson' => [],
					'message'  => 'The Qr does not correspond to the event'
				];
				echo json_encode($result);
				die();
			}

			$id_event_user = $data['id_event_user'];
			$id_event      = $data['id_event'];
			$ibo           = $data['ibo'];

			#check if user is no registered
			$sqlUser=$db->select("SELECT * FROM event_user WHERE id_event_user=$id_event_user AND id_event=$id_event AND ibo='$ibo'");
			//var_dump($sqlUser);
			if(count($sqlUser)==0){
				$result = [
					'success'  => 'false',
					'dataJson' => [],
					'message'  => 'Unregistered user'
				];
				echo json_encode($result);
				die();
			}
			#check if user is no deleted
			if($sqlUser[0]['status']==2){
				$result = [
					'success'  => 'false',
					'dataJson' => [],
					'message'  => 'Deleted user'
				];
				echo json_encode($result);
				die();
			}

			$dataJson = $db->insert('event_user_record',$data);
			$success  = 'true';
			$message  = 'Registered Attendance';
			//$dataJson =[];

			$qrScaned  = $_POST['qrScaned'];
			$list = explode("|",$qrScaned);
			$sqlIn='';
			foreach ($list as $k => $v) {
				if($v!=""){
					$sqlIn = "'$v'".",".$sqlIn;
				}
			}
			$in = trim($sqlIn, ',');

			/*$records = $db->select("SELECT * FROM event_user_record WHERE id_event_user=$id_event_user AND id_event=$id_event AND ibo='$ibo' ORDER BY id_event_record DESC");*/
			$records = $db->select("SELECT * FROM event_user_record WHERE id_event_user IN($in) AND id_event=$id_event  ORDER BY id_event_record DESC");
			$result = [
				'success'  => $success,
				'dataJson' => json_encode($records),
				'message'  => $message
			];

			echo json_encode($result);

		} catch (Exception $e) {
			$result = [
				'success'  => 'fasle',
				'dataJson' => [],
				'message'  => 'Exception caught: '.$e->getMessage()
			];
		}
	break;

	case 'getRecords':
		$id_event_user = $_POST['id_event_user'];
		$id_event = $_POST['id_event'];
		$ibo      = $_POST['ibo'];

		$records = $db->select("SELECT * FROM event_user_record WHERE id_event_user=$id_event_user AND id_event=$id_event AND ibo='$ibo'");
		#var_dump($records);
		$message  = 'All records';
		$result = [
			'success'  => 'true',
			'dataJson' => json_encode($records),
			'message'  => $message
		];
		echo json_encode($result);
	break;

	case 'loadCsv':
		$errosFile  = [];
		$flag = true;
		$id_event = $_POST['id_event'];
		$place_available = (int) $_POST['place_available'];

		if(isset($_FILES['file_csv'])){

			if($_FILES['file_csv']["size"]==0){
				$flag=false;
				echo json_encode([
					'success'  => 'false',
					'dataJson' => [],
					'message'  => 'File is empty'
				]);
				die();
			}
			$extFile = $_FILES['file_csv']["type"];
			$allowedExt =['text/csv'];
			if(!in_array($extFile,$allowedExt)){
				$flag=false;
				echo json_encode([
					'success'  => 'false',
					'dataJson' => [],
					'message'  => 'The file must be in .csv format'
				]);
				die();
			}
		}

		$row      = 0;
		$cols     = 5;
		$result   = [];
		$success  = '';
		$dataJson = [];
		$message  = '';
		if (($handle = fopen($_FILES['file_csv']["tmp_name"], "r")) !== FALSE) {
			$allRows = file($_FILES['file_csv']["tmp_name"], FILE_SKIP_EMPTY_LINES);
			$tUser   = (count($allRows) -1 );

			if($tUser > $place_available){
				echo json_encode([
					'success'  => 'false',
					'dataJson' => [],
					'message'  => "There are only ".$place_available." places available and you are trying to register ".$tUser." users, insufficient course capacity"
				]);
				die();
			}

			while (($csv = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$num = count($csv); //num = cols
				if($num!=$cols){
					echo json_encode([
							'success'  => 'false',
							'dataJson' => [],
							'message'  => 'The number of columns does not match'
						]);
						die();
				}else{
					if($row!=0){
						$data  = [
							'id_event_user' => null,
							'id_event'      => $id_event,
							'type_ibo'      => $csv[0],
							'ibo'           => $csv[1],
							'name'          => $csv[2],
							'email'         => $csv[3],
							'phone'         => $csv[4],
							'cdate'         => date('Y-m-d H:i:s'),
							'status'        => 1
						];
						$id_event_user = $db->insert('event_user',$data);
						$data['base_url']=$_POST['base_url'];
						$rstQr = createQr($data, $id_event_user);
						if($rstQr['success']){
							$upQr['qr_path']  = $rstQr['qr_path'];
							$upQr['qr_info']  = $rstQr['qr_info'];
							$db->update('event_user',$upQr," `id_event_user` = $id_event_user");

							$data['typeNotifications'] = ['SMS','EMAIL'];
							$data['fullUrlCode']       = $upQr['qr_path'];
							$data['id_event_user'] = $id_event_user;
							$notification       = notificationEvent(WS_URL,TOKEN,$data);
							$rstNot             = $notification->data;
							$dataJson['rstNot'] = $rstNot->message;
						}
					}
					$row++;
				}
			}
			fclose($handle);
			$success  = 'true';
			$dataJson = ($row-1);
			$message  = $dataJson.' Registered Users';
		}else{
			$message  = 'An error occurred while reading the file';
			$dataJson = [];
			$success  = 'false';
		}

		$result   = [
			'success'  => $success,
			'dataJson' => json_encode($dataJson),
			'message'  => $message
		];
		echo json_encode($result);
		die();

		case 'getCheckList':
			$id_event = $_POST['id_event'];
			$list=$db->select("SELECT
				r.ibo,
				r.cdate,
				eu.name
			FROM
				event_user_record r
				INNER JOIN event_user eu ON eu.id_event_user = r.id_event_user
			WHERE
				1
				AND r.id_event = $id_event
			ORDER BY
				r.cdate DESC");
			$result   = [
				'success'  => 'true',
				'dataJson' => json_encode($list),
				'message'  => ''
			];
			echo json_encode($result);

		break;

		default:
		header('Location: '.BASE_URL);
		die();
	break;

	break;
}


function createQr($request,$id_event_user){
	try {
		$id_event      = $request['id_event'];
		$ibo           = $request['ibo'];
		$stringQr      = base64_encode(base64_encode(COD_ENZ_B64)."|".$id_event_user."|".$id_event."|".$ibo);
		$base_url      = $request['base_url'];
		include_once('../system/phpqrcode/qrlib.php');
		$codesDir = "../".FOLDER_QR;
		$uniqueID = rand(11111, 99999);
		$now       = new DateTime();
        $timeStamp = $now->getTimestamp();
		$u         = $timeStamp.'-'.$uniqueID;
		$codeFile  = $codesDir."/".$u.'.png';
		QRcode::png($stringQr,$codeFile,'M','7');
		return ['success'=>true, "qr_path"=>$base_url.''.$u.'.png', "qr_info"=>$stringQr];
	} catch (Exception $e) {
		return ['success'=>false, "qr_path"=>'', $qr_info=>''];
	}

}

function notificationEvent ($url_ws,$token,$data){
	$url     = $url_ws."api/auth/ibo/notification-event";
	$headers = [
		'Content-Type: application/json',
		'X-Requested-With: XMLHttpRequest',
		'Authorization : '.$token
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	$result = curl_exec($ch);
	curl_close($ch);
	return json_decode($result);
}