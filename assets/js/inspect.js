$(document).ready(function() {
	let baseController = 'controllers/packageController.php';

  	let table = $('#tbl-inspect').DataTable({
		"bPaginate": true,
		"lengthMenu": [[10, 50, 100, -1], [10, 50, 100, "All"]], // Definir las opciones de longitud del menú
        "pageLength": 500, // Establecer el número de registros por página predeterminado
        "bInfo" : true,
		scrollCollapse: true,
		scroller: true,
		scrollY: 450,
		scrollX: true,
		dom: 'Bfrtip',
		buttons: [
			'excel'
		],
		"columns" : [
			{title: `Télefono`,          name:`phone`,      data:`phone`},     //0
			{title: `Nombre`,            name:`main_name`,  data:`main_name`}, //1
			{title: `Total Paquetes`,    name:`total_p`,    data:`total_p`},   //2
            {title: `Folios`,            name:`folios`,     data:`folios`},    //3
			{title: `Guías`,             name:`trackings`,  data:`trackings`}, //4
			{title: `ids`,             name:`ids`,  data:`ids`}  //5+ 1 last
		],
		"columnDefs": [
			{ "targets": [5], "visible"   : false, "searchable": false, "orderable": false},
		],
        'order': [[3, 'desc']]
	});

	let idLocationSelected = $('#option-location');
	$('#tbl-inspect').on('click', '.btn-pull-realise', function() {
		let tpaquetes = $(this).data('tpaquetes');
		let tphone = $(this).data('tphone');
		let tname = $(this).data('tname');
		let tids = $(this).data('tids');
		swal({
			title: `Total ${tpaquetes} paquetes a liberar: ${tphone} - ${tname}`,
			text: `Está seguro ?`,
			icon: "info",
			buttons: true,
			dangerMode: false,
		})
		.then((weContinue) => {
		  if (weContinue) {
			console.log('continuar',tids);

			let formData = new FormData();
			formData.append('id_location', idLocationSelected.val());
			formData.append('tracking', tids);
			formData.append('option', 'pullRealise');
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
					console.log(response);
					swal(`Script`,`${response.message}`, "success");
				});
			} catch (error) {
				console.log("Opps algo salio mal",error);

			}
		  } else {
			return false;
		  }
		});
	});

});