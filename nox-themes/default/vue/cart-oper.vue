<template>
    <div @click="cmd" :data-v="view" class="f14">
        <template v-if="view">
            <div prm="goToCart" class="btn hover">
                <img src="/nox-themes/default/images/cart-icon16.png" width="16" height="13">&nbsp;
                Go to Cart
            </div>
            <div prm="removeItem" class="btn" style="margin-left: 5px;">
                Remove
            </div>
        </template>
        <div v-else prm="addItem" class="btn hover">
            <img src="/nox-themes/default/images/add-icon16.png" width="14" height="14">&nbsp;
            Add to Cart&nbsp;&nbsp;${{price}}
        </div>
    </div>
</template>

<!--suppress FallThroughInSwitchStatementJS -->
<script>
    (function () {
        return {
            props: ['ind', 'price', 'mode'],
            data: function () {
                return {
                    mode: this.$options.propsData.mode,
                    price: this.$options.propsData.price,
                    cb: {details: false},
                    self: {}
                };
            },
            methods: {
                cmd: function (e) {
                    let prm = e.target.getAttribute('prm'),
                        ind = this.$options.propsData.ind,
                        self = this.$data.self;
                    switch (prm) {
                        case 'addItem':
                            self.addItem = true;
                        case 'removeItem':
                            this.update({
                                method: 'POST',
                                url: '/checkout/cartCmd',
                                data: {
                                    cmd: prm,
                                    ind: ind
                                },
                                _vc: {
                                    storage: this.$data.cb,
                                    property: 'details'
                                }
                            });
                            break;
                        case 'goToCart':
                            location.assign('/checkout/cart');
                            break;
                    }
                }
            },
            computed: {
                view: function () {
                    let data = this.$data,
                        cb = data.cb.details,
                        ind = this.$options.propsData.ind,
                        res = false;
                    if(cb && (cb.status === 200)){
                        res = cb.data.filter(el => el.id === ind).length;
                        this.upCartCounter(cb.data.length);
                        data.self.addItem && this.update({
                            vc: 'msg',
                            ui: {
                                title: 'Added to Cart',
                                msg: 'This item has been added to your Cart.',
                                buttons: [
                                    {title: 'Continue Shopping', cl: 'btn violet', cmd: 'cancel'},
                                    {title: 'Go to Checkout', cl: 'btn violet hover', cmd: 'cart',
                                        img: '/nox-themes/default/images/cart-icon16.png', img_w: 16, img_h: 13}
                                ]
                            }
                        });
                    }
                    data.self = {};
                    return res;
                }
            }
        }
    })();
</script>
