'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');



module.exports = EquipmentsSync;


function EquipmentsSync(app, io) {

    var validIndexes = {
        '_id': true,
        'datecreate': true,
        'headline': true,
        'description': true,
        'ishidden': true,
        'validfrom': true,
        'validuntil': true,
        'geo': true,
        'georesult': true,
        'georeverse': true,
        'attributes': true
    };

    io.sockets.on('connection', function(socket) {

        socket.on('equipments:read', function(data, callback) {
            var attributes = data;

            var criteria = {};
            _.filter(attributes, function(v, i) {
                if (validIndexes[i]) criteria[i] = v;
            })

            app.db.model('Equipments').find(criteria)
                .populate('owner')
                .exec(function(err, docs) {
                    if (err) {
                        callback(err);
                        return;
                    }
                    callback(null, docs);
                });
        });

        socket.on('equipments:update', function(data, callback) {
            var attributes = data.attrs;

            _.filter(attributes, function(v, i) {
                if (_.isUndefined(validIndexes[i])) delete attributes[i];
            });


            app.db.model('Equipments').findById(attributes, function(err, doc) {

                if (err) {
                    console.log('ERR: ~~~');
                    console.log(err);
                    console.log('END: ~~~');
                }

                if (doc) {

                    _.forEach(attributes, function(n, key) {
                        try {

                            doc[key] = n;

                        } catch (ex) {
                            console.log('EX: ~~~ 80');
                            console.log(ex);
                            console.log('END: ~~~');
                        }
                    });

                    console.log(doc);

                    doc.save(function(err) {
                        if (err) {
                            console.log('ERR: ~~~ 88');
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

        socket.on('equipments:create', function(data, callback) {
            var attributes = data.attrs;

            var doc = app.db.model('Equipments');
            doc = new doc(attributes);
            doc.save();
            callback(null, doc);    
        });

        socket.on('equipments:delete', function(data, callback) {
            var attributes = data.attrs;

            if (err) {
                console.log('ERR: ~~~');
                console.log(err);
                console.log('END: ~~~');
            }

            app.db.model('Equipments').findById(attributes, function(err, doc) {
                if(doc) {
                    doc.remove();
                    callback(null, doc);
                }

            });
        });

    });
}
