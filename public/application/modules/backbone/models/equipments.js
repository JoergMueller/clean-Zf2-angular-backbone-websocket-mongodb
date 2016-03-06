var Equipment = Backbone.Model.extend({
    urlRoot: 'equipments',
    noIoBind: false,
    socket: window.socket,
    save: function(attrs, options) {
        options = options || {};
        attrs = _.extend({}, _.clone(this.attributes), attrs);

        // Filter the data to send to the server
        attrs.id = attrs._id;
        delete attrs._id;
        options.attrs = attrs;
        // Proxy the call to the original save function
        return Backbone.Model.prototype.save.call(this, attrs, options);
    },
    initialize: function(opt) {

        _.each(opt, function(value, key) {
            Object.defineProperty(this, key, {
                get: function() {
                    return this.get(key);
                },
                set: function(value) {
                    this.set(key, value);
                },
                enumerable: true,
                configurable: true
            });
        }, this);

        _.bindAll(this, 'serverChange', 'serverDelete', 'modelCleanup');

        /*!
         * if we are creating a new model to push to the server we don't want
         * to iobind as we only bind new models from the server. This is because
         * the server assigns the id.
         */
        if (!this.noIoBind) {
            this.ioBind('update', this.serverChange, this);
            this.ioBind('delete', this.serverDelete, this);
        }
    },
    serverChange: function(data) {
        // Useful to prevent loops when dealing with client-side updates (ie: forms).
        data.fromServer = true;
        this.set(data);
    },
    serverDelete: function(data) {
        if (this.collection) {
            this.collection.remove(this);
        } else {
            this.trigger('remove', this);
        }
        this.modelCleanup();
    },
    modelCleanup: function() {
        this.ioUnbindAll();
        return this;
    }
});

var Equipments = Backbone.QueryCollection.extend({
    model: Equipment,
    url: 'equipments',
    socket: window.socket,
    initialize: function() {
        _.bindAll(this, 'serverCreate', 'collectionCleanup', 'search');
        this.ioBind('create', this.serverCreate, this);
        this.bind("change", this.reset_query_cache, this);
    },
    search: function(letters) {
        if (letters === "") return this;

        var pattern = new RegExp(letters, "gi");
        return _(this.filter(function(data) {
            return pattern.test(data.get("title")) ||
                pattern.test(data.get("description")) ||
                pattern.test(data.get("place")) ||
                pattern.test(data.get("placename"));
        }));
    },
    serverCreate: function(data) {
        // make sure no duplicates, just in case
        var exists = this.get(data.id);
        if (!exists) {
            this.add(data);
        } else {
            data.fromServer = true;
            exists.set(data);
        }
    },
    collectionCleanup: function(callback) {
        this.ioUnbindAll();
        this.each(function(model) {
            model.modelCleanup();
        });
        return this;
    }
});

Models.Equipments = new Equipments();