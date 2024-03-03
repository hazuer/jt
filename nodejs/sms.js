const adb = require("adbkit");
const { spawn } = require("child_process");
const client = adb.createClient();
const phoneNumber = `7341346283`;
const message = `Hola`;
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
