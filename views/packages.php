<?php
#error_reporting(E_ALL);
#ini_set('display_errors', '1');

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
CASE 
    WHEN DATEDIFF(NOW(), p.c_date) >= 3 THEN 'background-color: #FF9999;'
    WHEN DATEDIFF(NOW(), p.c_date) >= 2 THEN 'background-color: #FFFF99;'
    ELSE ''
END AS styleCtrlDays,
cc.contact_name receiver,
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
						<i class="fa fa-cube fa-lg" aria-hidden="true"></i>
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
								<th>receiver</th>
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
								<td><?php echo $d['receiver']; ?></td>
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

		<?php
		include('modal/folio.php');
		include('modal/contact.php');
		include('modal/package.php');
		include('modal/messages.php');
		include('modal/release.php');
		?>
	</body>
</html>