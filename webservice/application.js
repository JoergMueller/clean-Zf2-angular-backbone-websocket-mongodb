var args = {};
process.argv.forEach(function(arg) {
    arg = arg.match(/^--([^=]+)=(.+)$/);
    if (arg)
        args[arg[1]] = arg[2];
});
process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";
process.setMaxListeners(500);
require('events').EventEmitter.prototype._maxListeners = 500;


var async = require('async'),
    express = require('express'),
    app = express(),
    _ = require('lodash'),
    jwt = require('jwt-simple'),
    mongoose = require('mongoose'),
    appRoot = require('app-root-path').resolve('./'),
    server, io;

var moment = require('moment');



// BUILD THE APP
// ============
async.series([
    loadConfig,
    loadDb,
    loadMiddleware,
    loadHttpsServer,
    loadApi,
    loadSocketIO,
    startServer
]);


// LOAD THE APP CONFIG
// ===================
function loadConfig(done) {
    app.config = require(appRoot + 'config/config.js')[args.env || 'local'];
    done();
}

// LOAD THE DB
// ===========
function loadDb(done) {

    var mongoose = require('mongoose'),
        Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    app.db = mongoose.createConnection(app.config.db, app.config.dbopts);

    _eachModuleIn('schemas', function(file) {
        require(file)(app);
    });

    done();
}


// LOAD THE MIDDLEWARE
// ===================
function loadMiddleware(done) {

    var bodyParser = require('body-parser'),
        serveIndex = require('serve-index'),
        serveStatic = require('serve-static'),
        session = require('express-session'),
        compression = require('compression'),
        methodOverride = require('method-override'),
        errorHandler = require('errorhandler'),
        cookieParser = require('cookie-parser'),
        morgan = require('morgan'),
        MongoDBStore = require('connect-mongodb-session')(session);

    app.use(bodyParser.json()); // for parsing application/json
    app.use(bodyParser.urlencoded({
        extended: true
    })); // for parsing application/x-www-form-urlencoded

    app.set('jwtTokenSecret', 'dx3nter');
    app.config.session.store = new MongoDBStore({
        uri: app.config.db,
        collection: 'Sessions'
    });
    app.use(express.static(appRoot + 'public'));

    app.use(cookieParser());
    app.use(session(app.config.session));

    app.use(serveStatic(appRoot + 'public'));


    done();
}


// LOAD THE API
// ============
function loadApi(done) {


    restify = require('restify');
    bunyan = require('bunyan');
    routes = require(appRoot + 'routes/');

    log = bunyan.createLogger({
        name: 'mcApp',
        level: process.env.LOG_LEVEL || 'info',
        stream: process.stdout,
        serializers: bunyan.stdSerializers
    });

    r_server = restify.createServer({
        name: app.config.serverName,
        url: app.config.serverUrl
    });

    r_server.use(restify.bodyParser());
    r_server.use(restify.queryParser());
    r_server.use(restify.gzipResponse());
    r_server.pre(restify.pre.sanitizePath());


    r_server.pre(restify.fullResponse());

    function unknownMethodHandler(req, res) {
        if (req.method.toLowerCase() === 'options') {
          var allowHeaders = ['Accept', 'Accept-Version', 'Content-Type', 'Api-Version', 'Origin', 'X-Requested-With', 'Authorization', 'Access-Control-Allow-Origin','Access-Control-Allow-Headers',
                'Access-Control-Allow-Methods','x-access-token']; // added Origin & X-Requested-With & **Authorization**

          if (res.methods.indexOf('OPTIONS') === -1) res.methods.push('OPTIONS');

          res.header('Access-Control-Allow-Credentials', true);
          res.header('Access-Control-Allow-Headers', allowHeaders.join(', '));
          res.header('Access-Control-Allow-Methods', res.methods.join(', '));
          res.header('Access-Control-Allow-Origin', req.headers.origin);

          return res.send(200);
       } else {
          return res.send(new restify.MethodNotAllowedError());
       }
    }
    r_server.on('MethodNotAllowed', unknownMethodHandler);


    routes(r_server, app, appRoot, isAuthenticated);

    r_server.on('after', restify.auditLogger({
        log: log
    }));

    console.log('Server started.');
    r_server.listen(app.config.authPort, function() {
        log.info('%s listening at %s', r_server.name, r_server.url);
    });

    done();
}


// LOAD THE SERVER
// ================
function loadHttpsServer(done) {
    var http = require('http');

    server = http.createServer(function(req, res) {}, app);
    server.listen(app.config.port, app.config.serverName);
    
    done();
}


// LOAD SOCKETIO
// =============
function loadSocketIO(done) {
    var socketio = require('socket.io');
    io = app.io = socketio.listen(server);
    require('backbone-socketio').init(io);

    _eachModuleIn('resources', function(file) {
        require(file)(app, io);
    });

    done();
}


// START THE SERVER
// ================
function startServer(done) {

    server.listen(app.config.port);
    console.log('\n\nYou\'ve been ready!\n\n');
    app.emit('started');
    done();
}


function _eachModuleIn(dir, callback) {
    var fs = require("fs");
    dir = appRoot + dir;

    fs.readdir(dir, function(err, files) {
        _.each(files, function(f){
            if (/.js$/.test(f))
                callback(dir + '/' + f);
        })
    });
}


isAuthenticated = function(req, res, next) {

    var expires = moment().add(2, 'h').valueOf();
    var token = (req.body && req.body.access_token) || (req.query && req.query.access_token) || req.headers['x-access-token'];

    if (token) {
        try {
            var decoded = jwt.decode(token, app.get('jwtTokenSecret'));

            if (decoded.exp <= Date.now()) {
                res.end('Access token has expired', 400);
            }

        } catch (err) {
            return next();
        }
    } else {
        // username (email oder nickname)

        if(_.isUndefined(req.body)) return;

        var un = req.body.un || res.end(400, 'login not found');
        // password im klartext
        var up = req.body.up || res.end(400, 'login not found');
        // password hashed
        //var hash = bcrypt.hashSync(up);

        var name = 'email';
        if (un.indexOf('@') === -1) name = 'nickname';

        app.db.model('Users').find()
            .where(name).equals(un)
            //  .where(password).equals(hash)
            .exec(function(err, users) {

                var user = users[0] || false;

                if (err || !user) {
                    res.end(err, 400);
                }

                req.user = user;
                var token = jwt.encode({
                    iss: user.id,
                    exp: expires
                }, app.get('jwtTokenSecret'));
                req.accesstoken = token;
                res.accesstoken = token;

                var response = {
                    token: token,
                    expires: expires,
                    user: JSON.stringify(user)
                };
                return response;
            });
        next();
    }

}