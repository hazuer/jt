const qrcode = require('qrcode-terminal');
const { Client } = require('whatsapp-web.js');
const client = new Client();

client.on('qr', (qr) => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', async () => {
    console.log('Client is ready!');
    const numbers = ["7341346283", "7772314822", "7341008654", "7341109763", "7341326995"]; // Array con los números telefónicos
    const message = '🤖🤖🤖 Le notifico que llegó paquete de JT - Tlaquiltenango el cual podrás recogerlo: A PARTIR DE ESTE MOMENTO Y HASTA LAS 3 DE LA TARDE Y/O MAÑANA MARTES 5 DE MARZO,, de 10:00 a.m. a 3:00 p.m. Si no puedes hacerlo dentro de este plazo, tu paquete será devuelto el 06 DE MARZO de 2024 a las 11:00 a.m. Por favor, asegúrate de ajustarte a los días y horarios mencionados. Recuerda que no hay servicio de entrega los sábados y domingos. Ten en cuenta que JT ya no realiza entregas a domicilio, por lo que deberás recoger tu paquete en el lugar indicado (ENVÍO UBICACIÓN) https://maps.app.goo.gl/HEuDqdKmwjZxESdBA Recuerda presentar una identificación al momento de recoger el paquete. Puede ser cualquier persona que designes. ¡Gracias!';
    for (let i = 0; i < numbers.length; i++) {
        const number = numbers[i];
        const number_details = await client.getNumberId(number); // get mobile number details
        if (number_details) {
            //await client.sendMessage(number_details._serialized, message); // send message
            console.log("Mensaje enviado con éxito a", number);
        } else {
            console.log(number, "Número de móvil no registrado");
        }
        if (i < numbers.length - 1) {
            await sleep(5000); // Espera de 5 segundos entre cada envío
        }
    }
});
client.initialize();
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
