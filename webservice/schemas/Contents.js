// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Contents(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
            'datecreate': {type: Date, required: true, 'default': Date, index: true},
            'from': { type: Date, required: true, 'default': Date, index: true },
            'until': { type: Date, 'default': null, index: true },
            'content': [],                                                                       // array from languages 
            'clients': [{ type: Schema.Types.ObjectId, ref: 'Clients', index: true }],
            'georeverse': { type: String, default: null },
            'geo': { type: [Number], index: "2d", default: null },
            'georesult': { type: Object, default: null },
            'tracking': []
    }, {
        collection: 'Contents'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Contents', Schema);
};

module.exports = Contents;
