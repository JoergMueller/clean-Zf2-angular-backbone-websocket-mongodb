'use strict';

var _ = require('lodash'),
	mongoose = require('mongoose'),
	Schema = mongoose.Schema,
	ObjectId = Schema.ObjectId,
	md5 = require('md5');


module.exports = PromotionsSync;


function PromotionsSync(app, io) {

	var validIndexes = {
			'datecreate': true,
			'from': true,
			'until': true,
			'title': true,
			'content': true,
			'owner': true,
			'members': true,
			'tracking': true
	};

	io.sockets.on('connection', function(socket) {

		socket.on('promotions:read', function(data, callback) {
			var attributes = data;

			var criteria = {};
			_.filter(attributes, function(v, i) {
				if (validIndexes[i]) criteria[i] = v;
			})

			app.db.model('Promotions').find(criteria)
				.exec(function(err, docs) {
					if (err) {
						callback(err);
						return;
					}
					callback(null, docs);
				});
		});

		socket.on('promotions:update', function(data, callback) {
			var attributes = data.attrs;

			app.db.model('Promotions').findById(attributes, function(err, doc) {

				if (doc) {
					_.forEach(attributes, function(n, key) {
						try {
							doc[key] = n;

						} catch (ex) {
							console.log('EX: ~~~');
							console.log(ex);
							console.log('END: ~~~');
						}
					});

					doc.save(function(err) {
						if (err) {
							console.log('ERR: ~~~');
							console.log(err);
							console.log('END: ~~~');
							callback(null, doc);
						} else {
							console.log('SAVED CREATE: ~~~');
							console.log(doc);
							callback(doc);
						}

					});

				}

			});
		});

		socket.on('promotions:create', function(data, callback) {
			var attributes = data.attrs;

			var doc = app.db.model('Promotions');
			doc = new doc(attributes);
			doc.save(function(err){
				if(err) {
					console.log('ERR: ~~~');
					console.log(err);
					console.log('END: ~~~');
					callback(null, err);
				}
				else {
					callback(doc);
				}
			});
		});

	});
}
