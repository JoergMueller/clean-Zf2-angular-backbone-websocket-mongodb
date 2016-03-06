'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');



module.exports = ClientsSync;


function ClientsSync(app, io) {

    var validIndexes = {
        id: true,
        _id: true,
        geo: true,
        georesult: true,
        datecreate: true,
        country: true,
        parent: true,
        name: true,
        firm : true,
        zipcode : true,
        streetnr : true,
        city : true,
        phone : true,
        mail : true,
        layout : true,
        token : true,
        shortname : true,
        owner: true,
        members: true
    };

    io.sockets.on('connection', function(socket) {

        socket.on('clients:read', function(data, callback) {
            var attributes = data;

            var criteria = {};
            _.filter(attributes, function(v, i) {
                if (validIndexes[i]) criteria[i] = v;
            })

            app.db.model('Clients').find(criteria)
                .exec(function(err, docs) {
                    if (err) {
                        callback(err);
                        return;
                    }
                    callback(null, docs);
                });
        });

        socket.on('clients:update', function(data, callback) {
            var attributes = data.attrs;

            _.filter(attributes, function(v, i) {
                if (_.isUndefined(validIndexes[i])) delete attributes[i];
            });
            if (!_.isUndefined(attributes['parent']) && !_.isNull(attributes['parent']) && attributes['parent'].length<=0) {
                attributes['parent'] = null;
            }


            app.db.model('Clients').findById(attributes, function(err, doc) {

                if (err) {
                    console.log('ERR: ~~~');
                    console.log(err);
                    console.log('END: ~~~');
                    callback(err);
                }

                if (doc) {

                    _.forEach(attributes, function(n, key) {
                        try {

                            doc[key] = n;

                        } catch (ex) {
                            console.log('EX: ~~~ 80');
                            console.log(ex);
                            console.log('END: ~~~');
                            callback(ex);
                        }
                    });

                    doc.save(function(err) {
                        if (err) {
                            console.log('ERR: ~~~ 88');
                            console.log(err);
                            console.log('END: ~~~');
                            callback(err);
                        } else {
                            console.log('SAVED CREATE: ~~~');
                            console.log(doc);
                            callback(null, doc);
                        }

                    });

                }

            });
        });

        socket.on('clients:create', function(data, callback) {
            var attributes = data.attrs;

            var doc = app.db.model('Clients');
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

        socket.on('clients:delete', function(data, callback) {

            app.db.model('Clients').findById(data, function(err, doc) {

                if (err) {
                    console.log('ERR: ~~~');
                    console.log(err);
                    console.log('END: ~~~');
                    callback(err);
                }

                if(doc) {
                    doc.remove();
                    callback(null, doc);
                }

            });
        });

    });
}
