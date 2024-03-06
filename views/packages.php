<?php
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


/*CASE 
    WHEN DAYOFWEEK(p.c_date) = 6 AND DATEDIFF(NOW(), p.c_date) >= 5 THEN '5 dias FDS'
    WHEN DAYOFWEEK(p.c_date) = 6 AND DATEDIFF(NOW(), p.c_date) = 4 THEN '3 dias FDS'
    WHEN DAYOFWEEK(p.c_date) != 6 AND DATEDIFF(NOW(), p.c_date) = 3 THEN '3 dias'
    WHEN DAYOFWEEK(p.c_date) != 6 AND DATEDIFF(NOW(), p.c_date) = 2 THEN '2 dias'
    ELSE DATEDIFF(NOW(), p.c_date)
END AS diasTrans,*/

$sql = "SELECT 
p.id_package,
p.tracking,
cc.phone,
p.id_location,
p.c_date,
p.folio,
CASE 
	WHEN DAYOFWEEK(p.c_date) = 6 THEN IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 3,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=4,
			'background-color: #FFFF99;',
			'background-color: #FF9999;')
	) 
	WHEN DAYOFWEEK(p.c_date) = 7 THEN IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 2,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=3,
			'background-color: #FFFF99;',
			'background-color: #FF9999;')
	) 
	WHEN DAYOFWEEK(p.c_date) = 1 THEN IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 1,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=2,
			'background-color: #FFFF99;',
			'background-color: #FF9999;')
	) 
	ELSE IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 1,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=2,
			'background-color: #FFFF99;',
			'background-color: #FF9999;')
	) 
END AS styleCtrlDays,
CASE 
	WHEN DAYOFWEEK(p.c_date) = 6 THEN IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 3,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=4,
			'2DT',
			'3DT')
	) 
	WHEN DAYOFWEEK(p.c_date) = 7 THEN IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 2,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=3,
			'2DT',
			'3DT')
	) 
	WHEN DAYOFWEEK(p.c_date) = 1 THEN IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 1,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=2,
			'2DT',
			'3DT')
	) 
	ELSE IF(DATEDIFF(NOW(), p.c_date) BETWEEN 0 AND 1,
		'',
		IF(DATEDIFF(NOW(), p.c_date)=2,
			'2DT',
			'3DT')
	) 
END AS diasTrans,
cc.contact_name receiver,
cs.id_status,
cs.status_desc,
p.note,
IF(p.n_date is null,'', CONCAT('el ',p.n_date)) n_date,
p.id_contact 
FROM package p 
LEFT JOIN cat_contact cc ON cc.id_contact=p.id_contact 
LEFT JOIN cat_status cs ON cs.id_status=p.id_status 
WHERE 1 
AND p.id_location IN ($id_location)
AND p.id_status IN(1,2,6,7)";
$packages = $db->select($sql);

$sqlTemp ="SELECT template FROM cat_template WHERE id_location IN ($id_location) LIMIT 1";
$user = $db->select($sqlTemp);
$templateMsj=$user[0]['template']

?>
<!doctype html>
<html lang = "en">
	<head>
		<?php include '../views/header.php'; ?>
		<script>
    	let templateMsj =`<?php echo $templateMsj;?>`;
		</script>
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
				<form id="frm-package">
				<h3>Paquetes</h3>
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
								<th>Editar</th>
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
								<td><?php echo $d['diasTrans']; ?> <?php echo $d['status_desc']; ?> <?php echo $d['n_date']; ?></td>
								<td><?php echo $d['note']; ?></td>
								<td><?php echo $d['id_contact']; ?></td>
								<td style="text-align: center;">
									<div class="row">
										<div class="col-md-12">
											<span class="badge badge-pill badge-info" style="cursor: pointer;" id="btn-records" title="Editar">
												<i class="fa fa-edit fa-lg" aria-hidden="true"></i>
											</span>
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
		include('modal/template.php');
		include('modal/package.php');
		include('modal/messages.php');
		include('modal/release.php');
		?>
	</body>
</html>