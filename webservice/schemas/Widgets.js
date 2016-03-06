// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Widgets(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId,
        DBRef = mongoose.SchemaTypes.DBRef;

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
        type: {
            type: String,
            index: true
        },
        parent: {
            type: Schema.Types.ObjectId,
            ref: 'Documents'
        },
        anker: {
            type: String,
            required: true,
            index: true
        },
        attributes: {
            type: Object
        },
        'georeverse': { type: String, default: null },
        'geo': { type: [Number], index: "2d", default: null },
        'georesult': { type: Object, default: null }
    }, {
        collection: 'Widgets'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Widgets', Schema);

};

module.exports = Widgets;
