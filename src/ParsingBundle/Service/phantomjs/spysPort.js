var system = require('system'),
    string = system.args[1];

<eval>

try {
    console.log(eval("'' + " + string));
} catch (Ex) {
    
}

phantom.exit();