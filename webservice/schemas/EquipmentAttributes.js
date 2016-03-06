// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function EquipmentAttributes(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
        datecreate: {
            type: Date,
            default: Date.now()
        },
        title: {},
        attributes: [],
    }, {
        collection: 'EquipmentAttributes'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('EquipmentAttributes', Schema);

};

module.exports = EquipmentAttributes;
