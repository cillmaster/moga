<template>
    <div id="admin">
        <collection-list></collection-list>
        <app-modal :is="vc.view.modal"></app-modal>
    </div>
</template>

<script>
    (function () {
        return {
            data: function () {
                if(!window.Storage){
                    window.Storage = {external: {}, internal: {}, local: {}};
                } else {
                    window.Storage.external = window.Storage.external || {};
                    window.Storage.internal = window.Storage.internal || {};
                    window.Storage.local = window.Storage.local || {};
                }
                return window.Storage;
            }
        }
    })();
</script>