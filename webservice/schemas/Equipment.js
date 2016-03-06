// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Equipments(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
            'datecreate': {
                type: Date,
                default: Date.now()
            },
            'headline': [{}],
            'description': [{}],
            'ishidden': {
                type: Boolean,
                default: true,
                index:  true
            },
            'validfrom': {
                type: Date,
                default: Date.now(),
                index: true
            },
            'geo': { type: [Number], index: "2d", default: null },
            'georesult': { type: Object, default: null },
            'georeverse': { type: String, default: null },
            'validuntil': {
                type: Date,
                default: null,
                index: true
            },
            'attributes': {}
    }, {
        collection: 'Equipments'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Equipments', Schema);

};

module.exports = Equipments;
