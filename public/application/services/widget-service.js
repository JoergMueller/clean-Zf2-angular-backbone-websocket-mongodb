 define(['angularAMD'], function(app) {
	'use strict'


	function WidgetService($http, $rootScope, localStorageService, md5) {

		var service = this;







		return service;
	}



	// ===============================================================================


	WidgetService.$inject = ['$http','$rootScope','localStorageService','md5'];

	app.factory('WidgetService', WidgetService);
	return app;
});