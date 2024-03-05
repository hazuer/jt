
const qrcode = require('qrcode-terminal');
const { Client } = require('whatsapp-web.js');
const client = new Client();

client.on('qr', (qr) => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', async () => {
    console.log('Client is ready!');

    const number = "7341346283";
    const sanitized_number = number.toString().replace(/[- )(]/g, ""); // remove unnecessary chars from the number
    const final_number = `52${sanitized_number.substring(sanitized_number.length - 10)}`; // add 91 before the number here 91 is country code of India

    const number_details = await client.getNumberId(final_number); // get mobile number details

    if (number_details) {
        const sendMessageData = await client.sendMessage(number_details._serialized,'Le notifico que llegó paquete de JT - Tlaquiltenango el cual podrás recogerlo: A PARTIR DE ESTE MOMENTO Y HASTA LAS 3 DE LA TARDE Y/O MAÑANA MARTES 5 DE MARZO,, de 10:00 a.m. a 3:00 p.m. Si no puedes hacerlo dentro de este plazo, tu paquete será devuelto el 06 DE MARZO de 2024 a las 11:00 a.m. Por favor, asegúrate de ajustarte a los días y horarios mencionados. Recuerda que no hay servicio de entrega los sábados y domingos. Ten en cuenta que JT ya no realiza entregas a domicilio, por lo que deberás recoger tu paquete en el lugar indicado (ENVÍO UBICACIÓN) https://maps.app.goo.gl/HEuDqdKmwjZxESdBA Recuerda presentar una identificación al momento de recoger el paquete. Puede ser cualquier persona que designes. ¡Gracias!'); // send message
    } else {
        console.log(final_number, "Mobile number is not registered");
    }
});

client.initialize();
