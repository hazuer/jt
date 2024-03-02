// Cuando aparezca el mensaje en la pantalla del dispositivo pidiendo permiso para depurar USB, asegúrate de marcar la opción "Permitir siempre desde esta computadora" y luego haz clic en "Aceptar".
const adb = require('adbkit');
const client = adb.createClient();

// Obtener una lista de dispositivos conectados
client.listDevices()
    .then(devices => {
        devices.forEach(device => {
            console.log('ID del dispositivo:', device.id);

            // Obtener información del dispositivo utilizando el ID
            client.getProperties(device.id)
                .then(properties => {
                    console.log('::::::::::::::::::::::', properties['ro.product.manufacturer'],'CONECTADO','::::::::::::::::::::::');
                })
                .catch(err => {
                    console.error('Error al obtener propiedades del dispositivo:', err);
                });
        });
    })
    .catch(err => {
        console.error('Error al obtener la lista de dispositivos:', err);
    });
