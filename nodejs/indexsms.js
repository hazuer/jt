const adbkit = require('adbkit');
const adb = adbkit.createClient();

// Conectar a un dispositivo Android
adb.listDevices()
    .then(function(devices) {
        if (devices.length > 0) {
            const device = devices[0]; // Tomar el primer dispositivo de la lista
            console.log('Conectado al dispositivo:', device.id);
            
            // Ejecutar comando para enviar SMS
            const phoneNumber = '7341346283';
            const message = '¡Hola! Tienes un paquete pendiente por recoger en C. Nicolas Bravo 203, Col. Gabriel Tepepa, Tlaquiltenango Mor. Horario: Lun a Vie 10:00-18:00. ¡Gracias!';
            const command = `am start -a android.intent.action.SENDTO -d sms:${phoneNumber} --es sms_body "${message}" --ez exit_on_sent true`;
            return adb.shell(device.id, command);
        } else {
            throw new Error('No se encontraron dispositivos conectados.');
        }
    })
    .then(function(output) {
        console.log('SMS enviado exitosamente:', output);
    })
    .catch(function(err) {
        console.error('Error al enviar SMS:', err);
    });
