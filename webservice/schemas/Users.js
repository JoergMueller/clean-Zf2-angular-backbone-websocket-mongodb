// DEPENDENCIES
// ============
var mongoose = require('mongoose');



// {
//     "datecreate" : { type: Date, default: Date.now() },
//     "lastaction" : { type: Date, default: Date.now() },
//     "nickname" : { type: String, required: true, unique: true, index: true },
//     "email" : { type: String, required: true, unique: true, index: true },
//     "password" : { type: String, required: true },
//     "visible" : { type: Boolean },
//     "sheet" : { "gender" : "", "firstname" : "", "name" : "", "city" : "", "streetnr" : "", "zipcode" : "", "teaminfo" : "" },
//     "role" : { type: String },
//     "token" : { type: String },
//     "groups" : { type: Array }
// }


function Users(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId,
        DBRef = mongoose.SchemaTypes.DBRef;



    var Schema = new mongoose.Schema({
        datecreate: {
            type: Date,
            default: Date.now()
        },
        nickname: {
            type: String,
            required: true,
            unique: true,
            index: true
        },
        email: {
            type: String,
            required: true,
            unique: true,
            index: true
        },
        'token': {
            type: String,
            required: true,
            unique: true,
            index: true
        },
        password: String,
        role: String,
        lastaction: Date,
        parent: {
            type: Schema.Types.ObjectId,
            ref: 'Users',
            default: null,
            index: true
        },
        'geo': { type: [Number], index: "2d", default: null },
        'georesult': { type: Object, default: null },
        sheet: {
            "gender" : { type: String, default: "mrs"},
            "firstname" : { type: String },
            "name" : { type: String },
            "city" : { type: String },
            "streetnr" : { type: String },
            "zipcode" : { type: String },
            "country" : { type: Schema.Types.ObjectId, ref: 'Countries', default: ObjectId("56a42db251b5c64124f7d304") },
            "teaminfo" : { type: String }
        }
    }, {
        collection: 'Users'
    });

    Schema.virtual('name.fullName').get(function() {
        return [this.sheet.firstname, this.sheet.name].join(' ');
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Users', Schema);
};

module.exports = Users;

