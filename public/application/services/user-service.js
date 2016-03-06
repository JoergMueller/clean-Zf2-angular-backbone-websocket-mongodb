 define(['angularAMD'], function(app) {
	'use strict'


	function UserService($http, $rootScope, localStorageService, md5) {


		var service = this;
			service.currentUser = null;
			service.currentToken = null;

		service.socket = 'http://' + location.hostname + ':3131';


		$rootScope.$on('authorized', function() {
			window.SessionUser = $rootScope.SessionUser = service.getCurrentUser();
		});


		$rootScope.$on('unauthorized', function() {
			window.SessionUser = service.setCurrentUser(null);
		});


		service.getGroup = function() {
			return !_.isUndefined(service.currentUser) && !_.isUndefined(service.currentUser.role) ? service.currentUser.role : 'guest';
		}


		service.isInBitGroup = function(role, needle) {
			if(_.isUndefined(role) || _.isEmpty(role)) role = 'guest';
			if(_.isUndefined(needle) || _.isEmpty(needle)) role = 'guest';
			var roleNum = Data.Roles['USR_' + role.toUpperCase()];
			var needleNum = Data.Roles['GRP_' + needle.toUpperCase()];

			return (parseInt(roleNum) & parseInt(needleNum)) == 0 ? false : true;
		}


		service.setCurrentUser = function(user) {
			service.currentUser = user;
			localStorageService.set('user', user);
			return service.currentUser;
		};


		service.getCurrentUser = function() {

			if(_.isNull(service.currentUser)) {
				service.currentUser = localStorageService.get('user');

				if(_.isNull(service.currentUser)) {
					$http.get(service.socket + '/api/user').success(function(r){
						console.debug('########### get api user', r);
					});
				}
			}
			return !_.isObject(service.currentUser) ? JSON.parse(service.currentUser) : service.currentUser;
		};


		service.setCurrentToken = function(token) {
			service.currentToken = token;
			localStorageService.set('token', token);
			return service.currentToken;
		};


		service.getCurrentToken = function() {

			if(!service.currentToken) {
				service.currentToken = localStorageService.get('token');
			}
			return service.currentToken;
		};


		service.login = function(loginData) {

			$http.post(service.socket + '/user/auth', loginData)
				.success(function(r) {
					if(r.data.access_token) {
						service.currentUser = JSON.parse(r.data.user);
						service.currentUser.access_token = r.data.access_token;

						service.setCurrentUser(r.data.user);
						service.setCurrentToken(r.data.access_token);

						$rootScope.$broadcast('authorized');
						$rootScope.$broadcast('islogedin');
					}
					else {
						service.setCurrentUser(null);
						service.setCurrentToken(null);

						$rootScope.$broadcast('unauthorized');
					}
				});
		};


		service.isLoggedIn = function() {
			var u = service.getCurrentUser();
			if(_.isNull(u)) {
				u = {
					'nickname': 'Guest',
					'role': 'guest'
				}
			}
			return service.isInBitGroup(u.role, 'user');
		};


		return _.extend({
			getRandomNumber: function(range) {
				return Math.floor(Math.random() * range);
			},
			getRandomChar: function() {
				var chars = "0123456789abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
				return chars.substr(this.getRandomNumber(62), 1);
			},
			getId: function(size) {
				var i = 0,
					str = '';
				for(i = 0; i < (size || 10); i += 1) {
					str += this.getRandomChar();
				};
				return str;
			},
			getUUID: function() {
				var d = new Date().getTime(),
					uuid;
				uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
					var r = (d + Math.random() * 16) % 16 | 0;
					d = Math.floor(d / 16);
					return(c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
				});
				return uuid;
			},
			getToken: function(c) {
				if (typeof c === (typeof undefined)) c = 10;
				var d = new Date().getTime(),
					token;
				var range = [].range('x', c);
				token = range.replace(/[xy]/g, function(c) {
					var r = (d + Math.random() * 16) % 16 | 0;
					d = Math.floor(d / 16);
					return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
				});
				return token;
			}
		}, service);


	};

	// ===============================================================================


	UserService.$inject = ['$http','$rootScope','localStorageService','md5'];

	app.factory('UserService', UserService);
	return app;
});
