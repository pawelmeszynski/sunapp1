i18next.loadNamespaces(['core']);

var app = new Vue({
    el: '#app',
    data: Object.assign({ moduleUrl: adminBaseUrl + '/sec-exceptions' }, dataObj),
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
        checkAbilities(e) {
            let vm = this;

            var checkbox = e.target;
            var is_checked = checkbox.checked;
            var ability = checkbox.getAttribute('data-ability');
            var row = checkbox.closest('tr');

            var with_show = ['create', 'edit', 'destroy'];

            if (ability === '*') {
                row.querySelectorAll('input[type="checkbox"]').forEach(function (item) {
                    var current_ability = item.getAttribute('data-ability');
                    if (current_ability != ability) {
                        item.checked = false;
                    }
                });
            } else if (with_show.includes(ability) && is_checked) {
                row.querySelector('input[type="checkbox"][data-ability="show"]').checked = true;
            }

            if (ability !== '*') {
                row.querySelector('input[type="checkbox"][data-ability="*"]').checked = false;
            }

            if (ability === 'show' && !is_checked) {
                row.querySelectorAll('input[type="checkbox"]').forEach(function (item) {
                    var current_ability = item.getAttribute('data-ability');
                    if (with_show.includes(current_ability)) {
                        item.checked = false;
                    }
                });
            }
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
