$(document).ready(function() {
	let baseController = 'controllers/packageController.php';

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
	let divStatusTracking  = $('#div-scan-tracking');
	let divStatus          = $('#div-status');
	let qrScaned           = '';

	phone.on('input', function() {
        let input = $(this).val();
        input = input.replace(/\D/g, '').slice(0, 10); // Elimina caracteres no numéricos y limita a 10 dígitos
        $(this).val(input);

        if (input.length === 10) {
			// $('#coincidencias').empty();
			// $('#coincidencias').hide();
			receiver.focus();
        }
    });

  	let table = $('#tbl-packages').DataTable({
		"bPaginate": true,
		//"bFilter": false,
		"bInfo" : true,
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
		$('#form-modal-package')[0].reset();
		divStatusTracking.show();
		divStatus.hide();

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
			folio.val(row.folio);
			titleModal=`Editar Paquete ${row.folio}`;
			action.val('update');
		}else{
			let newFolio = await getFolio('new');
			folio.val(newFolio);
			titleModal = `Nuevo Paquete ${newFolio}`;
		}

		$('#modal-package-title').html(titleModal);
		$('#modal-package').modal({backdrop: 'static', keyboard: false}, 'show');
		setTimeout(function(){
			phone.focus();
		}, 600);
	}

	async function getFolio(type) {
		let folio    = 0;
		let formData =  new FormData();
		formData.append('id_location', $('#option-location').val());
		formData.append('type', type);
		formData.append('option', 'getFolio');
		try {
			const response = await $.ajax({
				url: `${base_url}/${baseController}`,
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
		html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox : { width: 350, height: 150 } });
		html5QrcodeScanner.render(onScanSuccess);
	});

	function getNow(){
		let date = new Date();
		return date.getTime();
	}

	function readQr(decodedText){
		try {
			//->JMXif(cveCod==codEnzB64){
				//if (decodedText !== lastResult) {
					tracking.val(decodedText);
					savePackage(decodedText);
				//}

				/*if (decodedText == lastResult){
					swal("QR was scanned!", "", "info");
					$('.swal-button-container').hide();
					setTimeout(function(){
						swal.close();
					}, 2500);
				}*/
			//->}
		} catch (error) {
			swal("Invalid QR!", "", "error");
			$('.swal-button-container').hide();
			setTimeout(function(){
				swal.close();
			}, 2500);
		}
	}

	$('#btn-save').click(function(){
		let tracking = $('#tracking').val();
		savePackage(tracking);
	});

	function savePackage(decodedText) {
		//console.log('here');
		//TODO:Validate lengh of tracking 15 caracteres
		if(phone.val()=='' || receiver.val()=='' || tracking.val()==''){
			swal("Atención!", "* Campos requeridos", "error");
			return;
		}
		//console.log('continue');
		//return;
		// qrScaned = decodedText+'|'+qrScaned;
		let formData = new FormData();
		// formData.append('qrScaned',qrScaned);
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
			url        : `${base_url}/${baseController}`,
			type       : 'POST',
			data       : formData,
			cache      : false,
			contentType: false,
			processData: false,
		})
		.done(function(response) {

			if(response.success=='true'){
				if(action.val()=='new'){
					$('audio#beep-sound')[0].play();
					html5QrcodeScanner.clear();
				}
				swal(`${response.message}`, "", "success");
				$('.swal-button-container').hide();
				$('#modal-package').modal('hide');
				setTimeout(function(){
					swal.close();
					window.location.reload();
				}, 1500);

			}
			if(response.success=='false'){
				swal("Attention!", `${response.message}`, "info");
				$('.swal-button-container').hide();
				setTimeout(function(){
					swal.close();
				}, 3500);
			}
		}).fail(function(e) {
			console.log("Something went wrong",e);
		});
	}

	$('#tracking').on('focus', function() {
        let valTracking = $(this).val();
        if (valTracking === '') {
            $(this).val('JMX000');
			$(this).focus();
        }
    });

	$('#phone').on('input', function() {
        let phoneNumber = $(this).val();
		let id_location = $('#id_location').val();
        let coincidenciasDiv = $('#coincidencias');

        $.ajax({
            url: `${base_url}/${baseController}`, // URL ficticia de la API
            method: 'POST',
            data: { phone: phoneNumber,id_location:id_location,option:'getContact' },
            success: function(data) {
                let coincidencias = data.dataJson; // Supongamos que la respuesta contiene una lista de coincidencias
                // Limpiar el contenido del div de coincidencias
                coincidenciasDiv.empty();
				if (phoneNumber.length==10){
					coincidenciasDiv.hide();
					return;
				}
                // Mostrar el div de coincidencias si hay coincidencias
                if (phoneNumber.length > 0 && coincidencias.length > 0) {
                    coincidenciasDiv.show();
					let coincidenciasArray = Object.values(coincidencias);

                    // Agregar cada coincidencia como un elemento <p> al div
                    coincidenciasArray.forEach(function(coincidencia) {
						coincidenciasDiv.append('<p data-phone="' + coincidencia.phone + '">' + coincidencia.contact_name + '</p>');
                    });
                } else {
                    coincidenciasDiv.hide();
                }
            },
            error: function(xhr, status, error) {
                console.error(error); // Manejo de errores
            }
        });
    });

	// Manejar la selección de una coincidencia
	$('#coincidencias').on('click', 'p', function() {
		let coincidenciaSeleccionada = $(this).text();
		let phoneNumber = $(this).data('phone');
		$('#receiver').val(coincidenciaSeleccionada);
		$('#phone').val(phoneNumber);
		$('#coincidencias').hide();
		if($('#action').val()=='new'){
			$('#btn-scan-code').click();// enable camera
		}
	});

	$('#close-qr-b,#close-qr-x').click(function(){
		let scanner = html5QrcodeScanner;
		if(scanner!=''){
			html5QrcodeScanner.clear();
		}
	});

	// ----------------------------------------------------
	$('#mfNumFolio').on('input', function() {
        let input = $(this).val();
        input = input.replace(/\D/g, '').slice(0, 3); // Elimina caracteres no numéricos y limita a 10 dígitos
        $(this).val(input);
    });

	$('#btn-folio').click(function(){
		loadModalFolio();
	});

	$('#mfModo').on('change', function() {
		let id_mode = $('#mfModo').val();
		if(id_mode==1){
			$('#mfNumFolio').val(0);
			$('#mfNumFolio').prop('disabled', true);
		}else{
			$('#mfNumFolio').val('');
			$('#mfNumFolio').prop('disabled', false);
			setTimeout(function(){
				$('#mfNumFolio').focus();
			}, 250);
		}
	});

	async function loadModalFolio() {
		let foliActual= await getFolio('current');
		$('#mfFolioActual').val(foliActual);
		$('#mfIdLocation').val($('#option-location').val());
		$('#mfModo').val(1);
		$('#mfNumFolio').val(0);
		$('#mfNumFolio').prop('disabled', true);
		$('#modal-folio-title').html('Control de Folios');
		$('#modal-folio').modal({backdrop: 'static', keyboard: false}, 'show');
	}

	$(`#btn-save-folio`).click(function(){
		if($('#mfNumFolio').val()==''){
			swal("Atención!", "* Campos requeridos", "error");
			return;
		}

		let formData =  new FormData();
		formData.append('id_location', $('#mfIdLocation').val());
		formData.append('mfNumFolio', $('#mfNumFolio').val());
		formData.append('option', 'saveFolio');
		try {
			$.ajax({
				url        : `${base_url}/${baseController}`,
				type       : 'POST',
				data       : formData,
				cache      : false,
				contentType: false,
				processData: false,
			})
			.done(function(response) {
				if(response.success=='true'){
					swal(`${response.message}`, "", "success");
					$('.swal-button-container').hide();
					$('#modal-folio').modal('hide');
					setTimeout(function(){
						swal.close();
						window.location.reload();
					}, 1500);
				}
			}).fail(function(e) {
				console.log("Something went wrong",e);
			});
		} catch (error) {
			console.error(error);
		}
	});
});