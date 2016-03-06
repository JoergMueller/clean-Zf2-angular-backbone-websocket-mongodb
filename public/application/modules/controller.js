define(['angularAMD'], function(app) {
    'use strict'

    /* @ngInject */
    function MainCtrl($rootScope, $scope, $state, $stateParams, $window, $timeout, localStorageService, $translate, $location, $http,
        $filter) {

        window.$rootScope = $rootScope;

        $scope.$on('$viewContentLoaded', function() {
            $timeout(function(){
                $('#page').addClass('in');
            });
        });
    };

    // ===============================================================================



    MainCtrl.$inject = ['$rootScope', '$scope', '$state', '$stateParams', '$window', '$timeout', 'localStorageService', '$filter',
        '$location', '$modal', '$sce', '$http', '$translate', '$templateCache'];


    app.controller('MainCtrl', MainCtrl);
    return app;
});
