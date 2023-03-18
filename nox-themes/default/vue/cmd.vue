<template>
    <div @click="cmd" style="display: inline-block;">
        <div v-if="mode === 'addItem'" prm="addItem" class="btn hover f14">
            <img src="/nox-themes/default/images/add-icon16.png" width="14" height="14">&nbsp;
            Add to Cart&nbsp;&nbsp;${{price}}
        </div>
        <div v-else-if="mode === 'removeItem'" prm="removeItem" class="btn f14" style="margin-left: 10px;">
            Remove
        </div>
        <div v-else-if="mode === 'goToCart'" prm="goToCart" class="btn hover f14">
            <img src="/nox-themes/default/images/cart-icon16.png" width="16" height="13">&nbsp;
            Go to Cart
        </div>
    </div>
</template>

<script>
    (function () {
        return {
            props: ['ind', 'price', 'mode'],
            data: function () {
                return {
                    mode: this.$options.propsData.mode,
                    price: this.$options.propsData.price
                };
            },
            methods: {
                cmd: function (e) {
                    let prm = e.target.getAttribute('prm'),
                        ind = this.$options.propsData.ind;
                    switch (prm) {

                    }
                    if(prm === 'addItem'){
                        console.log('add', ind);
                        // this.update({
                        //     vc: 'explore',
                        //     ui: {
                        //         id: ind
                        //     }
                        // });
                    }
                }
            }
        }
    })();
</script>
