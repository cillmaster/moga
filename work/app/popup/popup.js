(function() {
    "use strict";

    var PopupController = function(popupService) {
        this.popupService = popupService;

        Object.defineProperty(this, 'template', {
            get: function() {
                return this.popupService.template;
            }
        });
    };

    PopupController.prototype.isOpen = function() {
        return this.popupService.isOpen;
    };

    PopupController.$inject = ['popupService'];

    angular.module('outlines').directive('popup2', function() {
        return {
            restrict: 'E',
            templateUrl: '/nox-themes/default/templates/app/popup.html',
            controller: PopupController,
            controllerAs: 'popupCtrl'
        }
    });

    var PopupCloseController = function(popupService) {
        this.popupService = popupService;
    };

    PopupController.$inject = ['popupService'];

    PopupCloseController.prototype.close = function() {
        this.popupService.close();
    };

    angular.module('outlines').directive('popupClose', function() {
        return {
            restrict: 'E',
            replace: true,
            template: '<div class="popup-close" ng-click="popupCloseCtrl.close()"></div>',
            controller: PopupCloseController,
            controllerAs: 'popupCloseCtrl'
        }
    });
})();
