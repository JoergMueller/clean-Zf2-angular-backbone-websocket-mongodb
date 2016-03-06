'use strict';

var jwt = require('jwt-simple');
var moment = require('moment');
var _ = require('lodash');
var md5 = require('md5');

//var bcrypt = require('bcrypt');

module.exports = function(server, app, isAuthenticated) {

    server.get('/api/user', function(req, res, next) {
        var expires = moment().add(2, 'd').valueOf();
        var token = (req.body && req.body.access_token) || (req.query && req.query.access_token) || req.headers['x-access-token'];

        if (token) {
            try {
                var decoded = jwt.decode(token, app.get('jwtTokenSecret'));

                if (decoded.exp >= Date.now()) {
                    app.db.model('Users').findOne()
                        .where('_id').equals(decoded.iss)
                        .exec(function(err, user) {
                            if (err || user == false) {
                                res.send({
                                    user: false,
                                    access_token: false
                                });
                            } else {
                                req.user = user;
                                var token = jwt.encode({
                                    iss: user.id,
                                    exp: expires
                                }, app.get('jwtTokenSecret'));
                                req.access_token = token;

                                var data = {};
                                data.access_token = token;
                                data.expires = expires;
                                data.user = JSON.stringify(user);
                                req.headers['x-access-token'] = token;
                                res.json({
                                    'data': data
                                })
                            }
                            next();
                        });
                }
                if (decoded.exp <= Date.now()) {
                    //
                    var data = {};
                    data.access_token = false;
                    data.expires = false;
                    data.user = false;
                    req.headers['x-access-token'] = null;
                    res.json({
                        'data': data
                    })
                    res.end(400, 'Access token has expired');
                }
            } catch (err) {}
        } else {
            var data = {};
            data.access_token = false;
            data.expires = false;
            data.user = false;
            req.headers['x-access-token'] = null;
        }
        next();
    });
    server.put('/api/user/', function(req, res, next) {
        next()
    });

    server.post('/user/auth', function(req, res, next) {

        var expires = moment().add(2, 'd').valueOf();
        var token = (req.body && req.body.access_token) || (req.query && req.query.access_token) || req.headers['x-access-token'];

        if (token && !_.isUndefined(token) && token != 'null') {
            try {
                var decoded = jwt.decode(token, app.get('jwtTokenSecret'));
                if (decoded.exp <= Date.now()) {
                    res.end(400, 'Access token has expired');
                }

                app.db.model('Users').findOne()
                    .where('_id').equals(decoded.iss)
                    .where('password').equals(req.body.up)
                    .exec(function(err, user) {
                        if (err || user == false) {
                            res.send({
                                user: false,
                                access_token: false
                            });
                        } else {
                            req.user = user;
                            var token = jwt.encode({
                                iss: user.id,
                                exp: expires
                            }, app.get('jwtTokenSecret'));
                            req.access_token = token;

                            var data = {};
                            data.access_token = token;
                            data.expires = expires;
                            data.user = JSON.stringify(user);

                            req.headers['x-access-token'] = token;
                            res.json({
                                'data': data
                            });
                        }
                        next();
                    });

            } catch (err) {
                return next();
            }
        } else {

            // username (email oder nickname) 
            var un = req.body.un || res.end(400, 'login not found');
            // password im klartext
            var up = req.body.up || res.end(400, 'login not found');
            // password hashed
            /*
            bcrypt.genSalt(10, function(err, salt) {
                bcrypt.hash(up, salt, function(err, hash) {
                    console.log("x2", hash);
                });
            });
            */

            var name = 'email';
            //console.log("x2.2 ", un.indexOf('@'));
            // es sei denn, es wurde keine email angegeben, dann überprüfung nach nickname
            if (un.indexOf('@') === -1) name = 'nickname';

            app.db.model('Users').findOne()
                .where(name).equals(un)
                .where('password').equals(up)
                .exec(function(err, user) {
                    if (err || user == false) {
                        res.end(400, err);
                    } else {
                        req.user = user;
                        var token = jwt.encode({
                            iss: user.id,
                            exp: expires
                        }, app.get('jwtTokenSecret'));
                        req.access_token = token;
                        res.access_token = token;

                        var data = {};
                        data.access_token = token;
                        data.expires = expires;
                        data.user = JSON.stringify(user);

                        res.json({
                            'data': data
                        });
                    }
                });

        }
    });
};
