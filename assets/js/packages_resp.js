$(document).ready(function() {

	let id_event           = $('#id_event');
	let id_event_user      = $('#id_event_user');
	let action             = $('#action');
	let type_ibo           = $('#type_ibo');
	let ibo                = $('#ibo');
	let email              = $('#email');
	let name                = $('#name');
	let phone              = $('#phone');
	let status             = $('#status');
	let place_available    = $('#place_available');
	let html5QrcodeScanner = '';
	let lastResult         = 0;
	let divStatusUser =$('#div-status-user');
	let qrScaned = '';

  	let table = $('#tbl-event-users').DataTable({
		"bPaginate": false,
		//"bFilter": false,
		"bInfo" : false,
		"scrollX": false,
		"scrollY": '500px',
        "scrollCollapse": true,
		"columns" : [
			{title: `id_package`,  name : `id_package`,  data : `id_package`}, //0
			{title: `Tracking`,    name : `tracking`,    data : `tracking`},   //1
			{title: `Phone`,       name : `phone`,       data : `phone`},      //2
			{title: `id_location`, name : `id_location`, data : `id_location`},//3
			{title: `c_date`,      name : `c_date`,      data : `c_date`},     //4
			{title: `c_user_id`,   name : `c_user_id`,   data : `c_user_id`},  //5
			{title: `Folio`,       name : `id_folio`,    data : `id_folio`},   //6
			{title: `Code`,        name : `code`,        data : `code`},       //7
			{title: `receiver`,    name : `receiver`,    data : `receiver`},   //8
			{title: `d_date`,      name : `d_date`,      data : `d_date`},     //9
			{title: `d_user_id`,   name : `d_user_id`,   data : `d_user_id`},  //10
			{title: `id_status`,   name : `id_status`,   data : `d_user_id`},  //11
			{title: `Status`,      name : `id_status`,   data : `id_status`}   //12
		],
		"columnDefs": [
			{"orderable": false,'targets': 0,'checkboxes': {'selectRow': true}},
			{ "targets": [0,3,4,5,8,9,10,11], "visible"   : false, "searchable": false, "orderable": false},
			{ "orderable": false,"targets": 13 },
			// { "width": "40%", "targets": [1,2] }
		],
		'select': {
			'style': 'multi'
		},
		'order': [[6, 'asc']]
	});

	$("#btn-first-user, #btn-add-user").click(function(e){
		let row = {
			id_event_user: 0,
			id_event     : id_event.val(),
			type_ibo      : 1,
			ibo          : '',
			name          : '',
			email        : '',
			phone        : '',
			status       : 1,
		}
		loadEventForm(row);
	});

	$(`#tbl-event-users tbody`).on( `click`, `#btn-edit-user`, function () {
		let row = table.row( $(this).closest('tr') ).data();
		loadEventForm(row);
	});

	$(`#tbl-event-users tbody`).on( `click`, `#btn-records`, function () {
		let row = table.row( $(this).closest('tr') ).data();
		let titleModal = `Records: ${row.ibo}`;
		let formData = new FormData();
		formData.append('id_event_user',row.id_event_user);
		formData.append('id_event',row.id_event);
		formData.append('ibo',row.ibo);
		formData.append('option','getRecords');

		$.ajax({
			url : `${base_url}/controllers/packageController.php`,
			type: 'POST',
			data:formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() {
			  showSwal();
			  $('.swal-button-container').hide();
			}
		  })
		  .done(function(response) {
				swal.close();
				if(response.success==='true'){
					let tblOpen=`<table class="table table-striped" style="width: 100%;">
					<thead>
						<tr>
							<td>#</td>
							<td>Attendance</td>
						</tr>
					</thead>
					<tbody id="attendance-result-table-body">`;
					let rows='';
					let tblClose = `</tbody>
					</table>`;
					let dataJson = JSON.parse(response.dataJson);
					let r=0;
					dataJson.forEach(element => {
						r++;
						rows = rows +`<tr>
						<td>${r}</td>
						<td>${element.cdate}</td>
					</tr>`;
					});
					$('#tbl-div-records').html(`${tblOpen} ${rows} ${tblClose}`);

					$('#modal-records-title').html(titleModal);
					$('#modal-records').modal('show');
				}else{
					swal("Error", response.message, "warning");
				}
			}).fail(function(e) {
				console.log("Something went wrong",e);
			});


	});

	$(`#tbl-event-users tbody`).on( `click`, `#btn-show-qr`, function () {
		let row = table.row( $(this).closest('tr') ).data();
		let titleModal = `QR: ${row.ibo}`;
		$('#modal-show-qr-title').html(titleModal);
		$('#modal-show-qr').modal('show');
		$('#div-show-qr').html(`<img src="${row.qr_path}" />`);
	});

	$('#btn-check-list').click(function(){
		let formData = new FormData();
		formData.append('option','getCheckList');
		formData.append('id_event',id_event.val());
		$.ajax({
			url        : `${base_url}/controllers/packageController.php`,
			type       : 'POST',
			data       : formData,
			cache      : false,
			contentType: false,
			processData: false,
			beforeSend: function() {
				showSwal();
				$('.swal-button-container').hide();
			  }
			})
			.done(function(response) {
			swal.close();
			let tblOpen=`
			<div style="100%; height:350px overflow-y:scroll;"><table class="table table-striped" style="width: 100%;">
			<thead>
				<tr>
					<td>#</td>
					<td>IBO</td>
					<td>Name</td>
					<td>Last check in</td>
				</tr>
			</thead>
			<tbody id="scanned-result-table-body">`;
			let rows='';
			let tblClose=`</tbody>
			</table></div>`;
			if(response.success=='true'){
				let userActives= $('#userActives');
				let dataJson = JSON.parse(response.dataJson);
				let r=0;
				dataJson.forEach(element => {
				r++;
				rows = rows +`<tr>
					<td>${r}</td>
					<td>${element.ibo}</td>
					<td>${element.name}</td>
					<td>${element.cdate}</td>
				</tr>`;
				});
				let tReg = userActives.val();
				//let tChecking = tReg-r;

				$('#tbl-div-check').html(`${tblOpen} ${rows} ${tblClose}`);
				//let titleModal = `Check List, ${r} of ${tReg}`;
				let titleModal = `Check List, ${tReg} of ${r}`;
				$('#modal-check-title').html(titleModal);
				$('#modal-check').modal('show');
			}
			if(response.success=='false'){
				swal("Attention!", `${response.message}`, "warning");
				$('#tbl-div-check').html(``);
			}

		}).fail(function(e) {
			console.log("Something went wrong",e);
		});
	});


	$('#btn-scan-qr').click(function(){
		let counter   = 0;
		let initialTime = 0;
		let titleModal =  'Scan QR';
		qrScaned ='';
		function onScanSuccess(decodedText, decodedResult) {
			if(counter==0){
				initialTime = getNow();
				readQr(decodedText);
			}else{
				let now = getNow();
				let diffTime = Math.abs(now-initialTime);
				if(diffTime>=3000){
					readQr(decodedText);
					initialTime = getNow();
				}
			}
			counter++;
		}

		html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox : { width: 210, height: 210 } });
		html5QrcodeScanner.render(onScanSuccess);

		$('#modal-scan-qr-title').html(titleModal);
		$('#modal-scan-qr').modal({backdrop: 'static', keyboard: false}, 'show');
	});

	function readQr(decodedText){
		try {
			//let decodedString = atob(decodedText);
			// let arrayDecode   = decodedString.split("|");
			//let cveCod        = atob(arrayDecode[0]);
			console.log(decodedText);
			//->JMXif(cveCod==codEnzB64){
				if (decodedText !== lastResult) {
					qrScaned = decodedText+'|'+qrScaned;
					let formData = new FormData();
					// formData.append('id_event_scan',id_event.val());
					 formData.append('qrScaned',qrScaned);
					formData.append('option','saveRecordsQr');
					formData.append('decodedText',decodedText);
					$.ajax({
						url        : `${base_url}/controllers/packageController.php`,
						type       : 'POST',
						data       : formData,
						cache      : false,
						contentType: false,
						processData: false,
					})
					.done(function(response) {
						let tblOpen=`
						<div style="100%; height:150px; overflow-y:scroll;"><table class="table table-striped" style="width: 100%;">
						<thead>
							<tr>
								<td>#</td>
								<td>tracking</td>
								<td>cdate</td>
							</tr>
						</thead>
						<tbody id="scanned-result-table-body">`;
						let rows='';
						let tblClose=`</tbody>
						</table></div>`;
						if(response.success=='true'){
							lastResult   = decodedText;
							swal(`${decodedText} Scanned`, "", "success");
							$('.swal-button-container').hide();
							setTimeout(function(){
								swal.close();
							}, 2500);
							$('audio#beep-sound')[0].play();
							let dataJson = JSON.parse(response.dataJson);
							let r=0;
							dataJson.forEach(element => {
							r++;
							rows = rows +`<tr>
								<td>${r}</td>
								<td>${element.tracking}</td>
								<td>${element.cdate}</td>
							</tr>`;
							});
							$('#div-rst-scan-qr').html(`${tblOpen} ${rows} ${tblClose}`);
						}
						if(response.success=='false'){
							swal("Attention!", `${response.message}`, "warning");
							$('.swal-button-container').hide();
							setTimeout(function(){
								swal.close();
							}, 2500);
							//$('#div-rst-scan-qr').html(``);
						}
					}).fail(function(e) {
						console.log("Something went wrong",e);
					});
				}

				if (decodedText == lastResult){
					swal("QR was scanned!", "", "info");
					$('.swal-button-container').hide();
					setTimeout(function(){
						swal.close();
					}, 2500);
				}
			//->}
		} catch (error) {
			$('#div-rst-scan-qr').html(``);
			swal("Invalid QR!", "", "error");
			$('.swal-button-container').hide();
			setTimeout(function(){
				swal.close();
			}, 2500);
		}
	}

	function getNow(){
		let date = new Date();
		return date.getTime();
	}

	$('#close-qr-b,#close-qr-x').click(function(){
		html5QrcodeScanner.clear();
		lastResult         = 0;
		$('#div-rst-scan-qr').html(``);
	});

	function loadEventForm(row){
		$('#form-modal-user')[0].reset();
		let titleModal = 'New User';
		divStatusUser.hide();
		$('#btn-resend').hide();
		ibo.attr('readonly', false);
		name.attr('readonly', false);

		id_event_user.val(row.id_event_user);
		type_ibo.val(row.type_ibo);
		ibo.val(row.ibo);
		name.val(row.name);
		email.val(row.email);
		phone.val(row.phone);

		status.val(row.status);
		action.val('new');

		isIboOrGuest(row.type_ibo);

		if(row.id_event_user!=0){
			divStatusUser.show();
			$('#btn-resend').show();
			ibo.attr('readonly', true);
			name.attr('readonly', true);
			titleModal='Edit User';
			action.val('update');
		}

		$('#modal-user-title').html(titleModal);
		$('#modal-user').modal('show');
	}

	$('#btn-save-user').click(function(){
		saveResend('registerUser');
	});

	$('#btn-resend').click(function(){
		saveResend('resend');
	});

	$('#type_ibo').on('change', function() {
		isIboOrGuest(this.value);
	  });

	function isIboOrGuest(type){
		$('#lbl-desc-ibo').html('IBO Number:');
		if(type==2){
			$('#lbl-desc-ibo').html('Invited by IBO:');
		}
	}

	function saveResend(typeOption){
		if( ibo.val()=='' ||  name.val()=='' ||  email.val()=='' ||  phone.val()==''){
			swal("Attention!", "Required fields (*)", "warning");
			return false;
		}

		let mask = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/);
		if (mask.test(email.val()) == false){
			swal("Attention!", "Invalid mail", "warning");
			return false;
		}

		let formData = new FormData();
		formData.append('id_event_user',id_event_user.val());
		formData.append('id_event',id_event.val());
		formData.append('type_ibo',type_ibo.val());
		formData.append('ibo',ibo.val());
		formData.append('name',name.val());
		formData.append('email',email.val());
		formData.append('phone',phone.val());
		formData.append('status',status.val());
		formData.append('action',action.val());
		formData.append('base_url',`${base_url}/${folder_qr}/`);
		formData.append('option',typeOption); //registerUser || resend

		$.ajax({
			url : `${base_url}/controllers/packageController.php`,
			type: 'POST',
			data:formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() {
			  showSwal();
			  $('.swal-button-container').hide();
			}
		})
		  .done(function(response) {
				swal.close();
				if(response.success==='true'){
					$('#modal-event').modal('hide');
					swal('Success', response.message, "success");
					$('.swal-button-container').hide();
					setTimeout(function(){
						window.location.href = `${base_url}/views/eventsManage.php?id=${id_event.val()}`;
					}, 1000);
				}else{
					swal("Error", response.message, "warning");
				}
		}).fail(function(e) {
				console.log("Something went wrong",e);
		});
	}

	$('#btn-first-csv,#btn-load-csv').click(function(){
		$('#form-modal-csv')[0].reset();
		$('#upload-file-csv').html('');
		$("#upload-file-csv").hide();
		$('#lbl-max-user').html(`Maximum capacity to import: ${place_available.val()} users`);
		let titleModal = 'Load CSV';
		$('#btn-save-csv').hide();
		$('#modal-csv-title').html(titleModal);
		$('#modal-csv').modal('show');
	});

	$('#file-csv').bind('change', function() {
		let result = validateSize('file-csv','upload-file-csv',this.files[0].size,s3MaxLoadBytes,s3MaxLoadDesc);
		if(result){
			$('#btn-save-csv').show();
		}else{
			$('#btn-save-csv').hide();
		}
    });

	$('#btn-save-csv').click(function(){
		let formData = new FormData();
		let csv    = $('#file-csv')[0].files[0];
		formData.append('file_csv',csv);
		formData.append('id_event',id_event.val());
		formData.append('base_url',`${base_url}/${folder_qr}/`);
		formData.append('place_available',place_available.val());
		formData.append('option','loadCsv');

		$.ajax({
			url : `${base_url}/controllers/packageController.php`,
			type: 'POST',
			data:formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() {
			  showSwal();
			  $('.swal-button-container').hide();
			}
		})
		  .done(function(response) {
				swal.close();
				if(response.success==='true'){
					$('#modal-csv').modal('hide');
					swal('Success', response.message, "success");
					$('.swal-button-container').hide();
					setTimeout(function(){
						window.location.href = `${base_url}/views/eventsManage.php?id=${id_event.val()}`;
					}, 1000);
				}else{
					swal("Error", response.message, "warning");
				}
		}).fail(function(e) {
				console.log("Something went wrong",e);
		});
	});

});