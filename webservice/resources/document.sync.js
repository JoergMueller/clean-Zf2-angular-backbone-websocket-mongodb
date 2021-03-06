'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');


module.exports = DocumentsSync;


function DocumentsSync(app, io) {

    var validIndexes = {
        'datecreate': true,
        'datefrom': true,
        'dateuntil': true,
        'path': true,
        'parent': true,
        'visible': true,
        'layout': true,
        'title': true,
        'description': true,
        'keywords': true,
        'indexfollow': true,
        'georeverse': true,
        'geo': true,
        'georesult': true
    };

    io.sockets.on('connection', function(socket) {

        socket.on('documents:read', function(data, callback) {
            var attributes = data;

            var criteria = {};
            _.filter(attributes, function(v, i) {
                if (validIndexes[i]) criteria[i] = v;
            })

            app.db.model('Documents').find(criteria)
                .exec(function(err, docs) {
                    if (err) {
                        callback(err);
                        return;
                    }
                    callback(null, docs);
                });
        });

        socket.on('documents:update', function(data, callback) {
            var attributes = data.attrs;

            app.db.model('Documents').findById(attributes, function(err, doc) {

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

        socket.on('documents:create', function(data, callback) {
            var attributes = data.attrs;

            var doc = app.db.model('Documents');
            doc = new doc(attributes);
            doc.save();
        });

    });
}
