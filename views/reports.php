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
cc.contact_name receiver,
cs.status_desc,
p.note 
FROM package p 
INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
INNER JOIN cat_status cs ON cs.id_status=p.id_status 
WHERE 1 
AND p.id_location IN ($id_location)
ORDER BY p.id_package DESC";
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
		</style>
	</head>
	<body>
		<div class="main">
			<?php
				include '../views/navTop.php';
			?>
			<form id="frm-package">
				<h3>Reportes</h3>
				<table id="tbl-reports" class="table table-striped table-bordered nowrap table-hover" cellspacing="0" style="width:100%">
					<thead>
						<tr>
							<th>id_package</th>
							<th>guia</th>
							<th>phone</th>
							<th>id_location</th>
							<th>c_date</th>
							<th>folio</th>
							<th>receiver</th>
							<th>status_desc</th>
							<th>note</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($packages as $d): ?>
							<tr>
							<td><?php echo $d['id_package']; ?></td>
							<td><?php echo $d['tracking']; ?></td>
							<td><?php echo $d['phone']; ?></td>
							<td><?php echo $d['id_location']; ?></td>
							<td><?php echo $d['c_date']; ?></td>
							<td><?php echo $d['folio']; ?></td>
							<td><?php echo $d['receiver']; ?></td>
							<td><?php echo $d['status_desc']; ?></td>
							<td><?php echo $d['note']; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</form>
		</div>
	</body>
</html>