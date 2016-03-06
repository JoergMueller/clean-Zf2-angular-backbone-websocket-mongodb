// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Notes(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
        datecreate: {
            type: Date,
            default: Date.now()
        },
        datefrom: {
            type: Date,
            default: Date.now(),
            index: true
        },
        dateuntil: {
            type: Date,
            default: null,
            index: true
        },
        owner: {
            type: Schema.Types.ObjectId,
            ref: 'Users'
        },
        georeverse: { type: String, default: null },
        geo: { type: [Number], index: "2d", default: null },
        priority: {
            type: String,
            index: true,
            default: 'normal'
        },
        content: {
            type: String
        }
    }, {
        collection: 'Notes'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Notes', Schema);

};

module.exports = Notes;
