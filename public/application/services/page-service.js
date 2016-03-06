 define(['angularAMD'], function(app) {
	'use strict'


	function PageService($http, $rootScope, localStorageService, md5) {

		var service = this;







		return service;
	}



	// ===============================================================================


	PageService.$inject = ['$http','$rootScope','localStorageService','md5'];

	app.factory('PageService', PageService);
	return app;
});