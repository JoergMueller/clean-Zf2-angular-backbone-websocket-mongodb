// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Messages(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
			'datecreate': {type: Date, required: true, 'default': Date, index: true},
			'dateread': {type: Date },
			'from': { type: Schema.Types.ObjectId, ref: 'Users', index: true },
			'to': [{ type: Schema.Types.ObjectId, ref: 'Users', index: true }],
			'message': String
    }, {collection: 'Messages'});

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Messages', Schema);
};

module.exports = Messages;
