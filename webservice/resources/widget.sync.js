'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId;


module.exports = WidgetsSync;


function WidgetsSync(app, io) {

    var validIndexes = ['datecreate', 'parent', 'anker', 'attributes', 'type'];

    io.sockets.on('connection', function(socket) {

        socket.on('widgets:read', function(data, callback) {
            var attributes = data.attrs;

            var criteria = {};
            for (var i in data) {
                if (_.indexOf(validIndexes, i)) {
                    criteria[i] = data[i];
                }
            }
            app.db.model('Widgets').find()
                .exec(function(err, docs) {

                    if (err) {
                        callback(err);
                    }
                    callback(null, docs);
                });
        });

        socket.on('widgets:update', function(data, callback) {
            var attributes = data.attrs;

            app.db.model('Widgets').findById(attributes.id, function(err, doc) {

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

        socket.on('widgets:create', function(data, callback) {
            var attributes = data.attrs;

            var _widget = app.db.model('Widgets');
            _widget = new _widget(attributes);
            _widget.save();
        });

    });

}
