// DEPENDENCIES
// ============
var mongoose = require('mongoose');


mongoose.set('debug', true);

function Documents(app) {

    var Schema = mongoose.Schema,
        ObjectId = Schema.ObjectId,
        DBRef = mongoose.SchemaTypes.DBRef;



    var Schema = new mongoose.Schema({
        datecreate: {
            type: Date,
            default: Date.now()
        },
        datefrom: {
            type: Date,
            default: Date.now()
        },
        dateuntil: {
            type: Date,
            default: null
        },
        title: {
            type: Object,
            index: true
        },
        description: {
            type: Object,
            index: true
        },
        keywords: {
            type: Object,
            index: true
        },
        indexfollow: {
            type: Object,
            index: true,
            default: {
                'de': 'index,follow',
                'en': 'index,follow'
            }
        },
        inlanguage: {
            type: Array,
            index: true,
            default: ['de', 'en']
        },
        layout: {
            type: Object,
            default: {
                'de': '',
                'en': ''
            }
        },
        visible: {
            type: Boolean,
            default: false
        },
        parent: {
            type: Schema.Types.ObjectId,
            ref: 'Documents',
            default: null
        },
        path: {
            type: Object
        },
        children: {
            type: Array
        },
        'georeverse': { type: String, default: null },
        'geo': { type: [Number], index: "2d", default: null },
        'georesult': { type: Object, default: null }
    }, {
        collection: 'Documents'
    });

    Schema.virtual('id').get(function() {
        return this._id.$id;
    });
    Schema.methods.findChildren = function findChildren(cb) {
        return this.model('Documents').find({
            parent: this.id
        }, cb);
    };

    app.db.model('Documents', Schema);
};

module.exports = Documents;
