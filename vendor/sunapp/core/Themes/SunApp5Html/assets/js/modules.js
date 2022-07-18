i18next.loadNamespaces(['core']);

var app = new Vue({
    el: '#app',
    data: Object.assign({
        moduleUrl: adminBaseUrl + '/modules',
        showModulesToInstall: false,
        installModuleStatus: false,
        installModuleStatusText: '',
        modulesToInstall: []
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
        getModulesToInstall() {
            let url = adminBaseUrl+"/getModules/getFromComposer";
            let vm = this;
            vm.isLoading = true;
            vm.items = [];
            axios.get(url, {
                headers: vm.queryHeaders
            }).then(function (response) {
                vm.isLoading = false;
                vm.showModulesToInstall = true;
                let data = response.data;
                vm.modulesToInstall = data;
            });
        },
        installModule(status, obj) {//dd
            let url = adminBaseUrl+"/getModules/install";
            let vm = this;

            vm.queryHeaders['X-CSRF-TOKEN'] = vm.getMetaValue('csrf-token');

            switch (status) {
                case true:
                    info = i18next.t('core::modules.installModule');
                    vm.installModuleStatusText = i18next.t('core::modules.installingModule');
                    break;
                case false:
                    info = i18next.t('core::modules.uninstallModule');
                    vm.installModuleStatusText = i18next.t('core::modules.uninstalingModule');
                    break;
            }

            swal.fire({
                title: i18next.t('core::messages.sure'),
                text: info,
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: i18next.t('core::actions.yes'),
                cancelButtonText: i18next.t('core::actions.cancel'),
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    vm.items = [];
                    vm.installModuleStatus = true;

                    axios.post(url, {
                        headers: vm.queryHeaders,
                        data: {
                            status: status,
                            obj:obj
                        }
                    }).then(function (response) {
                        console.log(response.data);
                        vm.installModuleStatus = false;
                        vm.installModuleStatusText = '';
                        window.location.reload();
                    });
                }
            });
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

Vue.prototype.getMetaValue = (name) => {
    const element = document.querySelector(`meta[name="${name}"]`)
    return element?.getAttribute('content')
}

Vue.prototype.permitElement = function (e, type, status, item) {
    let vm = this;
    var href = e.currentTarget.getAttribute('href');
    var dependency = '';
    switch (type) {
        case 'enableModule':
            info = i18next.t('core::modules.enable');
            vm.installModuleStatusText = i18next.t('core::modules.enableModuleStatus');
            break;
        case 'disableModule':
            dependency = dependencyCheckingModule(vm, item);
            info = i18next.t('core::modules.disable');
            vm.installModuleStatusText = i18next.t('core::modules.disableModuleStatus');
            break;
    }

    if(dependency != '') {
        info = dependency;
    }

    vm.queryHeaders['X-CSRF-TOKEN'] = vm.getMetaValue('csrf-token');

    swal.fire({
        title: i18next.t('core::messages.sure'),
        text: info,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: i18next.t('core::actions.yes'),
        cancelButtonText: i18next.t('core::actions.cancel'),
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: false,
    }).then(function (result) {
        if (result.value) {
            vm.items = [];
            vm.isLoading = true;
            vm.installModuleStatus = true;
            axios.post(href, {
                headers: vm.queryHeaders,
                status: status,
                name: item.name,
                type: type
            }).then(function (response) {
                var data = response.data;
                vm.installModuleStatus = false;
                vm.installModuleStatusText = '';
                if (data.status === 'error') {
                    toastr.error(data.message, i18next.t('core::messages.error'), {
                        positionClass: 'toast-bottom-right'
                    });
                } else if (data.status === 'success') {
                    toastr.success(data.message, i18next.t('core::messages.success'), {
                        positionClass: 'toast-bottom-right'
                    });
                }
                window.location.reload();
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            }).catch(function (error) {
                vm.catchErrors(error);
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            });
        }
    });
};

function dependencyCheckingModule(vm, item) {
    var moduleStatuses = vm.loadedData.meta.params.moduleStatuses;
    var str = '';
    item.checkWhereUsedModule.forEach(function(val, key) {
        if(moduleStatuses[val] == true) {
            str += ' '+val;
        }
    });
    if(str == '') {
        return '';
    }
    return i18next.t('core::modules.disableMoreModules')+str;
}
