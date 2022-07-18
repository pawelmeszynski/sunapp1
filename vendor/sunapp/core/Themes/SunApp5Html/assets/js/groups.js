i18next.loadNamespaces(['core']);

var app = new Vue({
    el: '#app',
    data: Object.assign({
        moduleUrl: adminBaseUrl + '/groups',
        maxRootCount: 1,
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
    updated: function () {
        var ref = this;
        ref.$nextTick(function () {
            if (typeof ref.$refs.nested != 'undefined') {
                if (document.querySelectorAll('.dd-item').length) {
                    var items = document.querySelectorAll('.dd-item');
                    for (var item = items.length - 1; item >= 0; item--) {
                        if (items[item].getAttribute('data-parent')) {
                            var parentId = items[item].getAttribute('data-parent');
                            var parent = document.querySelector('.dd-item[data-id="' + parentId + '"] ol');
                            if (parent) {
                                if (!parent.firstChild) {
                                    parent.appendChild(items[item]);
                                } else {
                                    parent.insertBefore(items[item], parent.firstChild);
                                }
                            }
                        }
                    }
                }
            }
        })
    },
    created: function () {
        let vm = this;
        vm.popChange();
    },
});
