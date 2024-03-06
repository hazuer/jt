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
			{title: `Guías`,             name:`trackings`,  data:`trackings`}  //4+ 1 last
		],
        'order': [[3, 'desc']]
	});
});