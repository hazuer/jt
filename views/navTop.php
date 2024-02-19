<br>
<div class="row">
	<div class="col-md-12">
		<div class="btn-group" role="group">
			<button id="logoff" type="button" class="btn-sm btn-light">
				<i class="fa fa-power-off" aria-hidden="true" style="color:#ffc107;"></i>
			</button>
			<button id="home" type="button" class="btn-sm btn-primary">
				<i class="fa fa-home" aria-hidden="true"></i>
			</button>
			<button id="btn-folio" type="button" class="btn-sm btn-info" title="Configurar folio">
				<i class="fa fa-hashtag" aria-hidden="true"></i>
			</button>
			<button id="btn-contacto" type="button" class="btn-sm btn-info" title="Contactos">
				<i class="fa fa-user" aria-hidden="true"></i>
			</button>
			<input style="width:100px;" type="text" class="form-control" value="<?php echo $_SESSION['uName']; ?>" value="" disabled="">
			<select name="option-location" id="option-location" class="form-control">
				<option value="1" <?php echo ($_SESSION['uLocation']==1) ? 'selected': ''; ?> >Tlaquiltenango</option>
				<option value="2" <?php echo ($_SESSION['uLocation']==2) ? 'selected': ''; ?> >Zacatepec</option>
			</select>
			<button id="btn-add-package" type="button" class="btn-success btn-sm" title="Nuevo paquete">
				<i class="fa fa-cube" aria-hidden="true"></i>
			</button>
			<button id="btn-send-messages" type="button" class="btn-sm btn-info" title="Enviar mensajes">
				<i class="fa fa-comment" aria-hidden="true"></i>
			</button>
			<button id="btn-release-package" type="button" class="btn-sm btn-info" title="Entrega de paquetes">
				<i class="fa fa-check" aria-hidden="true"></i>
			</button>
		</div>
	</div>
</div>
<hr>