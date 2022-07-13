i18next.loadNamespaces(['core']);

var app = new Vue({
    el: '#app',
    data: Object.assign({
        moduleUrl: adminBaseUrl + '/sunbet/users',
        itemsLDAP: 0,
        itemsNotLDAP: 0,
        itemsNotVerified: 0,
        itemsSuperadmins: 0,
        itemsBanned: 0,
        itemsVerified: 0,
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
        enableField(fieldName, e) {
            var message = '',
                field = document.querySelector('[name="' + fieldName + '"]');

            if (message) {
                swal.fire({
                    title: i18next.t('core::messages.sure'),
                    text: message,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: i18next.t('core::actions.enable'),
                    cancelButtonText: i18next.t('core::actions.cancel'),
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.value) {
                        return false;
                    } else {
                        e.target.classList.add('hidden');
                        field.readOnly = false;
                        field.disabled = false;
                    }
                });
            } else {
                e.target.classList.add('hidden');
                field.readOnly = false;
                field.disabled = false;
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
