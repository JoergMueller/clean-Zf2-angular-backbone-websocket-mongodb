'use strict';


var mongoose = require('mongoose'),
    _ = require('lodash'),
    ObjectId = mongoose.Schema.ObjectId;

module.exports = userAPI;

function userAPI(RouteAPI) {


    RouteAPI
        .all(function(req, res, next) {
            res.send('haha');
            next();
        })
        .post('/user/auth', isAuthenticated, function(req, res, next) {
            res.send('haha');
            next();
        })
        .post('/api/user', function(req, res, next) {
            res.send('haha');
            next();
        })
        .get('/api/user/:action', function(req, res, next) {
            res.send('haha');
            next();
        })
        .get('/api/users', function(req, res, next) {
            res.send('haha');
            next();
        })
        .delete('/api/user', function(req, res, next) {
            res.send('DELETE Flag found');
            next();
        });



    // route('/api/user')
    //  .all( app.isAuthenticated, function(req, res, next) {
    //      app.db.model('Users').find({},function(err, documents) {});
    //      next();
    //  })
    //  .get(function(req, res, next) {
    //      app.db.model('Users').find({},function(err, documents) {});
    //      next();
    //  })
    //  .post(function(req, res, next) {
    //      app.db.model('Users').find({},function(err, documents) {});
    //      next()
    //  })
    //  .delete(function(req, res, next) {

    //      next()
    //  });


}
