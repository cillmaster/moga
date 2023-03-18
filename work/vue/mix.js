/*! mix.js */
    Vue.mixin({
      methods: {
        upCartCounter: function (count) {
          let cont = document.getElementById('cartBtn'),
              counter = document.getElementById('cartTotal');
          if(cont && counter){
            counter.innerText = 'Cart ' + count;
            count ? cont.removeAttribute('data-empty') : cont.setAttribute('data-empty', 'true');
          }
        },
        initJsPopup: function (prm) {
          let fakeJsPopup = document.getElementById('fakeJsPopup');
          for(let i = 0; i < prm.length; i++){
            fakeJsPopup.setAttribute(prm[i][0], prm[i][1]);
          }
          fakeJsPopup.click();
          for(let i = 0; i < prm.length; i++){
            fakeJsPopup.removeAttribute(prm[i][0]);
          }
        }
      }
    });
