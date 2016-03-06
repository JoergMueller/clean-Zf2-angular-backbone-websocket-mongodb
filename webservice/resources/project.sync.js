'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');


module.exports = ProjectsSync;


function ProjectsSync(app, io) {

    var validIndexes = {
        'datecreate': true,
        'title': true,
        'subline': true,
        'content': true,
        'images': true,
        'token': true
    };

    io.sockets.on('connection', function(socket) {

        socket.on('projects:read', function(data, callback) {
            var attributes = data;

            var criteria = {};
            _.filter(attributes, function(v, i) {
                if (validIndexes[i]) criteria[i] = v;
            })

            app.db.model('Projects').find(criteria)
                .exec(function(err, docs) {
                    if (err) {
                        callback(err);
                    }
                    callback(null, docs);
                });
        });

        socket.on('projects:update', function(data, callback) {
            var attributes = data.attrs;

            app.db.model('Projects').findById(attributes, function(err, doc) {

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

        socket.on('projects:create', function(data, callback) {
            var attributes = data.attrs;

            var model = app.db.model('Projects');
            var project = new model(attributes);
            project.save(function(e) {
                if (!e) {
                    callback(null, project);
                } else {
                    console.log(e);
                }
            });
        });

    });
}
