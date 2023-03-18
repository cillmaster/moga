(function() {
    "use strict";

    angular.module('outlines').directive('loader', function() {
        return {
            restrict: 'E',
            templateUrl: '/nox-themes/default/templates/app/loader.html',
            controller: function() {},
            controllerAs: 'loaderCtrl',
            bindToController: true,
            scope: {
                style: '='
            }
        }
    })
})();