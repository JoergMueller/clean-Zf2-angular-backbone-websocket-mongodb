// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Promotions(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
			'datecreate': {type: Date, required: true, 'default': Date, index: true},
			'from': { type: Date, required: true, 'default': Date, index: true },
			'until': { type: Date, 'default': null, index: true },
            'title': {type: Object, 'default': null, index: true},
			'content': {type: Object, 'default': null, index: true},                              // array from languages
            'qrcode': {
                'uri': '',
                'src': ''
            },
            'owner': {
                type: Schema.Types.ObjectId,
                ref: 'Users',
                default: null
            },
            'member': [{
                type: Schema.Types.ObjectId,
                ref: 'Clients',
                default: null
            }],
            'tracking': []
    }, {collection: 'Promotions'});

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Promotions', Schema);
};

module.exports = Promotions;
