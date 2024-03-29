const qrcode = require('qrcode-terminal');
const { Client } = require('whatsapp-web.js');
const Database = require("./database.js");
const client = new Client();

client.on('qr', (qr) => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('Client is ready!');
});

// Objeto para almacenar el estado de la conversación
const conversationState = {};

client.on('message', async (message) => {
    let iconBot = `🤖`;
    let db = new Database("false");
    const lowerCaseBody = message.body.toLowerCase();

    // Verificar si ya se ha enviado el mensaje de bienvenida en esta conversación
    if (!conversationState[message.from]) {
        await client.sendMessage(message.from, `${iconBot} Buen día, Te escribe el asistente virtual de JT. ¿En qué puedo ayudarte hoy?`);
        conversationState[message.from] = true; // Marcar la conversación como iniciada
    }

    // Esperar la respuesta del usuario
    if (lowerCaseBody === '1') {
        await client.sendMessage(message.from, `${iconBot} Por favor, ingresa tu número de teléfono de 10 dígitos:`);
    } else if (!isNaN(lowerCaseBody) && lowerCaseBody.length === 10) {
        phoneNumber = lowerCaseBody; // Guardar el número de teléfono en la variable
        const sql = `SELECT 
            cc.phone,
            GROUP_CONCAT(p.id_package) AS ids,
            GROUP_CONCAT(p.folio) AS folios,
            GROUP_CONCAT(p.tracking) AS trackings 
            FROM package p 
            INNER JOIN cat_contact cc ON cc.id_contact=p.id_contact 
            WHERE 
            p.id_location IN (1) 
            AND p.id_status IN (1,2,6,7) 
            AND cc.phone IN('${phoneNumber}')
            GROUP BY cc.phone`;
        const data = await db.processDBQueryUsingPool(sql);
        const rst = JSON.parse(JSON.stringify(data));
        const trackings = rst[0] ? rst[0].trackings : 0;

        if(trackings!=0){
            await client.sendMessage(message.from, `${iconBot} Tus paquetes estan listos para entrega, tus guías de entrega son las siguientes: *${trackings}*`);
        }else{
            await client.sendMessage(message.from, `Lo sentimos, no tienes paquetes para entrega`);
        }
    } else if (conversationState[message.from]) { // Verificar si el mensaje de bienvenida ya se ha enviado
        await client.sendMessage(message.from, `Ingresa *1* para consultar tus paquetes o tu *número de teléfono de 10 dígitos* para consultar si tus paquetes estan listos para entrega`);
    }
});

client.initialize();
