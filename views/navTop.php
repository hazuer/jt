<br>
<div class="row">
	<div class="col-md-12">
		<div class="btn-group" role="group">
			<button id="logoff" type="button" class="btn-sm btn-light">
				<i class="fa fa-power-off fa-lg" aria-hidden="true" style="color:#ffc107;"></i>
			</button>
			<button id="home" type="button" class="btn-sm btn-primary">
				<i class="fa fa-home fa-lg" aria-hidden="true"></i>
			</button>
			<button id="btn-folio" type="button" class="btn-sm btn-success" title="Configurar folio">
				<i class="fa fa-hashtag fa-lg" aria-hidden="true"></i>
			</button>
			<button id="btn-contacto" type="button" class="btn-sm btn-success" title="Agregar contacto">
				<i class="fa fa-user fa-lg" aria-hidden="true"></i>
			</button>
			<input style="width:100px;" type="text" class="form-control" value="<?php echo $_SESSION['uName']; ?>" value="" disabled="">
			<select name="option-location" id="option-location" class="form-control">
				<option value="1" <?php echo ($_SESSION['uLocation']==1) ? 'selected': ''; ?> >Tlaquiltenango</option>
				<option value="2" <?php echo ($_SESSION['uLocation']==2) ? 'selected': ''; ?> >Zacatepec</option>
			</select>
			<button id="btn-add-package" type="button" class="btn-success btn-sm" title="Nuevo paquete">
				<i class="fa fa-cube fa-lg" aria-hidden="true"></i>
			</button>
			<button id="btn-send-messages" type="button" class="btn-sm btn-success" title="Enviar mensajes">
				<i class="fa fa-comments-o fa-lg" aria-hidden="true"></i>
			</button>
			<button id="btn-release-package" type="button" class="btn-sm btn-success" title="Entrega de paquetes">
				<i class="fa fa-check-square-o fa-lg" aria-hidden="true"></i>
			</button>
			<button id="btn-report" type="button" class="btn-sm btn-success" title="Reportes">
			<i class="fa fa-bar-chart fa-lg" aria-hidden="true"></i>
			</button>
		</div>
	</div>
</div>
<hr>