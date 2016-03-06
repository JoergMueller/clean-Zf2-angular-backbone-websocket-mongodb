'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');



module.exports = NotesSync;


function NotesSync(app, io) {

    var validIndexes = {
        _id: true,
        datecreate:true,
        datefrom:true,
        dateuntil:true,
        owner:true,
        member:true,
        priority: true,
        content:true
    };

    io.sockets.on('connection', function(socket) {

        socket.on('notes:read', function(data, callback) {
            var attributes = data;

            var criteria = {};
            _.filter(attributes, function(v, i) {
                if (validIndexes[i]) criteria[i] = v;
            })

            app.db.model('Notes').find(criteria)
                .populate('owner')
                .exec(function(err, docs) {
                    if (err) {
                        callback(err);
                        return;
                    }
                    callback(null, docs);
                });
        });

        socket.on('notes:update', function(data, callback) {
            var attributes = data.attrs;

            _.filter(attributes, function(v, i) {
                if (_.isUndefined(validIndexes[i])) delete attributes[i];
            });


            app.db.model('Notes').findById(attributes, function(err, doc) {

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

        socket.on('notes:create', function(data, callback) {
            var attributes = data.attrs;

            var doc = app.db.model('Notes');
            doc = new doc(attributes);
            doc.save();
            callback(null, doc);    
        });

        socket.on('notes:delete', function(data, callback) {
            var attributes = data.attrs;

            if (err) {
                console.log('ERR: ~~~');
                console.log(err);
                console.log('END: ~~~');
            }

            app.db.model('Notes').findById(attributes, function(err, doc) {
                if(doc) {
                    doc.remove();
                    callback(null, doc);
                }

            });
        });

    });
}
