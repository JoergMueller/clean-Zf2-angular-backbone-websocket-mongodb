
define(['angularAMD'], function(app) {
	'use strict'


	/**
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 * @param {[type]}
	 */
	function UserCtrl($rootScope, $scope, $state, $stateParams, $window, $timeout, localStorageService, $translate, $location, $http,
		$filter, $sce) {


		$scope.genders = [{code: "mrs", name:"Female"},{code: "mr", name:"Male"}];
		$scope.userData = SessionUser || {
			'nickname': '',
			'email': '',
			'password': '',
			'sheet': {
				'gender': 'mr',
				'firstname': '',
				'name': '',
				'streetnr': '',
				'city': '',
				'zipcode': '',
			}
		};


		$scope.$on('$viewContentLoaded', function() {
			$timeout(function(){
				$('#page').addClass('in');
			});

			$scope.run();
		});

		/**
		 * @return {[type]}
		 */
		$scope.run = function() {
		};

		/**
		 * [loginData description]
		 * @type {Object}
		 */
		$scope.loginData = {
			'un': '',
			'up': ''
		};

		/**
		 * @return {[type]}
		 */
		$scope.login = function() {
			$scope.loginData.up = $rootScope.md5.createHash($scope.loginData.up || '');
			UserService.login($scope.loginData);
		};

		$scope.logout = function() {};


		$rootScope.currentModal;
		$scope.showRegister = function() {

			try {
					$rootScope.currentModal.hide();

					$rootScope.currentModal = $rootScope.modal({
							'templateUrl': '/application/partials/user/register.html',
							'show': false,
							'html': true,
							'$sce': $sce
						});

				$rootScope.currentModal.$promise.then($rootScope.currentModal.show);
			}
			catch(e) {}

		};
	};

	// ===============================================================================



	UserCtrl.$inject = ['$rootScope', '$scope', '$state', '$stateParams', '$window', '$timeout', 'localStorageService', '$filter',
		'$location', '$modal', '$sce', '$http', '$translate', '$templateCache', '$sce'];


	app.controller('UserCtrl', UserCtrl);
	return app;
});
