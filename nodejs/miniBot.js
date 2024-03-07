const qrcode = require('qrcode-terminal');
const { Client } = require('whatsapp-web.js');
const client = new Client();

client.on('qr', (qr) => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('Client is ready!');
});


client.on('message', async (message) => {
    console.log(message.body);
	if (message.body === 'hola') {
		await message.reply('mundo');
	}
    await client.sendMessage(message.from, 'Hola Mundo ..!');
});

/*
client.on('message', async (message) => {
    await client.sendMessage(message.from, 'Por favor escribe: suma, resta o multiplicacion:');
    if (message.body.toLowerCase() === 'suma') {
        const reply = 'Ingresa dos números separados por espacio para sumar.';
        await message.reply(reply);
    } else if (message.body.toLowerCase() === 'resta') {
        const reply = 'Ingresa dos números separados por espacio para restar.';
        await message.reply(reply);
    } else if (message.body.toLowerCase() === 'multiplicacion') {
        const reply = 'Ingresa dos números separados por espacio para multiplicar.';
        await message.reply(reply);
    } else if (message.body.includes(' ')) {
        const [num1, num2] = message.body.split(' ').map(Number);
        let result;
        if (message.body.toLowerCase().startsWith('suma')) {
            result = num1 + num2;
        } else if (message.body.toLowerCase().startsWith('resta')) {
            result = num1 - num2;
        } else if (message.body.toLowerCase().startsWith('multiplicacion')) {
            result = num1 * num2;
        }
        await message.reply(`El resultado es: ${result}`);
    }
});*/

client.initialize();
