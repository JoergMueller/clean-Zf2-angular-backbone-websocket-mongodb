"use strict";


var $lang, app,
	languages = {
		'default': 'de',
		'list': {
			'de': {
				'short': 'de',
				'locale': 'de_DE',
				'name': 'Deutsch',
				'timezone': 'Europe/Berlin',
				'datefmt': 'dd.MM.yyyy HH:mm'
			},
			'en': {
				'short': 'en',
				'locale': 'en_US',
				'name': 'English',
				'timezone': 'UTC',
				'datefmt': 'd MMM  yyyy h:mma'
			}
		}
	};

require([
	'angularAMD',
	'LocalStorageModule',
	'uiRouter',
	'uiBootstrap',
	'angularTranslate',
	'angularTranslateLoader',
	'ngStrap',
	'angularMoment',
	'stateHelper',
	'ngMd5',
	'jquery',
	'underscore',
	'jqForm',
	'jqTags',
	'jqFancybox',
	'jqBrowser',
	'smoothScroll',
	'jqMPopup',
	'services/service-loader',
	'modules/controller-loader',
], function(angularAMD, controllers) {

	console.debug("inside application", Date());

	app = angular.module("mcApp", ['LocalStorageModule', 'ui.router', 'ui.router.stateHelper', 'pascalprecht.translate', 'mgcrea.ngStrap', 'angularMoment', 'ngMd5']);
	angular.module("mcApp");
	app.config(function(localStorageServiceProvider, $urlRouterProvider, stateHelperProvider, $translateProvider, $modalProvider, $httpProvider, $locationProvider) {


			$locationProvider.html5Mode({
				enabled: true,
				requireBase: false
			});


			$translateProvider.useSanitizeValueStrategy('escaped');
			$translateProvider.useStaticFilesLoader({
				prefix: 'application/language/lang-',
				suffix: '.json'
			});
			$translateProvider.preferredLanguage(languages['default']);

			var _splitted = location.host.split('.');
			var _subdomain = _splitted.shift();

			localStorageServiceProvider
				.setPrefix(_subdomain)
				.setStorageType('sessionStorage')
				.setStorageCookie(45, '/')
				.setStorageCookieDomain(_subdomain)
				.setNotify(true, true);

			stateHelperProvider.state({
				name: 'Root',
				url: '/',
				controller: 'MainCtrl',
				views: {},
				children: [{
					name: 'Service',
					url: 'service',
					views: {},
					children: [{
						name: 'User',
						url: '/user',
						children: [{
							name: 'New',
							url: '/new',
							views: {
								'f@': {
									templateUrl: '/application/modules/user/views/new.html',
									controller: 'UserCtrl',
								}
							},
							children: []
						}, {
							name: 'Edit',
							url: '/edit',
							views: {
								'f@': {
									templateUrl: '/application/modules/user/views/edit.html',
									controller: 'UserCtrl',
								}
							},
							children: []
						}, {
							name: 'My',
							url: '/my',
							views: {
								'f@': {
									templateUrl: '/application/modules/user/views/my.html',
									controller: 'UserCtrl',
								}
							},
							children: []
						}, {
							name: 'Forgot',
							url: '/forgot',
							views: {
								'f@': {
									templateUrl: '/application/modules/user/views/my.html',
									controller: 'UserCtrl',
								}
							},
							children: []
						}]
					}]
				}, {
					name: 'EquipmentNew',
					url: ':lang/baumaschinen/:acategory/:bcategory',
					resolve: {
						lang: function($state, $stateParams) {
							return $stateParams.lang || 'de';
						},						
						acategory: function($state, $stateParams) {
							return $stateParams.acategory || '';
						},
						bcategory: function($state, $stateParams) {
							return $stateParams.bcategory || '';
						}
					},
					views: {
						'f@': {
							templateUrl: '/application/modules/equipment/views/dashboard.html',
							controller: 'EquipmentCtrl',
						}
					},
					children: [{
						name: 'Create',
						url: '/:action',
						views: {
							'f@': {
								templateUrl: '/application/modules/equipment/views/dashboard.html',
								controller: 'EquipmentCtrl',
							}
						},
						children: []
					}]
				},{
					name: 'EquipmentUsed',
					url: ':lang/gebrauchtmaschinen/:acategory',
					resolve: {
						lang: function($state, $stateParams) {
							return $stateParams.lang || 'de';
						},						
						acategory: function($state, $stateParams) {
							return $stateParams.acategory || '';
						},
						bcategory: function($state, $stateParams) {
							return $stateParams.bcategory || '';
						}
					},
					views: {
						'f@': {
							templateUrl: '/application/modules/equipment/views/dashboard.html',
							controller: 'EquipmentCtrl',
						}
					},
					children: [{
						name: 'Create',
						url: '/:action',
						views: {
							'f@': {
								templateUrl: '/application/modules/equipment/views/dashboard.html',
								controller: 'EquipmentCtrl',
							}
						},
						children: []
					}]
				}]
			});

			$urlRouterProvider.otherwise(function($a, $b, $c, $d){
				window.location.href = $b.$$path;
			})

			$httpProvider.defaults.useXDomain = true;
			delete $httpProvider.defaults.headers.common['X-Requested-With'];
			// $httpProvider.interceptors.push('APIInterceptor');

		})
		.run(function($rootScope, $window, localStorageService, amMoment, $modal, md5, $templateCache,
						UserService, EventService, PageService) {


			window.UserService = UserService;
			window.EventService = EventService;
			window.PageService = PageService;

			$rootScope.md5 = md5;
			$rootScope.modal = $modal;

			window.UserService = $rootScope.UserService = UserService;

			$rootScope.$lang = languages['default'];
			amMoment.changeLocale($rootScope.$lang);

			if (localStorageService.isSupported) {
				var _loggedin = UserService.getCurrentUser();
				if (false === _loggedin) {
					_loggedin = {
						'nickname': 'Guest',
						'role': 'guest',
						'token': UserService.getId(16)
					};
				}
				$rootScope.SessionUser = $window.SessionUser = _loggedin;
			}

			// optical helper
			$rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {});
			$rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {});
		});

	app.filter('urlvalide', function() {
		return function(str) {
			return (str || '').replace(/ /gi, '+')
				.replace(/(ö|Ö)/gi, 'oe')
				.replace(/(ä|Ä)/gi, 'ae')
				.replace(/(ü|Ü)/gi, 'ue')
				.replace(/\W/gi, '+')
				.replace(/(\+){1,}/gi, '+')
				.toLowerCase();
		}
	});

	app.filter('search', function($filter) {
		return function(items, text) {
			if (!text || text.length === 0)
				return items;

			// split search text on space
			var searchTerms = text.split(' ');

			// search for single terms.
			// this reduces the item list step by step
			searchTerms.forEach(function(term) {
				if (term && term.length)
					items = $filter('filter')(items, term);
			});

			return items
		};
	});

	app.filter('limitFromTo', function() {
		return function(input, offset, limit) {
			if (!(input instanceof Array) && !(input instanceof String) && !(typeof input === "string")) return input;

			limit = parseInt(limit, 10);

			if (input instanceof String || (typeof input === "string")) {
				if (limit) {
					for (var ix = 0; ix < 10; ix++) {
						if (input.charAt(limit) !== ' ') {
							limit = limit + 1;
						}
					}
					return limit >= 0 ? input.slice(offset, limit) + ' […]' : input.slice(limit, input.length);
				} else {
					return "";
				}
			}

			var out = [],
				i, n;

			if (limit > input.length)
				limit = input.length;
			else if (limit < -input.length)
				limit = -input.length;

			if (limit > 0) {
				i = offset;
				n = limit;
			} else {
				i = input.length + limit;
				n = input.length;
			}

			for (; i < n; i++) {
				out.push(input[i]);
			}

			return out;
		};
	});


	/*
	 *
	 */
	app.filter('bbcode', function() {
		return function(input) {
			return (input || '').replace(/\n/g, '<br>')
				.replace(/\[(\/?.+?)\]/g, '<$1>');
		};
	});


	/*
	 *
	 */
	app.filter('unsafe', function($sce) {
		return $sce.trustAsHtml;
	});



	app.service('APIInterceptor', function($rootScope) {
		var service = this;

		service.request = function(config) {
			var currentUser = $rootScope.UserService.getCurrentUser(),
				access_token = currentUser ? currentUser.access_token : null;

			if (access_token) {
				config.headers.authorization = access_token;
			}
			return config;
		};

		service.responseError = function(response) {
			if (response.status === 401) {
				$rootScope.$broadcast('unauthorized');
			}
			return response;
		};
	});



	angularAMD.bootstrap(app, ['mcApp']);
	return app;
});
