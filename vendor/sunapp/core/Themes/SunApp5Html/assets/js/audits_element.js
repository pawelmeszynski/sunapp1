i18next.loadNamespaces(['core']);

var app = new Vue({
    el: '#app',
    data: Object.assign({
        moduleName: 'audits',
        moduleUrl: adminBaseUrl + '/update-history/element'
    }, dataObj),
    watch: {
        searchPhrase(newSearch, oldSearch) {
            this.searchPhraseWatch(newSearch, oldSearch);
        }
    },
    methods: {
        clearSearchPhrase() {
            let vm = this;
            vm.searchPhrase = '';
            if (vm.searchedWithPhrase) {
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            }
            vm.searchedWithPhrase = false;
        },
        hideCard() {
            let vm = this;

            vm.prepareToHide();
        }
    },
    mounted: function () {
        this.mountedHook();
    },
    created: function () {
        let vm = this;
        vm.popChange();
    },
});
