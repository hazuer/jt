<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define( '_VALID_MOS', 1 );

session_start();
if(!isset($_SESSION["uActive"])){
	header('Location: '.BASE_URL);
	die();
}
require_once('../system/configuration.php');
require_once('../system/DB.php');
$db = new DB(HOST,USERNAME,PASSWD,DBNAME,PORT,SOCKET);

if(isset($_SESSION['uLocation'])){
	$_SESSION['uLocation'] = $_SESSION['uLocation'];
}else{
	$_SESSION['uLocation'] = $_SESSION['uLocationDefault'];
}
$id_location = $_SESSION['uLocation'];

$sql = "SELECT 
p.id_package,
p.tracking,
cc.phone,
p.id_location,
p.c_date,
p.folio,
p.d_validity,
CASE 
    WHEN DATEDIFF(NOW(), p.c_date) >= 5 THEN 'background-color: #FF9999;'
    WHEN DATEDIFF(NOW(), p.c_date) >= 3 THEN 'background-color: #FFFF99;'
    ELSE ''
END AS styleCtrlDays,
cc.contact_name receiver,
p.d_date,
p.d_user_id,
cs.id_status,
cs.status_desc,
p.note,
p.id_contact 
FROM package p 
INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
INNER JOIN cat_status cs ON cs.id_status=p.id_status 
WHERE 1 
AND p.id_location IN ($id_location)
AND p.id_status IN(1,2,6,7)";
$packages = $db->select($sql);

?>
<!doctype html>
<html lang = "en">
	<head>
		<?php include '../views/header.php'; ?>
		<script src="<?php echo BASE_URL;?>/assets/js/packages.js"></script>
		<script src="<?php echo BASE_URL;?>/assets/js/functions.js"></script>
		<script src="<?php echo BASE_URL;?>/assets/js/html5-qrcode.min.js"></script>
		<style>
		.dataTables_scrollBody
			{
				overflow-x:hidden !important;
				overflow-y:auto !important;
			}
			.label-info {
				padding: 0.2em 0.6em 0.3em;
				font-size: 15px;
				font-weight: 700;
				line-height: 1;
				color: #fff;
				text-align: center;
				white-space: nowrap;
				vertical-align: baseline;
				border-radius: 0.25em;
				color: white;
				background-color: #5bc0de;
			}

			#coincidencias {
				position: absolute;
				top: calc(100% + 7px); /* Posición debajo del campo #phone */
				left: 0;
				width: 100%;
				max-height: 200px; /* Altura máxima para evitar el desplazamiento */
				overflow-y: auto; /* Mostrar barra de desplazamiento vertical si es necesario */
				background-color: white; /* Color de fondo */
				border: 1px solid #ccc; /* Borde */
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra */
				z-index: 1000; /* Z-index para que se superponga a otros elementos */
			}

			#coincidencias p {
				padding: 10px;
				margin: 0;
				cursor: pointer; /* Cambiar el cursor al pasar sobre los elementos de la lista */
			}

			#coincidencias p:hover {
				background-color: #ADD8E6; /* Cambiar el color de fondo al pasar el cursor */
			}

		</style>
	</head>
	<body>
		<div class="main">
			<?php
				include '../views/navTop.php';
			?>

      		<?php if(empty($packages)): ?>
				<div class="alert alert-info" role="alert" style="text-align: center;">
					No hay paquetes en la ubicacion seleccionada, haz clik en el boton nuevo paquete <br>
					<button id="btn-first-package" type="button" class="btn-success btn-sm" title="Nuevo paquete">
						<i class="fa fa-cube" aria-hidden="true"></i>
					</button>
				</div
			<?php else: ?>
				<br />
				<form id="frm-package">
					<table id="tbl-packages" class="table table-striped table-bordered nowrap table-hover" cellspacing="0" style="width:100%">
						<thead>
							<tr>
								<th></th>
								<th>guia</th>
								<th>phone</th>
								<th>id_location</th>
								<th>c_date</th>
								<th>folio</th>
								<th>d_validity</th>
								<th>receiver</th>
								<th>d_date</th>
								<th>d_user_id</th>
								<th>id_status</th>
								<th>status_desc</th>
								<th>note</th>
								<th>id_contact</th>
								<th>Opc.</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($packages as $d): ?>
								<tr style="<?php echo $d['styleCtrlDays']; ?>">
								<td><?php echo $d['id_package']; ?></td>
								<td><?php echo $d['tracking']; ?></td>
								<td><?php echo $d['phone']; ?></td>
								<td><?php echo $d['id_location']; ?></td>
								<td><?php echo $d['c_date']; ?></td>
								<td><?php echo $d['folio']; ?></td>
								<td><?php echo $d['d_validity']; ?></td>
								<td><?php echo $d['receiver']; ?></td>
								<td><?php echo $d['d_date']; ?></td>
								<td><?php echo $d['d_user_id']; ?></td>
								<td><?php echo $d['id_status']; ?></td>
								<td><?php echo $d['status_desc']; ?></td>
								<td><?php echo $d['note']; ?></td>
								<td><?php echo $d['id_contact']; ?></td>
								<td style="text-align: center;">
									<div class="row">
										<div class="col-md-12">
											<button type="button" id='btn-records' class="btn-info btn-sm" title="Editar">
												<i class="fa fa-edit" aria-hidden="true"></i>
											</button>
										</div>
									</div>
								</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</form>
			<?php endif; ?>
		</div>

		<div class="modal fade" id="modal-package" tabindex="-1" role="dialog" aria-labelledby="modal-package-title" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-package-title"> </span></h3>
						<button id="close-qr-x" type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="form-modal-package" name="form-modal-package" class="form" enctype="multipart/form-data">
							<div class="form-group">
								<input type="hidden" name="id_package" id="id_package" value="" >
								<input type="hidden" name="folio" id="folio" value="" >
								<input type="hidden" name="id_contact" id="id_contact" value="" >
								<input type="hidden" name="action" id="action" value="" >
							</div>
							<div class="row">
								<div class="col-md-12" style="text-align: center;">
									<div id="qr-reader" style="width: 100%;"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="id_location">Ubicacion:</label>
										<select name="id_location" id="id_location" class="form-control" disabled>
										<option value="1">Tlaquiltenango</option>
										<option value="2">Zacatepec</option>
									</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="c_date">Fecha:</label>
										<input type="text" class="form-control" name="c_date" id="c_date" value="" disabled>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="phone">* Télefono:</label>
										<input type="text" class="form-control" name="phone" id="phone" value="" autocomplete="off" >
									</div>
									<div id="coincidencias" style="display: none;"></div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="receiver">* Nombre:</label>
										<input type="receiver" class="form-control" name="receiver" id="receiver" value="" autocomplete="off">
									</div>
								</div>
							</div>
							<div class="row" id="div-scan-tracking">
								<div class="col-md-2">
									<label for="btn-scan-code">* Scan:</label>
									<button id="btn-scan-code" type="button" class="btn-primary btn-sm" title="Iniciar Escaner">
									<i class="fa fa-camera" aria-hidden="true"></i>
									</button>
								</div>
								<div class="col-md-10">
									<div class="form-group">
										<label for="tracking">* Guía:</label>
										<input type="text" class="form-control" name="tracking" id="tracking" value="" autocomplete="off">
									</div>
								</div>
							</div>
							<div class="row" id="div-status">
								<div class="col-md-6">
									<div class="form-group">
										<label for="id_status">Status:</label>
										<select name="id_status" id="id_status" class="form-control">
											<option value="1">Nuevo</option>
											<option value="2">SMS Enviado</option>
											<option value="4">Devuelto</option>
											<option value="5">Eliminado</option>
											<option value="7">Contactado</option>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="note">Nota:</label>
										<input type="note" class="form-control" name="note" id="note" value="" autocomplete="off">
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button id="btn-erase" type="button" class="btn btn-default" title="Borrar">Borrar</button>
						<button id="btn-save" type="button" class="btn btn-success" title="Guardar">Guardar</button>
						<button id="close-qr-b" type="button" class="btn btn-danger" title="Cerrar" data-dismiss="modal">Cerrar</button>
						<audio id="beep-sound" style="display: none;">
								<source src="<?php echo BASE_URL;?>/assets/beep-sound.mp3" type="audio/mpeg">
						</audio>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-folio" tabindex="-1" role="dialog" aria-labelledby="modal-folio-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-folio-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="mfIdLocation">Ubicacion:</label>
									<select name="mfIdLocation" id="mfIdLocation" class="form-control" disabled>
										<option value="1">Tlaquiltenango</option>
										<option value="2">Zacatepec</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="mfModo">Modo:</label>
									<select name="mfModo" id="mfModo" class="form-control">
										<option value="1">Automatico</option>
										<option value="2">Personalizado</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
						<div class="col-md-6">
								<div class="form-group">
									<label for="mfFolioActual">Folio Actual:</label>
									<input type="text" class="form-control" name="mfFolioActual" id="mfFolioActual" value="" autocomplete="off" disabled>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="mfNumFolio">* Folio:</label>
									<input type="text" class="form-control" name="mfNumFolio" id="mfNumFolio" value="" autocomplete="off" >
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="btn-save-folio" type="button" class="btn btn-success" title="Guardar">Guardar</button>
						<button type="button" class="btn btn-danger" title="Cerrar" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-contacto" tabindex="-1" role="dialog" aria-labelledby="modal-contacto-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-contacto-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="mCIdLocation">Ubicacion:</label>
									<select name="mCIdLocation" id="mCIdLocation" class="form-control" disabled>
										<option value="1">Tlaquiltenango</option>
										<option value="2">Zacatepec</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="form-group">
										<label for="mCPhone">* Télefono:</label>
										<input type="text" class="form-control" name="mCPhone" id="mCPhone" value="" autocomplete="off" >
									</div>
								</div>
							</div>
						</div>
						<div class="row">
						<div class="col-md-6">
								<div class="form-group">
									<label for="mCName">* Nombre:</label>
									<input type="text" class="form-control" name="mCName" id="mCName" value="" autocomplete="off" >
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="mCContactType">Tipo:</label>
									<select name="mCContactType" id="mCContactType" class="form-control" >
										<option value="1">Sms</option>
										<option value="2">WhatsApp</option>
										<option value="3">Casa</option>
										<option value="4">Domicilio</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="mCEstatus">Estatus:</label>
									<select name="mCEstatus" id="mCEstatus" class="form-control" >
										<option value="1">Activo</option>
										<option value="2">Inactivo</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="btn-save-contacto" type="button" class="btn btn-success" title="Guardar">Guardar</button>
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-messages" tabindex="-1" role="dialog" aria-labelledby="modal-messages-title" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-messages-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label for="mMIdLocation">Ubicacion:</label>
									<select name="mMIdLocation" id="mMIdLocation" class="form-control" disabled>
										<option value="1">Tlaquiltenango</option>
										<option value="2">Zacatepec</option>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="mMContactType">Tipo:</label>
									<select name="mMContactType" id="mMContactType" class="form-control" disabled>
										<option value="1">Sms</option>
										<option value="2">WhatsApp</option>
										<option value="3">Casa</option>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="mMEstatus">Estatus del Paquete:</label>
									<select name="mMEstatus" id="mMEstatus" class="form-control" disabled>
											<option value="1">Nuevo</option>
									</select>
								</div>
							</div>

						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label for="mMMessage">Mensaje:</label>
									<textarea class="form-control" id="mMMessage" name="mMMessage" rows="2"></textarea>
								</div>
							</div>
						</div>
						<div class="row" style="overflow: auto; max-height: 250px; width: 100%;">
							<div class="col-md-12">
								<table class="table" id="tbl-list-sms">
									<thead>
									<tr>
										<th>#</th>
										<th>Télefono</th>
										<th>Nombre</th>
										<th>Paquetes</th>
										<th>Guías</th>
									</tr>
									</thead>
									<tbody id="tbl-listPackage">
									<!-- Las filas de la tabla se generarán aquí -->
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="btn-save-messages" type="button" class="btn btn-success" title="Enviar">Enviar</button>
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>


		<div class="modal fade" id="modal-release-package" tabindex="-1" role="dialog" aria-labelledby="modal-release-package-title" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-release-package-title"> </span></h3>
						<button id="close-mrp-x" type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="form-modal-release-package" name="form-modal-release-package" class="form" enctype="multipart/form-data">
							<div class="row">
								<div class="col-md-12" style="text-align: center;">
									<div id="code-reader" style="width: 100%;"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="mrp-id_location">Ubicacion:</label>
										<select name="mrp-id_location" id="mrp-id_location" class="form-control" disabled>
										<option value="1">Tlaquiltenango</option>
										<option value="2">Zacatepec</option>
									</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="mrp-date-release">Fecha:</label>
										<input type="text" class="form-control" name="mrp-date-release" id="mrp-date-release" value="" disabled>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-2">
									<label for="btn-mrp-scan">* Escaner:</label>
									<button id="btn-mrp-scan" type="button" class="btn-primary btn-sm" title="Iniciar Escaner">
									<i class="fa fa-camera" aria-hidden="true"></i>
									</button>
								</div>
								<div class="col-md-10">
									<div class="form-group">
										<label for="mrp-tracking">* Guía:</label>
										<input type="text" class="form-control" name="mrp-tracking" id="mrp-tracking" value="" autocomplete="off">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12" style="overflow: auto; max-height: 250px; width: 100%;">
								<table class="table" id="tablaPaquetes">
								<thead>
									<tr>
										<th>Guía</th>
										<th>Télefono</th>
										<th>Destinatario</th>
										<th>Folio</th>
									</tr>
								</thead>
								<tbody>
									<!-- Los datos se agregarán aquí mediante jQuery -->
								</tbody>
								</table>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button id="btn-mrp-save" type="button" class="btn btn-success" title="Liberar">Liberar</button>
						<button id="close-mrp-b" type="button" class="btn btn-danger" title="Cerrar" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>

	</body>
</html>