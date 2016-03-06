'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');


module.exports = CountriesSync;


function CountriesSync(app, io) {

    var validIndexes = {
        iso: true,
        title: true
    };

    io.sockets.on('connection', function(socket) {

        socket.on('countries:read', function(data, callback) {
            var attributes = data;

            var criteria = {};
            _.filter(attributes, function(v, i) {
                if (validIndexes[i]) criteria[i] = v;
            })

            app.db.model('Countries').find(criteria)
                .exec(function(err, docs) {
                    if (err) {
                        callback(err);
                        return;
                    }
                    console.log(docs);
                    callback(null, docs);
                });
        });

        socket.on('countries:update', function(data, callback) {
            var attributes = data.attrs;

            app.db.model('Countries').findById(attributes, function(err, doc) {

                if (doc) {
                    _.forEach(attributes, function(n, key) {
                        try {
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

        socket.on('countries:create', function(data, callback) {
            var attributes = data.attrs;

            var doc = app.db.model('Countries');
            doc = new doc(attributes);
            doc.save();
            callback(null, doc);    
        });

    });
}
