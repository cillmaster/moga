/*! scope.js */
    Object.defineProperty(window, 'Storage', {writable: false, value: {
        external: {}
    }});

    Vue.use(VC, {
        url: {
            prefix: '/nox-themes/default/vue/',
            postfix: '.vue?r=' + Math.ceil(Math.random() * 1e6)
        },
        scope: '#vue-scope',
        vc: ['cart-oper', 'msg'],
    });
