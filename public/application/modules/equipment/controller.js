
define(['angularAMD'], function(app) {
	'use strict'

	/* @ngInject */
	function EquipmentCtrl($rootScope, $scope, $state, $stateParams, $window, $timeout, localStorageService, $translate, $location, $http,
		$filter, $sce, $templateCache) {


		$scope.selectedEquipmentItem = {
			'datecreate': moment(),
			'headline': {'de':'Die Überschrift in Deutsch für dieses wunderbare Gerät'},
			'description': {'de':'Der <strong><u>Seooptimierte</u></strong> Werbetext lastige Beschreibungstext dazu in Deutsch'},
	        'ishidden': true,
	        'validfrom': moment(),
	        'validuntil': null,
	        'geo': [],
	        'georesult': {},
	        'georeverse': '',
	        'attributes': {}
		};


		var userRole = 'guest';
		if(!_.isNull(SessionUser) && !_.isUndefined(SessionUser.role)) {
			userRole = SessionUser.role;
		}
		$scope.hasWriteAccess = (UserService.isInBitGroup(userRole, 'editor'));


		/** [description] */
		$scope.$on('$viewContentLoaded', function() {
			$timeout(function(){
				$('#page').addClass('in');
			});

			$scope.run();

			setTimeout(function() {

				window.postDispatch();
				EventService.notify("mc.doc.postDispatch");

			}, 1200);

		});

		$scope.showLogin = function() {
			if(false === UserService.isLoggedIn()) {
				$rootScope.currentModal = $rootScope.modal({
							'templateUrl': '/application/partials/user/login.html',
							'show': false,
							'html': true,
							'$sce': $sce
						});

				$rootScope.currentModal.$promise.then($rootScope.currentModal.show);

				$rootScope.$on('islogedin', function() {
					var user = UserService.getCurrentUser();
					$scope.SessionUser = window.SessionUser = user;
					$scope.hasWriteAccess = (UserService.isInBitGroup(userRole, 'editor'));
					$rootScope.currentModal.hide();
				});
			}
		};

		$scope.logout = function() {

			SessionUser = null;
			UserService.setCurrentUser(null);
			UserService.setCurrentToken(null);

			window.location.reload();
		};

		$scope.showForm = function() {
			console.log($stateParams);
		};

		$scope.createItem = function() {};

		$scope.editItem = function(item) {};

		$scope.removeItem = function(item) {};

		$scope.hideItem = function(item) {};


		/** [run description] */
		$scope.run = function() {

			switch($stateParams.action) {

				case 'create':
					break;

				case 'edit':
					break;

				case 'remove':
					break;

				default:
					break;
			}
			// 
		}
	};

	// ===============================================================================



	EquipmentCtrl.$inject = ['$rootScope', '$scope', '$state', '$stateParams', '$window', '$timeout', 'localStorageService', '$filter',
		'$location', '$modal', '$sce', '$http', '$translate', '$templateCache', '$sce', '$templateCache'];


	app.controller('EquipmentCtrl', EquipmentCtrl);
	return app;
});
