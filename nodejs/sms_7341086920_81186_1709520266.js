const adb = require("adbkit");
		const { spawn } = require("child_process");
		const client = adb.createClient();
		const phoneNumber = `7341086920`;
		const message = `Le notifico que llegó paquete de JT - Tlaquiltenango el cual podrás recogerlo  en los siguientes días y horarios: 04 y 05 DE MARZO, de 10:00 a.m. a 3:00 p.m. Si no puedes hacerlo dentro de este plazo, tu paquete será devuelto el 06 DE MARZO de 2024 a las 11:00 a.m.
Por favor, asegúrate de ajustarte a los días y horarios mencionados. Recuerda que no hay servicio de entrega los sábados y domingos.
Ten en cuenta que JT ya no realiza entregas a domicilio, por lo que deberás recoger tu paquete en el lugar indicado (ENVÍO UBICACIÓN) https://maps.app.goo.gl/HEuDqdKmwjZxESdBA
Recuerda presentar una identificación al momento de recoger el paquete. Puede ser cualquier persona que designes.
¡Gracias!`;
		// Comando adb para enviar el SMS
		const command = `am start -a android.intent.action.SENDTO -d sms:${phoneNumber} --es sms_body "${message}" --ez exit_on_sent true`;
		client.listDevices()
			.then((devices) => {
				if (devices.length > 0) {
					const deviceId = devices[0].id;
					const child = spawn(`adb`, [`-s`, deviceId, `shell`, command], { stdio: `inherit` });
					child.on(`exit`, (code) => {
						console.log(`Proceso de envío de SMS finalizado con código de salida ${code}`);
					});
				} else {
					console.error(`No se encontraron dispositivos conectados.`);
				}
			})
			.catch((err) => {
				console.error(`Error al obtener la lista de dispositivos:`, err);
			});