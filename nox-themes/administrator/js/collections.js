/*! collections.js */
    Object.defineProperty(window, 'Storage', {writable: false, value: {
        external: {}
    }});
    Vue.use(VC, {
        url: {
            prefix: '/nox-themes/administrator/vue/',
            postfix: '.vue?r=' + Math.ceil(Math.random() * 1e6)
        },
        app: 'collection-app',
        vc: ['collection-list', 'collection-edit'],
    });
