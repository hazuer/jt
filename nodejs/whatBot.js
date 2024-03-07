const qrcode = require('qrcode-terminal');
const { Client } = require('whatsapp-web.js');
const client = new Client();

client.on('qr', (qr) => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', async () => {
    console.log('Client is ready!');
    const numbers = ["5539248378","7343407032","5535463033","7775085229","7772250082","7776345858","7353619408","6692314722","7341203518","7341558445","7341378607"];
    const message = `🤖 Buenos días, Solo para informarle que el día 03 de marzo del presente año, se le notifico mediante mensaje de texto SMS, que había llegado paquete por compra que hizo por internet, el cual a la fecha no ha sido recogido, por lo que solo tiene el día de hoy y mañana para hacerlo, de lo contrario será devuelto al día siguiente de haberle notificado con este mensaje.
El horario en que podrá recogerlo en estos días es de 10 de la mañana a 3 de la  tarde y de 5 de la tarde a 7 de la noche
Envío ubicación https://maps.app.goo.gl/HEuDqdKmwjZxESdBA`;
    for (let i = 0; i < numbers.length; i++) {
        const number = numbers[i];
        const number_details = await client.getNumberId(number); // get mobile number details
        if (number_details) {
            await client.sendMessage(number_details._serialized, message); // send message
            console.log("Mensaje enviado con éxito a", number);
        } else {
            console.log(number, "Número de móvil no registrado");
        }
        if (i < numbers.length - 1) {
            await sleep(8000); // Espera de 5 segundos entre cada envío
        }
    }
    console.log('Proceso finalizado...');
});
client.initialize();
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
