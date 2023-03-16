/// <reference path="../../../typings/tsd.d.ts" />
var outlines;
(function (outlines) {
    outlines.MODULE = 'outlines';
    angular.module(outlines.MODULE, []);
    angular.element(document).ready(function () {
        angular.bootstrap(document, [outlines.MODULE]);
    });
})(outlines || (outlines = {}));
function base64_decode(data) {
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, enc = '';
    do {
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));
        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;
        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;
        if (h3 == 64)
            enc += String.fromCharCode(o1);
        else if (h4 == 64)
            enc += String.fromCharCode(o1, o2);
        else
            enc += String.fromCharCode(o1, o2, o3);
    } while (i < data.length);
    return enc;
}

var outlines;
(function (outlines) {
    var VectorPrintController = (function () {
        function VectorPrintController(popupService) {
            this.popupService = popupService;
        }
        VectorPrintController.prototype.openPaymentDialog = function (vectorId, daysMax) {
            this.popupService.open(PopupViews.VECTOR_PAYMENT, { vectorId: vectorId, daysMax: daysMax });
        };
        VectorPrintController.$inject = ['popupService'];
        return VectorPrintController;
    }());
    angular.module('outlines').controller('vectorPrintController', VectorPrintController);
    /*
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    var PaymentController = (function () {
        function PaymentController(popupService) {
            this.popupService = popupService;
            this.processing = false;
            this.paymentType = null;
        }
        Object.defineProperty(PaymentController.prototype, "vectorId", {
            get: function () {
                return this.popupService.scope.vectorId;
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(PaymentController.prototype, "daysMax", {
            get: function () {
                return this.popupService.scope.daysMax;
            },
            enumerable: true,
            configurable: true
        });
        PaymentController.prototype.pay = function (paymentType) {
            this.paymentType = paymentType;
            this.processing = true;
        };
        PaymentController.$inject = ['popupService'];
        return PaymentController;
    }());
    angular.module('outlines').controller('paymentController', PaymentController);
})(outlines || (outlines = {}));

var outlines;
(function (outlines) {
    var PopupService = (function () {
        function PopupService($timeout) {
            this.$timeout = $timeout;
            this.isOpen = false;
            this.scope = null;
            this.template = null;
            document.addEventListener('keyup', function (e) {
                if (this.isOpen) {
                    return true;
                }
                switch (e.keyCode) {
                    case 27:
                        $timeout(this.close.bind(this));
                        return false;
                    default:
                        return true;
                }
            });
        }
        PopupService.prototype.open = function (template, scope) {
            if (typeof template === 'string')
                this.template = template;
            this.isOpen = true;
            this.scope = (scope) ? scope : null;
        };
        PopupService.prototype.close = function () {
            this.isOpen = false;
        };
        PopupService.$inject = ['$timeout'];
        return PopupService;
    }());
    outlines.PopupService = PopupService;
    angular.module('outlines').service('popupService', PopupService);
})(outlines || (outlines = {}));
var PopupViews = {
    VECTOR_PAYMENT: '/nox-themes/default/templates/app/payment-type.html'
};

var outlines;
(function (outlines) {
    var VectorPayController = (function () {
        function VectorPayController($attrs, $sce, popupService) {
            this.popupService = popupService;
            this.price = $sce.trustAsHtml($attrs['price']);
            this.paytype = $sce.trustAsHtml($attrs['paytype']);
        }
        VectorPayController.prototype.openDialog = function () {
            this.popupService.open(PopupViews.VECTOR_PAYMENT, { vectorId: this.vectorId, daysMax: this.daysMax });
        };
        VectorPayController.$inject = ['$attrs', '$sce', 'popupService'];
        return VectorPayController;
    }());
    outlines.VectorPayController = VectorPayController;
})(outlines || (outlines = {}));

var outlines;
(function (outlines) {
    var vectorPayDirective = {
        restrict: 'E',
        templateUrl: '/nox-themes/default/templates/app/vector-pay.html',
        controller: outlines.VectorPayController,
        controllerAs: 'vectorPayCtrl',
        scope: {
            vectorId: '=',
            daysMax: '=',
            large: '=?',
            invertColors: '=?'
        },
        bindToController: true
    };
    angular.module(outlines.MODULE).directive('vectorPay', function () { return vectorPayDirective; });
})(outlines || (outlines = {}));

var outlines;
(function (outlines) {
    var VectorPreviewController = (function () {
        function VectorPreviewController() {
        }
        return VectorPreviewController;
    }());
    outlines.VectorPreviewController = VectorPreviewController;
    var Vector = (function () {
        function Vector() {
            this.id = null;
            this.name = null;
            this.full_name = null;
        }
        return Vector;
    }());
    outlines.Vector = Vector;
})(outlines || (outlines = {}));

var outlines;
(function (outlines) {
    var vectorPreviewDirective = {
        restrict: 'E',
        templateUrl: '/nox-themes/default/templates/app/vector-preview.html',
        controller: outlines.VectorPreviewController,
        controllerAs: 'vectorPreviewCtrl',
        link: function (scope, element, attrs, ctrl) {
            ctrl.vector = JSON.parse(base64_decode(attrs['vector']));
        }
    };
    angular.module(outlines.MODULE).directive('vectorPreview', function () { return vectorPreviewDirective; });
})(outlines || (outlines = {}));
