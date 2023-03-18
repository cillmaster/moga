<template>
    <div @click="cmd" class="app-shadow">
        <div class="app-modal-wrapper">
            <div class="frame">
                <div class="title">{{view.title}}</div>
                <div class="content">
                    <div class="d-table w100p msg" v-html="view.msg"></div>
                    <div class="buttons-cont">
                        <template v-for="btn in view.buttons">
                            <div :prm="btn.cmd" :class="btn.cl">
                                <template v-if="btn.img">
                                    <img :src="btn.img" :width="btn.img_w" :height="btn.img_h">&nbsp;
                                </template>
                                {{btn.title}}
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div prm="0" class="app-modal-close"></div>
        </div>
    </div>
</template>

<script>
    (function () {
        return {
            data: function () {
                return {
                    self: {}
                };
            },
            methods: {
                cmd: function (e) {
                    let prm = e.target.getAttribute('prm');
                    if(!prm) return;
                    switch (prm) {
                        case 'cancel':
                            this.response(false);
                            break;
                        case 'cart':
                            location.assign('/checkout/cart');
                            break;
                        default:
                            this.response(!+prm ? false : this.vc.modal);
                    }
                }
            },
            computed: {
                view: function () {
                    return this.vc.modal.ui;
                }
            }
        }
    })();
</script>
