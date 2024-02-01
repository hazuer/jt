$(document).ready(function() {

	let id_package         = $('#id_package');
	let folio              = $('#folio');
	let action             = $('#action');
	let id_location        = $('#id_location');
	let c_date             = $('#c_date');
	let phone              = $('#phone');
	let receiver           = $('#receiver');
	let tracking           = $('#tracking');
	let id_status          = $('#id_status');
	let html5QrcodeScanner = '';
	let lastResult         = 0;
	let divStatusTracking  = $('#div-scan-tracking');
	let divStatus          = $('#div-status');
	let qrScaned           = '';

  	let table = $('#tbl-packages').DataTable({
		"bPaginate": false,
		//"bFilter": false,
		"bInfo" : false,
		"scrollX": false,
		"scrollY": '500px',
        "scrollCollapse": true,
		"columns" : [
			{title: `id_package`,   name : `id_package`,   data : `id_package`}, //0
			{title: `Tracking`,     name : `tracking`,     data : `tracking`},   //1
			{title: `Phone`,        name : `phone`,        data : `phone`},      //2
			{title: `id_location`,  name : `id_location`,  data : `id_location`},//3
			{title: `c_date`,       name : `c_date`,       data : `c_date`},     //4
			{title: `Folio`,        name : `folio`,        data : `folio`},      //5
			{title: `Code`,         name : `code`,         data : `code`},       //6
			{title: `Destinatario`, name : `receiver`,     data : `receiver`},   //7
			{title: `d_date`,       name : `d_date`,       data : `d_date`},     //8
			{title: `d_user_id`,    name : `d_user_id`,    data : `d_user_id`},  //9
			{title: `id_status`,    name : `id_status`,    data : `id_status`},  //10
			{title: `Status`,       name : `status_desc`,  data : `status_desc`} //11 + 1
		],
		"columnDefs": [
			{"orderable": false,'targets': 0,'checkboxes': {'selectRow': true}},
			{ "targets": [0,3,4,8,9,10], "visible"   : false, "searchable": false, "orderable": false},
			{ "orderable": false,"targets": 12 },
			// { "width": "40%", "targets": [1,2] }
		],
		'select': {
			'style': 'multi'
		},
		'order': [[5, 'desc']]
	});

	$("#btn-first-package, #btn-add-package").click(function(e){
		let fechaActual = new Date();
		let idLocation  = $('#option-location').val();
		// Obteniendo cada parte de la fecha y hora
		let year     = fechaActual.getFullYear();
		let mes      = String(fechaActual.getMonth() + 1).padStart(2, '0'); // Agrega un cero al mes si es menor que 10
		let dia      = String(fechaActual.getDate()).padStart(2, '0'); // Agrega un cero al día si es menor que 10
		let horas    = String(fechaActual.getHours()).padStart(2, '0'); // Agrega un cero a las horas si es menor que 10
		let minutos  = String(fechaActual.getMinutes()).padStart(2, '0'); // Agrega un cero a los minutos si es menor que 10
		let segundos = String(fechaActual.getSeconds()).padStart(2, '0'); // Agrega un cero a los segundos si es menor que 10
		// Formateando la fecha en el formato deseado
		let fechaFormateada = `${year}-${mes}-${dia} ${horas}:${minutos}:${segundos}`;
		let row = {
			id_package : 0,
			phone      : '',
			id_location: idLocation,
			c_date     : fechaFormateada,
			id_status  : 1,
			tracking   : '',
			id_status  : 1
		}
		loadEventForm(row);
	});

	$(`#tbl-packages tbody`).on( `click`, `#btn-edit-package`, function () {
		let row = table.row( $(this).closest('tr') ).data();
		loadEventForm(row);
	});

	async function loadEventForm(row){
		let titleModal = '';
		let newFolio   = '';
		$('#form-modal-package')[0].reset();
		divStatusTracking.show();
		divStatus.hide();
		$('#btn-save').hide();
		$('#close-qr-b').hide();

		id_package.val(row.id_package);
		phone.val(row.phone);
		id_location.val(row.id_location);
		c_date.val(row.c_date);
		receiver.val(row.receiver);
		tracking.val(row.tracking);
		id_status.val(row.id_status);
		action.val('new');

		if(row.id_package!=0){
			divStatusTracking.hide();
			divStatus.show();
			$('#btn-save').show();
			$('#close-qr-b').show();
			folio.val(row.folio);
			titleModal=`Editar Paquete ${row.folio}`;
			action.val('update');
		}else{
			newFolio = await getFolio();
			folio.val(newFolio);
			titleModal = `Nuevo Paquete ${newFolio}`;
		}

		$('#modal-package-title').html(titleModal);
		$('#modal-package').modal({backdrop: 'static', keyboard: false}, 'show');
	}

	async function getFolio() {
		let folio    = 0;
		let formData =  new FormData();
		formData.append('id_location', $('#option-location').val());
		formData.append('option', 'getFolio');
		try {
			const response = await $.ajax({
				url: `${base_url}/controllers/packageController.php`,
				type: 'POST',
				data: formData,
				cache: false,
				contentType: false,
				processData: false
			});
			folio = response.folio;
		} catch (error) {
			console.error(error);
		}
		return folio;
	}


	$('#btn-scan-code').click(function(){
		let counter   = 0;
		let initialTime = 0;
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
		html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox : { width: 300, height: 150 } });
		html5QrcodeScanner.render(onScanSuccess);
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
					$('#tracking').val(decodedText);
					formData.append('qrScaned',qrScaned);
					formData.append('id_package',id_package.val());
					formData.append('id_location',id_location.val());
					formData.append('folio',folio.val());
					formData.append('c_date',c_date.val());
					formData.append('phone',phone.val());
					formData.append('receiver',receiver.val());
					formData.append('tracking',decodedText);
					formData.append('id_status',id_status.val());
					formData.append('action',action.val());
					formData.append('option','savePackage');

					$.ajax({
						url        : `${base_url}/controllers/packageController.php`,
						type       : 'POST',
						data       : formData,
						cache      : false,
						contentType: false,
						processData: false,
					})
					.done(function(response) {

						if(response.success=='true'){
							lastResult   = decodedText;
							swal(`${decodedText} Scanned`, "", "success");
							$('.swal-button-container').hide();
							$('#modal-package').modal('hide');
							setTimeout(function(){
								swal.close();
								window.location.reload();
							}, 1000);

							$('audio#beep-sound')[0].play();
							html5QrcodeScanner.clear();
						}
						if(response.success=='false'){
							swal("Attention!", `${response.message}`, "warning");
							$('.swal-button-container').hide();
							setTimeout(function(){
								swal.close();
							}, 2500);
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
	});

});