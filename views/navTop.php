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
			<select name="option-location" id="option-location" class="form-control">
				<option value="1" <?php echo ($_SESSION['uLocation']==1) ? 'selected': ''; ?> >Tlaquiltenango</option>
				<option value="2" <?php echo ($_SESSION['uLocation']==2) ? 'selected': ''; ?> >Zacatepec</option>
			</select>
		</div>
	</div>
</div>
<hr>