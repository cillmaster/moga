<template>
    <div class="app-modal">
        <div @click="cmd" class="modal-content-wrapper">
            <div class="header app-header modal">
                <div>{{self.mapMode[vc.modal.data.cmd].title}}</div>
                <i prm="0" class="fa fa-close btn-close"></i>
            </div>
            <form>
                <div class="form-item row-container">
                    <div>
                        <input v-model="vc.modal.data.name">
                        <label>название <span v-show="!vc.modal.data.name">не может быть пустым</span></label>
                    </div>
                    <div>
                        <input v-model="vc.modal.data.url">
                        <label>url <span v-show="!vc.modal.data.url">не может быть пустым</span></label>
                    </div>
                    <div>
                        <input v-model="vc.modal.data.title">
                        <label>заголовок окна <span v-show="!vc.modal.data.title">не может быть пустым</span></label>
                    </div>
                    <div>
                        <input v-model="vc.modal.data.caption">
                        <label>заголовок H1 <span v-show="!vc.modal.data.caption">не может быть пустым</span></label>
                    </div>
                    <div>
                        <textarea v-model="vc.modal.data.text"></textarea>
                        <label>текст под заголовком <span v-show="!vc.modal.data.text">не может быть пустым</span></label>
                    </div>
                    <div>
                        <textarea v-model="vc.modal.data.description"></textarea>
                        <label>META description <span v-show="!vc.modal.data.description">не может быть пустым</span></label>
                    </div>
                </div>
            </form>
            <footer class="oper-wrapper feed-item-last">
                <div class="oper-container">
                    <button v-for="btn in btn" :prm="btn.cmd" class="btn">{{btn.title}}</button>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    (function () {
        return {
            data: function () {
                return {
                    self: {
                        mapMode: {
                            create: {
                                btn: 'Создать',
                                title: 'Создание коллекции'
                            },
                            edit: {
                                btn: 'Сохранить',
                                title: 'Редактирование коллекции'
                            }
                        }
                    }
                };
            },
            methods: {
                cmd: function (e) {
                    let prm, data, res = false;
                    if(!(prm = e.target.getAttribute('prm'))) return;
                    if(+prm){
                        res = this.vc.modal;
                    }
                    this.response(res);
                }
            },
            computed: {
                btn: function () {
                    let data = this.vc.modal.data,
                        self = this.$data.self,
                        res = [], check;
                    check = data.name.length
                        && data.url.length
                        && data.caption.length
                        && data.title.length
                        && data.text.length
                        && data.description.length;
                    res.push({title: 'Отмена', cmd: 0});
                    if(check){
                        res.push({title: self.mapMode[data.cmd].btn, cmd: 1});
                    }
                    return res;
                }
            }
        }
    })();
</script>