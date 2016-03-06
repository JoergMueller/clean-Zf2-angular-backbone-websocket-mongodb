// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Tasks(app) {

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
        member: [{
            type: Schema.Types.ObjectId,
            ref: 'Users'
        }],
        priority: {
            type: String,
            index: true
        },
        content: {
            type: String
        },
        geo: { type: [Number], index: "2d", default: null }
    }, {
        collection: 'Tasks'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Tasks', Schema);

};

module.exports = Tasks;
