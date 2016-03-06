// DEPENDENCIES
// ============
var mongoose = require('mongoose');

function Projects(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId;



    var Schema = new mongoose.Schema({
        datecreate: {
            type: Date,
            default: Date.now()
        },
        token: {
            type: String,
            required: true,
            unique: true,
            index: true
        },
        title: {
            type: String,
            required: true,
            unique: true,
            index: true
        },
        subline: {
            type: String,
            required: true,
            index: true
        },
        content: {
            type: String
        },
        images: {
            type: Array
        }

    }, {
        collection: 'Projects'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });

    app.db.model('Projects', Schema);
};

module.exports = Projects;
