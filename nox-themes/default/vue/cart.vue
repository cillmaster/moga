<template>
    <div @click="cmd" id="cart-wrapper">
        <div v-if="view.ready" class="content">
            <div v-if="view.items.length" class="container_12">
                <div class="grid_9 grid_first">
                    <div class="cart-oper">
                        <div><strong>Items:</strong></div>
                        <div class="cart-btn crumb">Continue Shopping</div>
                    </div>
                    <div class="hr"></div>
                    <div v-for="item in view.items">
                        <div class="grid_4 grid_first block vector-block-preview">
                            <div class="cont-in-cart">
                                <a v-if="item.prepay" :href="item.url" title="%14$s %15$s blueprint and drawing" class="prepay-link-cont">
                                    <img :src="item.preview[0]" alt="%14$s %15$s blueprint" class="prepay-image">
                                    <img :src="item.preview[1]" alt="%14$s %15$s blueprint" class="prepay-image-hover">
                                </a>
                                <a v-else :href="item.url" title="%14$s %15$s blueprint and drawing">
                                    <img :src="item.preview" alt="%14$s %15$s blueprint">
                                </a>
                            </div>
                        </div>
                        <div class="grid_4">
                            <div><strong>{{item.prm.make}} {{item.name}}</strong></div>
                            <div>{{item.name_version}} {{item.name_spec}} {{item.prm.body}}</div>
                            <div class="f12 text-color-note">{{item.prm.year}} - {{item.prm.end || 'Present'}}</div>
                            <div><span class="icon-vector">{{item.prepay ? 'PREPAY' : 'VECTOR'}}</span></div>
                            <br>
                            <div>
                                <span title="Views / Projections / Planes">Views:</span>
                                <template v-for="index in 4">
                                    &nbsp;<span :class="(item.views & self.mapViews[index].mask) ? 'ui-view_active' : 'ui-view_unactive'">{{self.mapViews[index].name}}</span>
                                </template>
                            </div>
                            <div>License: <a class="color-cyan" href="/terms-of-service" target="_blank">Basic License</a></div>
                            <div>Scale: 1:{{item.scale}}</div>
                            <div>Units: Millimeters (mm)</div>
                            <div>Formats included:</div>
                            <div>
                                <template v-for="(ext,index) in item.ext">
                                    <template v-if="index"> | </template>
                                    <span class="cursor-help" :title="self.mapFormats[ext]">{{ext}}</span>
                                </template>
                            </div>
                        </div>
                        <div v-if="item.viewsReal !== 15" class="grid_4">
                            <div class="cheсkbox-cont">
                                <label>
                                    <input @change="top" v-model="self.map[item.id].top" :ind="item.id" type="checkbox">
                                    <span class="name">Add Top View</span>
                                    <span class="details">(+$9)</span>
                                </label>
                            </div>
                            <br>
                            <p>Our standard blueprints has Front-Side-Rear views.
                                Order this popular option and get additional Top View.</p>
                            <br>
                            <p v-if="!item.prepay">Please note: Top View is not ready so you will get 3-views blueprint now
                                and 4-views blueprint in 1-{{view.days}} work days.</p>
                        </div>
                        <div class="grid_12 grid_first cart-oper">
                            <div :prm="'view|' + item.id" class="cart-btn crumb">View Item</div>
                            <div :prm="'removeItem|' + item.id" class="cart-btn crumb">Remove Item</div>
                            <div class="discount">Discount (<span class="old">${{item.priceOld}}</span>) $11 Saved</div>
                            <div class="price">${{item.price}}</div>
                        </div>
                        <div class="clear"></div>
                        <div class="hr"></div>
                    </div>
                    <div class="grid_12 grid_first cart-oper">
                        <div prm="emptyCart" class="cart-btn crumb">Empty cart</div>
                        <div class="cart-btn crumb">Continue Shopping</div>
                    </div>
                </div>
                <div class="grid_3">
                    <div style="margin-bottom: 16px;" class="cart-oper">
                        <div><strong>Proceed Checkout:</strong></div>
                    </div>
                    <div class="block block-wide-padding">
                        <div>Choose payment method:</div>
                        <br>
                        <p>
                            <span :prm="view.auth ? 'pay|login' : 'noAuth|pay'" class="btn w100p invert-colors">Pay with PayPal</span>
                        </p>
                        <p>
                            <span :prm="view.auth ? 'pay|billing' : 'noAuth|pay'" class="btn w100p">Pay with Debit/Credit Card</span>
                        </p>
                        <br>
                        <p class="f12">Please note: Some of your items have PREPAY status and are not ready now.
                            These items will be ready in up to 1-{{view.days}} work days.</p>
                        <br>
                        <template v-if="self.topOptions.on">
                            <p class="f12">Please note: Top View is not ready so you will get 3-views blueprint
                                now and 4-views blueprint in 1-{{view.days}} work days.</p>
                            <br>
                        </template>
                        <template v-if="self.topOptions.active.length">
                            <div class="cheсkbox-cont">
                                <label>
                                    <input @change="top" v-model="self.topOptions.all" v-indeterminate="self.topOptions.part" type="checkbox">
                                    <span class="name">Add Top View to all</span>
                                </label>
                            </div>
                            <br>
                        </template>
                        <div class="full-size">
                            <div>Items:</div>
                            <div>{{view.items.length}}</div>
                        </div>
                        <div class="full-size ui-color">
                            <div>Total Saved:</div>
                            <div>US ${{view.saved}}</div>
                        </div>
                        <div class="full-size">
                            <div>Total Price:</div>
                            <div>US ${{view.total}}</div>
                        </div>
                        <br>
                        <p class="f12" style="display: flex; align-items: center;">
                            <img src="/nox-themes/default/images/secure-icon.png" width="10" height="12">&nbsp;
                            Secure Checkout
                        </p>
                        <p class="f12">Your payment will be encrypted and processed by PayPal using 3D Secure and TLS.
                            Outlines won't access or collect your billing information.</p>
                    </div>
                </div>
            </div>
            <div v-else style="text-align: center" class="container_12 cart-empty">
                <img src="/nox-themes/default/images/cart-icon100.png" width="108" height="100"/>
                <div class="cart-empty-header">Your shopping cart is empty.</div>
                <div>Try to use <a href="/search">search</a> and <a href="/car-vector-drawings">catalog</a>
                    to find vector blueprints or order prepay blueprints.</div>
            </div>
        </div>
    </div>
</template>

<script>
    (function () {
        return {
            data: function () {
                Storage.external.cart = {details: {}};
                return {
                    external: Storage.external.cart,
                    self: {
                        map: {},
                        mapFormats: {
                            PDF: 'Portable Document Format 1.5 (Acrobat 6)',
                            EPS: 'Encapsulated Postscript',
                            AI: 'Compatible with Adobe Illustrator CS4 / CS5 / CS6 and newer',
                            SVG: 'Scalable Vector Graphics 1.1',
                            DWG: 'Compatible with AutoCAD 2004 / 2005 / 2006 and newer',
                            DXF: 'Drawing Exchange Format (AutoCAD 2004 / 2005 / 2006 and newer)'
                        },
                        mapViews: [null,
                            {name: 'Front', mask: 1},
                            {name: 'Top', mask: 2},
                            {name: 'Rear', mask: 4},
                            {name: 'Side', mask: 8}
                        ],
                        topOptions: {
                            active: [],
                            all: false,
                            on: 0,
                            part: false
                        }
                    }
                };
            },
            methods: {
                cmd: function (e, force) {
                    let prm = e.target.getAttribute('prm') || force;
                    if(!prm) return;
                    let storage = this.$data.external,
                        self = this.$data.self, tmp;
                    prm = prm.split('|');
                    if(!/(noAuth|pay|view)/.test(prm[0])){
                        tmp = {
                            method: 'POST',
                            url: '/checkout/cartCmd',
                            data: {
                                cmd: prm[0],
                                ind: prm[1]
                            },
                            _vc: {
                                storage: this.$data.external,
                                property: 'details'
                            }
                        }
                    }
                    switch (prm[0]) {
                        case 'view':
                            location.assign(self.map[prm[1]].url);
                            break;
                        case 'editItem':
                            tmp.data.ind = tmp.data.ind || self.topOptions.active;
                            tmp.data.prm = +prm[2] ? {top:1} : {};
                            break;
                        case 'emptyCart':
                            tmp._vc.beforeExec = {
                                vc: 'msg',
                                ui: {
                                    title: 'Delete all items',
                                    msg: 'Are you sure you want to remove all items from your cart?',
                                    buttons: [
                                        {title: 'Yes', cl: 'btn violet', cmd: '1'},
                                        {title: 'No', cl: 'btn violet', cmd: 'cancel'}
                                    ]
                                }
                            };
                            break;
                        case 'noAuth':
                            this.initJsPopup([
                                ['data-wid', 'reg_main'],
                                ['data-wprm', 'buy']
                            ]);
                            break;
                        case 'pay':
                            location.assign('/payments/cartBuy?webProfile=' + prm[1]);
                            break;
                        default:
                            console.log(prm)
                    }
                    tmp && this.update(tmp);
                },
                srcMini: function(str){
                    return 'https://getoutlines.com'
                        + str.replace('/preview/', '/mini-preview/').replace('.png', '-blueprint-preview208.png');
                },
                getDetails: function () {
                    this.update({
                        method: 'GET',
                        url: '/checkout/cartDetails?rnd=' + Math.random(),
                        _vc: {
                            storage: this.$data.external,
                            property: 'details'
                        }
                    });
                },
                top: function (e) {
                    let ind = e.target.getAttribute('ind');
                    this.cmd(e, ['editItem', ind, +e.target.checked].join('|'));
                }
            },
            computed: {
                view: function () {
                    console.log('view');
                    let data = this.$data,
                        external = data.external,
                        self = data.self,
                        details = external.details,
                        res = {items: []},
                        items, tmp, total = 0, withoutTop = [], totalTop = 0;
                    if(details.status === 200){
                        items = JSON.parse(JSON.stringify(details.data));
                        for(let i = 0; i < items.length; i++){
                            tmp = items[i];
                            tmp.prm = tmp.prm ? JSON.parse(tmp.prm) : {};
                            tmp.prepay = +tmp.prepay;
                            tmp.preview = tmp.prepay
                                ? ['/nox-themes/default/images/prepay-preview208.png', '/nox-themes/default/images/prepay-magic208.png']
                                : this.srcMini(tmp.preview);
                            tmp.top = +tmp.top;
                            tmp.price = +tmp.price+ (tmp.top ? 9 : 0);
                            tmp.viewsReal = +tmp.views;
                            tmp.views = tmp.top ? 15 : tmp.viewsReal;
                            tmp.ext = tmp.ext.split(',');
                            tmp.priceOld = (tmp.price + 11).toFixed(2);
                            total += tmp.price;
                            if(!(tmp.viewsReal & 2)){
                                withoutTop.push(tmp.id);
                            }
                            totalTop += tmp.top ? 1 : 0;
                            res.items.push(tmp);
                            self.map[tmp.id] = tmp;
                        }
                        self.topOptions = {
                            active: withoutTop,
                            all: totalTop && (totalTop === withoutTop.length),
                            on: totalTop,
                            part: totalTop && (totalTop < withoutTop.length)
                        };
                        res.total = total.toFixed(2);
                        res.saved = (res.items.length * 11).toFixed(2);
                        res.auth = details.auth;
                        res.days = details.days;
                        this.upCartCounter(items.length);
                        res.ready = true;
                    }
                    return res;
                }
            },
            directives: {
                indeterminate: function(el, binding) {
                    el.indeterminate = Boolean(binding.value)
                }
            },
            mounted: function () {
                this.getDetails();
            },
        }
    })();
</script>
