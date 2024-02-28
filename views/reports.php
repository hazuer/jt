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

$rFstatus = $_POST['rFstatus'] ?? null;
$rFIni    = $_POST['rFIni'] ?? null;
$rFFin    = $_POST['rFFin'] ?? null;

$andStatusIn =" AND p.id_status IN (1,2,3,4,5,6,7)";
if(isset($rFstatus)){
	if($rFstatus!='99'){
		$andStatusIn = " AND p.id_status IN ($rFstatus)";
	}
}

$andFechas = "";
if(!empty($rFIni) && !empty($rFFin)){
	$andFechas = " AND p.c_date BETWEEN '$rFIni 00:00:00' AND '$rFFin 23:59:59'";
}

$sql = "SELECT 
p.id_package,
cl.location_desc,
p.c_date,
uc.user registro,
p.tracking,
cc.phone,
p.folio,
cc.contact_name receiver,
cs.status_desc,
p.n_date,
un.user sms_by_user,
(SELECT count(n.id_notification) FROM notification n WHERE n.id_package in(p.id_package)) t_sms_sent,
p.d_date,
ud.user user_libera,
p.note 
FROM package p 
INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
INNER JOIN cat_status cs ON cs.id_status=p.id_status 
INNER JOIN users uc ON uc.id = p.c_user_id 
INNER JOIN cat_location cl ON cl.id_location = p.id_location 
LEFT JOIN users un ON un.id = p.n_user_id 
LEFT JOIN users ud ON ud.id = p.d_user_id 
WHERE 1 
AND p.id_location IN ($id_location) 
$andStatusIn 
$andFechas 
ORDER BY p.id_package DESC";
$packages = $db->select($sql);
?>
<!doctype html>
<html lang = "en">
	<head>
		<?php include '../views/header.php'; ?>
		<script src="<?php echo BASE_URL;?>/assets/js/reports.js"></script>
		<script src="<?php echo BASE_URL;?>/assets/js/functions.js"></script>
		<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet">
		<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
	</head>
	<body>
		<div class="main">
			<?php
				include '../views/navTop.php';
			?>
			<h3>Reportes</h3>
			<form id="frm-reports" action="<?php echo BASE_URL;?>/views/reports.php" method="POST">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label for="rFstatus"><b>Estatus:</b></label>
							<select name="rFstatus" id="rFstatus" class="form-control">
								<option value="99" <?php echo ($rFstatus==99) ? 'selected': ''; ?>>Todos</option>
								<option value="1" <?php echo ($rFstatus==1) ? 'selected': ''; ?>>Nuevo</option>
								<option value="2" <?php echo ($rFstatus==2) ? 'selected': ''; ?>>SMS Enviado</option>
								<option value="3" <?php echo ($rFstatus==3) ? 'selected': ''; ?>>Entregado</option>
								<option value="4" <?php echo ($rFstatus==4) ? 'selected': ''; ?>>Devuelto</option>
								<option value="5" <?php echo ($rFstatus==5) ? 'selected': ''; ?>>Deleted</option>
								<option value="6" <?php echo ($rFstatus==6) ? 'selected': ''; ?>>Error al enviar SMS</option>
								<option value="7" <?php echo ($rFstatus==7) ? 'selected': ''; ?>>Contactado</option>
							</select>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label for="rFIni"><b>* Fecha Inicio:</b></label>
							<input type="date" class="form-control" name="rFIni" id="rFIni" value="<?php echo $rFIni; ?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label for="rFFin"><b>* Fecha Fin:</b></label>
							<input type="date" class="form-control" name="rFFin" id="rFFin" value="<?php echo $rFFin; ?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<button id="btn-filter-rep" type="submit" class="btn btn-success" title="Filtrar" data-dismiss="modal">Filtrar</button>
						</div>
					</div>
				</div>
			</form>
			<hr>
			<table id="tbl-reports" class="table table-striped table-bordered nowrap table-hover" cellspacing="0" style="width:100%">
				<thead>
					<tr>
						<th>id_package</th>
						<th>location_desc</th>
						<th>fecha_registro</th>
						<th>registrado_por</th>
						<th>guia</th>
						<th>folio</th>
						<th>phone</th>
						<th>receiver</th>
						<th>status_desc</th>
						<th>fecha_envio_sms</th>
						<th>sms_enviado_por</th>
						<th>total_sms</th>
						<th>fecha_liberacion</th>
						<th>libero</th>
						<th>note</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($packages as $d): ?>
						<tr>
						<td><?php echo $d['id_package']; ?></td>
						<td><?php echo $d['location_desc']; ?></td>
						<td><?php echo $d['c_date']; ?></td>
						<td><?php echo $d['registro']; ?></td>
						<td><?php echo $d['tracking']; ?></td>
						<td><?php echo $d['folio']; ?></td>
						<td><?php echo $d['phone']; ?></td>
						<td><?php echo $d['receiver']; ?></td>
						<td><?php echo $d['status_desc']; ?></td>
						<td><?php echo $d['n_date']; ?></td>
						<td><?php echo $d['sms_by_user']; ?></td>
						<td><button type="button" id='btn-details' class="btn-info btn-sm" title="Ver">
							<i class="fa fa-eye" aria-hidden="true"> <?php echo $d['t_sms_sent']; ?></i>
							</button>
						</td>
						<td><?php echo $d['d_date']; ?></td>
						<td><?php echo $d['user_libera']; ?></td>
						<td><?php echo $d['note']; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
		include('modal/sms-report.php');
		?>
	</body>
</html>