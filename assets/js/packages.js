$(document).ready(function() {
	let baseController = 'controllers/packageController.php';

	let idLocationSelected = $('#option-location');
	let id_location        = $('#id_location');
	let id_package         = $('#id_package');
	let folio              = $('#folio');
	let action             = $('#action');
	let c_date             = $('#c_date');
	let phone              = $('#phone');
	let receiver           = $('#receiver');
	let tracking           = $('#tracking');
	let id_status          = $('#id_status');
	let divStatus          = $('#div-status');

  	let table = $('#tbl-packages').DataTable({
		"bPaginate": true,
		//"bFilter": false,
		"bInfo" : true,
		"scrollX": false,
		"scrollY": '500px',
        "scrollCollapse": true,
		"columns" : [
			{title: `id_package`,   name : `id_package`,   data : `id_package`},  //0
			{title: `Guía`,         name : `tracking`,     data : `tracking`},    //1
			{title: `Télefono`,     name : `phone`,        data : `phone`},       //2
			{title: `id_location`,  name : `id_location`,  data : `id_location`}, //3
			{title: `c_date`,       name : `c_date`,       data : `c_date`},      //4
			{title: `Folio`,        name : `folio`,        data : `folio`},       //5
			{title: `Destinatario`, name : `receiver`,     data : `receiver`},    //6
			{title: `id_status`,    name : `id_status`,    data : `id_status`},   //7
			{title: `Estatus`,      name : `status_desc`,  data : `status_desc`}, //8
			{title: `note`,         name : `note`,         data : `note`},        //9
			{title: `id_contact`,   name : `id_contact`,   data : `id_contact`}   //10 + 1 last
		],
		"columnDefs": [
			{"orderable": false,'targets': 0,'checkboxes': {'selectRow': true}},
			{ "targets": [0,3,4,7,9,10], "visible"   : false, "searchable": false, "orderable": false},
			{ "orderable": false,"targets": 11 }, // last
			// { "width": "40%", "targets": [1,2] }
		],
		'select': {
			'style': 'multi'
		},
		'order': [[5, 'desc']]
	});

	$("#btn-first-package, #btn-add-package").click(function(e){
		let fechaFormateada = getCurrentDate();
		let row = {
			id_package : 0,
			phone      : '',
			id_location: idLocationSelected.val(),
			c_date     : fechaFormateada,
			id_status  : 1,
			tracking   : '',
			id_status  : 1,
			note       : '',
			id_contact : 0,
		}
		loadPackageForm(row);
	});

	function getCurrentDate(){
		let fechaActual = new Date();
		// Obteniendo cada parte de la fecha y hora
		let year     = fechaActual.getFullYear();
		let mes      = String(fechaActual.getMonth() + 1).padStart(2, '0'); // Agrega un cero al mes si es menor que 10
		let dia      = String(fechaActual.getDate()).padStart(2, '0'); // Agrega un cero al día si es menor que 10
		let horas    = String(fechaActual.getHours()).padStart(2, '0'); // Agrega un cero a las horas si es menor que 10
		let minutos  = String(fechaActual.getMinutes()).padStart(2, '0'); // Agrega un cero a los minutos si es menor que 10
		let segundos = String(fechaActual.getSeconds()).padStart(2, '0'); // Agrega un cero a los segundos si es menor que 10
		// Formateando la fecha en el formato deseado
		let dtCurrent = `${year}-${mes}-${dia} ${horas}:${minutos}:${segundos}`;
		return dtCurrent;
	}

	$(`#tbl-packages tbody`).on( `click`, `#btn-records`, function () {
		let row = table.row( $(this).closest('tr') ).data();
		loadPackageForm(row);
	});

	async function loadPackageForm(row){
		let titleModal = '';
		$('#form-modal-package')[0].reset();
		divStatus.hide();

		id_package.val(row.id_package);
		$('#id_contact').val(row.id_contact);
		phone.val(row.phone);
		id_location.val(row.id_location);
		c_date.val(row.c_date);
		receiver.val(row.receiver);
		tracking.val(row.tracking);
		id_status.val(row.id_status);
		$('#note').val(row.note);
		action.val('new');
		$('#btn-erase').show();
		$('#phone').prop('disabled', false);
		$('#receiver').prop('disabled', false);
		$('#tracking').prop('disabled', false);

		if(row.id_package!=0){
			$('#div-keep-modal').hide();
			divStatus.show();
			folio.val(row.folio);
			titleModal=`Editar Paquete ${row.folio}`;
			action.val('update');
			$('#tracking').prop('disabled', true);

			if(row.id_status!=1){
				$('#phone').prop('disabled', true);
				$('#receiver').prop('disabled', true);
			}
			$('#btn-erase').hide();
		}else{
			$('#opcMA').prop('checked', true);
			$('#div-keep-modal').show();
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
		formData.append('id_location', idLocationSelected.val());
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

	$('#btn-save').click(function(){
		savePackage();
	});


	$('#btn-erase').click(function(){
		$('#id_contact').val(0);
		$('#phone').val('');
		$('#receiver').val('');
		$('#tracking').val('');
		$('#phone').focus();
	});

	//-----------------------
	$('#tracking').on('input', function() {
		let input = $(this).val().trim(); // Eliminar espacios en blanco al inicio y al final
		if (input.length === 15 && input.substr(0, 3).toUpperCase() === "JMX") {
			$('#btn-save').click();
		}
	});

	function savePackage() {
		let decodedText = $('#tracking').val();

		if(phone.val()=='' || receiver.val()=='' || tracking.val()==''){
			swal("Atención!", "* Campos requeridos", "error");
			return;
		}

		let p = phone.val().trim(); // Eliminar espacios en blanco al inicio y al final
		if (p.length!=10){
			swal("Atención!", "* El número de télefono no es válido", "error");
			return;
		}

		let t = tracking.val().trim(); // Eliminar espacios en blanco al inicio y al final
		if (t.length !== 15 || t.substr(0, 3).toUpperCase() !== "JMX") {
			let mensajeError = "* Código de barras no válido:";
			if (t.length !== 15) {
				mensajeError += " Debe tener 15 caracteres.";
			} else {
				mensajeError += " Debe comenzar con 'JMX'.";
			}
			swal("Atención!", mensajeError, "error");
			return;
		}
		let guia = decodedText.substring(0, 3).toUpperCase() + decodedText.substring(3);

		let formData = new FormData();
		formData.append('id_package',id_package.val());
		formData.append('id_location',idLocationSelected.val());
		formData.append('folio',folio.val());
		formData.append('c_date',c_date.val());
		formData.append('phone',phone.val());
		formData.append('receiver',receiver.val());
		formData.append('id_contact',$('#id_contact').val());
		formData.append('tracking',guia);
		formData.append('id_status',id_status.val());
		formData.append('action',action.val());
		formData.append('option','savePackage');
		formData.append('note',$('#note').val());

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
				$('#modal-package').modal('hide');
				if(action.val()=="update"){
					setTimeout(function(){
						swal.close();
						window.location.reload();
					}, 1500);
					return;
				}

				if(action.val()=="new"){
					if ($('#opcMA').prop('checked')) {
						setTimeout(function(){
							swal.close();
							setTimeout(function(){
								$('#btn-add-package').click();
								setTimeout(function(){
									phone.focus();
								}, 100);
							}, 300);
						}, 500);
						return;
					} else{
						setTimeout(function(){
							swal.close();
							window.location.reload();
						}, 1500);
						return;
					}
				}
			}
			if(response.success=='false'){
				swal("Atención!", `${response.message}`, "info");
				$('.swal-button-container').hide();
				setTimeout(function(){
					swal.close();
				}, 3500);
				return;
			}
		}).fail(function(e) {
			console.log("Opps algo salio mal",e);
		});
	}

	phone.on('input', function() {
        let phoneNumber = $(this).val();
		let id_location = idLocationSelected.val();
        let coincidenciasDiv = $('#coincidencias');

        input = phoneNumber.replace(/\D/g, '').slice(0, 10); // Elimina caracteres no numéricos y limita a 10 dígitos
        $(this).val(input);
        if (input.length === 10) {
			receiver.focus();
        }

        $.ajax({
            url: `${base_url}/${baseController}`, // URL ficticia de la API
            method: 'POST',
            data: { phone: phoneNumber,id_location:id_location,option:'getContact' },
            success: function(data) {
                let coincidencias = data.dataJson; // Supongamos que la respuesta contiene una lista de coincidencias
                // Limpiar el contenido del div de coincidencias
                coincidenciasDiv.empty();
				$('#id_contact').val(0);
				$('#receiver').val('');
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
						coincidenciasDiv.append(`<p data-phone="${coincidencia.phone}" data-name="${coincidencia.contact_name}" data-idcontact="${coincidencia.id_contact}">${coincidencia.phone} - ${coincidencia.contact_name}</p>`);
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
		let name        = $(this).data('name');
		let phoneNumber = $(this).data('phone');
		let id_contact = $(this).data('idcontact');
		$('#receiver').val(name);
		$('#phone').val(phoneNumber);
		$('#id_contact').val(id_contact);
		$('#coincidencias').hide();
		$('#tracking').focus();
	});

	$('#close-qr-b,#close-qr-x').click(function(){
		window.location.reload();
	});

	$('#mfNumFolio').on('input', function() {
        let input = $(this).val();
        input = input.replace(/\D/g, '').slice(0, 5); // Elimina caracteres no numéricos y limita a 10 dígitos
        $(this).val(input);
    });

// ----------------------------------------------------

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
		$('#mfIdLocation').val(idLocationSelected.val());
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
		formData.append('id_location', idLocationSelected.val());
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
				console.log("Opps algo salio mal",e);
			});
		} catch (error) {
			console.error(error);
		}
	});

// ----------------------------------------------------
	$('#mCPhone').on('input', function() {
		let input = $(this).val();
		input = input.replace(/\D/g, '').slice(0, 10); // Elimina caracteres no numéricos y limita a 10 dígitos
		$(this).val(input);

		if (input.length === 10) {
			$('#mCName').focus();
		}
	});

	$('#btn-contacto').click(function(){
		$('#mCIdLocation').val(idLocationSelected.val());
		$('#mCPhone').val('');
		$('#mCName').val('');

		$('#modal-contacto-title').html('Nuevo Contacto');
		$('#modal-contacto').modal({backdrop: 'static', keyboard: false}, 'show');
		setTimeout(function(){
			$('#mCPhone').focus();
		}, 600);
	});

	$(`#btn-save-contacto`).click(function(){
		if($('#mCPhone').val()=='' || $('#mCName').val()==''){
			swal("Atención!", "* Campos requeridos", "error");
			return;
		}

		let formData =  new FormData();
		formData.append('id_location', idLocationSelected.val());
		formData.append('mCPhone', $('#mCPhone').val());
		formData.append('mCName', $('#mCName').val());
		formData.append('mCContactType', $('#mCContactType').val());
		formData.append('mCEstatus', $('#mCEstatus').val());
		formData.append('option', 'saveContact');
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
					$('#modal-contacto').modal('hide');
					setTimeout(function(){
						swal.close();
					}, 1500);
				}
			}).fail(function(e) {
				console.log("Opps algo salio mal",e);
			});
		} catch (error) {
			console.error(error);
		}
	});

// ----------------------------------------------------
	$('#mMMessage').on('input', function() {
		var message = $(this).val();
		if (message.length > 160) {
		$(this).val(message.slice(0, 160));
		}
	});

	$('#btn-send-messages').click(function(){
		$("#tbl-list-sms").hide();
		selectMessages();
	});

	async function selectMessages() {
		let listPackage = await getPackageNewSms();
		generateTable(listPackage);

		$('#mMIdLocation').val(idLocationSelected.val());
		$('#mMContactType').val(1);
		$('#mMEstatus').val(1);
		let msj=`Te notificamos que tu paquete con J&T - Zacatepec está listo para ser recogido. Podrás hacerlo en los siguientes días y horarios: Martes 27 y Miércoles 28 de febrero, de 10:00 a.m. a 3:00 p.m. Si no puedes hacerlo dentro de este plazo, tu paquete será devuelto el jueves 29 de febrero de 2024 a las 11:00 a.m.
Por favor, asegúrate de ajustarte a los días y horarios mencionados. Recuerda que no hay servicio de entrega los sábados y domingos.
Ten en cuenta que J&T ya no realiza entregas a domicilio, por lo que deberás recoger tu paquete en el lugar indicado:https://maps.app.goo.gl/pj2QbZCFF3xcKzD7A
Recuerda presentar una identificación al momento de recoger el paquete. Puede ser cualquier persona que designes.
¡Gracias y esperamos que disfrutes de tu paquete!`;
		$('#mMMessage').val(msj);
		$('#modal-messages-title').html('Envio de Mensajes');
		$('#modal-messages').modal({backdrop: 'static', keyboard: false}, 'show');
	}

	async function getPackageNewSms() {
		let listPackage = [];
		let formData =  new FormData();
		formData.append('id_location', idLocationSelected.val());
		formData.append('IdContactType', $('#mMContactType').val());
		formData.append('idStatus', $('#mMEstatus').val());
		formData.append('option','getPackageNewSms');
		try {
			const response = await $.ajax({
				url: `${base_url}/${baseController}`,
				type: 'POST',
				data: formData,
				cache: false,
				contentType: false,
				processData: false
			});
			if(response.success=='true'){
				listPackage = response;
			}
		} catch (error) {
			console.error(error);
		}
		return listPackage;
	}

	// Función para procesar el JSON y generar filas de tabla
	function generateTable(data) {
		// Limpiar la tabla
		$('#tbl-listPackage').empty();
		$('#btn-save-messages').show();
		let tmsj = data.dataJson;
		if(tmsj.length==0){
			swal("Estás al día!", "No hay mensajes para enviar", "success");
			$('.swal-button-container').hide();
			$('#btn-save-messages').hide();
			setTimeout(function(){
				swal.close();
				$('#modal-messages').modal('hide');
			}, 2500);
			return;
		}
		$("#tbl-list-sms").show();

		// Iterar sobre los datos del JSON y generar filas de tabla
		let c=1;
		$.each(data.dataJson, function(index, item) {
			let row = `<tr>
				<td><b>${c}</b></td>
				<td>${item.phone}</td>
				<td>${item.main_name}</td>
				<td>${item.total_p}</td>
				<td style="text-align:center"><button type="button" class="btn-info btn-sm btn-idx" title="Ver Paquetes" data-phone="${item.phone}" data-name="${item.main_name}" data-trackings="${item.trackings}" data-ids="${item.ids}"><i class="fa fa-eye" aria-hidden="true"></i></button></td>
			</tr>`;
			$('#tbl-listPackage').append(row);
			c++;
		});

		$('#tbl-listPackage').on('click', '.btn-idx', function() {
			let name = $(this).data('name');
			let trackings = $(this).data('trackings');
			swal(`${name}`,trackings, "success");
			$('.swal-button-container').hide();
		});
	}

	$('#btn-save-messages').click(function(){
		// Array para almacenar los ids de las filas seleccionadas
		let arrayNotification = [];
		// Iterar sobre las filas de la tabla
		$('#tbl-listPackage tr').each(function(index, row) {
			// Obtener el id de la fila actual
			let phonex = $(row).find('.btn-idx').data('phone');
			let namex = $(row).find('.btn-idx').data('name');
			let trackingsx = $(row).find('.btn-idx').data('trackings');
			let idsx = $(row).find('.btn-idx').data('ids');
			arrayNotification.push({phone:phonex,
				name:namex,
				trackings:trackingsx,
				ids:idsx
			});
		});

		swal({
				title: "Enviar Mensajes",
				text: "Está seguro?",
				icon: "info",
				buttons: true,
				dangerMode: false,
			})
			.then((weContinue) => {
			  if (weContinue) {
				enviarNotificaciones(arrayNotification);
			  } else {
				return false;
			  }
			});
	});

	function sendAllMessages(arrayNotification) {
		
		//enviarNotificaciones(arrayNotification);


		/*let formData = new FormData();
		formData.append('id_location', $('#mCIdLocation').val());
		formData.append('idContactType', $('#mCContactType').val());
		formData.append('message', $('#mMMessage').val());
		formData.append('arrayNotification', JSON.stringify(arrayNotification));
		formData.append('option', 'sendMessages');

		console.log(arrayNotification);*/
		


		return;

		$.ajax({
			url: `${base_url}/${baseController}`,
			type: 'POST',
			data: formData,
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
					swal("Exito!", response.message, "success");
					$('#modal-messages').modal('hide');
					$('.swal-button-container').hide();
					setTimeout(function(){
						swal.close();
						window.location.reload();
					}, 5500);
				}else{
					swal("Error!", "Ocurrio un error al enviar los sms", "error");
				}
		}).fail(function(e) {
			console.log("Opps algo salio mal",e);
		});
	}


async function enviarNotificaciones(arrayNotification) {
	let sentCount = 0;
    const totalNotifications = arrayNotification.length;
    swal({
        title: 'Procesando',
        text: `Enviando 1 de ${totalNotifications}`,
        icon: 'info',
        buttons: false
    });

    for (let i = 0; i < totalNotifications; i++) {
        const data = arrayNotification[i];
		console.log(data);
		return;
		let formData = new FormData();
		formData.append('id_location', idLocationSelected.val());
		formData.append('idContactType', $('#mCContactType').val());
		formData.append('message', $('#mMMessage').val());
		formData.append('arrayNotification', JSON.stringify(data));
		formData.append('option', 'sendMessages');

		try {
			const response = await $.ajax({
				url: `${base_url}/${baseController}`,
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
			});
			console.log(response);
			if(response.success==='true'){
				sentCount++;
                swal({
                    title: 'Procesando',
                    text: `Enviando ${sentCount} de ${totalNotifications}`,
                    icon: 'info',
                    buttons: false
                });

                if (sentCount === totalNotifications) {
                    swal({
                        title: 'Proceso completado',
                        text: 'Se han enviado todas las notificaciones',
                        icon: 'success',
                        button: 'Aceptar'
                    });
                }
			}
			
			// Manejar la respuesta aquí
		} catch (error) {
			// Manejar errores aquí
		}
       /* try {
            const response = await fetch(`${base_url}/${baseController}`, {
                method: 'POST',
                data: formData,
				contentType: false,
				processData: false,
            });
			console.log(response);

            if (response.ok) {
                sentCount++;
                swal({
                    title: 'Procesando',
                    text: `Enviando ${sentCount} de ${totalNotifications}`,
                    icon: 'info',
                    buttons: false
                });

                if (sentCount === totalNotifications) {
                    swal({
                        title: 'Proceso completado',
                        text: 'Se han enviado todas las notificaciones',
                        icon: 'success',
                        button: 'Aceptar'
                    });
                }
            } else {
                console.error(`Error al enviar operación ${i + 1}`);
            }
        } catch (error) {
            console.error(`Error al enviar operación ${i + 1}: ${error.message}`);
        }*/
    }
}

	//------------------------------------------ release
	let  listPackageRelease=[];

	$('#btn-release-package').click(function(){
		listPackage = [];
		$('#form-modal-release-package')[0].reset();
		$('#mrp-id_location').val(idLocationSelected.val());
		let fechaFormateada = getCurrentDate();
		$('#mrp-date-release').val(fechaFormateada);
		$('#tablaPaquetes').hide();

		$('#modal-release-package-title').html('Entrega de Paquetes');
		$('#modal-release-package').modal({backdrop: 'static', keyboard: false}, 'show');
		setTimeout(function(){
			$('#mrp-tracking').focus();
		}, 600);
	});

	$('#btn-mrp-scan').click(function(){
		loadReaderScan()
	});

	$('#close-mrp-x,#close-mrp-b').click(function(){
		window.location.reload();
	});

	$('#btn-mrp-save').click(function(){
		
		saveAndReleasePakage();
	});

	function saveAndReleasePakage(){
		try {
			let tracking = $('#mrp-tracking').val();
			let t = $('#mrp-tracking').val().trim(); // Eliminar espacios en blanco al inicio y al final
			if (t.length !== 15 || t.substr(0, 3).toUpperCase() !== "JMX") {
				let mensajeError = "* Código de barras no válido:";
				if (t.length !== 15) {
					mensajeError += " Debe tener 15 caracteres.";
				} else {
					mensajeError += " Debe comenzar con 'JMX'.";
				}
				swal("Atención!", mensajeError, "error");
				return;
			}

			let guia = tracking.substring(0, 3).toUpperCase() + tracking.substring(3);
			listPackageRelease.push(`'${guia}'`);


			let formData = new FormData();
			formData.append('id_location',idLocationSelected.val());
			formData.append('tracking',guia);
			formData.append('listPackageRelease', JSON.stringify(listPackageRelease));
			formData.append('option','releasePackage');
			$.ajax({
				url: `${base_url}/${baseController}`,
				type       : 'POST',
				data       : formData,
				cache      : false,
				contentType: false,
				processData: false,
			})
			.done(function(response) {
				$('#mrp-tracking').val('');
				if(response.success==='true'){
					if (response.dataJson.length > 0) {
						$('#tablaPaquetes').show();
						$('#tablaPaquetes tbody').empty();
						$.each(response.dataJson, function(index, item) {
							let row = `<tr>
								<td>${item.tracking}</td>
								<td>${item.phone}</td>
								<td>${item.receiver}</td>
								<td>${item.folio}</td>
							</tr>`;
							$('#tablaPaquetes tbody').append(row);
						});
					}
					swal(guia, response.message, "success");
				}else {
					let index = listPackageRelease.indexOf(`'${guia}'`);
					if (index !== -1) {
						listPackageRelease.splice(index, 1);
					}
					swal(guia, response.message, "warning");
				}
				$('.swal-button-container').hide();
				setTimeout(function(){
					swal.close();
					$('#mrp-tracking').focus();
				}, 2500);

			}).fail(function(e) {
				console.log("Opps algo salio mal",e);
			});

		} catch (error) {
			console.log("Opps algo salio mal",error);
		}
	}

	//-----------------------
	$('#mrp-tracking').on('input', function() {
		let input = $(this).val().trim();
		if (input.length === 15 && input.substr(0, 3).toUpperCase() === "JMX") {
			$('#btn-mrp-save').click();
		}
	});

});