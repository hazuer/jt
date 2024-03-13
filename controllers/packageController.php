<?php
#error_reporting(E_ALL);
#ini_set('display_errors', '1');

define( '_VALID_MOS', 1 );
session_start();
date_default_timezone_set('America/Mexico_City');

require_once('../system/configuration.php');
require_once('../system/DB.php');
$db = new DB(HOST,USERNAME,PASSWD,DBNAME,PORT,SOCKET);

header('Content-Type: application/json; charset=utf-8');
/*$path_file = NODE_PATH_FILE;
$output = null;
		$retval = null;
		$rstNodeJs = null;
			exec("node " . $path_file . ' 2>&1', $output, $retval);
			//exec('node -v', $output, $retval);
			echo json_encode($output);
			die();*/

switch ($_POST['option']) {

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
					if (empty($data['id_contact']) || $data['id_contact'] == 0 || $data['id_contact'] === null) {
						$success  = 'false';
						$dataJson = [];
						$message  = 'No se registro el usuario, vuelve a intentarlo';
					}else{
						$data['id_package']  = null;
						$data['folio']       = $_POST['folio'];
						$data['c_date']      = date("Y-m-d H:i:s");
						$data['c_user_id']   = $_SESSION["uId"];
						$data['tracking']    = $_POST['tracking'];
						$sqlCheck = "SELECT COUNT(tracking) total FROM package WHERE tracking IN ('".$data['tracking']."')";
						$rstCheck = $db->select($sqlCheck);
						$total = $rstCheck[0]['total'];
						if($total==0){

							$id_location = $data['id_location'];
							$sqlCanBeAgrouped = "SELECT p.folio 
							FROM package p 
							LEFT JOIN cat_contact cc ON cc.id_contact=p.id_contact 
							LEFT JOIN cat_status cs ON cs.id_status=p.id_status 
							WHERE 1 
							AND cc.phone IN('$phone')
							AND p.id_location IN ($id_location)
							AND p.id_status IN(1,2,6,7) ORDER BY p.folio DESC";
							$rstCanBeAgrouped = $db->select($sqlCanBeAgrouped);
							$totalPaquetesAgrouped = count($rstCanBeAgrouped);

							$titleMsj  = 'Registrado';
							$msjFolios = "";
							if($totalPaquetesAgrouped>=1){
								$titleMsj  = 'Paquete listo para Agrupar';
								$msjFolios = $phone." - ".$receiver."\n Folios: ";
								foreach ($rstCanBeAgrouped as $resultado) {
									$msjFolios .= $resultado['folio'] . ", ";
								}
								$msjFolios = rtrim($msjFolios, ', ');
							}
							$db->insert('package',$data);

							$success  = 'true';
							$dataJson = $msjFolios;
							$message  = $titleMsj;
						}else{
							$success  = 'false';
							$dataJson = [];
							$message  = 'El número de guía: '.$data['tracking'].' ya está registrado';
						}
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
		GROUP_CONCAT(p.id_package) AS ids,
		GROUP_CONCAT(p.folio) AS folios 
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

		$ids   = $_POST['ids'];
		$phone = $_POST['phone'];


		$nameFile = "sms_".$phone;
		$jsfile_content = 'const adb = require("adbkit");
		const { spawn } = require("child_process");
		const client = adb.createClient();
		const phoneNumber = `'.$phone.'`;
		const message = `'.$smsMessage.'`;
		// Comando adb para enviar el SMS
		const command = `am start -a android.intent.action.SENDTO -d sms:${phoneNumber} --es sms_body "${message}" --ez exit_on_sent true`;
		client.listDevices()
			.then((devices) => {
				if (devices.length > 0) {
					const deviceId = devices[0].id;
					const child = spawn(`adb`, [`-s`, deviceId, `shell`, command], { stdio: `inherit` });
					child.on(`exit`, (code) => {
						console.log(`Proceso de envío de SMS finalizado con código de salida ${code}`);
					});
				} else {
					console.error(`No se encontraron dispositivos conectados.`);
				}
			})
			.catch((err) => {
				console.error(`Error al obtener la lista de dispositivos:`, err);
			});';
		$init = array(
			"nameFile" => $nameFile,
		);
		require_once('../nodejs/NodeJs.php');
		$nodeFile = new NodeJs($init);
		$path_file = NODE_PATH_FILE;
		$nodeFile->createContentFileJs($path_file, $jsfile_content);
		//$nodeFile->getContentFile(true); # true:continue
		$nodeJsPath = $nodeFile->getFullPathFile();
		//var_dump($nodeJsPath);
		$output = null;
		$retval = null;
		$rstNodeJs = null;
		try {
			exec("node " . $nodeJsPath . ' 2>&1', $output, $retval);
			if (isset($output[0]) && !empty($output[0])) {
				$rstNodeJs = $output[1];
				$data['sid']   = $rstNodeJs;
				$statusPackage = 2; // SMS Enviado
			}else{
				$data['sid']   = "Sin respueta de nodeJs";
				$statusPackage = 6; //Error al enviar SMS
			}
		} catch (Exception $e) {
			$data['sid']   = $e->getMessage();
			$statusPackage = 6; //Error al enviar SMS
		}
		unlink($nodeJsPath);

		$listIds = explode(",", $ids);
		foreach ($listIds as $id_package) {
			$nDate = date("Y-m-d H:i:s");
			$data['id_package']  = $id_package;
			$data['n_date']      = $nDate;
			$db->insert('notification',$data);
			$upData['n_date']    = $nDate;
			$upData['n_user_id'] = $_SESSION["uId"];
			$upData['id_status'] = $statusPackage;
			$db->update('package',$upData," `id_package` IN($id_package)");
		}
		sleep(2);
		$result = [
			'success'  => 'true',
			'dataJson' => [$rstNodeJs],
			'message'  => "Enviados"
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

	case 'getRecordsSms':
		try {
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error al consultar mensajes enviados';
		$id_package   = $_POST['id_package'];
		$sql="SELECT 
			n.n_date,
			cc.phone,
			cc.contact_name,
			un.user,
			n.message 
			FROM 
				notification n 
			INNER JOIN users un ON un.id = n.n_user_id 
			INNER JOIN package p  ON p.id_package = n.id_package 
			INNER JOIN cat_contact cc ON cc.id_contact = p.id_contact 
			WHERE 
			n.id_package IN($id_package) 
			ORDER  BY n.n_date DESC";
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

	case 'saveTemplate':
	$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error al guardar el folio';

		$id_location      = $_POST['id_location'];
		$data['template']    = $_POST['mTTemplate'];
		try {
			$success  = 'true';
			$dataJson = $db->update('cat_template',$data," `id_location` = $id_location");
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

	case 'bot':
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

		//$ids   = $_POST['ids'];
		//$phone = $_POST['phone'];
		
		$sqlMessages="SELECT 
		cc.phone,
		(SELECT cct2.contact_name FROM cat_contact cct2 WHERE cct2.phone=cc.phone AND cct2.id_location IN($id_location) LIMIT 1) main_name,
		COUNT(p.tracking) AS total_p,
		GROUP_CONCAT(p.tracking) AS trackings,
		GROUP_CONCAT(p.id_package) AS ids,
		GROUP_CONCAT(p.folio) AS folios 
		FROM package p 
		INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
		INNER JOIN cat_contact_type cct ON cct.id_contact_type = cc.id_contact_type 
		WHERE 
		p.id_location IN ($id_location) 
		AND p.id_status IN (1) 
		AND cct.id_contact_type IN (1) 
		GROUP BY cc.phone,main_name
		ORDER BY cc.phone ASC";

		$nameFile = "whats";
		$jsfile_content = 'const qrcode = require("qrcode-terminal");
		const mysql = require("mysql");
		const { Client } = require("whatsapp-web.js");
		const client = new Client();

		const connection = mysql.createConnection({
			host: `'.HOST.'`,
			user: `'.USERNAME.'`,
			password: `'.PASSWD.'`,
			database: `'.DBNAME.'`,
			port: 3306,
			socketPath: null // Si no estás usando un socket, deja esto como null
		});
		connection.connect((err) => {
			if (err) {
				console.error(`Error al conectar a la base de datos:`, err);
				return;
			}
			console.log(`Conexión exitosa a la base de datos MySQL`);
		});
		client.on(`qr`, (qr) => {
			qrcode.generate(qr, { small: true });
		});
		client.on(`ready`, async () => {
			console.log(`Client is ready!`);
			const query = `'.$sqlMessages.'`;
			console.log(query);
			connection.query(query, async (error, results, fields) => {
				if (error) {
					console.error(`Error al ejecutar la consulta:`, error);
					return;
				}
				const numbers = results.map(result => result.phone); 
				const message = `'.$smsMessage.'`;
				for (let i = 0; i < numbers.length; i++) {
					const number = numbers[i];
					const number_details = await client.getNumberId(number); // Obtener detalles del número de teléfono
					if (number_details) {
						const result = results[i]; // Obtener el registro correspondiente a este número de teléfono
						const trackings = result.trackings;
						// Concatenar los campos trackings y folios al final de la variable message
						const updatedMessage = `${message} Guías: ${trackings}`;
						await client.sendMessage(number_details._serialized, updatedMessage); // Enviar mensaje
						//console.log(updatedMessage);
						console.log(`Mensaje enviado con éxito a`, number);
					} else {
						console.log(number, `Número de móvil no registrado`);
					}
					if (i < numbers.length - 1) {
						await sleep(3000);
					}
				}
				console.log(`Proceso Finalizado`);
				connection.end();
			});
		});
		client.initialize();
		function sleep(ms) {
			return new Promise(resolve => setTimeout(resolve, ms));
		}
		';
		$init = array(
			"nameFile" => $nameFile,
		);
		require_once('../nodejs/NodeJs.php');
		$nodeFile = new NodeJs($init);
		$path_file = NODE_PATH_FILE;
		$nodeFile->createContentFileJs($path_file, $jsfile_content);
		//$nodeFile->getContentFile(true); # true:continue
		$nodeJsPath = $nodeFile->getFullPathFile();

		$result = [
			'success'  => true,
			'dataJson' => '',
			'message'  => $nodeJsPath
		];

		echo json_encode($result);
	break;

	case 'pullRealise':
		$result   = [];
		$success  = 'false';
		$dataJson = [];
		$message  = 'Error liberar el pull de paquetes';
		$id_location = $_POST['id_location'];
		$idsx    = $_POST['idsx'];
		$listIds = explode(",", $idsx);
		$totPaqPorLiberar = count($listIds);
		try {
			$sql="SELECT p.id_status,p.folio,cs.status_desc 
		   	FROM package p 
		   	INNER JOIN cat_status cs ON cs.id_status=p.id_status 
		   	WHERE p.id_package IN ($idsx) 
			AND p.id_location IN ($id_location) 
			AND p.id_status IN (2,7)";
			$checkRelease = $db->select($sql);
			$totalPaqueteDisponibles = count($checkRelease);

			if($totPaqPorLiberar==$totalPaqueteDisponibles){
				$success="true";
				$message="Paquetes liberados";
				$data['id_status']  = 3; //Liberado
				$data['d_date']     = date("Y-m-d H:i:s");
				$data['d_user_id']  = $_SESSION["uId"];
				$rst = $db->update('package',$data," `id_package` IN ($idsx)");
			}else{
				$sql="SELECT p.id_status,p.folio,cs.status_desc 
				FROM package p 
				INNER JOIN cat_status cs ON cs.id_status=p.id_status 
				WHERE p.id_package IN ($idsx) 
				AND p.id_location IN ($id_location) 
				AND p.id_status NOT IN (2,7)";
				$noAvailable = $db->select($sql);
				$success="error";
				$mensaje="No es posible liberar el grupo de paquetes, por favor verifica el estatus de los paquetes:\n";
				foreach ($noAvailable as $resultado) {
					$mensaje .= "Folio:" . $resultado['folio'] . ", Estatus:" . $resultado['status_desc'] . "\n";
				}
				$message = $mensaje. "\nNota:Solo paquetes con estatus:mensaje enviado o contactado pueden ser liberados.";
			}

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