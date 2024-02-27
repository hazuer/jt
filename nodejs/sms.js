// Número de teléfono al que deseas enviar el SMS
// const phoneNumber = '7341346283'; karen
// 7341109763 josue
// 7341326995 cirilo
// jessi 7341008654

const adb = require('adbkit');
const { spawn } = require('child_process');

const client = adb.createClient();

// Número de teléfono al que deseas enviar el SMS
const phoneNumber = '7341008654';

// Mensaje que deseas enviar
const message = `Te notificamos que tu paquete con J&T - Zacatepec está listo para ser recogido. Podrás hacerlo en los siguientes días y horarios: Martes 27 y Miércoles 28 de febrero, de 10:00 a.m. a 3:00 p.m. Si no puedes hacerlo dentro de este plazo, tu paquete será devuelto el jueves 29 de febrero de 2024 a las 11:00 a.m.
Por favor, asegúrate de ajustarte a los días y horarios mencionados. Recuerda que no hay servicio de entrega los sábados y domingos.
Ten en cuenta que J&T ya no realiza entregas a domicilio, por lo que deberás recoger tu paquete en el lugar indicado:https://maps.app.goo.gl/pj2QbZCFF3xcKzD7A
Recuerda presentar una identificación al momento de recoger el paquete. Puede ser cualquier persona que designes.
¡Gracias y esperamos que disfrutes de tu paquete!`;

// Comando adb para enviar el SMS
const command = `am start -a android.intent.action.SENDTO -d sms:${phoneNumber} --es sms_body "${message}" --ez exit_on_sent true`;

client.listDevices()
    .then((devices) => {
        if (devices.length > 0) {
            const deviceId = devices[0].id;
            const child = spawn('adb', ['-s', deviceId, 'shell', command], { stdio: 'inherit' });
            child.on('exit', (code) => {
                console.log(`Proceso de envío de SMS finalizado con código de salida ${code}`);
            });
        } else {
            console.error('No se encontraron dispositivos conectados.');
        }
    })
    .catch((err) => {
        console.error('Error al obtener la lista de dispositivos:', err);
    });
