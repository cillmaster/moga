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