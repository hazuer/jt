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

$id       = $_GET['id'];

$eventUser = $db->select("SELECT * FROM package");
$totalUsers = $eventIfo[0]['total_users'];
$userActives = array_count_values(array_column($eventUser, 'status'))[1];

$placeAvailable = $totalUsers - $userActives;

?>
<!doctype html>
<html lang = "en">
	<head>
		<?php include '../views/header.php'; ?>
		<?php 
		#include '../views/dt.php'; 
		?>
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
		</style>
	</head>
	<body>
		<div class="main">

			<?php
				$titleHead = "demo";
				include '../views/navTop.php';
			?>
			<div class="row">
				<input type="hidden" name="id_event" id="id_event" value="<?php echo $id;?>" >
				<input type="hidden" name="place_available" id="place_available" value="<?php echo $placeAvailable;?>" >
				<input type="hidden" name="userActives" id="userActives" value="<?php echo $userActives;?>" >
				<div class="col-md-12"><label><?php echo $titleHead;?></label></div>
			</div>

			<div class="row">
				<div class="col-md-12 row justify-content-end">
					<div class="btn-group" role="group" aria-label="Basic example">
					<?php if ($placeAvailable>=1) {	?>
						<button id="btn-add-user" type="button" class="btn-success btn-sm" title="Add User">
							<i class="fa fa-user-plus" aria-hidden="true"></i>
						</button>
						<button id="btn-load-csv" type="button" class="btn-secondary btn-sm" title="Load CSV">
							<i class="fa fa-file" aria-hidden="true"></i>
						</button>
					<?php } ?>
					<?php
					if(!empty($eventUser)){
					?>
						<button id="btn-scan-qr" type="button" class="btn-primary btn-sm" title="Scan QR">
							<i class="fa fa-camera" aria-hidden="true"></i>
						</button>
						<button id="btn-check-list" type="button" class="btn-warning btn-sm" title="Check List">
							<i class="fa fa-check-square" aria-hidden="true" style="color:white;"></i>
						</button>
					<?php }?>
					</div>
				</div>
			</div>

      		<?php if(empty($eventUser)): ?>
				<div class="alert alert-info" role="alert" style="text-align: center;">
				There are no registered users in the event, click the button to add a new user <br>
					<button id="btn-first-user" type="button" class="btn-success btn-sm" title="Add User">
						<i class="fa fa-user-plus" aria-hidden="true"></i>
					</button>
					<button id="btn-first-csv" type="button" class="btn-secondary btn-sm" title="Load CSV">
						<i class="fa fa-file" aria-hidden="true"></i>
					</button>
				</div
			<?php else: ?>
				<br />
				<form id="frm-event">
					<table id="tbl-event-users" class="table table-striped table-bordered nowrap table-hover" cellspacing="0" style="width:100%">
						<thead>
							<tr>
								<th></th>
								<th>id_package</th>
								<th>phone</th>
								<th>tracking</th>
								<th>receiver</th>
								<th>status</th>
								<th>phone</th>
								<th>status</th>
								<th>qr_path</th>
								<th>Opc.</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($eventUser as $d): ?>
								<tr>
								<td><?php echo $d['id_package']; ?></td>
								<td><?php echo $d['phone']; ?></td>
								<td><?php echo $d['tracking']; ?></td>
								<td id="btn-edit-user" title="Click to edit">
									<?php echo $d['receiver']; ?>
								</td>
								<td><?php echo $d['status']; ?></td>
								<td>.</td>
								<td>.</td>
								<td>.</td>
								<td>.</td>
								<td style="text-align: center;">
									<div class="row">
										<div class="col-md-6">
											<button type="button" id='btn-records' class="btn-light btn-sm" title="Records">
												<i class="fa fa-eye" aria-hidden="true"></i>
											</button>
										</div>
										<div class="col-md-6">
											<button type="button" id='btn-show-qr' class="btn-light btn-sm" title="QR">
												<i class="fa fa-qrcode" aria-hidden="true"></i>
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

		<div class="modal fade" id="modal-user" tabindex="-1" role="dialog" aria-labelledby="modal-user-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-user-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="form-modal-user" name="form-modal-user" class="form" enctype="multipart/form-data">
							<div class="form-group">
								<input type="hidden" name="id_event_user" id="id_event_user" value="" >
								<input type="hidden" name="action" id="action" value="" >
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="title">Type:</label>
										<select name="type_ibo" id="type_ibo" class="form-control">
											<option value="1">IBO</option>
											<option value="2">Guest</option>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="title" id="lbl-desc-ibo">* IBO Number:</label>
										<input type="text" class="form-control" name="ibo" id="ibo" value="" autocomplete="off">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="title">* Name:</label>
										<input type="name" class="form-control" name="name" id="name" value="" autocomplete="off">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="title">* Email:</label>
										<input type="email" class="form-control" name="email" id="email" value="" autocomplete="off">
									</div>
								</div>
							</div>
							<div class="row">
							<div class="col-md-6">
									<div class="form-group">
										<label for="title">* Phone:</label>
										<input type="number" class="form-control" name="phone" id="phone" value="" autocomplete="off" minlength="10" maxlength="10" size="10">
									</div>
								</div>
								<div class="col-md-6" id="div-status-user">
									<div class="form-group">
										<label for="title">Status:</label>
										<select name="status" id="status" class="form-control">
											<option value="1">Active</option>
											<option value="2">Deleted</option>
										</select>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button id="btn-resend" type="button" class="btn btn-success" title="Just forward sms and mail">
							<i class="fa fa-paper-plane" aria-hidden="true"></i> Send
						</button>
						<button id="btn-save-user" type="button" class="btn btn-success" title="Save">
							<i class="fa fa-paper-plane" aria-hidden="true"></i> Save and Send
						</button>
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Close</button>
						<audio id="beep-sound" style="display: none;">
								<source src="<?php echo BASE_URL;?>/assets/beep-sound.mp3" type="audio/mpeg">
						</audio>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-scan-qr" tabindex="-1" role="dialog" aria-labelledby="modal-scan-qr-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-scan-qr-title"> </span></h3>
						<button id="close-qr-x" type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12" style="text-align: center;">
								<div id="qr-reader" style="width: 100%;"></div>
							</div>
						</div>
						<div class="row">
						<div class="col-md-1"></div>
							<div class="col-md-10">
								<div id="div-rst-scan-qr"></div>
							</div>
							<div class="col-md-1"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="close-qr-b" type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-show-qr" tabindex="-1" role="dialog" aria-labelledby="modal-show-qr-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-show-qr-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-8" style="text-align: center;">
								<div id="div-show-qr"></div>
							</div>
							<div class="col-md-2"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-records" tabindex="-1" role="dialog" aria-labelledby="modal-records-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-records-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="tbl-div-records"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-csv" tabindex="-1" role="dialog" aria-labelledby="modal-csv-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-csv-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="form-modal-csv" name="form-modal-csv" class="form" enctype="multipart/form-data">
							<div class="row">
								<div class="col-md-12">
										<label id="lbl-max-user"></label>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<div style="position:relative;">
											<a class='btn btn-primary' href='javascript:;' style="width: 290px;">
												Choose File...[csv][max size <?php echo MAX_LOAD_DESC; ?>]
												<input type="file"
												style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;'
												id="file-csv"
												name="file-csv"
												size="10"
												onchange='$("#upload-file-csv").html($(this).val());$("#upload-file-csv").show()'>
											</a>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<span class="label label-info" id="upload-file-csv"></span>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button id="btn-save-csv" type="button" class="btn btn-success" title="Save">Save</button>
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="modal-check" tabindex="-1" role="dialog" aria-labelledby="modal-check-title" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title"><span id="modal-check-title"> </span></h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="tbl-div-check"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" title="Close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>