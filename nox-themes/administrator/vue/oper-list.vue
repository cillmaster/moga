<template>
    <div @click="cmd">
        <br>
        <table>
            <thead>
                <th>Task</th>
                <th>Action</th>
            </thead>
            <tbody>
            <tr v-for="item in view.items">
                <td>{{item.name}}</td>
                <td>
                    <span :prm="item.cmd" class="oper">run</span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    (function () {
        return {
            data: function () {
                Storage.external.oper = {details: {}};
                return {
                    external: Storage.external.oper
                };
            },
            methods: {
                cmd: function (e) {
                    let prm = e.target.getAttribute('prm');
                    if(!prm) return;
                    this.update({
                        method: 'POST',
                        url: '/administrator/system?section=oper&action=cmd',
                        data: {
                            cmd: prm
                        },
                        _vc: {
                            storage: this.$data.external,
                            property: 'details'
                        }
                    });
                },
                getDetails: function () {
                    this.update({
                        method: 'GET',
                        url: '/administrator/system?section=oper&action=data&rnd=' + Math.random(),
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
                        details = external.details,
                        res = {items: []};
                    if(details.status === 200){
                        res.items = details.data;
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