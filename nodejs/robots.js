const robot = require("robotjs");
const { exec } = require("child_process");
  setTimeout(() => {
    // Enviar la tecla "Enter"
    robot.keyTap("enter");
    console.log("1");
  }, 4000);
