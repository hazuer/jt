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

$sql="SELECT 
		cc.phone,
		(SELECT cct2.contact_name FROM cat_contact cct2 WHERE cct2.phone=cc.phone AND cct2.id_location IN($id_location) LIMIT 1) main_name,
		COUNT(p.tracking) AS total_p,
		GROUP_CONCAT(p.tracking) AS trackings,
		GROUP_CONCAT(p.folio) AS folios,
		GROUP_CONCAT(p.id_package) AS ids 
		FROM package p 
		INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
		INNER JOIN cat_contact_type cct ON cct.id_contact_type = cc.id_contact_type 
		WHERE 
		p.id_location IN ($id_location) 
		AND p.id_status IN (1,2,7) 
		AND cct.id_contact_type IN (1) 
		GROUP BY cc.phone,main_name
		HAVING total_p > 1
		ORDER BY total_p DESC";
$packages = $db->select($sql);
?>
<!doctype html>
<html lang = "en">
	<head>
		<?php include '../views/header.php'; ?>
		<script src="<?php echo BASE_URL;?>/assets/js/inspect.js"></script>
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
			<h3>Usuarios y Paquetes Pendientes de Entrega</h3>
			<table id="tbl-inspect" class="table table-striped table-bordered nowrap table-hover" cellspacing="0" style="width:100%">
				<thead>
					<tr>
						<th>phone</th>
						<th>main_name</th>
						<th>total_p</th>
						<th>folios</th>
						<th>trackings</th>
						<th>ids</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($packages as $d): ?>
						<tr>
						<td><?php echo $d['phone']; ?></td>
						<td><?php echo $d['main_name']; ?></td>
						<td>
							<span class="badge badge-pill badge-info btn-pull-realise" style="cursor: pointer;" title="Liberar Paquetes" data-tpaquetes="<?php echo $d['total_p']; ?>" data-tphone="<?php echo $d['phone']; ?>" data-tname="<?php echo $d['main_name']; ?>" data-tids="<?php echo $d['ids']; ?>">
								Liberar <?php echo $d['total_p']; ?> Paquetes
							</span>
						</td>
						<td><?php echo $d['folios']; ?></td>
						<td><?php echo $d['trackings']; ?></td>
						<td><?php echo $d['ids']; ?></td>
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