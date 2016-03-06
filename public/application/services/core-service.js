 define(['angularAMD'], function(app) {
	'use strict'


	function CoreService($http, $rootScope, localStorageService, md5) {

		var service = this;

		service.init = function() {
			
		}





		return service;
	}



	// ===============================================================================


	CoreService.$inject = ['$http','$rootScope','localStorageService','md5'];

	app.factory('CoreService', CoreService);
	return app;
});