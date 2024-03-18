const qrcode = require("qrcode-terminal");
const { Client } = require("whatsapp-web.js");
const client = new Client();
client.on("qr", (qr) => {
    qrcode.generate(qr, { small: true });
});
client.on("ready", async () => {
    console.log("Client is ready!");
    let iconBot= `🤖 `;
    const numbers = ["7341346283","7343735062","7341326995"];
    const message = `🤖 Hola Mundo`;
    let fullMessage = `${iconBot} ${message}`;
    for (let i = 0; i < numbers.length; i++) {
        const number = numbers[i];
        try {
            const number_details = await client.getNumberId(number); // get mobile number details
            if (number_details) {
                await client.sendMessage(number_details._serialized, fullMessage); // send message
                console.log("Mensaje enviado con éxito a", number);
            } else {
                console.log(number, "Número de móvil no registrado");
            }
            if (i < numbers.length - 1) {
                await sleep(3000); // Espera de 5 segundos entre cada envío
            }
        } catch (error) {
            console.error("Ocurrió un error al procesar el número", number, ":", error.message);
        }
    }
    console.log("Proceso finalizado...");
});
client.initialize();
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
