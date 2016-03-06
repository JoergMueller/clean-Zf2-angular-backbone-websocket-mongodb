// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Countries(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
            'iso': String,
            'title': {},
    }, {
        collection: 'Countries'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Countries', Schema);
};

module.exports = Countries;
