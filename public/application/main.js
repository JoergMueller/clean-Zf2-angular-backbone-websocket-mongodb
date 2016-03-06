require.config({
    baseUrl: "/application",
    urlArgs: "bust=" + (new Date()).getTime(),
    waitSeconds: 300,
    paths: {
        'underscore': '../plugins/underscore/underscore-min',
        'jquery': '../js/jquery',
        'jqForm': '../plugins/jquery-form/jquery.form',
        'jqTags': '../plugins/bootstrap-tagsinput.min',
        'jqFancybox': '../plugins/fancybox/source/jquery.fancybox',
        'jqBrowser': '../plugins/jquery.browser',
        'bLazy': '../plugins/bLazy/blazy.min',
        'smoothScroll': '../plugins/SmoothScroll',
        'jqMPopup': '../plugins/magnific-popup/jquery.magnific-popup.min',
        'domReady': '../plugins/domready/domReady',
        'angular': '../plugins/angular/angular.min',
        'angularAMD': '../plugins/angularAMD/angularAMD',
        'ngMd5': '../plugins/angular-md5/angular-md5.min',
        'LocalStorageModule': '../plugins/angular-local-storage/dist/angular-local-storage.min',
        'moment': '../plugins/moment/min/moment-with-locales.min',
        'angularMoment': '../plugins/angular-moment/angular-moment.min',
        'ngStrap': '../plugins/angular-strap/dist/angular-strap.min',
        'angularTranslate': '../plugins/angular-translate/angular-translate.min',
        'angularTranslateLoader': '../plugins/angular-translate-loader-static-files/angular-translate-loader-static-files.min',
        'uiBootstrap': '../plugins/angular-bootstrap/ui-bootstrap.min',
        'uiRouter': '../plugins/angular-ui-router/release/angular-ui-router.min',
        'stateHelper': '../plugins/angular-ui-router.statehelper/statehelper',
        'ngLoad': '../plugins/ng-load/ng-load'
    },
    shim: {
        'underscore': {
            exports: '_'
        },
        'jquery': {
            exports: ['jquery','jQuery','$']
        },
        'angular': {
            exports: 'angular'
        },
        'ngStrap': ['angular'],
        'LocalStorageModule': ['angular'],
        'ngLoad': ['angular'],
        'ngMd5': ['angular'],
        'angularTranslate': ['angular'],
        'angularTranslateLoader': ['angular', 'angularTranslate'],
        'uiBootstrap': ['angular'],
        'uiRouter': ['angular'],
        'stateHelper': ['uiRouter', 'angular'],
        'angularMoment': ['angular'],
        'angularAMD': ['angular', 'uiBootstrap', 'angularTranslate', 'ngLoad', 'LocalStorageModule', 'ngStrap', 'angularMoment', 'ngMd5']
    },
    deps: ['application']
});

require(['domReady'], function (domReady) {
  domReady(function () {
    //This function is called once the DOM is ready.
    //It will be safe to query the DOM and manipulate
    //DOM nodes in this function.
    console.debug('domReady', Date())

  });
});
