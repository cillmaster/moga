/// <reference path="../../../typings/tsd.d.ts" />
// var outlines;
// (function (outlines) {
//     outlines.MODULE = 'outlines';
//     angular.module(outlines.MODULE, []);
//     angular.element(document).ready(function () {
//         angular.bootstrap(document, [outlines.MODULE]);
//     });
// })(outlines || (outlines = {}));
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

(function () {
    function NavController($scope, userService) {
        this.$inject = ['$scope', 'userService'];

        $scope.isAuthentificated = userService.isAuthentificated();
    }

    angular.module('outlines').controller('NavController', NavController);
})();
(function() {
    angular.module('outlines').factory('dataService', [dataService]);

    function dataService() {
        var session = window.outlinesSession;
        var locationQuery;

        return {
            getFromSession: function(key) {
                return session[key];
            },
            getFromLocationQuery: function(key) {
                
            }
        }
    }
})();
function upData(method, url, data, callback, onerror, prm) {
    var r = new XMLHttpRequest();
    r.open(method, url);
    method === 'POST' && r.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    r.onreadystatechange = function() {
        if(r.readyState === 4)
            if(r.status === 200){
                callback && callback(r.responseText, prm);
            }else{
                onerror && onerror(prm);
            }
    };
    r.send(data);
}

document.addEventListener('DOMContentLoaded', function () {
    //php console
    let cons = document.getElementById('console'),
        headerStripe = document.getElementById('header-stripe'),
        headerTopCars = document.getElementById('header-top-cars'),
        topCarsCont = document.getElementById('top-cars-cont'),
        makeCarsBtn = document.getElementById('make-cars-btn'),
        makeCarsHide = false;
    if(cons) {
        let re = /FILE:/g,
            errors = document.body.textContent.match(re),
            d = document.createElement('div');
        d.innerHTML = 'Errors: ' + (errors ? errors.length : 0);
        cons.insertBefore(d, cons.firstElementChild);
        cons.addEventListener('click', function () {
            cons.className = cons.className ? '' : 'full';
        });
    }
    //top panel position
    function onScrollMain() {
        let topPanelHeight = 28,
            scroll = topPanelHeight - window.scrollY;
        headerStripe.style.top = (scroll > 0 ? scroll : 0) + 'px';
        headerTopCars.style.top = (scroll > 0 ? (scroll + 48) : 48) + 'px';
    }
    window.addEventListener('scroll', onScrollMain);
    onScrollMain();

    let pageScrollTo = document.getElementById('pageScrollTo'),
        _pageScrollTo = document.getElementById('_pageScrollTo');
    function _scrollTo() {
        pageScrollTo && pageScrollTo.scrollIntoView();
    }
    /page=\d/.test(location.search) && _scrollTo();
    _pageScrollTo && _pageScrollTo.addEventListener('click', _scrollTo);

    if(makeCarsBtn){
        makeCarsBtn.addEventListener('mouseenter', function () {
            if(makeCarsHide){
                clearTimeout(makeCarsHide);
                makeCarsHide = false;
            }
            document.body.setAttribute('cars-top', 'show');
        });
        makeCarsBtn.addEventListener('mouseleave', function () {
            makeCarsHide = setTimeout(hideTopCars, 100);
        });
        topCarsCont && topCarsCont.addEventListener('mouseleave', function () {
            if(!makeCarsHide){
                makeCarsHide = setTimeout(hideTopCars, 100)
            }
        })
    }
    function hideTopCars(){
        document.body.removeAttribute('cars-top');
        makeCarsHide = false;
    }

    //Scroll left block
    let scCont = document.querySelector('.content .js_column-scroll'),
        mCont = document.getElementById('mc');
    if(scCont && mCont){
        let rCont = document.getElementById('rc'), db = false,
            rH = rCont ? rCont.getBoundingClientRect().height : 0,
            mH = mCont.getBoundingClientRect().height, h, wCont;
        if(rH > mH){
            h = rH; wCont = rCont;
        } else {
            h = mH; wCont = mCont;
        }
        db && console.log(wCont);
        if(scCont.getBoundingClientRect().height < h){
            let sc = document.getElementById('sc'),
                wH = document.documentElement.clientHeight,
                xyCont = scCont.getBoundingClientRect(),
                offTop = +sc.getAttribute('data-os') || (xyCont.top - 50),
                touchY = 0,
                scrOn = false,
                deltaY = 0;
            console.log(offTop, sc.getBoundingClientRect());
            function onScrollLeft() {
                let scrollY = window.pageYOffset || document.documentElement.scrollTop;
                if(scrollY > offTop && !scrOn) {
                    scrOn = true;
                    scCont.classList.add('column-fixed');
                    scCont.style.width = xyCont.width + 'px';
                }
                else if(scrollY < offTop && scrOn) {
                    scrOn = false;
                    scCont.classList.remove('column-fixed');
                    scCont.removeAttribute('style');
                    sc.removeAttribute('style');
                }
                if(scrOn){
                    let xySc = sc.getBoundingClientRect(),
                        xyData = wCont.getBoundingClientRect(),
                        fix = scrollY + xyData.top;
                    if(xyData.bottom <= wH) {
                        sc.style.top = Math.min(xyData.bottom - xySc.height, 74) + 'px';
                        db && console.log('1');
                    } else if(xySc.height + 74 < wH){
                        sc.style.top = '74px';
                        db && console.log('1.5');
                    } else if((deltaY > 0) && (xySc.bottom > wH)){
                        sc.style.top = (xySc.y - deltaY) + 'px';
                        db && console.log('2');
                    } else if((deltaY < 0) && (xySc.top > xyData.top)){
                        sc.style.top = Math.min(xySc.y - deltaY, 74) + 'px';
                        db && console.log('3');
                    } else {
                        db && console.log('4');
                    }
                    db && console.log(offTop, scrollY, 't', xySc.top, xyData.top, 'b', xySc.bottom, xyData.bottom, deltaY, wH, h, fix);
                }
                deltaY = 0;
            }
            document.addEventListener('scroll', onScrollLeft);
            sc.addEventListener('wheel', function (e) {
                deltaY = e.deltaY || 0;
            });
            sc.addEventListener('touchstart', touchHandler);
            sc.addEventListener('touchend', touchHandler);
            sc.addEventListener('touchcancel', touchHandler);
            function touchHandler(e) {
                switch (e.type) {
                    case 'touchstart':
                        touchY = e.touches[0].screenY;
                        break;
                    case 'touchcancel':
                    case 'touchend':
                        if(touchY){
                            deltaY = touchY - e.changedTouches[0].screenY;
                        }
                        touchY = 0;
                        break;
                }
            }
            onScrollLeft();
        }
    }

    // countries
    let cBtn = document.getElementById('country-btn'),
        cCont = document.getElementById('country-cont');
    cBtn.addEventListener('click', function (e) {
        e.stopImmediatePropagation();
        let xy = cBtn.getBoundingClientRect(),
            scrolled = window.pageYOffset || document.documentElement.scrollTop;
        cCont.style.top = (scrolled + xy.y - 5) + 'px';
        cCont.style.left = (xy.x - 15) + 'px';
        cCont.style.display = 'block';
        console.log(xy, scrolled);
    });
    document.addEventListener('click', function () {
        cCont.style.display = 'none';
    });
});
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

    angular.module('outlines').directive('popup', function() {
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
(function() {
    angular.module('outlines').factory('userService', ['dataService', userService]);

    function userService(dataService) {
        var user = dataService.getFromSession('user');

        return {
            getUser: function() {
                return user;
            },
            isAuthentificated: function() {
                return !!user;
            },
            authentificate: function() {

            }
        }
    }
})();
window.appGlobals = {
    afterLoginUrl: window.location.toString()
};

// GoogleAnalytics
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
// Google Tag Manager
(function(w,d,s,l,i) { w[l]=w[l]||[];w[l].push( { 'gtm.start':new Date().getTime(),event:'gtm.js' } );var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); } )(window,document,'script','dataLayer','GTM-TRR3XTH');
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'AW-1033691228');
// Facebook Pixel Code
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '434568580641569');
fbq('track', 'PageView');

window.outlinesSession.user
? ga('create', 'UA-59815736-1', 'auto', {
    userId: window.outlinesSession.user.id
})
: ga('create', 'UA-59815736-1', 'auto');
ga('send', 'pageview');

jQuery.fn.Windows = function( ) {
    var self = this;
    self.shadow = $('#shadow');
    self.activeWindow = false;
    self.templates = {
        buy: {
            msg: 'Sign up to buy blueprints. All purchased items will be available in your Downloads page. Or ' +
            '<a href="#" class="js-popup" data-wid="auth">login now</a>'
        },
        confirm_email: {
            title: 'Confirm your email',
            msg: 'Confirmation email sent. Check your email and confirm your email address. <br/><br/>' +
                'If you have not received our email in a few minutes, please check your spam folder. ' +
                'Otherwise <a href="mailto:hello@getoutlines.com?subject=Please activate my account' +
                '&body=Hello, Outlines. I have not received your confirmation email. Please activate ' +
                'my account manually. Regards." target="_blank">contact us</a> ' +
                'from the same email for manual activation.',
            ok: 'I received Confirm Email'
        },
        confirm_email_fb: {
            title: 'Your email is confirmed',
            msg: 'Your email is confirmed now you could receive personal notifications.'
        },
        email_re_sent: {
            title: 'This email is not confirmed.',
            msg: 'This email is not confirmed. We have re-sent a confirmation email to you right now. ' +
                'Please check your email and confirm your email.',
        },
        email_sent: {
            title: 'This email is not confirmed.',
            msg: 'This email is not confirmed. We have sent a confirmation email to you after your registration. ' +
                'Please check your email and confirm your email.',
        },
        on_cant: {
            title: 'Can not be produced',
            msg: 'We have estimated this request before and unfortunately marked it as impossible to ' +
                'produce.<br><br>Please ensure that the vehicle you need exists and it is possible to find ' +
                'appropriate sources to produce its blueprint. Try to change year, model or version name ' +
                'and request again.'
        },
        on_want_free: {
            title: 'Request submitted',
            msg: 'We got your Free request, now please wait, due to many free requests it could be processed ' +
                'in a long time.<br><br>Otherwise, if you want to purchase it in premium vector quality and ' +
                'get in a few work days, please use “Want to buy vector” button.'
        },
        on_want_pay: {
            title: 'Now wait for Quotation',
            msg: 'We got your request. We are going to estimate the price and if it\'s feasible to make ' +
                'this blueprint. Please wait for Quotation and Pre-payment Link to your email in a few work ' +
                'days in case if your request is feasible.'
        },
        prepayment_success: {
            title: 'Prepayment Success',
            msg: 'Your order is in development now.<br/><br/>You will receive ready vector blueprints ' +
                'on your email and got in <a href="/users/downloads">My Downloads</a> page.'
        },
        registration_success: {
            title: 'Successful Registration',
            msg: 'Your account has ben activated. Let’s read our <a href="/" target="_blank">Get started</a> ' +
                'tips.<br/><br/>Now you can purchase vector blueprints and request any blueprint. All your purchased ' +
                'blueprints collect on My Downloads page.'
        },
        registration_success_fb: {
            title: 'Successful Registration',
            msg: 'Please confirm your email now to receive personal notifications.<br><br>' +
                'Your account has ben activated. Let’s read our <a href="/" target="_blank">Get started</a> ' +
                'tips.<br/><br/>Now you can purchase vector blueprints and request any blueprint. All your purchased ' +
                'blueprints collect on My Downloads page.'
        },
        reset_email: {
            title: 'Check your email',
            msg: 'We have sent you an email to reset your password. Please check your email.'
        },
        reset_pass: {
            title: 'Password changed',
            msg: 'You have successfully changed your password.'
        },
        blueprint_force: {
            msg: 'Sign up per 1 click to post your request and get personal notification when it will be done. Or ' +
            '<a class="js-popup" data-wid="auth" href="#">Login</a>'
        },
        blueprint_download: {
            msg: 'To continue downloading free blueprints you have to create an account.'
        },
        bulk: {
            msg: 'Sign up and click again to contact us with Bulk Purchase Order. Or ' +
                '<a class="js-popup" data-wid="auth" href="#">Login</a> now.'
        },
        request: {
            msg: 'Your request has been added. Please sign up or login to keep in touch with Outlines and know ' +
            'when we upload this blueprint. Thanks.'
        },
        estimate: {
            msg: 'Please sign up or login to get a quotation.'
        },
        unsubscribe: {
            msg: 'You have been unsubscribed from these emails.',
            title: 'You are now unsubscribed'
        },
        use_login: {
            title: 'Use login',
            msg: 'Use login.'
        },
        vote: {
            msg: 'Please sign up or login to add your vote.'
        }
    };

    self.open = function(w, prm, url) {
        var tmp;
        self.shadow.addClass('visible');
        if(self.activeWindow) {
            url = self.closeActiveWindow(true);
        }

        self.activeWindow = $('#js-window__' + w);
        console.log(w, prm);
        if(prm && (tmp = self.templates[prm])){
            tmp.title && self.activeWindow.find('.title').html(tmp.title);
            self.activeWindow.find('.msg').html(tmp.msg);
            tmp.ok && self.activeWindow.find('.ok-close').html(tmp.ok);
        }
        if(url){
            self.activeWindow.attr('wnd-onClose-last', 'forceLocation');
            self.activeWindow.attr('forceLocation', url);
        }
        self.activeWindow.addClass('visible');
        self.activeWindow.height(self.activeWindow.find('.frame').height());
    };

    self.closeActiveWindow = function(doNotHideShadow) {
        var url = (self.activeWindow.attr('wnd-onClose-last') === 'forceLocation') && self.activeWindow.attr('forceLocation');
        onClose('wnd-onClose');
        if(!doNotHideShadow){
            self.shadow.removeClass('visible');
            onClose('wnd-onClose-last');
        }
        self.activeWindow.removeClass('visible');
        onClose('wnd-onClose-after');
        self.activeWindow.find('.msg').html('');
        self.activeWindow.attr('wnd-onClose-last', '');
        self.activeWindow.attr('forceLocation', '');
        self.activeWindow = false;
        return url;
        function onClose(step) {
            var onCloseFuncName = self.activeWindow.attr(step);
            if(onCloseFuncName && (typeof window[onCloseFuncName] === 'function')) {
                window[onCloseFuncName](self.activeWindow);
            }
        }
    };

    $('.js-popup').on('click', function() {
        var w = this.getAttribute('data-wid') || this.getAttribute('wid'),
            prm = this.getAttribute('data-wprm'),
            action = this.getAttribute('data-action');
        console.log(w);
        if(action){
            cookie.set('myLastAction', action, {
                path: '/',
                expires: 1e6
            });
        }
        self.open(w, prm);
        return false;
    });

    $('.window .close, .window .ok-close').on('click', function() {
        self.closeActiveWindow();
        return false;
    });
    self.shadow.on('click', function() {
        self.closeActiveWindow();
        return false;
    });

    return self;
};

function forceLocation(obj) {
    var to = obj.attr('forceLocation');
    to && location.assign(to);
}

function glSign(authResult, reg) {
    if (authResult['status'] && authResult['status']['signed_in']) {
        gapi.client.load('plus', 'v1').then(function() {
            var request = gapi.client.plus.people.get({
                'userId': 'me'
            });
            request.then(function(resp) {
                $.ajax({
                    url: '/users/login',
                    data: {
                        'auth': authResult,
                        'user': resp,
                        'user_type': 'google'
                    },
                    method: 'post',
                    success: function(data) {
                        if(reg){
                            windows.open('msg', 'registration_success', data.url);
                        }else{
                            window.location = data.url || window.appGlobals.afterLoginUrl.split('#')[0];
                        }
                    },
                    error: function() {
                        alert('Some error. Try again later.');
                    }
                });
            }, function(reason) {
                alert('Error:' + reason.result.error.message);
            });
        });
    } else if (authResult['error']) {

    }
}
function glSigninCallbackReg(authResult) {
    glSign(authResult, true);
}

function glSigninCallback(authResult) {
    glSign(authResult)
}

function social() {
    // [FB]
    function FB_login(email) {
        FB.login(function () {
            FB.getLoginStatus(function(response) {
                // console.log('FB', response);
                if (response.status === 'connected') {
                    // FB.api('/me', {fields: ['name', 'email']}, function(r) {
                    //     console.log('Successful login for: ', r);
                    // });
                    response.user_type = 'facebook';
                    response.email = email;
                    $.ajax({
                        url: '/users/login',
                        data: response,
                        method: 'post',
                        success: function(data) {
                            if(email){
                                windows.open('msg', 'registration_success_fb', data.url);
                            }else{
                                window.location = data.url || window.appGlobals.afterLoginUrl.split('#')[0];
                            }
                        },
                        error: function() {
                            alert('Some error. Try again later.');
                        }
                    });
                }  else {

                }
            });
        }/*, {scope: 'email'}*/);
    }
    $('.js-fb_login').on('click', function() {
        window.appGlobals.afterLoginUrl = (this.getAttribute('data-continueurl')) ? this.getAttribute('data-continueurl') : window.location.toString();
        FB_login();
    });
    let $form = $('#js-window__reg_fb').find('form');
    $form.ajaxForm(
        function() {
            FB_login($form.find('[name="registration[email]"]').val());
        },
        function(a) {
            let x = JSON.parse(a.responseText);
            console.log(a.responseText);
            alert(x.error);
        }
    );
// [/FB]
// [GL]
    $('.js-gl_login').on('click', function() {
        window.appGlobals.afterLoginUrl = (this.getAttribute('data-continueurl')) ? this.getAttribute('data-continueurl') : window.location.toString();
        gapi.auth.signIn({
            'cookiepolicy' : 'single_host_origin',
            'callback' : 'glSigninCallback',
            'scope' : 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email',
            'requestvisibleactions': 'http://schema.org/AddAction'
        });
        //window.windows.closeActiveWindow();
    });
    $('.js-gl_reg').on('click', function() {
        gapi.auth.signIn({
            'cookiepolicy' : 'single_host_origin',
            'callback' : 'glSigninCallbackReg',
            'scope' : 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email',
            'requestvisibleactions': 'http://schema.org/AddAction'
        });
    });
// [/GL]
}

jQuery.fn.ajaxForm = function(onSuccess, onError) {
    var self = $(this);
    self.on('submit', function(e) {
        e.preventDefault();
        var data = self.serializeArray();

        $.ajax({
            async: false,
            method: self.attr('method') || 'get',
            url: self.attr('action'),
            data: data,
            success: onSuccess,
            error: onError
        });
        return false;
    });
};


$(document).ready(function(){
    social();
    window.windows = jQuery.fn.Windows();

    $('.captcha').click(function () {
        var i = $(this);
        var src = i.data('src') || i.attr('src');
        i.data('src', src);
        i.attr('src', src + '?' + Math.random());
    });

    (function() {
        var po = document.createElement('script'); po.type = 'text/javascript';
        po.src = 'https://apis.google.com/js/client:platform.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
    })();

    //Позволяет делать в форме одно требуемое поле из нескольких
    $('form').has('.js-input-requires').each(function() {
        var $requires = $(this).find('.js-input-requires');
        $requires.attr('required', 'required');
        $requires.on('input', function() {
            if(this.value) {
                $requires.removeAttr('required');
            }
            else {
                for(var i in $requires) {
                    if($requires.eq(i).val()) {
                        $requires.removeAttr('required');
                        return;
                    }
                }
                $requires.attr('required', 'required');
            }
        });
    });
    (function() {
        //Делаем лоадер на кнопках покупки
        $('.js-fb_login, .js-gl_login').addClass('js-loader');
        var $jsProgress = $('.js-loader');
        var busy = false;
        var tmp_ls = {
            orange: '/nox-themes/default/images/loader-orange.gif',
            purple: '/nox-themes/default/images/loader-purple.gif'
        };
        if($jsProgress.length) {
            for(var i in tmp_ls) {
                if(tmp_ls.hasOwnProperty(i)) {
                    var l = new Image();
                    l.src = tmp_ls[i];
                }
            }
        }
        $jsProgress.on('click', function() {
            if(busy) return false;
            if(this.target === '_blank') return true;
            busy = true;
            var that = $(this);
            if(that.hasClass('.jss-loader-action')) {
                busy = false;
                return false;
            }
            else {
                this.setAttribute('data-olddata', this.innerHTML);
                if(that.hasClass('violet')) {
                    this.innerHTML = '<img src="/nox-themes/default/images/loader-purple.gif">&nbsp;Please&nbsp;wait';
                }
                else {
                    this.innerHTML = '<img src="/nox-themes/default/images/loader-orange.gif">&nbsp;Please&nbsp;wait';
                }
                that.parents('.vector-block-preview').addClass('hover');

                $jsProgress.filter('.jss-loader-action').each(function() {
                    this.innerHTML = this.getAttribute('data-olddata');
                    $(this).removeClass('jss-loader-action').parents('.vector-block-preview').removeClass('hover');
                });
                this.classList.add('jss-loader-action');

                busy = false;
            }
        });
    })();

    $('.js-lh').each(function() {
        var that = $(this);
        that.css('line-height', that.height() + 'px');
    });
});

window.cookie = {
    set: function(name, value, options) {
        options = options || {};
        var expires = options.expires;
        if (typeof expires == "number" && expires) {
            var d = new Date();
            d.setTime(d.getTime() + expires*1000);
            expires = options.expires = d;
        }
        if (expires && expires.toUTCString) options.expires = expires.toUTCString();
        var updatedCookie = name + "=" + encodeURIComponent(value);
        for(var propName in options) {
            updatedCookie += "; " + propName;
            var propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }
        document.cookie = updatedCookie;
    },
    get: function(name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    },
    'delete': function(name) {
        this.set(name, "", {expires: -1})
    }
};

var AuthEvents = {
    // Listen
    AuthMe: 'AuthMe'
};

(function() {
    angular.module('outlines').directive('auth', [authDirective]);

    function AuthCtrl(userService, $scope) {
        $scope.showDialog = false;

        this.show = function() {
            $scope.showDialog = true;
        };
        this.hide = function() {
            $scope.showDialog = false;
        };

        $scope.$on(AuthEvents.AuthMe, function() {

        });
    }

    function authDirective() {
        return {
            restrict: 'E',
            controllerAs: 'authCtrl',
            templateUrl: '/nox-themes/default/templates/app/auth.html',
            controller: ['userService', '$scope', AuthCtrl]
        }
    }
})();



