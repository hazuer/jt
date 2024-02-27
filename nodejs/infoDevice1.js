const adbkit = require('adbkit');
const adb = adbkit.createClient();

// Conectar a un dispositivo Android
adb.listDevices()
    .then(function(devices) {
        if (devices.length > 0) {
            const device = devices[0]; // Tomar el primer dispositivo de la lista
            console.log('Conectado al dispositivo:', device.id);
            
            // Obtener las propiedades del dispositivo
            return adb.getProperties(device.id);
        } else {
            throw new Error('No se encontraron dispositivos conectados.');
        }
    })
    .then(function(properties) {
        // Buscar la propiedad que contiene el fabricante y el modelo del dispositivo
        const manufacturer = properties['ro.product.manufacturer'];
        const model = properties['ro.product.model'];
        
        console.log('Fabricante:', manufacturer);
        console.log('Modelo:', model);
    })
    .catch(function(err) {
        console.error('Error al obtener la información del dispositivo:', err);
    });
