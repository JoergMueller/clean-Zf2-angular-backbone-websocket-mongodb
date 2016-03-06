'use strict';

var _ = require('lodash'),
    mongoose = require('mongoose'),
    Schema = mongoose.Schema,
    ObjectId = Schema.ObjectId,
    md5 = require('md5');


module.exports = DocumentsIO;


function DocumentsIO(app, io) {

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
        'indexfollow': true
    };

    io.on('connection', function(socket) {

    })
}
