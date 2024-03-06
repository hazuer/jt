const qrcode = require("qrcode-terminal");
		const mysql = require("mysql");
		const { Client } = require("whatsapp-web.js");
		const client = new Client();

		const connection = mysql.createConnection({
			host: `localhost`,
			user: `root`,
			password: ``,
			database: `jt_local`,
			port: 3306,
			socketPath: null // Si no estás usando un socket, deja esto como null
		});
		connection.connect((err) => {
			if (err) {
				console.error(`Error al conectar a la base de datos:`, err);
				return;
			}
			console.log(`Conexión exitosa a la base de datos MySQL`);
		});
		client.on(`qr`, (qr) => {
			qrcode.generate(qr, { small: true });
		});
		client.on(`ready`, async () => {
			console.log(`Client is ready!`);
			const query = `SELECT 
		cc.phone,
		(SELECT cct2.contact_name FROM cat_contact cct2 WHERE cct2.phone=cc.phone AND cct2.id_location IN(1) LIMIT 1) main_name,
		COUNT(p.tracking) AS total_p,
		GROUP_CONCAT(p.tracking) AS trackings,
		GROUP_CONCAT(p.id_package) AS ids,
		GROUP_CONCAT(p.folio) AS folios 
		FROM package p 
		INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
		INNER JOIN cat_contact_type cct ON cct.id_contact_type = cc.id_contact_type 
		WHERE 
		p.id_location IN (1) 
		AND p.id_status IN (1) 
		AND cct.id_contact_type IN (1) 
		GROUP BY cc.phone,main_name
		ORDER BY cc.phone ASC`;
			console.log(query);
			connection.query(query, async (error, results, fields) => {
				if (error) {
					console.error(`Error al ejecutar la consulta:`, error);
					return;
				}
				const numbers = results.map(result => result.phone); 
				const message = `Le notifico que llegó paquete de JT - Tlaquiltenango el cual podrás recogerlo  en los siguientes días y horarios: 04 y 05 DE MARZO, de 10:00 a.m. a 3:00 p.m. Si no puedes hacerlo dentro de este plazo, tu paquete será devuelto el 06 DE MARZO de 2024 a las 11:00 a.m.
Por favor, asegúrate de ajustarte a los días y horarios mencionados. Recuerda que no hay servicio de entrega los sábados y domingos.
Ten en cuenta que JT ya no realiza entregas a domicilio, por lo que deberás recoger tu paquete en el lugar indicado (ENVÍO UBICACIÓN) https://maps.app.goo.gl/HEuDqdKmwjZxESdBA
Recuerda presentar una identificación al momento de recoger el paquete. Puede ser cualquier persona que designes.
¡Gracias!`;
				for (let i = 0; i < numbers.length; i++) {
					const number = numbers[i];
					const number_details = await client.getNumberId(number); // Obtener detalles del número de teléfono
					if (number_details) {
						const result = results[i]; // Obtener el registro correspondiente a este número de teléfono
						const trackings = result.trackings;
						// Concatenar los campos trackings y folios al final de la variable message
						const updatedMessage = `${message} Guías: ${trackings}`;
						//await client.sendMessage(number_details._serialized, updatedMessage); // Enviar mensaje
						//console.log(updatedMessage);
						console.log(`Mensaje enviado con éxito a`, number);
					} else {
						console.log(number, `Número de móvil no registrado`);
					}
					if (i < numbers.length - 1) {
						await sleep(3000);
					}
				}
				console.log(`Proceso Finalizado`);
				connection.end();
			});
		});
		client.initialize();
		function sleep(ms) {
			return new Promise(resolve => setTimeout(resolve, ms));
		}
		