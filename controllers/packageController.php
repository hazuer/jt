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
				$statusPackage = 6; //Error al enviar mensaje
			}
		} catch (Exception $e) {
			$data['sid']   = $e->getMessage();
			$statusPackage = 6; //Error al enviar mensaje
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
		$messagebot    = $_POST['messagebot'];
		$plb  = $_POST['phonelistbot'];
		
		$lineas = explode("\n", $plb);

		// Iterar sobre cada línea y limpiarla (eliminar espacios y comillas)
		$numeros_de_telefono = [];
		foreach ($lineas as $linea) {
			$numero = trim(str_replace('"', '', $linea));
			if (!empty($numero)) {
				$numeros_de_telefono[] = '"' . $numero . '"';
			}
		}

		// Unir los números de teléfono en un solo string con comas
		$phonelistbot = implode(",", $numeros_de_telefono);

		$nameFile = "chat_bot";
		$jsfile_content = 'const qrcode = require("qrcode-terminal");
const moment = require("moment-timezone");
const { Client } = require("whatsapp-web.js");
const Database = require("./database.js")
const client = new Client();
client.on("qr", (qr) => {
	qrcode.generate(qr, { small: true });
});
client.on("ready", async () => {
	console.log("Client is ready!");
	let iconBot= `🤖 `;
	let db = new Database("false")
	const id_location = '.$id_location.';
	const n_user_id='.$_SESSION["uId"].'
	const numbers = ['.$phonelistbot.'];
	const message = `'.$messagebot.'`;

	let ids =  0;
	let folios = 0;
	for (let i = 0; i < numbers.length; i++) {
		const number = numbers[i];
		const sql =`SELECT 
		cc.phone,
		GROUP_CONCAT(p.id_package) AS ids,
		GROUP_CONCAT(p.folio) AS folios 
		FROM package p 
		INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
		WHERE 
		p.id_location IN (${id_location}) 
		AND p.id_status IN (1) 
		AND cc.phone IN(${number})
		GROUP BY cc.phone`
		const data = await db.processDBQueryUsingPool(sql)
		const rst = JSON.parse(JSON.stringify(data))
		ids = rst[0] ? rst[0].ids : 0;
		folios = rst[0] ? rst[0].folios : 0;
		let fullMessage = `${iconBot} ${message}`;
		if(ids!=0){
			fullMessage = `${iconBot} ${message} \n*Folio(s) control interno: ${folios}*`;
		}

		let sid ="";
		let statusPackage = 1;
		try {
			const number_details = await client.getNumberId(number); // get mobile number details
			if (number_details) {
				await client.sendMessage(number_details._serialized, fullMessage); // send message
				sid =`Mensaje enviado con éxito a, ${number}`
				statusPackage = 2
			} else {
				sid = `${number}, Número de móvil no registrado`
				statusPackage = 6
			}
			if (i < numbers.length - 1) {
				await sleep(2000); // tiempo de espera en segundos entre cada envío
			}
		} catch (error) {
			sid =`Ocurrió un error al procesar el número, ${number}`
			statusPackage = 6
		}
		console.log(sid);
		if(ids!=0){
			const listIds = ids.split(",");
			const nDate = moment().tz("America/Mexico_City").format("YYYY-MM-DD HH:mm:ss");
			for (let i = 0; i < listIds.length; i++) {
				const id_package = listIds[i];
				const sqlSaveNotification = `INSERT INTO notification 
				(id_location,n_date,n_user_id,message,id_contact_type,sid,id_package) 
				VALUES 
				(${id_location},\'${nDate}\',${n_user_id},\'${message} \n*Folio(s) control interno: ${folios}*\',2,\'${sid}\',${id_package})`
				await db.processDBQueryUsingPool(sqlSaveNotification)

				const sqlUpdatePackage = `UPDATE package SET 
				n_date = \'${nDate}\', n_user_id = \'${n_user_id}\', id_status=${statusPackage} 
				WHERE id_package IN (${id_package})`
				await db.processDBQueryUsingPool(sqlUpdatePackage)
			}
		}
	}
	console.log("Proceso finalizado...");
});
client.initialize();
function sleep(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}';
		$init = array(
			"nameFile" => $nameFile,
		);
		require_once('../nodejs/NodeJs.php');
		$nodeFile = new NodeJs($init);
		$path_file = NODE_PATH_FILE;
		$nodeFile->createContentFileJs($path_file, $jsfile_content);
		//$nodeFile->getContentFile(true); # true:continue
		$nodeJsPath = $nodeFile->getFullPathFile();

		//handler emergency

		$nombreArchivo = '../views/modal/handler.php';
	$contenidoHTML='<div class="col-md-6">
	<div class="form-group">
		<div class="form-group">
		<textarea class="form-control" id="msjbt" name="msjbt" rows="4" readonly="">'.$messagebot.'</textarea>
		</div>
	</div>
</div>
<div class="col-md-6">
	<div class="form-group">
		<div class="form-group">
		<input type="hidden" class="form-control" name="idlocbt" id="idlocbt" value="'.$id_location.'" autocomplete="off" >
		</div>
	</div>
</div>
<div class="col-md-6">
	<div class="form-group">
		<div class="form-group">
		<input type="hidden" class="form-control" name="uidbt" id="uidbt" value="'.$_SESSION["uId"].'" autocomplete="off" >
		</div>
	</div>
</div>';
		foreach ($lineas as $telefono) {
			$contenidoHTML .="<a href='#' class='mensaje'  data-phone='$telefono'>Enviar mensaje a $telefono</a> <br>";
		}

		// Intenta abrir el archivo para escritura
		if ($archivo = fopen($nombreArchivo, 'w')) {
			// Escribe el contenido en el archivo
			fwrite($archivo, $contenidoHTML);
			// Cierra el archivo
			fclose($archivo);
			#echo "El archivo $nombreArchivo ha sido creado con éxito.";
		} else {
			#echo "No se pudo crear el archivo $nombreArchivo.";
		}

		$result = [
			'success'  => true,
			'dataJson' => $nodeJsPath,
			'message'  => 'Chatbot creado .!'
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

		case 'mensajeManual':
			$result   = [];
			$success  = 'false';
			$dataJson = [];
			$message  = 'Error envio de mensaje';
			$id_location   = $_POST['id_location'];
			$uidbt    = $_POST['uidbt'];
			$msjbt    = $_POST['msjbt'];
			$telefono = $_POST['telefono'];
			try {
				$sql="SELECT 
				cc.phone,
				GROUP_CONCAT(p.id_package) AS ids,
				GROUP_CONCAT(p.folio) AS folios 
				FROM package p 
				INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
				WHERE 
				p.id_location IN ($id_location) 
				AND p.id_status IN (1) 
				AND cc.phone IN ($telefono)
				GROUP BY cc.phone";
				$rst = $db->select($sql);
				$exist = count($rst);
				$txtFolios='';
				if($exist!=0){
					$success="true";
					$ids = $rst[0]['ids'];
					$folios = $rst[0]['folios'];
					$txtFolios="\n*Folio(s) control interno: $folios*";
					$fullMesage= $msjbt." ".$txtFolios;

					$listIds = explode(",", $ids);
					foreach ($listIds as $id_package) {
						$sid ="Mensaje enviado con éxito a, $telefono";
						$nDate = date("Y-m-d H:i:s");
						$data['id_location']  = $id_location;
						$data['n_date']      = $nDate;
						$data['n_user_id']   = $uidbt;
						$data['message']  = $fullMesage;
						$data['id_contact_type']  = 2;
						$data['sid']  = $sid;
						$data['id_package']  = $id_package;
						$db->insert('notification',$data);

						$upData['n_date']    = $nDate;
						$upData['n_user_id'] = $uidbt;
						$upData['id_status'] = 2;
						$db->update('package',$upData," `id_package` IN($id_package)");
					}

				}

				$result = [
					'success'  => $success,
					'dataJson' => [],
					'message'  => $txtFolios
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