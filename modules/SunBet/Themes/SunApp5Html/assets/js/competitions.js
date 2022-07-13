i18next.loadNamespaces(['core']);

var app = new Vue({
    el: '#app',
    data: Object.assign({
        moduleUrl: adminBaseUrl + '/sunbet/competitions',
    }, dataObj),
    watch: {
        searchPhrase(newSearch, oldSearch) {
            this.searchPhraseWatch(newSearch, oldSearch)
        }
    },
    methods: {
        hideCard() {
            let vm = this;

            vm.prepareToHide();

            if (vm.lastSearchUrl) {
                vm.changeUrl({}, "SunApp5", vm.lastSearchUrl);
            } else {
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            }
        },
        clearSearchPhrase() {
            let vm = this;
            vm.searchPhrase = '';
            if (vm.searchedWithPhrase) {
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            }
            vm.searchedWithPhrase = false;
        },
    },
    mounted: function () {
        this.mountedHook();
    },
    created: function () {
        let vm = this;
        vm.popChange();
    },
});
