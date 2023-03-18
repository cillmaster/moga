<template>
    <div @click="cmd">
        <div class="action-panel">
            <select v-model="self.active" style="margin-bottom: 12px;">
                <option v-for="opt in self.optCollections" :value="opt.val">{{opt.text}}</option>
            </select>
            <div prm="edit|0" class="action-button">Добавить коллекцию</div>
        </div>
        <table>
            <thead>
                <th>ID</th>
                <th>Name</th>
                <th>Url</th>
                <th>Actions</th>
            </thead>
            <tbody>
            <template v-for="item in view.items">
                <template v-if="+item.id === +self.active">
                    <tr style="background: #f6f6f6;">
                        <td>{{item.id}}</td>
                        <td>{{item.name}}</td>
                        <td>{{item.url}}</td>
                        <td>
                            <span :prm="'edit|' + item.id" class="oper">edit</span>
                            <span :prm="'add|' + item.id" class="oper">add_vector</span>
                            <span :prm="'view|' + item.id" class="oper">view</span>
                        </td>
                    </tr>
                    <template v-if="item.list">
                        <tr v-for="v in item.list">
                            <td></td>
                            <td colspan="2">{{self.v[v]}}</td>
                            <td>
                                <span :prm="'del|' + item.id + '|' + v" class="oper">remove</span>
                            </td>
                        </tr>
                    </template>
                </template>
            </template>
            </tbody>
        </table>
    </div>
</template>

<script>
    (function () {
        return {
            data: function () {
                Storage.external.collections = {details: {}};
                return {
                    external: Storage.external.collections,
                    self: {
                        active: 1,
                        fields: ['id', 'name', 'url', 'caption', 'title', 'text', 'description'],
                        map: {},
                        optCollections: [],
                        tmp: {
                            collection: {id: 0, name: '', url: '', caption: '', title: '',
                                text: '', description: ''}
                        },
                        v: {}
                    }
                };
            },
            methods: {
                cmd: function (e) {
                    let prm = e.target.getAttribute('prm');
                    if(!prm) return;
                    let data = this.$data,
                        external = data.external,
                        self = data.self,
                        tmp, _prm, _tmp;
                    prm = prm.split('|');
                    if(!/(add|expand|collapse|view)/.test(prm[0])){
                        tmp = {
                            method: 'POST',
                            url: '/administrator/prints?section=collections&action=cmd',
                            data: {},
                            _vc: {
                                storage: external,
                                property: 'details'
                            }
                        }
                    }
                    switch (prm[0]) {
                        case 'add':
                            location.assign('/administrator/prints?section=vector&collection=' + prm[1]);
                            break;
                        case 'del':
                            tmp.data = {
                                cmd: 'remove',
                                cID: prm[1],
                                vID: prm[2]
                            };
                            break;
                        case 'edit':
                            if(prm[1] === '0'){
                                _prm = self.tmp.collection;
                            } else {
                                _prm = self.map[prm[1]];
                            }
                            for(let i = 0; i < self.fields.length; i++){
                                tmp.data[self.fields[i]] = _prm[self.fields[i]];
                            }
                            tmp.data.cmd = tmp.data.id ? 'edit' : 'create';
                            tmp._vc.beforeExec = {
                                vc: 'collection-edit'
                            };
                            break;
                        case 'expand':
                            break;
                        case 'collapse':
                            break;
                        case 'view':
                            window.open('/collections/' + self.map[prm[1]].url);
                            break;
                    }
                    tmp && this.update(tmp);
                },
                getDetails: function () {
                    this.update({
                        method: 'GET',
                        url: '/administrator/prints?section=collections&action=data&rnd=' + Math.random(),
                        _vc: {
                            storage: this.$data.external,
                            property: 'details'
                        }
                    });
                },
            },
            computed: {
                view: function () {
                    let data = this.$data,
                        external = data.external,
                        self = data.self,
                        details = external.details,
                        res = {items: []},
                        items, tmp, opt = [];
                    if(details.status === 200){
                        items = details.data;
                        for(let i = 0; i < items.length; i++){
                            tmp = items[i];
                            tmp.list = details.map[tmp.id];
                            res.items.push(tmp);
                            self.map[items[i].id] = tmp;
                            opt.push({
                                text: items[i].name,
                                val: items[i].id
                            })
                        }
                        self.v = details.vectors;
                        self.optCollections = opt;
                    }
                    return res;
                }
            },
            mounted: function () {
                this.getDetails();
            },
        }
    })();
</script>