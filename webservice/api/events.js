'use strict';


var mongoose = require('mongoose'),
    _ = require('lodash')
    , ObjectId = mongoose.Schema.ObjectId;

module.exports = eventAPI;

function eventAPI(RouteAPI) {


	RouteAPI
		.all(function(req, res, next) {
			res.send('haha');
			next();
		})
		.post('/api/event', function(req, res, next) {
			res.send('haha');
			next();
		})
		.get('/api/event', function(req, res, next) {
			res.send('haha');
			next();
		})
		.get('/api/event', function(req, res, next) {
			res.send('haha');
			next();
		})
		.delete('/api/event', function(req, res, next) {
			res.send('DELETE Flag found');
			next();
		});



	// route('/api/event')
	// 	.all( app.isAuthenticated, function(app, passport, req, res, next) {
	// 		app.db.model('Users').find({},function(err, documents) {});
	// 		next();
	// 	})
	// 	.get(function(req, res, next) {
	// 		app.db.model('Users').find({},function(err, documents) {});
	// 		next();
	// 	})
	// 	.post(function(req, res, next) {
	// 		app.db.model('Users').find({},function(err, documents) {});
	// 		next()
	// 	})
	// 	.delete(function(req, res, next) {

	// 		next()
	// 	});

}
