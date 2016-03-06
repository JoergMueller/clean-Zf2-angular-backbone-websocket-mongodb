/*jslint node: true, stupid: true */

'use strict';

var fs = require('fs');

module.exports = function(server, app, appRoot, isAuthenticated) {
    fs.readdirSync( appRoot + 'routes').forEach(function(file) {
        if (file.substr(-3, 3) === '.js' && file !== 'index.js') {
            require( appRoot + 'routes/' + file.replace('.js', ''))(server, app, isAuthenticated);
        }
    });
};
