 define(['angularAMD'], function(app) {
	 'use strict'


	 function EventService($http, $rootScope, localStorageService, md5) {

		 var service = this;

		 service.listeners = service.listeners || [];
		 service.defaults = {
			 'debug': false,
			 'listener': service.listeners
		 };



		 service.getRandomNumber = function(range) {
			 return Math.floor(Math.random() * range);
		 }


		 service.getRandomChar = function() {
			 var chars = "0123456789abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
			 return chars.substr(this.getRandomNumber(62), 1);
		 }


		 service.getId = function(size) {
			 var i = 0,
				 str = '';
			 for (i = 0; i < (size || 10); i += 1) {
				 str += this.getRandomChar();
			 };
			 return str;
		 }


		 service.notify = function(event, data) {
			 data = data ? [data] : [];
			 if (service.defaults.debug) console.log("app-notify: " + event, data);
			 var n = 0;

			 try {
				 var _keys = Array.prototype.slice.call(Object.keys(service.defaults.listener));
			 } catch (_ex) {
				 var _keys = Array.prototype.slice.call([]);
			 }

			 if (_.contains(_keys, event)) {
				 for (var id in service.defaults.listener[event]) {
					 var l = service.defaults.listener[event][id];
					 try {
						 if (service.defaults.debug) console.log(l);

						 setTimeout((function(l, data) {
							 l.func.apply(l.context, data);
						 })(l, data), 100);

						 n++;
					 } catch (e) {
						 if ("console" in window && "error" in console) {}
					 }
				 }
			 }
			 return n;
		 }

		 service.addListener = function(event, func, context, id) {

			 context = context || window;
			 id = id || "listener_" + service.getId(16);
			 if (service.defaults.debug) console.log("app-event-id: " + id);

			 if (event && context && func) {
				 if (!service.defaults.listener[event]) {
					 service.defaults.listener[event] = {};
				 }
				 service.defaults.listener[event][id] = {
					 context: context,
					 func: func
				 };
				 if (service.defaults.debug) {
					 console.log("app-add: " + event, context, func, id);
					 console.info(service.defaults.listener);
				 }

				 return this;
			 }
			 return false;
		 }

		 service.remListener = function(event, id) {
			 if (event in service.defaults.listener) {
				 delete service.defaults.listener[event][id];
				 return true;
			 }
			 return false;
		 }


		 service.addListener("mc.doc.postDispatch", function(tpl){

			$('.panel-group .panel-collapse.in').prev().addClass('active').find('.panel-title').addClass('active');
			$('.panel-collapse')
			  .on('show.bs.collapse', function(e) {
					$(e.target).prev('.panel-heading').addClass('active').find('.panel-title').addClass('active');
			  })
			  .on('hide.bs.collapse', function(e) {
					$(e.target).prev('.panel-heading').addClass('active').find('.panel-title').addClass('active');
			});
		 });



		 return service;
	 }



	 // ===============================================================================


	 EventService.$inject = ['$http', '$rootScope', 'localStorageService', 'md5'];

	 app.factory('EventService', EventService);
	 return app;
 });
