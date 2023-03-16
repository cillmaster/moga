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
