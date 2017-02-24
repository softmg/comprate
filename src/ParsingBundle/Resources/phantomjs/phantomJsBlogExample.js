var page = require('webpage').create();
var system = require('system');
var foo = 42;

page.onConsoleMessage = function(msg) {
    system.stderr.writeLine( 'console: ' + msg );
};

function evaluate(page, func) {
    var args = [].slice.call(arguments, 2);
    var fn = "function() { return (" + func.toString() + ").apply(this, " +     JSON.stringify(args) + ");}";
    return page.evaluate(fn);
}

page.open(
    'http://google.com',
    function() {
        var foo = 42;
        evaluate(
            page,
            function(foo) {
                console.log(foo);
            },
            foo
        );

        console.log( "Done" );

        phantom.exit( 0 ); // must exit somewhere in the script
    }
);