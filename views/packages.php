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
p.phone,
p.id_location,
p.c_date,
p.folio,
p.code,
p.receiver,
p.d_date,
p.d_user_id,
ce.id_status,
ce.status_desc 
FROM package p 
INNER JOIN cat_status ce ON ce.id_status=p.id_status 
WHERE 1 
AND p.id_location IN ($id_location)";
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
				top: calc(100% + 10px); /* Posición debajo del campo #phone */
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
				background-color: #f0f0f0; /* Cambiar el color de fondo al pasar el cursor */
			}

		</style>
	</head>
	<body>
		<div class="main">
			<?php
				include '../views/navTop.php';
			?>
			<div class="row">
				<div class="col-md-12 row justify-content-end">
					<div class="btn-group" role="group">
						<button id="btn-add-package" type="button" class="btn-success btn-sm" title="Nuevo paquete">
							<i class="fa fa-cube" aria-hidden="true"></i>
						</button>
					</div>
				</div>
			</div>

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
								<th>tracking</th>
								<th>phone</th>
								<th>id_location</th>
								<th>c_date</th>
								<th>folio</th>
								<th>code</th>
								<th>receiver</th>
								<th>d_date</th>
								<th>d_user_id</th>
								<th>id_status</th>
								<th>status_desc</th>
								<th>Opc.</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($packages as $d): ?>
								<tr>
								<td><?php echo $d['id_package']; ?></td>
								<td id="btn-edit-package" title="Click para editar"><?php echo $d['tracking']; ?></td>
								<td><?php echo $d['phone']; ?></td>
								<td><?php echo $d['id_location']; ?></td>
								<td><?php echo $d['c_date']; ?></td>
								<td><?php echo $d['folio']; ?></td>
								<td><?php echo $d['code']; ?></td>
								<td><?php echo $d['receiver']; ?></td>
								<td><?php echo $d['d_date']; ?></td>
								<td><?php echo $d['d_user_id']; ?></td>
								<td><?php echo $d['id_status']; ?></td>
								<td><?php echo $d['status_desc']; ?></td>
								<td style="text-align: center;">
									<div class="row">
										<div class="col-md-12">
											<button type="button" id='btn-records' class="btn-light btn-sm" title="Records">
												<i class="fa fa-eye" aria-hidden="true"></i>
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
			<div class="modal-dialog" role="document">
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
										<label for="phone">* Telefono:</label>
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
									<button id="btn-scan-code" type="button" class="btn-primary btn-sm" title="Scan Code">
									<i class="fa fa-camera" aria-hidden="true"></i>
									</button>
								</div>
								<div class="col-md-10">
									<div class="form-group">
										<label for="tracking">* Tracking:</label>
										<input type="text" class="form-control" name="tracking" id="tracking" value="" autocomplete="off">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6" id="div-status">
									<div class="form-group">
										<label for="id_status">Status:</label>
										<select name="id_status" id="id_status" class="form-control">
											<option value="1">Nuevo</option>
											<option value="2">En Proceso (SMS)</option>
											<option value="3">Entregado</option>
											<option value="4">Devuelto</option>
											<option value="5">Eliminado</option>
										</select>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
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
						<button id="close-qr-x" type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
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
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>