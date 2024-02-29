const robot = require("robotjs");
		const { exec } = require("child_process");
		// Esperar un momento para que la ventana de WhatsApp de escritorio esté activa
		exec("sleep 5 && open /Applications/WhatsApp.app", () => {
		  setTimeout(() => {
			// Enviar la tecla "Enter"
			robot.keyTap("enter");
			console.log("0");
		  }, 3000);
		});