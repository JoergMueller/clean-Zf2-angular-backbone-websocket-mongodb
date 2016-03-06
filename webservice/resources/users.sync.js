'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');


module.exports = UsersSync;


function UsersSync(app, io) {

    var validIndexes = {
        'id': true,
        '_id': true,
        'datecreate': true,
        'nickname': true,
        'password': true,
        'role': true,
        'email': true,
        'geo': true,
        'georesult': true,
        'sheet': true,
        'token': true
    };

    io.sockets.on('connection', function(socket) {

        socket.on('users:read', function(data, callback) {
            var attributes = data;

            var criteria = {};
            _.filter(attributes, function(v, i) {
                if (validIndexes[i]) criteria[i] = v;
            })

            app.db.model('Users').find(criteria)
                .exec(function(err, docs) {
                    if (err) {
                        callback(err);
                    }
                    callback(null, docs);
                });
        });

        socket.on('users:update', function(data, callback) {
            var attributes = data.attrs;

            _.filter(attributes, function(v, i) {
                if (_.isUndefined(validIndexes[i])) delete attributes[i];
            });
            if (!_.isUndefined(attributes['parent']) && !_.isNull(attributes['parent']) && attributes['parent'].length<=0) {
                attributes['parent'] = null;
            }

            app.db.model('Users').findById(attributes, function(err, doc) {

                if (doc) {
                    _.forEach(attributes, function(n, key) {
                        try {

                            if('sheet' === key) {
                                _.each(n, function(_n, _k) {
                                    doc['sheet'][_k] = _n;
                                });
                            }
                            else
                                doc[key] = n;

                        } catch (ex) {
                            console.log('EX: ~~~');
                            console.log(ex);
                            console.log('END: ~~~');
                        }
                    });

                    doc.save(function(err) {
                        if (err) {
                            console.log('ERR: ~~~');
                            console.log(err);
                            console.log('END: ~~~');
                        } else {
                            console.log('SAVED CREATE: ~~~');
                            console.log(doc);
                            callback(null, doc);
                        }

                    });

                }

            });
        });

        socket.on('users:create', function(data, callback) {
            var attributes = data.attrs;

            var doc = app.db.model('Users');
            doc = new doc(attributes);
            doc.save(function(err) {
                if (err) {
                    console.log('ERR: ~~~');
                    console.log(err);
                    console.log('END: ~~~');

                    callback(err);
                }
                else callback(null, doc);
            });
        });

        socket.on('users:delete', function(data, callback) {
            var attributes = data.attrs;

            app.db.model('Users').findById(data, function(err, doc) {

                if (err) {
                    console.log('ERR: ~~~');
                    console.log(err);
                    console.log('END: ~~~');
                }

                if(doc) {
                    doc.remove();
                    callback(null, doc);
                }

            });
        });

    });
}
