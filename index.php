<?php
session_start();
define( '_VALID_MOS', 1 );

if(isset($_SESSION["uActive"])){
	header('Location: views/packages.php');
	die();
}
require_once('system/configuration.php');
?>

<!doctype html>
<html lang = "en">
	<head>
		<?php include 'views/header.php'; ?>
		<link href="<?php echo BASE_URL;?>/assets/css/index.css" rel="stylesheet">
		<script src="<?php echo BASE_URL;?>/assets/js/index.js"></script>
		<script src="<?php echo BASE_URL;?>/assets/js/functions.js"></script>
	</head>
	<body>
	<section class="vh-100">
		<div class="container py-5 h-100">
			<div class="row d-flex justify-content-center align-items-center h-100">
				<div class="col-12 col-md-8 col-lg-6 col-xl-5">
					<div class="card bg-white text-dark" style="border-radius: 1rem;">
						<div class="card-body p-5 text-center">
							<div class="mb-md-5 mt-md-4 pb-5">
								<div class="form-outline mb-4">
									<img id="profile-img" class="profile-img-card" src="<?php echo BASE_URL;?>/assets/img/logo.png" title="JT"/>
								</div>
								<h4 class="fw-bold mb-2 text-uppercase">J&T Express</h4>
								<p class="text-dark-50 mb-5">Ingresa tu usuario y contraseña</p>
								<div class="form-outline form-dark mb-4">
									<input type="text" name="username" id="username" title="Usuario" placeholder="*Usuario" autofocus autocomplete="off" value="" class="form-control form-control-lg" />
								</div>
								<div class="form-outline form-dark mb-4">
									<input type="password" name="password" id="password" title="Contraseña" placeholder="*Contraseña" value="" class="form-control form-control-lg" />
								</div>
								<div class="form-outline form-dark mb-4">
								<button name="btn-login" id="btn-login" class="btn btn-outline-success btn-lg px-5" type="submit">Ingresar</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	</body>
</html>