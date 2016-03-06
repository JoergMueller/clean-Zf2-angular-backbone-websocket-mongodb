// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Clients(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;

    var Schema = new mongoose.Schema({
        datecreate: {
            type: Date,
            default: Date.now()
        },
        country: {
            type: Schema.Types.ObjectId,
            ref: 'Countries',
            default: null
        },
        parent: {
            type: Schema.Types.ObjectId,
            ref: 'Clients',
            default: null
        },
        name: {
            type: String,
            index: true
        },
        firm : {
            type: String,
            index: true
        },
        zipcode : {
            type: String,
            index: true
        },
        streetnr : {
            type: String,
            index: true
        },
        city : {
            type: String,
            index: true
        },
        geo: { type: [Number], index: "2d", default: null },
        georesult: { type: Object, default: null },
        phone : {
            type: String,
            index: true
        },
        mail : {
            type: String,
            index: true
        },
        layout : {},
        'token': {
            type: String,
            required: true,
            unique: true,
            index: true
        },
        shortname : {
            type: String,
            index: true
        },
        owner: {
            type: Schema.Types.ObjectId,
            ref: 'Users',
            default: null
        },
        members: [{
            type: Schema.Types.ObjectId,
            ref: 'Users',
            default: null
        }]
    }, {
        collection: 'Clients'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });


    app.db.model('Clients', Schema);

};

module.exports = Clients;
