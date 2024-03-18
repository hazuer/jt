const qrcode = require("qrcode-terminal");
const moment = require("moment-timezone");
const { Client } = require("whatsapp-web.js");
const Database = require("./database.js")
const client = new Client();
client.on("qr", (qr) => {
	qrcode.generate(qr, { small: true });
});
client.on("ready", async () => {
	console.log("Client is ready!");
	let iconBot= `🤖 `;
	let db = new Database("false")
	const id_location = 1;
	const n_user_id=1
	const numbers = ["7341346283"];
	const message = `Buenas tardes
Su paquete está disponible para recogerlo en la siguiente ubicación:
Nicolás Bravo 203, a una cuadra del Mercado de la Gabriel Tepepa

El horario de atención es el siguiente:
Lunes 18 y Martes 19 de Marzo de 2024, de 10:00 a.m. a 3:00 p.m.
En caso de no poder recogerlo, este se devolverá el día 20 de Marzo a las 11:00 a.m.

*NOTA IMPORTANTE*
Pronto, la entrega se hará el mismo día del aviso, con horarios ajustados como sigue, esto por instrucciones de la Compañía JT:

Lunes a viernes: 10:00 a.m. - 3:00 p.m. y 5:00 p.m. - 7:00 p.m.
Sábado: 10:00 a.m. - 2:00 p.m.
Recuerde que los domingos no hay servicio de entrega.

Ya no se realizan entregas a domicilio, por lo que le pedimos recoger su paquete en la dirección mencionada. Al hacerlo, asegúrese de llevar consigo una identificación válida.

Gracias por su comprensión y cooperación.`;

	let ids =  0;
	let folios = 0;
	for (let i = 0; i < numbers.length; i++) {
		const number = numbers[i];
		const sql =`SELECT 
		cc.phone,
		GROUP_CONCAT(p.id_package) AS ids,
		GROUP_CONCAT(p.folio) AS folios 
		FROM package p 
		INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
		WHERE 
		p.id_location IN (${id_location}) 
		AND p.id_status IN (1) 
		AND cc.phone IN(${number})
		GROUP BY cc.phone`
		const data = await db.processDBQueryUsingPool(sql)
		const rst = JSON.parse(JSON.stringify(data))
		ids = rst[0] ? rst[0].ids : 0;
		folios = rst[0] ? rst[0].folios : 0;
		let fullMessage = `${iconBot} ${message}`;
		if(ids!=0){
			fullMessage = `${iconBot} ${message} \n Folio(s) control interno: ${folios}`;
		}

		let sid ="";
		let statusPackage = 1;
		try {
			const number_details = await client.getNumberId(number); // get mobile number details
			if (number_details) {
				await client.sendMessage(number_details._serialized, fullMessage); // send message
				sid =`Mensaje enviado con éxito a, ${number}`
				statusPackage = 2
			} else {
				sid = `${number}, Número de móvil no registrado`
				statusPackage = 6
			}
			if (i < numbers.length - 1) {
				await sleep(2000); // Espera de 5 segundos entre cada envío
			}
		} catch (error) {
			sid =`Ocurrió un error al procesar el número, ${number}`
			statusPackage = 6
		}
		console.log(sid);
		if(ids!=0){
			const listIds = ids.split(",");
			const nDate = moment().tz("America/Mexico_City").format("YYYY-MM-DD HH:mm:ss");
			for (let i = 0; i < listIds.length; i++) {
				const id_package = listIds[i];
				const sqlSaveNotification = `INSERT INTO notification 
				(id_location,n_date,n_user_id,message,id_contact_type,sid,id_package) 
				VALUES 
				(${id_location},'${nDate}',${n_user_id},'${message} \n Folio(s) control interno: ${folios}',2,'${sid}',${id_package})`
				await db.processDBQueryUsingPool(sqlSaveNotification)

				const sqlUpdatePackage = `UPDATE package SET 
				n_date = '${nDate}', n_user_id = '${n_user_id}', id_status=${statusPackage} 
				WHERE id_package IN (${id_package})`
				await db.processDBQueryUsingPool(sqlUpdatePackage)
			}
		}
	}
	console.log("Proceso finalizado...");
});
client.initialize();
function sleep(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}