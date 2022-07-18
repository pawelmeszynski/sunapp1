formTranslation = true;

var dataObj = {
    canLeaveRoute: true,
    forceLeaveRoute: false,
    canSearch: true,
    loadedData: false,
    createUrl: '',
    currentLang: '',
    currentProgress: 0,
    currentPageUrl: false,
    defaultItemEdition: false,
    elementToShow: false,
    elementToShowCopy: false,
    itemDuplicating: false,
    itemEdition: false,
    itemSaved: false,
    itemPreview: false,
    itemRestoring: false,
    items: [],
    itemsFilter: 'all',
    itemsTotal: 0,
    itemsActive: 0,
    itemsInactive: 0,
    itemsTrashed: 0,
    isLoading: true,
    languages: [],
    lastCreatedUrl: '',
    lastUrl: '',
    lastSearchUrl: '',
    additional_search: {},
    additional_query_params: {},
    loaderHtml: '<div class="placeholders placeholders--domains placeholders--inner">' +
        '        <div class="placeholder placeholder--domains">' +
        '            <div class="placeholder-content-wrapper">' +
        '                <div class="placeholder-content">' +
        '                    <div class="placeholder-content_item"></div>' +
        '                </div>' +
        '            </div>' +
        '            <div class="placeholder-content-wrapper">' +
        '                <div class="placeholder-content">' +
        '                    <div class="placeholder-content_item"></div>' +
        '                </div>' +
        '            </div>' +
        '        </div>' +
        '    </div>',
    loadingCircle: false,
    mediaLoading: false,
    nextItemsLoading: false,
    nextPageUrl: false,
    ordering: false,
    panelOpen: false,
    parentId: null,
    perPage: false,
    preventChangeUrl: false,
    searchedWithPhrase: false,
    searchPhrase: '',
    sorting: false,
    storeUrl: '',
    userCanCreate: false,
    queryHeaders: {
        'X-Requested-With': 'XMLHttpRequest',
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0'
    }
};

var mediaCallback = {
    maxDepth: 1,
    callback: function (l, e) {
        var items = document.querySelectorAll('.dd-item-media');
        var i = items.length;

        for (var j = 0; j < i; j++) {
            $element = items[j];
            $element.querySelector('input[data-field="order"]').value = j + 1;
        }
    }
};

Vue.directive('select2', {
    inserted(el) {
        $(el).on('select2:select', () => {
            const event = new Event('change', {bubbles: true, cancelable: true});
            el.dispatchEvent(event);
        });

        $(el).on('select2:unselect', () => {
            const event = new Event('change', {bubbles: true, cancelable: true});
            el.dispatchEvent(event)
        })
    },
});

Vue.prototype.selectAll = function (e) {
    var vm = this;

    var checkboxesChecked = 0,
        check = e.target.checked,
        checkboxes = document.querySelectorAll('.list-item input[type="checkbox"]'),
        removeAll = document.getElementById('list-inline-item--remove'),
        restoreAll = document.getElementById('list-inline-item--restore');
    checkboxes.forEach(function (checkbox) {
        checkbox.checked = check;
        if (check) {
            checkboxesChecked++;
        }
    });
    if (checkboxesChecked && document.querySelectorAll('.destroy-element').length) {
        if (removeAll) {
            removeAll.classList.remove("hidden");
        }
        if (vm.itemsFilter === 'trashed') {
            if (restoreAll) {
                restoreAll.classList.remove("hidden");
            }
        }
    } else {
        if (removeAll) {
            removeAll.classList.add("hidden");
        }
        if (restoreAll) {
            restoreAll.classList.add("hidden");
        }
    }
};

Vue.prototype.selectOne = function (e) {
    var checkboxesChecked = 0,
        checkboxes = document.querySelectorAll('.list-item input[type="checkbox"]'),
        checkboxesCount = checkboxes.length;

    checkboxes.forEach(function (checkbox) {
        if (checkbox.checked) {
            checkboxesChecked++;
        }
    });

    var removeAll = document.getElementById('list-inline-item--remove'),
        restoreAll = document.getElementById('list-inline-item--restore');
    if (checkboxesChecked > 1 && document.querySelectorAll('.destroy-element').length) {
        if (checkboxesChecked === checkboxesCount) {
            document.getElementById('selectAllCheckbox').checked = true
        } else {
            document.getElementById('selectAllCheckbox').checked = false;
        }
        if (removeAll) {
            removeAll.classList.remove("hidden");
        }
        if (window.location.href.indexOf('trashed=only') > -1 && restoreAll) {
            restoreAll.classList.remove("hidden");
        }
    } else if (checkboxesChecked === 1) {
        var checkedItem = document.querySelector('.list-item input[type="checkbox"]:checked'),
            canTrash = 0;
        if (checkedItem) {
            canTrash = checkedItem.closest('.list-item').querySelectorAll('.destroy-element').length;
        }
        if (canTrash) {
            document.getElementById('selectAllCheckbox').checked = (checkboxesChecked === checkboxesCount);

            if (removeAll) {
                removeAll.classList.remove("hidden");
            }
            if (window.location.href.indexOf('trashed=only') > -1 && restoreAll) {
                restoreAll.classList.remove("hidden");
            }
        } else {
            if (removeAll) {
                removeAll.classList.add("hidden");
            }
            if (restoreAll) {
                restoreAll.classList.add("hidden");
            }
            document.getElementById('selectAllCheckbox').checked = false;
        }
    } else {
        if (removeAll) {
            removeAll.classList.add("hidden");
        }
        if (restoreAll) {
            restoreAll.classList.add("hidden");
        }
        document.getElementById('selectAllCheckbox').checked = false;
    }
};

Vue.prototype.removeAll = function () {
    let vm = this;

    swal.fire({
        title: i18next.t('core::messages.sure'),
        text: i18next.t('core::messages.will_remove_selected'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: i18next.t('core::actions.remove'),
        cancelButtonText: i18next.t('core::actions.cancel'),
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: false,
    }).then(function (result) {
        if (result.value) {
            vm.items = [];
            vm.isLoading = true;
            var checkboxes = document.querySelectorAll('.item-checkbox:checked'),
                formsCount = 0,
                i = 0;

            checkboxes.forEach(function (checkbox) {
                var form = checkbox.closest('.list-item').querySelector('form.form-delete');
                if (form) {
                    formsCount++;
                }
            });

            var progressPercent = 100 / formsCount,
                removedItems = 0;
            vm.currentProgress = 1;

            checkboxes.forEach(function (checkbox) {
                var form = checkbox.closest('.list-item').querySelector('form.form-delete');
                if (form) {
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        var field = document.querySelector('[name="' + pair[0] + '"]');
                        if (field.readOnly) {
                            formData.delete(pair[0]);
                        }
                    }
                    axios.post(form.getAttribute('action'), formData, {
                        headers: vm.queryHeaders
                    }).then(function (response) {
                        i++;
                        vm.currentProgress = progressPercent * ++removedItems;
                        var data = response.data;
                        if (data.status === 'error') {
                            toastr.error(data.message, i18next.t('core::messages.error'), {
                                positionClass: 'toast-bottom-right'
                            });
                        } else if (data.status === 'success') {
                            toastr.success(data.message, i18next.t('core::messages.success'), {
                                positionClass: 'toast-bottom-right'
                            });
                        }
                        if (i === formsCount) {
                            vm.currentProgress = 100;
                            vm.getItems(vm.itemsFilter, vm.searchPhrase);
                            document.getElementById('selectAllCheckbox').checked = false;

                            var removeAll = document.getElementById('list-inline-item--remove'),
                                restoreAll = document.getElementById('list-inline-item--restore');
                            if (removeAll) {
                                removeAll.classList.add('hidden');
                            }
                            if (restoreAll) {
                                restoreAll.classList.add('hidden');
                            }

                            if (vm.moduleUrl.indexOf('settings/languages') > -1) {
                                window.location.reload();
                            }
                        }
                    }).catch(function (error) {
                        vm.catchErrors(error);
                    });
                } else {
                    vm.getItems(vm.itemsFilter, vm.searchPhrase);
                    toastr.error(i18next.t('core::messages.try_again'), i18next.t('core::messages.error'), {
                        positionClass: 'toast-bottom-right'
                    });
                }
            });
        }
    });
};

Vue.prototype.removeElement = function (e, type = false, url = null) {
    let vm = this;
    var form = e.target.parentNode.nextElementSibling;

    swal.fire({
        title: i18next.t('core::messages.sure'),
        text: i18next.t('core::messages.will_remove_element'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: i18next.t('core::actions.remove'),
        cancelButtonText: i18next.t('core::actions.cancel'),
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: false,
    }).then(function (result) {
        if (result.value) {
            document.querySelector('.sg-user-list').scrollTop = 0;
            vm.items = [];
            vm.isLoading = true;

            if ((type && url) || form) {
                var formData;
                if (form) {
                    url = form.getAttribute('action');
                    formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        var field = document.querySelector('[name="' + pair[0] + '"]');
                        if (field.readOnly) {
                            formData.delete(pair[0]);
                        }
                    }
                } else {
                    formData = new FormData();
                    formData.append('_method', 'DELETE');

                    const token = document.querySelector('meta[name="csrf-token"]');
                    if (token) {
                        formData.append('_token', token.getAttribute('content'));
                    }
                }

                vm.loadingCircle = true;
                axios.post(url, formData, {
                    headers: vm.queryHeaders
                }).then(function (response) {
                    var data = response.data;
                    if (data.status === 'error') {
                        toastr.error(data.message, i18next.t('core::messages.error'), {
                            positionClass: 'toast-bottom-right'
                        });
                    } else if (data.status === 'success') {
                        toastr.success(data.message, i18next.t('core::messages.success'), {
                            positionClass: 'toast-bottom-right'
                        });
                        vm.hideCard();
                    }
                    vm.getItems(vm.itemsFilter, vm.searchPhrase);
                    if (vm.moduleUrl.indexOf('settings/languages') > -1) {
                        window.location.reload();
                    }
                }).catch(function (error) {
                    vm.catchErrors(error);
                    vm.getItems(vm.itemsFilter, vm.searchPhrase);
                });
            } else {
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
                toastr.error(i18next.t('core::messages.try_again'), i18next.t('core::messages.error'), {
                    positionClass: 'toast-bottom-right'
                });
            }
        }

        var $selectAll = document.getElementById('selectAllCheckbox');

        if ($selectAll) {
            $selectAll.checked = false;
        }
    });
};

Vue.prototype.restoreAll = function () {
    let vm = this;

    swal.fire({
        title: i18next.t('core::messages.sure'),
        text: i18next.t('core::messages.will_restore_selected'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: i18next.t('core::actions.restore'),
        cancelButtonText: i18next.t('core::actions.cancel'),
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: false,
    }).then(function (result) {
        if (result.value) {
            vm.items = [];
            vm.isLoading = true;
            var checkboxes = document.querySelectorAll('.item-checkbox:checked'),
                formsCount = 0,
                i = 0;

            checkboxes.forEach(function (checkbox) {
                var form = checkbox.closest('.list-item').querySelector('form.form-restore');
                if (form) {
                    formsCount++;
                }
            });

            var progressPercent = 100 / formsCount,
                restoredItems = 0;
            vm.currentProgress = 1;

            checkboxes.forEach(function (checkbox) {
                var form = checkbox.closest('.list-item').querySelector('form.form-restore');
                if (form) {
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        var field = document.querySelector('[name="' + pair[0] + '"]');
                        if (field.readOnly) {
                            formData.delete(pair[0]);
                        }
                    }
                    axios.post(form.getAttribute('action'), formData, {
                        headers: vm.queryHeaders
                    }).then(function (response) {
                        i++;
                        vm.currentProgress = progressPercent * ++restoredItems;
                        var data = response.data;
                        if (data.status === 'error') {
                            toastr.error(data.message, i18next.t('core::messages.error'), {
                                positionClass: 'toast-bottom-right'
                            });
                        } else if (data.status === 'success') {
                            toastr.success(data.message, i18next.t('core::messages.success'), {
                                positionClass: 'toast-bottom-right'
                            });
                        }
                        if (i === formsCount) {
                            vm.currentProgress = 100;
                            vm.getItems(vm.itemsFilter, vm.searchPhrase);
                            document.getElementById('selectAllCheckbox').checked = false;

                            var removeAll = document.getElementById('list-inline-item--remove'),
                                restoreAll = document.getElementById('list-inline-item--restore');
                            if (removeAll) {
                                removeAll.classList.add('hidden');
                            }
                            if (restoreAll) {
                                restoreAll.classList.add('hidden');
                            }

                            if (vm.moduleUrl.indexOf('settings/languages') > -1) {
                                window.location.reload();
                            }
                        }
                    }).catch(function (error) {
                        vm.catchErrors(error);
                    });
                } else {
                    vm.getItems(vm.itemsFilter, vm.searchPhrase);
                    toastr.error(i18next.t('core::messages.try_again'), i18next.t('core::messages.error'), {
                        positionClass: 'toast-bottom-right'
                    });
                }
            });
        }
    });
};

Vue.prototype.restoreElement = function (e, type = false, url = null) {
    let vm = this;
    vm.itemRestoring = true;
    var form = e.target.parentNode.nextElementSibling;

    swal.fire({
        title: i18next.t('core::messages.sure'),
        text: i18next.t('core::messages.will_be_restored'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: i18next.t('core::actions.restore'),
        cancelButtonText: i18next.t('core::actions.cancel'),
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: false,
    }).then(function (result) {
        if (result.value) {
            document.querySelector('.sg-user-list').scrollTop = 0;
            vm.items = [];
            vm.isLoading = true;

            if ((type && url) || form) {
                var formData;
                if (form) {
                    url = form.getAttribute('action');
                    formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        var field = document.querySelector('[name="' + pair[0] + '"]');
                        if (field.readOnly) {
                            formData.delete(pair[0]);
                        }
                    }
                } else {
                    formData = new FormData();
                    formData.append('_method', 'PATCH');
                    formData.append('restore', true);

                    const token = document.querySelector('meta[name="csrf-token"]');
                    if (token) {
                        formData.append('_token', token.getAttribute('content'));
                    }
                }

                vm.hideCard();
                axios.post(url, formData, {
                    headers: vm.queryHeaders
                }).then(function (response) {
                    var data = response.data;
                    if (data.status === 'error') {
                        toastr.error(data.message, i18next.t('core::messages.error'), {
                            positionClass: 'toast-bottom-right'
                        });
                    } else if (data.status === 'success') {
                        toastr.success(data.message, i18next.t('core::messages.success'), {
                            positionClass: 'toast-bottom-right'
                        });
                        if (vm.moduleUrl.indexOf('settings/languages') > -1) {
                            window.location.reload();
                        }
                    }
                    vm.getItems(vm.itemsFilter, vm.searchPhrase);
                }).catch(function (error) {
                    vm.catchErrors(error);
                    vm.getItems(vm.itemsFilter, vm.searchPhrase);
                });
            } else {
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
                toastr.error(i18next.t('core::messages.try_again'), i18next.t('core::messages.error'), {
                    positionClass: 'toast-bottom-right'
                });
            }
        }
        document.getElementById('selectAllCheckbox').checked = false;
    });
};

Vue.prototype.catchErrors = function (error) {
    let vm = this;

    if (error.response) {
        if (error.response.status === 401) {
            window.location.reload();
        }

        var data = error.response.data;
        toastr.error(data.message, i18next.t('core::messages.error'), {
            positionClass: 'toast-bottom-right'
        });

        if (typeof data.trace !== 'undefined') {
            console.error(data.trace);
        }

        vm.enableSaveButtons();

        var errors = error.response.data.errors;

        if (errors) {
            Object.keys(errors).forEach(function (key) {
                var element = document.querySelector('[data-id="' + key + '"]');
                if (element) {
                    var formError = element.querySelector('.form-error');
                    if (formError) {
                        element.querySelector('.form-error').innerText = errors[key];
                    }
                    element.classList.add('error');

                    var tab = element.closest('.tab-pane');

                    if (tab) {
                        var tabId = tab.id;
                        var navLink =  document.querySelector('.nav-link[href="#' + tabId + '"]')
                            ||  document.querySelector('.sg-nav-link[href="#' + tabId + '"]');
                        if (navLink) {
                            navLink.classList.add('error');
                        }
                    }

                    if (element.hasAttribute("data-lang")) {
                        var others = element.closest('.form-group-translation').querySelectorAll('.form-group');

                        if (others) {
                            for (var i = 0; i < others.length; i++) {
                                if (others[i] !== element) {
                                    others[i].querySelector('.other-lang-error').classList.remove('hidden');
                                }
                            }
                        }
                    }
                }
            });
        }
    }
};

Vue.prototype.readOnlyFields = function () {
    let vm = this;

    var form = vm.$refs.form_wrapper.querySelector('form#form-data');
    var elements = form.elements;

    for (var i = 0, len = elements.length; i < len; ++i) {
        if (elements[i].getAttribute('data-force_readonly') == "1") {
            elements[i].readOnly = true;
        }
    }

    var enableFieldsButtons = vm.$refs.form_wrapper.querySelectorAll('.enable-field');
    enableFieldsButtons.forEach(function (e) {
        e.classList.remove('hidden');
    });
};

Vue.prototype.searchPhraseWatch = function (newSearch) {
    var clearButton = document.getElementById('clear-search'),
        searchIcon = document.querySelector('.search-icon');
    if (newSearch) {
        clearButton.style.display = 'block';
        searchIcon.classList.add('active');
    } else {
        clearButton.style.display = 'none';
        searchIcon.classList.remove('active');
    }
};

Vue.prototype.activeElement = function (e) {
    let vm = this;
    var form;

    if (e.target.classList.contains('chip-text')) {
        form = e.target.closest('.chip').nextElementSibling;
    } else {
        form = e.target.parentNode.nextElementSibling;
    }

    swal.fire({
        title: i18next.t('core::messages.sure'),
        text: i18next.t('core::messages.activity_will_be_changed'),
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

            var formData = new FormData(form);
            for (var pair of formData.entries()) {
                var field = document.querySelector('[name="' + pair[0] + '"]');
                if (field.readOnly) {
                    formData.delete(pair[0]);
                }
            }

            axios.post(form.getAttribute('action'), formData, {
                headers: vm.queryHeaders
            }).then(function (response) {
                var data = response.data;
                if (data.status === 'error') {
                    toastr.error(data.message, i18next.t('core::messages.error'), {
                        positionClass: 'toast-bottom-right'
                    });
                } else if (data.status === 'success') {
                    toastr.success(data.message, i18next.t('core::messages.success'), {
                        positionClass: 'toast-bottom-right'
                    });
                }
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            }).catch(function (error) {
                vm.catchErrors(error);
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            });
        }
    });
};

Vue.prototype.createElement = function (type) {
    let vm = this;
    vm.elementToShow = false;
    vm.panelOpen = true;
    vm.itemEdition = false;
    vm.itemDuplicating = false;
    vm.itemPreview = false;

    if (typeof vm.beforeCreateElement !== "undefined") {
        vm.beforeCreateElement(type);
    }

    vm.removeFormErrors();
    vm.showForm(false);

    if (!vm.createUrl) {
        vm.createUrl = vm.moduleUrl + '/create'
    }

    var $dd_list = $('.dd-media');
    if ($dd_list.length && $dd_list.nestable('toArray')) {
        $dd_list.nestable('destroy');

        setTimeout(function () {
            $dd_list.nestable(vm.mediaCallback);
        }, 300);
    }

    vm.changeUrl({}, 'SunApp5', vm.createUrl + window.location.search);

    document.querySelector('.sg-app-details').classList.add('show');
};

Vue.prototype.removeFormErrors = function () {
    var formErrors = document.querySelectorAll('.form-group.error'),
        navErrors = document.querySelectorAll('.nav-link.error'),
        sgNavErrors = document.querySelectorAll('.sg-nav-link.error'),
        icons = document.querySelectorAll('.other-lang-error');

    if (formErrors) {
        var ife;
        for (ife = 0; ife < formErrors.length; ife++) {
            if (formErrors[ife].querySelector('.form-error')) {
                formErrors[ife].querySelector('.form-error').innerHTML = '';
            }
            formErrors[ife].classList.remove('error');
        }
    }

    if (navErrors) {
        var ine;
        for (ine = 0; ine < navErrors.length; ine++) {
            navErrors[ine].classList.remove('error');
        }
    }

    if (sgNavErrors) {
        var isne;
        for (isne = 0; isne < sgNavErrors.length; isne++) {
            sgNavErrors[isne].classList.remove('error');
        }
    }

    if (icons) {
        var ii;
        for (ii = 0; ii < icons.length; ii++) {
            icons[ii].classList.add('hidden');
        }
    }
};

Vue.prototype.getElement = function (url, disableForm) {
    let vm = this;

    if (vm.mediaLoading !== undefined) {
        vm.mediaLoading = true;
    }

    url += (url.indexOf('?') > -1) ? '&' : '?';
    url += 'timestamp=' + new Date().getTime();

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        vm.destroyEditors();

        document.querySelector('.sg-app-details').classList.add('show');
        vm.elementToShow = response.data.data;
        vm.elementToShowCopy = JSON.parse(JSON.stringify(vm.elementToShow));

        if (typeof vm.checkWarehousesAndPriceLists !== 'undefined') {
            vm.checkWarehousesAndPriceLists();
        }

        vm.defaultItemEdition = vm.elementToShow.attributes.default;

        if (typeof vm.afterGetElement !== 'undefined') {
            vm.afterGetElement();
        }

        if (vm.elementToShow.attributes) {
            if (vm.elementToShow.attributes.params) {
                var params = vm.elementToShow.attributes.params;
                if (params && params.user) {
                    for (const [key] of Object.entries(params.user)) {
                        params.user[key] = parseInt(params.user[key]);
                    }
                }
            }
            if (vm.elementToShow.attributes.parent_id) {
                vm.parentId = vm.elementToShow.attributes.parent_id;
            }
        }

        vm.showForm(disableForm);

        var $dd_list = $('.dd-media');
        if ($dd_list.length && $dd_list.nestable('toArray')) {
            $dd_list.nestable('destroy');
        }

        var $attr_v_list = $('.dd-attr-v');
        if ($attr_v_list.length && $attr_v_list.nestable('toArray')) {
            $attr_v_list.nestable('destroy');
        }

        setTimeout(function () {
            vm.loadingCircle = false;
            if ($dd_list.length) {
                $dd_list.nestable(vm.mediaCallback);
            }

            if ($attr_v_list.length) {
                $attr_v_list.nestable(vm.valuesCallback);
            }

            var mediaList = document.querySelectorAll('.item-media-list');
            if (mediaList.length>0) {
                for(i=0;i<mediaList.length;i++){
                    vm.getFiles(mediaList[i].getAttribute('data-get_files'),
                        mediaList[i].getAttribute('data-id'));
                }

            }
        }, 300);
    }).catch(function (error) {
        vm.catchErrors(error);
        vm.getItems(vm.itemsFilter, vm.searchPhrase);
    });
};

Vue.prototype.showForm = function (disableForm) {
    let vm = this;

    var actionLinks = document.querySelectorAll('.save-element');
    if (actionLinks) {
        actionLinks.forEach(function (element) {
            element.classList.add('disabled');
        });
    }

    var $select = $('select:not(.treeselect):not(.not-select2)');

    if ($select.length) {
        $select.select2(selectConfig);

        setTimeout(function () {
            $select.trigger('change.select2');

            var noValueSelects = document.querySelectorAll('.select--no-value');
            if (noValueSelects) {
                for (var i = 0; i < noValueSelects.length; ++i) {
                    var current = noValueSelects[i];

                    if (current.selectedIndex === -1) {
                        current.selectedIndex = 0;
                        vm.reloadSelect(current);
                    }
                }
            }
        }, 100);
    }

    var hasShowFields = false;
    var showFields = document.querySelectorAll('.show-field');

    if (showFields.length) {
        hasShowFields = true;
    }

    vm.$nextTick(() => {
        var form = vm.$refs.form_wrapper.querySelector('form#form-data');

        if (form) {
            var refreshButtons = vm.$refs.form_wrapper.querySelectorAll('.refresh-field');

            if (disableForm) {
                form.setAttribute('data-disabled', "1");
            } else {
                form.setAttribute('data-disabled', "0");

                if (typeof vm.refreshButtonsEvent !== 'undefined') {
                    vm.refreshButtonsEvent(refreshButtons);
                } else {
                    refreshButtons.forEach(function (e) {
                        e.click();
                    });
                }
            }

            if (hasShowFields) {
                showFields.forEach(function (e) {
                    var $inp = null,
                        value_only = false;

                    if (e.closest('label').nextElementSibling.name === "cms_user_name" && vm.elementToShow) {
                        value_only = vm.elementToShow.attributes.cms_user_id;
                    } else if (e.closest('label').nextElementSibling.name === "shop_name" && vm.elementToShow) {
                        value_only = vm.elementToShow.attributes.shop_id;
                    } else if (e.closest('label').nextElementSibling.name === "shop_contractor_name" && vm.elementToShow) {
                        value_only = vm.elementToShow.attributes.shop_contractor_id;
                    } else if (e.closest('label').nextElementSibling.name === "shop_contractor_contact_name" && vm.elementToShow) {
                        value_only = vm.elementToShow.attributes.shop_contractor_contact_id;
                    } else if (e.closest('label').nextElementSibling.name === "shop_currency" && vm.elementToShow) {
                        value_only = vm.elementToShow.attributes.shop_currency_id;
                    } else {
                        $inp = e.closest('label').nextElementSibling;
                    }

                    if ($inp && $inp.value) {
                        e.style.display = "inline-block";
                        e.href += '/' + $inp.value;
                    } else if (value_only) {
                        e.style.display = "inline-block";
                        e.href += '/' + value_only;
                    } else {
                        e.style.display = "none";
                    }
                });
            }

            var $nameField = $('[name="type"]');
            if ($nameField.length) {
                $nameField.trigger('change');
            }

            var elements = form.elements;
            var len = elements.length;

            for (var i = 0; i <= len; ++i) {
                if (elements[i]) {
                    if (elements[i].classList.contains('editor')) {
                        vm.makeEditor(elements[i].name);
                    }

                    if (!elements[i].getAttribute('data-force_readonly')) {
                        elements[i].disabled = disableForm;
                    }

                    if (typeof vm.serveField !== 'undefined') {
                        vm.serveField(elements[i]);
                    }

                    if (elements[i].classList.contains('datepicker') || elements[i].classList.contains('datetimepicker')) {
                        var fieldDate = false,
                            isDatePicker = false;

                        if (elements[i].classList.contains('datepicker')) {
                            isDatePicker = true;
                        }

                        var attribute = elements[i].name;
                        if (vm.elementToShow && vm.elementToShow.attributes && vm.elementToShow.attributes[attribute]) {
                            var date = Date.parse(vm.elementToShow.attributes[attribute]);
                            if (!isNaN(date)) {
                                if (typeof vm.formatDate !== "undefined" && vm.formatDate) {
                                    fieldDate = vm.formatDate(date);
                                } else {
                                    fieldDate = vm.elementToShow.attributes[attribute];
                                }
                            }
                        }

                        if ($('#' + elements[i].name).data('daterangepicker')) {
                            $('#' + elements[i].name).data('daterangepicker').remove();
                        }
                        if (isDatePicker) {
                            $('#' + elements[i].name).daterangepicker(datePickerConfig);
                        } else {
                            $('#' + elements[i].name).daterangepicker(dateRangePickerConfig);
                        }
                        if (fieldDate) {
                            $('#' + elements[i].name)
                                .data('daterangepicker')
                                .setStartDate(fieldDate);
                            $('#' + elements[i].name)
                                .data('daterangepicker')
                                .setEndDate(fieldDate);
                        }

                        if (isDatePicker) {
                            $('#' + elements[i].name).on('show.daterangepicker', function (ev, picker) {
                                picker.container.find(".calendar-time").hide();
                            }).on('showCalendar.daterangepicker', function (ev, picker) {
                                picker.container.find('.calendar-time').remove();
                            }).on('apply.daterangepicker', function (ev, picker) {
                                $(this).val(picker.startDate.format('YYYY-MM-DD'));
                            });
                        } else {
                            $('#' + elements[i].name).on('apply.daterangepicker', function (ev, picker) {
                                $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
                            });
                        }

                        if (fieldDate) {
                            vm.elementToShow.attributes[attribute] = fieldDate;
                        }
                    }

                    if (elements[i].classList.contains('selectize')) {
                        if ($('#' + elements[i].name).selectize) {
                            $('#' + elements[i].name).selectize();
                            $('#' + elements[i].name)[0].selectize.destroy();
                        }
                        $('#' + elements[i].name).selectize(selectizeConfig);
                    }

                    if (!vm.elementToShow && elements[i].className.indexOf('force-checked') > -1) {
                        elements[i].checked = true;
                    }
                }
            }

            vm.readOnlyFields();
            if (window.location.href.indexOf('#values') > -1) {
                vm.$refs.form_wrapper.querySelector('#values-tab').click();
            }

            var $scroll_area = vm.$refs.form_wrapper.querySelector('.sg-scroll-area');
            new PerfectScrollbar($scroll_area);

            var pickers = document.querySelectorAll('.datetimepicker');

            if (pickers) {
                $scroll_area.addEventListener('ps-scroll-y', function () {
                    var i = 0;
                    for (i; i < pickers.length; ++i) {
                        var datePickerData = $(pickers[i]).data('daterangepicker');
                        if (datePickerData) {
                            if (datePickerData.container[0].style.display === 'block') {
                                datePickerData.updateView();
                            }
                        }
                    }
                });
            }

            if ($select.length) {
                $select.trigger('change.select2');
            }
        }
        Pace.on('done', function () {
            vm.enableSaveButtons();
        });
        Pace.on('stop', function () {
            vm.enableSaveButtons();
        });
    });
    if (typeof vm.openCard !== "undefined") {
        vm.openCard();
    }
};

Vue.prototype.enableSaveButtons = function () {
    var actionLinks = document.querySelectorAll('.save-element.disabled');
    if (actionLinks) {
        actionLinks.forEach(function (element) {
            element.classList.remove('disabled');
        });
    }
}

Vue.prototype.getItems = function (type, name, action) {
    let vm = this;

    var userList = document.querySelector('.sg-user-list');

    if (userList) {
        document.querySelector('.sg-user-list').scrollTop = 0;
        if (document.querySelector('.domain-group-item.active')) {
            document.querySelector('.domain-group-item.active').classList.remove('active');
        }
        if (document.querySelector('.category-group-item.active')) {
            document.querySelector('.category-group-item.active').classList.remove('active');
        }
    }

    var $dd = $('.dd');
    if ($dd.length && $dd.nestable('toArray')) {
        $dd.nestable('destroy');
    }

    if (vm.canSearch) {
        if (vm.panelOpen && ((!vm.itemEdition && !vm.itemRestoring) || action === 'click')) {
            if (JSON.stringify(vm.elementToShow) !== JSON.stringify(vm.elementToShowCopy)) {
                swal.fire({
                    title: i18next.t('core::messages.sure'),
                    text: i18next.t('core::messages.will_lost_not_saved'),
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: i18next.t('core::actions.go_next'),
                    cancelButtonText: i18next.t('core::actions.cancel'),
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.value) {
                        return false;
                    } else {
                        if ((vm.itemEdition || vm.itemRestoring) && !vm.lastSearchUrl) {
                            vm.prepareToHide();
                        } else {
                            vm.itemsFilter = type;
                            vm.searchPhrase = name;
                            if (vm.lastSearchUrl) {
                                vm.hideCard();
                            }
                        }
                        if (!vm.lastSearchUrl) {
                            vm.changeItems(type, name);
                        }
                    }
                });
            } else {
                if ((vm.itemEdition || vm.itemRestoring) && !vm.lastSearchUrl) {
                    vm.prepareToHide();
                } else {
                    vm.itemsFilter = type;
                    vm.searchPhrase = name;
                    if (vm.lastSearchUrl) {
                        vm.hideCard();
                    }
                }
                if (!vm.lastSearchUrl) {
                    vm.changeItems(type, name);
                }
            }
        } else {
            vm.changeItems(type, name);
        }
    }
};

Vue.prototype.showElement = function (url) {
    let vm = this;
    vm.loadingCircle = true;
    vm.elementToShow = false;
    vm.itemPreview = true;
    vm.itemEdition = false;
    vm.itemDuplicating = false;
    vm.panelOpen = true;

    vm.removeFormErrors();

    vm.changeUrl({}, 'SunApp5', url);

    url += (url.indexOf('?') === -1) ? '?ajax=1' : '&ajax=1';

    vm.getElement(url, true);
};

Vue.prototype.editElement = function (url) {
    let vm = this;
    vm.loadingCircle = true;
    vm.panelOpen = true;
    vm.itemPreview = false;
    vm.itemEdition = true;

    vm.removeFormErrors();

    vm.changeUrl({}, 'SunApp5', url);

    vm.getElement(url, false);
};

Vue.prototype.duplicateElement = function (url) {
    let vm = this;
    vm.elementToShow = false;
    vm.panelOpen = true;
    vm.itemEdition = false;
    vm.loadingCircle = true
    vm.itemPreview = false;
    vm.itemDuplicating = true;

    if (vm.mediaLoading !== undefined) {
        vm.mediaLoading = true;
    }

    vm.getElement(url, false);

    if (!vm.createUrl) {
        vm.createUrl = vm.moduleUrl + '/create'
    }

    var $dd_list = $('.dd-media');
    if ($dd_list.length && $dd_list.nestable('toArray')) {
        $dd_list.nestable('destroy');

        setTimeout(function () {
            $dd_list.nestable(vm.mediaCallback);
        }, 300);
    }

    vm.changeUrl({}, 'SunApp5', vm.createUrl);
};

Vue.prototype.filterElement = function (e) {
    let vm = this;
    var form;

    if (e.target.classList.contains('chip-text')) {
        form = e.target.closest('.chip').nextElementSibling;
    } else {
        form = e.target.parentNode.nextElementSibling;
    }

    swal.fire({
        title: i18next.t('core::messages.sure'),
        text: i18next.t('shop::attributes.filter_will_be_changed'),
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

            var formData = new FormData(form);
            for (var pair of formData.entries()) {
                var field = document.querySelector('[name="' + pair[0] + '"]');
                if (field.readOnly) {
                    formData.delete(pair[0]);
                }
            }

            axios.post(form.getAttribute('action'), formData, {
                headers: vm.queryHeaders
            }).then(function (response) {
                var data = response.data;
                if (data.status === 'error') {
                    toastr.error(data.message, i18next.t('core::messages.error'), {
                        positionClass: 'toast-bottom-right'
                    });
                } else if (data.status === 'success') {
                    toastr.success(data.message, i18next.t('core::messages.success'), {
                        positionClass: 'toast-bottom-right'
                    });
                }
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            }).catch(function (error) {
                vm.catchErrors(error);
                vm.getItems(vm.itemsFilter, vm.searchPhrase);
            });
        }
    });
};

Vue.prototype.clearForm = function (url) {
    let vm = this;

    if (url === "fromOrdering") {
        url = window.location.href.replace('items-order', 'edit');
    }

    if (JSON.stringify(vm.elementToShowCopy) !== JSON.stringify(vm.elementToShow)) {
        swal.fire({
            title: i18next.t('core::messages.sure'),
            text: i18next.t('core::messages.will_lost_not_saved'),
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: i18next.t('core::actions.yes'),
            cancelButtonText: i18next.t('core::actions.cancel'),
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                vm.forceLeaveRoute = true;
                window.location.href = url;
            } else {
                return false;
            }
        });
    } else {
        window.location.href = url;
    }
};

String.prototype.capitalize = function() {
    var hasBracket = (this.indexOf('[') > -1 && this.indexOf(']') > -1),
        hasHash = (this.indexOf('#') > -1),
        newString = this;

    if (hasHash) {
        newString = newString.slice(1);
    }

    newString = newString.charAt(0).toUpperCase() + newString.slice(1);

    if (hasBracket) {
        var bracketValue = newString.match(/\[(.*?)\]/)[1];
        newString = newString.split('[')[0];

        if (bracketValue && bracketValue.length) {
            bracketValue = bracketValue.charAt(0).toUpperCase() + bracketValue.slice(1);
            newString += bracketValue;
        }
    }

    return newString;
}

Vue.prototype.changedField = function (name) {
    let vm = this;

    if (!name) {
        return false;
    }

    var field = document.querySelector('[name="' + name + '"]');

    let method = 'changedField'+name.capitalize();

    if (typeof vm[method] !== "undefined") {
        vm[method](field);
        return false;
    }

    if (name === "list-ordering") {
        vm.setOrder(document.querySelector('[name="list-ordering"]').value);
    }

    setTimeout(function () {
        $(field).trigger('change.select2');
    }, 1);
};

Vue.prototype.refreshField = function (url, field) {
    let vm = this;

    url += (url.indexOf('?') > -1) ? '&' : '?';
    url += 'per_page=-1';

    var htmlField = (field.charAt(0) === '#')
        ? document.querySelector(field) : document.querySelector('[name="' + field + '"]');

    htmlField.disabled = true;

    if (!url && htmlField.id === 'address_list') {
        url = vm.addressesUrl;
        vm.addressesLoading = true;
        vm.addresses = [];
    }

    var formDisabled = parseInt(document.getElementById('form-data').getAttribute('data-disabled'));

    let method = 'refreshField'+field.capitalize();

    Pace.restart();

    if (typeof vm[method] !== "undefined") {
        vm[method](htmlField, url, formDisabled);
    }
    return false;
};

Vue.prototype.refreshFieldCategory_list = function(htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            if (htmlField.id === 'category_list') {
                vm.categories = [];
                response.data.data.forEach(function (category) {
                    vm.categories.push(category);
                });
                setTimeout(function(){
                    $('#category_list table.tree').treegrid(treeConfig);
                }, 100);
            }
            if (!formDisabled) {
                htmlField.disabled = false;
            }
        }
    }).catch(function (error) {
        vm.catchErrors(error);
    });
};

Vue.prototype.refreshFieldCms_domain_id = function(htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            var currentValue;
            if (htmlField.selectedIndex > -1) {
                currentValue = parseInt(htmlField.options[htmlField.selectedIndex].value);
            } else {
                currentValue = null;
            }

            htmlField.innerHTML = "";

            response.data.data.forEach(function (el) {
                var opt = document.createElement('option');
                opt.value = el.id;

                var languages = Object.keys(el.attributes.name);

                languages.forEach(function (lang) {
                    opt.setAttribute('data-' + lang, el.attributes.name[lang] + " (#" + el.id + ")");
                });

                opt.innerHTML = el.attributes.name[currentLang] + " (#" + el.id + ")";
                if (el.id === currentValue && (vm.itemEdition || vm.itemRestoring)) {
                    opt.setAttribute('selected', 'selected');
                } else if (el.id === vm.elementToShow.id) {
                    opt.setAttribute('disabled', 'disabled');
                }
                htmlField.appendChild(opt);
            });

            if (!formDisabled) {
                htmlField.disabled = false;
            }
        }
    }).catch(function (error) {
        vm.catchErrors(error);
    });
};

Vue.prototype.refreshFieldParent_id = function(htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            if (htmlField.selectedIndex > -1) {
                var currentValue = parseInt(htmlField.options[htmlField.selectedIndex].value);
            } else {
                var currentValue = null;
            }

            htmlField.innerHTML = "";

            var opt = document.createElement('option');
            opt.innerHTML = "&nbsp;";
            opt.value = "";
            htmlField.appendChild(opt);

            response.data.data.forEach(function (el) {
                var opt = document.createElement('option');
                opt.value = el.id;

                if (typeof el.attributes.name === 'string' || el.attributes.name instanceof String) {
                    opt.innerHTML = el.attributes.nested_name + " (#" + el.id + ")";
                } else {
                    var languages = Object.keys(el.attributes.name);
                    languages.forEach(function (lang) {
                        opt.setAttribute('data-' + lang, el.attributes.name[lang] + " (#" + el.id + ")");
                    });
                    opt.innerHTML = el.attributes.nested_name[currentLang] + " (#" + el.id + ")";
                }

                if (el.id === currentValue && (vm.itemEdition || vm.itemRestoring)) {
                    opt.setAttribute('selected', 'selected');
                } else if (el.id === vm.elementToShow.id) {
                    opt.setAttribute('disabled', 'disabled');
                }
                htmlField.appendChild(opt);
            });

            if (!formDisabled) {
                htmlField.disabled = false;
            }
        }
    }).catch(function (error) {
        vm.catchErrors(error);
    });
};

Vue.prototype.refreshFieldUser_group = function(htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            var fieldValues = [],
                options = htmlField.options,
                opt;

            if (vm.elementToShow) {
                for (var i = 0, iLen = options.length; i < iLen; i++) {
                    opt = options[i];

                    if (opt.selected) {
                        fieldValues.push(parseInt(opt.value));
                    }
                }
            }
            if (!fieldValues.length) {
                fieldValues.push(htmlField.options[0].value)
            }
            htmlField.innerHTML = "";
            response.data.data.forEach(function (el) {
                var opt = document.createElement('option');
                opt.value = el.id;
                opt.innerHTML = el.attributes.nested_name + " (#" + el.id + ")";
                if (fieldValues.includes(el.id)) {
                    opt.setAttribute('selected', '');
                }
                htmlField.appendChild(opt);
            });
            if (!formDisabled) {
                htmlField.disabled = false;
            }
            htmlField.dispatchEvent(new Event("change"));
        }
    }).catch(function (error) {
        vm.catchErrors(error);
    });
};

Vue.prototype.refreshFieldTerms = function(htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            var values = [],
                selectedValues = [],
                options = htmlField && htmlField.options,
                opt,
                i;

            for (i = 0, iLen = options.length; i < iLen; i++) {
                opt = options[i];

                if (opt.selected && !isNaN(opt.value)) {
                    values.push(parseInt(opt.value));
                }
            }

            if (vm.elementToShow && vm.elementToShow.attributes && vm.elementToShow.attributes.terms) {
                for (i = 0, iLen = vm.elementToShow.attributes.terms.length; i < iLen; i++) {
                    values.push(parseInt(vm.elementToShow.attributes.terms[i].cms_term_id));
                }
            }

            htmlField.innerHTML = "";

            response.data.data.forEach(function (el) {
                var opt = document.createElement('option');
                opt.value = el.attributes.cms_term_id;

                var languages = Object.keys(el.attributes.name);
                languages.forEach(function (lang) {
                    opt.setAttribute('data-' + lang, el.attributes.name[lang] + " (#" + el.id + ")");
                });
                opt.innerHTML = el.attributes.name[currentLang] + " (#" + el.id + ")";

                if (values.includes(el.attributes.cms_term_id) && (vm.itemEdition || vm.itemRestoring)) {
                    selectedValues.push(el.attributes.cms_term_id);
                }

                htmlField.appendChild(opt);
            });

            if (!formDisabled) {
                htmlField.disabled = false;
            }

            vm.elementToShow.attributes.terms = selectedValues;

            setTimeout(function () {
                $('[name="terms[]"]').trigger('change.select2');
                $('[name="terms[]"]').trigger('change');
            }, 100);
        }
    }).catch(function (error) {
        vm.catchErrors(error);
    });
};

Vue.prototype.refreshFieldGroup = function(htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            var currentValue;
            if (htmlField.selectedIndex > -1) {
                currentValue = parseInt(htmlField.options[htmlField.selectedIndex].value);
            } else {
                currentValue = null;
            }

            htmlField.innerHTML = "";

            response.data.data.forEach(function (el) {
                var opt = document.createElement('option');
                opt.value = el.id;
                opt.innerHTML = el.attributes.nested_name + " (#" + el.id + ")";
                if (el.id === currentValue) {
                    opt.setAttribute('selected', 'selected');
                }
                htmlField.appendChild(opt);
            });
            if (!formDisabled) {
                htmlField.disabled = false;
            }
        }
    }).catch(function (error) {
        vm.catchErrors(error);
    });
};

Vue.prototype.refreshFieldGroups = function(htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            var values = [],
                options = htmlField && htmlField.options,
                opt,
                i;

            for (i = 0, iLen = options.length; i < iLen; i++) {
                opt = options[i];

                if (opt.selected) {
                    values.push(parseInt(opt.value));
                }
            }

            if (!values.length) {
                values.push(1);
            }

            htmlField.innerHTML = "";

            response.data.data.forEach(function (el) {
                var opt = document.createElement('option');
                opt.value = el.id;

                if (el.attributes.nested_name) {
                    opt.innerHTML = el.attributes.nested_name + " (#" + el.id + ")";
                } else {
                    opt.innerHTML = el.attributes.name + " (#" + el.id + ")";
                }
                if (values.includes(el.id)) {
                    opt.setAttribute('selected', 'selected');
                }
                htmlField.appendChild(opt);
                $('[name="groups[]"]').trigger('change.select2');
            });

            if (!formDisabled) {
                htmlField.disabled = false;
            }
        }
    }).catch(function (error) {
        vm.catchErrors(error);
    });
};

Vue.prototype.saveElement = function (url, andNew = false, andLeave = false) {
    let vm = this;

    if (url === undefined) {
        return false;
    }

    var actionLinks = document.querySelectorAll('.save-element');
    if (actionLinks) {
        actionLinks.forEach(function (element) {
            element.classList.add('disabled');
        });
    }

    vm.loadingCircle = true;
    var form = document.getElementById('form-data');

    var tab = document.querySelector('.nav-link.active')
            || document.querySelector('.nav-link')
            || document.querySelector('.sg-nav-link'),
        activeTab = null;

    if (tab) {
        activeTab = tab.id;
    }

    vm.removeFormErrors();

    vm.updateEditors();

    var elements = form.elements,
        formData = new FormData();
    for (var i = 0, len = elements.length; i < len; ++i) {
        if (!elements[i].readOnly) {
            if (elements[i].type === "select-multiple") {
                var result = [];
                var options = elements[i].options;
                var opt;

                for (var j = 0, iLen = options.length; j < iLen; j++) {
                    opt = options[j];

                    if (opt.selected) {
                        result.push(opt.value || opt.text);
                    }
                }
                for (var k = 0; k < result.length; k++) {
                    formData.append(elements[i].name, result[k]);
                }
            } else if (elements[i].type === "file") {
                for (var f = 0, f_len = elements[i].files.length; f < f_len; f++) {
                    formData.append(elements[i].name, elements[i].files[f]);
                }
            } else if (elements[i].type !== "checkbox" && elements[i].type !== "radio") {
                formData.append(elements[i].name, elements[i].value);
            } else {
                if (elements[i].checked) {
                    formData.append(elements[i].name, elements[i].value);
                }
            }
        }
    }

    if (typeof vm.checkNewFields !== 'undefined' && vm.checkNewFields) {
        var newFieldsValid = vm.checkNewFields();
        if (!newFieldsValid) {
            vm.loadingCircle = false;
            vm.enableSaveButtons();
            return false;
        }
    }

    if (typeof vm.beforeSaveElement !== 'undefined' && vm.beforeSaveElement) {
        vm.beforeSaveElement(formData);
    }

    if (url === 'actionUrl') {
        url = form.getAttribute('action');
    }

    url += (url.indexOf('?') > -1) ? '&' : '?';
    url += 'timestamp=' + new Date().getTime();

    axios.post(url, formData, {
        headers: vm.queryHeaders
    }).then(function (response) {
        var data = response.data;
        vm.loadingCircle = false;
        if (data.status === 'success') {
            var returnUrl = null;
            if (typeof vm.setReturnUrl !== "undefined" && vm.setReturnUrl) {
                returnUrl = vm.setReturnUrl();
            }

            if (vm.itemEdition) {
                toastr.success(data.message, i18next.t('core::messages.updated'), {
                    positionClass: 'toast-bottom-right'
                });
                if (returnUrl) {
                    window.onbeforeunload = null;
                    setTimeout(function() {
                        window.location.href = returnUrl;
                    }, 100);
                }
            } else if (vm.itemRestoring) {
                toastr.success(data.message, i18next.t('core::messages.restored'), {
                    positionClass: 'toast-bottom-right'
                });

                vm.hideCard();
                vm.itemRestoring = false;
            } else {
                toastr.success(data.message, i18next.t('core::messages.created'), {
                    positionClass: 'toast-bottom-right'
                });
                vm.lastCreatedUrl = response.data.data.links.edit;

                if (typeof vm.afterItemCreated !== "undefined") {
                    vm.afterItemCreated(andNew);
                } else {
                    if (!andNew) {
                        if (returnUrl) {
                            window.onbeforeunload = null;
                            setTimeout(function() {
                                window.location.href = returnUrl;
                            }, 100);
                        }
                        vm.itemEdition = true;
                    }
                }
            }
            vm.elementToShow = false;

            var refreshTree = true;

            if (typeof vm.removeNewFields !== "undefined") {
                vm.removeNewFields();
            }

            setTimeout(function () {
                var params = false;
                if (data.data.attributes && data.data.attributes.params) {
                    params = data.data.attributes.params;
                }
                if (params && params.user) {
                    for (const [key, value] of Object.entries(params.user)) {
                        params.user[key] = parseInt(params.user[key]);
                    }
                }

                var $dd_media = $('.dd-media');

                if (!andNew) {
                    vm.elementToShow = data.data;

                    if (typeof vm.formatDatesAfterSavingElement !== "undefined") {
                        vm.formatDatesAfterSavingElement();
                    }

                    vm.elementToShowCopy = JSON.parse(JSON.stringify(vm.elementToShow));
                    vm.canLeaveRoute = true;
                    if (data.data.attributes.hasOwnProperty('default')) {
                        vm.defaultItemEdition = data.data.attributes.default;
                    }
                } else {
                    if ($dd_media.length) {
                        $dd_media.find('.dd-list').html('');
                    }
                }
                if (data.data.attributes.hasOwnProperty('parent_id')) {
                    if (vm.parentId && data.data.attributes.parent_id && parseInt(vm.parentId) === parseInt(data.data.attributes.parent_id)) {
                        refreshTree = false;
                    }
                }

                if (typeof vm.clearFieldsAfterSavingElement !== "undefined") {
                    vm.clearFieldsAfterSavingElement();
                }

                if ($dd_media.length && $dd_media.nestable('toArray')) {
                    $dd_media.nestable('destroy');
                    setTimeout(function () {
                        $dd_media.nestable(vm.mediaCallback);
                    }, 300);
                }

                setTimeout(function () {
                    if (!andNew) {
                        if (activeTab) {
                            var tabToActivate = document.getElementById(activeTab),
                                tabToActivatePane = document.getElementById(activeTab.replace('-tab', ''));

                            if (tabToActivate && tabToActivatePane) {
                                tabToActivate.classList.add('active');
                                tabToActivatePane.classList.add('active');
                            }
                        }

                        var mediaList = document.querySelectorAll('.item-media-list');
                        if (mediaList.length>0) {
                            for(i=0;i<mediaList.length;i++){
                                vm.getFiles(mediaList[i].getAttribute('data-get_files'),
                                    mediaList[i].getAttribute('data-id'));
                            }
                        }
                    }

                    if (refreshTree) {
                        vm.getItems(vm.itemsFilter, vm.searchPhrase);
                        refreshTree = false;
                    }
                }, 200);
            }, 1);

            if (andNew) {
                vm.preventChangeUrl = true;
                vm.hideCard();
                setTimeout(function () {
                    if (refreshTree) {
                        vm.getItems(vm.itemsFilter, vm.searchPhrase);
                    }
                }, 1500);
                if (!andLeave) {
                    setTimeout(function () {
                        vm.createElement('click');
                    }, 1000);
                }
            }

            vm.itemSaved = true;
            vm.itemDuplicating = false;

            if (typeof vm.afterItemSaved !== "undefined") {
                vm.afterItemSaved();
            }

            vm.readOnlyFields();
        }
    }).catch(function (error) {
        vm.catchErrors(error);
        vm.loadingCircle = false;
    });
};

Vue.prototype.changeItems = function (type, name, column, sorting) {
    let vm = this;

    var selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }

    var removeAll = document.getElementById('list-inline-item--remove'),
        restoreAll = document.getElementById('list-inline-item--restore');
    if (removeAll) {
        removeAll.classList.add('hidden');
    }
    if (restoreAll) {
        restoreAll.classList.add('hidden');
    }

    vm.canSearch = false;
    vm.items = [];
    vm.loadingCircle = false;
    vm.isLoading = true;
    name = name.replace(/\\:/g, "\:");
    vm.searchPhrase = name;

    vm.searchedWithPhrase = !!name;

    var url = vm.moduleUrl,
        parameters = '',
        maxDepth = (typeof vm.maxDepth !== 'undefined' && vm.maxDepth) ? vm.maxDepth : 999,
        originalType = type;

    if (type.indexOf('type') > -1) {
        type = 'type';
    } else if (type.indexOf('shop_id') > -1) {
        type = 'shop_id';
    } else if (type.indexOf('category') > -1) {
        type = 'category';
    } else if (type.indexOf('domain') > -1) {
        type = 'domain';
    }

    if (typeof vm.moduleName !== 'undefined' && Cookies.get('sunapp_'+vm.moduleName+'_sort')) {
        var cookie = Cookies.get('sunapp_'+vm.moduleName+'_sort');

        vm.ordering = cookie.substring(cookie.indexOf("orderBy") + 8, cookie.indexOf('sortedBy') - 1);
        vm.sorting = cookie.substring(cookie.indexOf("sortedBy") + 9);

        parameters = cookie;
    } else if (typeof column !== 'undefined' && typeof sorting !== 'undefined') {
        parameters = '?orderBy='+column+'&sortedBy='+sorting;
    } else if (vm.ordering && vm.sorting) {
        parameters = '?orderBy='+vm.ordering+'&sortedBy='+vm.sorting;
    }

    if (typeof vm.switchSearchType !== "undefined" && type !== 'refresh') {
        parameters = vm.switchSearchType(type, parameters);
    } else {
        switch (type) {
            case 'all':
                vm.itemsFilter = type;
                break;
            case 'active':
                if (parameters.length) {
                    parameters = '?search=active:1' + '&'+parameters.substr(1);
                } else {
                    parameters = '?search=active:1';
                }
                vm.itemsFilter = type;
                break;
            case 'inactive':
                if (parameters.length) {
                    parameters = '?search=active:0' + '&'+parameters.substr(1);
                } else {
                    parameters = '?search=active:0';
                }
                vm.itemsFilter = type;
                break;
            case 'trashed':
                if (parameters.length) {
                    parameters = '?trashed=only' + '&'+parameters.substr(1);
                } else {
                    parameters = '?trashed=only';
                }
                vm.itemsFilter = type;
                break;
            case 'domain':
                if (parameters.length) {
                    parameters = '?search=' + originalType + '&'+parameters.substr(1);
                } else {
                    parameters = '?search=' + originalType;
                }
                vm.itemsFilter = originalType;
                break;
            case 'type':
                if (parameters.length) {
                    parameters = '?search=' + originalType + '&'+parameters.substr(1);
                } else {
                    parameters = '?search=' + originalType;
                }
                vm.itemsFilter = originalType;
                break;
            case 'shop_id':
                if (parameters.length) {
                    parameters = '?search=' + originalType + '&'+parameters.substr(1);
                } else {
                    parameters = '?search=' + originalType;
                }
                vm.itemsFilter = originalType;
                break;
            case 'category':
                if (originalType.indexOf(parameters.substr(1)) === -1) {
                    originalType += '&'+parameters.substr(1);
                }
                if (parameters.length) {
                    parameters = '?search=' + originalType + '&'+parameters.substr(1);
                } else {
                    parameters = '?search=' + originalType;
                }
                vm.itemsFilter = originalType;
                break;
            case 'refresh':
                url = vm.lastUrl;
                break;
        }
    }

    if (name !== '' && type !== 'refresh') {
        if (type === 'trashed') {
            parameters += '&search=' + name;
        } else {
            name = name.replace(/\:/g, "\\:");
            if (parameters.length) {
                if (parameters.indexOf('?search') > -1) {
                    parameters = '?search=' + name + ';' + parameters.substr(parameters.indexOf('=') + 1);
                } else {
                    if (parameters.indexOf('orderBy') === 1) {
                        parameters = '?search=' + name + '&' + parameters.substr(1);
                    } else {
                        parameters = '?search=' + name + ';' + parameters.substr(parameters.indexOf('=') + 1);
                    }
                }
            } else {
                parameters = '?search=' + name;
            }
        }
    } else {
        if (type !== 'refresh') {
            if (!parameters.length && type !== '' && type !== 'all') {
                parameters = '?search=' + originalType;
                vm.itemsFilter = originalType;
            }
            parameters += (parameters === '' && url.indexOf('?') === -1) ? '?ajax=1' : '&ajax=1';
        } else {
            if (url.indexOf(parameters.substr(1)) > -1) {
                parameters = (parameters.indexOf('?') === -1 && url.indexOf('?') === -1) ? '?ajax=1' : '&ajax=1';
            } else {
                parameters += (name === "" && parameters.indexOf('?') === -1 && url.indexOf('?') === -1) ? '?ajax=1' : '&ajax=1';
            }
        }
    }

    url += parameters;
    url += '&levels=1';

    if (vm.perPage) {
        url += '&per_page=' + vm.perPage;
    }

    var urlToChange;

    if (url.indexOf('ajax') > -1) {
        urlToChange = url.substr(0, url.indexOf('ajax') - 1);
    } else {
        urlToChange = url
    }

    if (urlToChange.indexOf('&levels') > -1) {
        var levelsParam = url.substring(
            urlToChange.lastIndexOf("&levels")
        );
        urlToChange = urlToChange.replace(levelsParam,'');
    }

    vm.lastUrl = urlToChange;
    vm.lastSearchUrl = urlToChange;

    for (const [key, value] of Object.entries(vm.additional_query_params)) {
        if (url.indexOf('search=') === -1) {
            url += (url.indexOf('?') === -1 ? '?' : '&') + key + '=' + value;
        } else {
            url += '&' + key + '=' + value;
        }
    }

    if (!vm.currentPageUrl) {
        vm.currentPageUrl = url;
    }

    var additional_search_data = [];
    Object.values(vm.additional_search).forEach(function (item, i) {
        Object.values(item).forEach(function (value, p) {
            additional_search_data.push(Object.keys(vm.additional_search)[i] + ':' +value);
        });
    });

    if (additional_search_data.length) {
        if (url.indexOf('search=') === -1) {
            url += (url.indexOf('?') === -1 ? '?' : '&') + 'search=' + Object.values(additional_search_data).join(';');
        } else {
            url = url.replace('search=', 'search=' + Object.values(additional_search_data).join(';') + ';');
        }
    }

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        vm.isLoading = false;
        var data = response.data;
        var meta = data.meta;
        var elements = data.data;
        var links = data.links;
        if (links.store) {
            vm.userCanCreate = true;
            vm.storeUrl = links.store;
        }
        if (links.next) {
            vm.nextPageUrl = links.next;
        } else {
            vm.nextPageUrl = false;
        }

        if (links.self) {
            vm.currentPageUrl = links.self;
        }
        vm.createUrl = links.create;
        vm.loadedData = data;
        if (meta.params && meta.params.counter) {
            vm.itemsTotal = meta.params.counter.all;
            if (meta.params.counter.active !== "undefined") {
                vm.itemsActive = meta.params.counter.active;
            }
            if (meta.params.counter.inactive !== "undefined") {
                vm.itemsInactive = meta.params.counter.inactive;
            }
            if (meta.params.counter.trashed !== "undefined") {
                vm.itemsTrashed = meta.params.counter.trashed;
            }
            if (meta.params.counter.types !== "undefined") {
                vm.typeItems = meta.params.counter.types;
            }
            if (meta.params.counter.shops !== "undefined") {
                vm.shops = meta.params.counter.shops;
            }
            if (meta.params.counter.category_items !== "undefined") {
                vm.categoryItems = meta.params.counter.category_items;
            }
            if (typeof vm.additionalMetaCounters !== "undefined") {
                vm.additionalMetaCounters(meta.params.counter);
            }
        }
        elements.forEach(function (item) {
            vm.items.push(item);
        });

        if (listScrollbar) {
            listScrollbar.destroy();
            listScrollbar = null;
        }

        var $list = document.querySelector(".sg-user-list");
        listScrollbar = new PerfectScrollbar($list);

        setTimeout(function () {
            $list.scrollTop = 1;
        }, 100);

        $list.addEventListener('ps-y-reach-end', function () {
            if (vm.nextPageUrl) {
                vm.getNextItems(vm.nextPageUrl);
            }
        });

        vm.currentProgress = 0;

        vm.canSearch = true;

        var $dd_main = $('.dd-main');

        if (typeof vm.moduleName !== 'undefined' && vm.moduleName === 'cms-forms') {
            vm.buildFormNestable();
        }

        if ((type === 'all' || type === 'refresh') && name === '' && $dd_main.length) {
            $dd_main.nestable({
                maxDepth: maxDepth,
                callback: function (l, e) {
                    var elementId = parseInt(e[0].getAttribute('data-id')),
                        list = $dd_main.nestable('toArray'),
                        listLength = list.length,
                        parent = null,
                        prev = null,
                        next = null,
                        items = [];
                    if (maxDepth > 1 || (typeof vm.disabledNesting !== undefined && vm.disabledNesting)) {
                        for (var i = 0; i < listLength; i++) {
                            if (list[i].id === elementId) {
                                var element = list[i];
                                if (element.parent_id) {
                                    parent = element.parent_id;
                                }
                                if (i > 0) {
                                    var j = i - 1;
                                    while (j >= 0) {
                                        if (list[j].parent_id === list[i].parent_id) {
                                            prev = list[j].id;
                                            break;
                                        }
                                        j--;
                                    }
                                }
                                if (i < listLength - 1) {
                                    var k = i + 1;
                                    while (k <= listLength - 1) {
                                        if (list[k].parent_id === list[i].parent_id) {
                                            next = list[k].id;
                                            break;
                                        }
                                        k++;
                                    }
                                }

                                var rootCount = document.querySelectorAll('.users-list-wrapper > li').length;
                                var updateUrl = vm.items.find(x => x.id === elementId).links.update;
                                var maxRootCount = (typeof vm.maxRootCount !== "undefined" && vm.maxRootCount) ? vm.maxRootCount : 999;

                                if ((rootCount === 1 && maxRootCount === 1) || maxRootCount > 1) {
                                    document.querySelectorAll('.dd-handle').forEach(function (e) {
                                        e.style.display = "none";
                                    });

                                    document.getElementById('spinner').style.display = "block";

                                    axios.post(updateUrl + '?moved=true', {
                                        '_method': 'PATCH',
                                        'parent_id': parent,
                                        'prev_id': prev,
                                        'next_id': next,
                                        '_token': document.querySelector('.dd').getAttribute('data-token')
                                    }, {
                                        headers: vm.queryHeaders
                                    }).then(function (response) {
                                        document.querySelectorAll('.dd-handle').forEach(function (e) {
                                            e.style.display = "block";
                                        });

                                        document.getElementById('spinner').style.display = "none";

                                        var data = response.data;
                                        if (data.status === 'error') {
                                            toastr.error(data.message, i18next.t('core::messages.error'), {
                                                positionClass: 'toast-bottom-right'
                                            });
                                            vm.items = [];
                                            vm.isLoading = true;
                                            document.querySelector('.sg-user-list').scrollTop = 0;
                                            vm.getItems(vm.itemsFilter, vm.searchPhrase);
                                        } else if (data.status === 'success') {
                                            toastr.success(data.message, i18next.t('core::messages.success'), {
                                                positionClass: 'toast-bottom-right'
                                            });
                                        }
                                    }).catch(function (error) {
                                        vm.catchErrors(error);
                                    });
                                    break;
                                } else if (rootCount !== 1) {
                                    toastr.error(i18next.t('core::messages.cant_move_there'), i18next.t('core::messages.error'), {
                                        positionClass: 'toast-bottom-right'
                                    });
                                    vm.getItems(type, name);
                                }
                            }
                        }
                    } else {
                        for (var i = 0; i < listLength; i++) {
                            items.push(list[i].id);
                        }

                        var updateUrl = vm.items.find(x => x.id === elementId).links.update;

                        document.querySelectorAll('.dd-handle').forEach(function (e) {
                            e.style.display = "none";
                        });

                        document.getElementById('spinner').style.display = "block";

                        axios.post(updateUrl + '?moved=true', {
                            '_method': 'PATCH',
                            '_token': document.querySelector('.dd').getAttribute('data-token'),
                            'items': items
                        }, {
                            headers: vm.queryHeaders
                        }).then(function (response) {
                            document.querySelectorAll('.dd-handle').forEach(function (e) {
                                e.style.display = "block";
                            });

                            document.getElementById('spinner').style.display = "none";

                            var data = response.data;
                            if (data.status === 'error') {
                                toastr.error(data.message, i18next.t('core::messages.error'), {
                                    positionClass: 'toast-bottom-right'
                                });
                                vm.items = [];
                                vm.isLoading = true;
                                document.querySelector('.sg-user-list').scrollTop = 0;
                                vm.getItems(vm.itemsFilter, vm.searchPhrase);
                            } else if (data.status === 'success') {
                                toastr.success(data.message, i18next.t('core::messages.success'), {
                                    positionClass: 'toast-bottom-right'
                                });
                            }
                        });
                    }
                }
            });
        }

        if (!vm.preventChangeUrl) {
            if (!vm.itemEdition) {
                vm.changeUrl({}, 'SunApp5', urlToChange);
            } else if (vm.lastCreatedUrl != '') {
                vm.changeUrl({}, 'SunApp5', vm.lastCreatedUrl);
                vm.lastCreatedUrl = '';
            }
        } else {
            vm.preventChangeUrl = false;
        }
    }).catch(function (error) {
        vm.catchErrors(error);
        vm.getItems(vm.itemsFilter, vm.searchPhrase);
    });
};

Vue.prototype.getNextItems = function (url) {
    let vm = this;
    vm.nextPageUrl = false;
    vm.nextItemsLoading = true;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        vm.nextItemsLoading = false;
        var elements = [];
        if (response.data && response.data.data) {
            elements = response.data.data;
        }
        if (response.data && response.data.links && response.data.links.next) {
            vm.nextPageUrl = response.data.links.next;
        }
        elements.forEach(function (item) {
            vm.items.push(item);
        });
    });
};

Vue.prototype.getSubItems = function (parent_id) {
    let vm = this;

    var parent = document.querySelector('.dd-item[data-id="' + parent_id + '"]');
    if (parent) {
        var subtreeBtn = parent.querySelector('.dd-subtree');
        var parentList = parent.querySelector('.dd-list');
    }

    if (subtreeBtn && subtreeBtn.classList.contains('open')) {
        if (parent) {
            parentList.style.display = "none";
        }
        subtreeBtn.classList.remove('open');
        return false;
    }

    var url = vm.currentPageUrl;

    url += (url.indexOf('?') > -1) ? '&' : '?';
    url += 'parent_id=' + parent_id;
    url += '&timestamp=' + new Date().getTime();

    if (url.indexOf('search') > -1) {
        var searchParam = url.substring(
            url.lastIndexOf("search"),
            url.indexOf("&")+1
        );
        url = url.replace(searchParam,'');
    }

    if (parent) {
        parentList.insertAdjacentHTML('beforeend', vm.loaderHtml );
        parentList.style.display = "block";
        if (subtreeBtn) {
            subtreeBtn.classList.add('open');
        }
    }
    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        var elements = [];
        if (parent) {
            parent.querySelector('.placeholders--inner').remove();
        }
        if (response.data && response.data.data) {
            elements = response.data.data;
            elements.forEach(function (item) {
                var alreadyExists = vm.items.find(obj => {
                    return obj.id === item.id
                });

                var parentId = item.attributes.parent_id;
                if (parentId && !alreadyExists) {
                    vm.items.push(item);
                    var domElement = document.querySelector('.dd-item[data-id="' + item.id + '"]');
                    var parent = document.querySelector('.dd-item[data-id="' + parentId + '"]');

                    if (domElement && parent) {
                        parentList.appendChild(domElement);
                    }
                }
            });
        }
    });
};

Vue.prototype.continueHidingCard = function () {
    let vm = this;

    if (vm.lastSearchUrl) {
        vm.changeUrl({}, "SunApp5", vm.lastSearchUrl);
    } else {
        vm.getItems(vm.itemsFilter, vm.searchPhrase);
    }

    vm.itemSaved = false;

    vm.destroyEditors();

    $('select').each(function (i, obj) {
        if ($(obj).data('select2')) {
            $(obj).select2("destroy");
        }
    });

    document.querySelector('.sg-scroll-area').scrollTop = 0;
    document.querySelector('.sg-app-details').classList.remove('show');
    if (document.querySelector('.sg-scroll-area .nav-link')) {
        document.querySelector('.sg-scroll-area .nav-link.active').classList.remove('active');
        document.querySelector('.sg-scroll-area .nav-link').classList.add('active');
    } else if (document.querySelector('.sg-scroll-area .sg-nav-link')) {
        document.querySelector('.sg-scroll-area .sg-nav-link.active').classList.remove('active');
        document.querySelector('.sg-scroll-area .sg-nav-link').classList.add('active');
    }

    if (document.querySelector('.sg-scroll-area .tab-pane')) {
        document.querySelector('.sg-scroll-area .tab-pane.active').classList.remove('active');
        document.querySelector('.sg-scroll-area .tab-pane').classList.add('active');
    }

    vm.elementToShow = false;
    vm.itemPreview = false;
    vm.itemEdition = false;
    vm.itemDuplicating = false;
    vm.panelOpen = false;

    var $dd_media = $('.dd-media');
    if ($dd_media.length) {
        $dd_media.find('.dd-list').html('');
    }

    var form = document.getElementById("form-data");

    if (form) {
        var elements = form.elements;
    }

    if (elements) {
        for (var i = 0, element; element = elements[i++];) {
            if (element.name === "publish_at"
                || element.name === "start_at"
                || element.name === "stop_at"
                || element.classList.contains('datepicker')
            ) {

                var isDatePicker = false;

                if (element.classList.contains('datepicker')) {
                    isDatePicker = true;
                }
                if ($('#' + element.name).data('daterangepicker')) {
                    $('#' + element.name).data('daterangepicker').remove();
                }

                if (isDatePicker) {
                    $('#' + element.name).daterangepicker(datePickerConfig);

                    $('#' + element.name)
                        .data('daterangepicker')
                        .setStartDate(moment(new Date()).format("YYYY-MM-DD"));
                    $('#' + element.name)
                        .data('daterangepicker')
                        .setEndDate(moment(new Date()).format("YYYY-MM-DD"));

                    $('#' + element.name).on('show.daterangepicker', function (ev, picker) {
                        picker.container.find(".calendar-time").hide();
                    }).on('showCalendar.daterangepicker', function (ev, picker) {
                        picker.container.find('.calendar-time').remove();
                    });
                } else {
                    $('#' + element.name).daterangepicker(dateRangePickerConfig);

                    $('#' + element.name)
                        .data('daterangepicker')
                        .setStartDate(moment(new Date()).format("YYYY-MM-DD HH:mm:00"));
                    $('#' + element.name)
                        .data('daterangepicker')
                        .setEndDate(moment(new Date()).format("YYYY-MM-DD HH:mm:00"));
                }
            }

            if (element.classList.contains('selectize')) {
                if ($('#' + element.name)[0].selectize) {
                    $('#' + element.name)[0].selectize.destroy();
                }
            }

            if (element.type.indexOf("select") > -1 && element.getAttribute('data-url')) {
                element.value = "";
            } else if (element.type.indexOf("select") > -1) {
                element.selectedIndex = 0;
            } else if (element.name === "options") {
                element.value = "{}";
            } else if (element.type.indexOf("text") > -1) {
                element.value = "";
            } else if (element.type.indexOf("number") > -1) {
                element.value = "0";
            } else if (element.type.indexOf("checkbox") > -1) {
                element.checked = false;
            } else if (element.type.indexOf("radio") > -1) {
                element.checked = false;
            }
        }
    }

    var orderSelect = document.querySelector('[name="list-ordering"]');

    if (orderSelect) {
        $(orderSelect).not('.treeselect').not('.not-select2').select2(selectConfig);
    }
}

Vue.prototype.prepareToHide = function () {
    let vm = this;

    const etscCopyString = JSON.stringify(vm.elementToShowCopy).replaceAll('null', '""');
    const etsCopyString = JSON.stringify(vm.elementToShow).replaceAll('null', '""');

    if (vm.elementToShowCopy && vm.elementToShow && (etscCopyString !== etsCopyString)) {
        shouldClose = swal.fire({
            title: i18next.t('core::messages.sure'),
            text: i18next.t('core::messages.will_lost_not_saved'),
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: i18next.t('core::actions.yes'),
            cancelButtonText: i18next.t('core::actions.cancel'),
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                vm.continueHidingCard();
            } else {
                return false;
            }
        });
    } else {
        vm.continueHidingCard();
    }
};

Vue.prototype.popChange = function () {
    window.addEventListener('popstate', function (event) {
        window.location.reload();
    }, false);
};

Vue.prototype.mountedHook = function () {
    var currentHref = window.location.href;

    if (currentHref === this.moduleUrl) {
        if (typeof this.moduleName !== 'undefined'
            && typeof this.sorting !== 'undefined'
            && this.sorting
            && typeof this.ordering !== 'undefined'
            && this.ordering
        ) {
            Cookies.set('sunapp_'+this.moduleName+'_sort', '?orderBy='+this.ordering+'&sortedBy='+this.sorting, {expires: 30})
        }
        this.getItems('all', "");
    } else {
        if (currentHref.indexOf('/create') > -1) {
            this.createElement();
        } else if (currentHref.indexOf('?trashed=only') > -1) {
            if (currentHref.indexOf('&search=') > -1) {
                this.getItems('trashed', currentHref.substring(currentHref.indexOf("&search=") + 8));
            } else {
                var url = currentHref.split('/');
                var idPart = (url[url.length - 1].substr(0, url[url.length - 1].indexOf('?')));
                if (!isNaN(idPart)) {
                    this.itemsFilter = "trashed";
                    this.showElement(currentHref);
                } else {
                    this.getItems('trashed', "");
                }
            }
        } else if (currentHref.indexOf('?search=') > -1) {
            if (currentHref.indexOf('active:0') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('inactive', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('inactive', "");
                }
            } else if (currentHref.indexOf('active:1') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('active', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('active', "");
                }
            }
            if (currentHref.indexOf('domain:') > -1) {
                var domainId;
                if (currentHref.indexOf(';') > -1) {
                    domainId = currentHref.substring(currentHref.indexOf("domain:") + 7, currentHref.indexOf(";"));
                    this.getItems('domain:' + domainId, currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    domainId = currentHref.substring(currentHref.indexOf("domain:") + 7);
                    this.getItems('domain:' + domainId, "");
                }
            } else if (currentHref.indexOf('type:') > -1) {
                var type;
                if (currentHref.indexOf(';') > -1) {
                    type = currentHref.substring(currentHref.indexOf("type:") + 5, currentHref.indexOf(";"));
                    this.getItems('type:' + type, currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    type = currentHref.substring(currentHref.indexOf("type:") + 5);
                    this.getItems('type:' + type, "");
                }
            } else if (currentHref.indexOf('shop_id:') > -1) {
                var type;
                if (currentHref.indexOf(';') > -1) {
                    type = currentHref.substring(currentHref.indexOf("shop_id:") + 8, currentHref.indexOf(";"));
                    this.getItems('shop_id:' + type, currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    type = currentHref.substring(currentHref.indexOf("shop_id:") + 8);
                    this.getItems('shop_id:' + type, "");
                }
            } else if (currentHref.indexOf('category:') > -1) {
                var type;
                if (currentHref.indexOf(';') > -1) {
                    type = currentHref.substring(currentHref.indexOf("category:") + 9, currentHref.indexOf(";"));
                    this.getItems('category:' + type, currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    type = currentHref.substring(currentHref.indexOf("category:") + 9);
                    this.getItems('category:' + type, "");
                }
            } else if (currentHref.indexOf('parent:') > -1) {
                var parentId;

                if (currentHref.indexOf(';') > -1 && currentHref.lastIndexOf(';') > currentHref.indexOf(';level')) {
                    parentId = currentHref.substring(currentHref.indexOf("parent:") + 7, currentHref.lastIndexOf(";"));
                    this.getItems('parent:' + parentId, currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    parentId = currentHref.substring(currentHref.indexOf("parent:") + 7);
                    this.getItems('parent:' + parentId, "");
                }
            } else {
                var colon = currentHref.indexOf(';'),
                    amp = currentHref.indexOf('&'),
                    start = currentHref.indexOf("?search=") + 8;

                var specialCharPosition = (colon > amp) ? colon : amp;

                if (specialCharPosition > start) {
                    this.getItems('all', currentHref.substring(start, specialCharPosition));
                } else {
                    this.getItems('all', currentHref.substring(start));
                }
            }
        } else if (currentHref.indexOf('/edit') > -1) {
            this.editElement(currentHref);
        } else if (currentHref.indexOf('orderBy') > -1 && currentHref.indexOf('sortedBy') > -1) {
            this.ordering = currentHref.substring(currentHref.indexOf("orderBy") + 8, currentHref.indexOf('sortedBy') - 1);
            this.sorting = currentHref.substring(currentHref.indexOf("sortedBy") + 9);

            this.setOrder(this.ordering, this.sorting);
        } else {
            var url = currentHref.split('/');
            var lastPart = (url[url.length - 1]);
            if (lastPart.indexOf('_') > -1) {
                lastPart = lastPart.split('_')[0];
            }
            if (!isNaN(lastPart)) {
                this.showElement(currentHref);
                return false;
            } else if (lastPart !== 'items-order') {
                this.getItems('all', "");
            } else if (lastPart === 'items-order') {
                var $scroll_area = this.$refs.form_wrapper.querySelector('.sg-scroll-area');
                new PerfectScrollbar($scroll_area);
            }
        }
    }

    var $dd_ordering = $('.dd-ordering');
    if ($dd_ordering) {
        var vm = this;
        var $ordering_wrapper = document.querySelector('.sg-app-details');
        $dd_ordering.nestable({
            maxDepth: 1,
            callback: function (l, e) {
                var url = vm.moduleUrl + '/' + $ordering_wrapper.getAttribute('data-category-id');
                var values = [];
                var items = document.querySelectorAll("input[name='items_ordering[]']");
                for (var i = 0; i < items.length; i++) {
                    values.push(items[i].value);
                }
                var postData = {
                    'items_ordering': values,
                    '_token': $ordering_wrapper.getAttribute('data-token')
                };
                axios.put(url, postData, {
                    headers: vm.queryHeaders
                }).then(function (response) {
                    var data = response.data;
                    if (data.status === 'error') {
                        toastr.error(data.message, i18next.t('core::messages.error'), {
                            positionClass: 'toast-bottom-right'
                        });
                    } else if (data.status === 'success') {
                        toastr.success(data.message, i18next.t('core::messages.success'), {
                            positionClass: 'toast-bottom-right'
                        });
                    }
                }).catch(function (error) {
                    vm.catchErrors(error);
                });
            }
        });
    }
};

Vue.prototype.changeUrl = function (data, title, url) {
    window.history.pushState(data, title, url);
};

Vue.prototype.setOrder = function (column = 'id', sorting = 'asc') {
    let vm = this;

    if (column.indexOf(',') > -1) {
        var orderingParams = column.split(',');

        column = orderingParams[0];
        sorting = orderingParams[1];
    } else if (sorting === 'reverse') {
        if (!vm.sorting || vm.sorting === "asc") {
            sorting = "desc";
        } else if (vm.sorting === "desc") {
            sorting = "asc";
        }
    }

    if (typeof vm.moduleName !== 'undefined') {
        Cookies.set('sunapp_'+vm.moduleName+'_sort', '?orderBy='+column+'&sortedBy='+sorting, {expires: 30})
    }

    vm.sorting = sorting;
    vm.ordering = column;

    vm.changeItems(vm.itemsFilter, vm.searchPhrase, column, sorting);
}

Vue.prototype.reloadSelect = function (field) {
    if ($(field).data('select2')) {
        $(field).select2('destroy').trigger('change');
        $(field).select2(selectConfig);
    }
}

CKEDITOR.on( 'dialogDefinition', function( ev )
{
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;

    if ( dialogName == 'link' || dialogName == 'image' )
    {
        dialogDefinition.removeContents( 'Upload' );
    }
});

Vue.prototype.destroyEditors = function () {
    var cke;
    for (cke in CKEDITOR.instances) {
        CKEDITOR.instances[cke].destroy();
    }
}

Vue.prototype.updateEditors = function () {
    var cke;
    for (cke in CKEDITOR.instances) {
        CKEDITOR.instances[cke].updateElement();
    }
}

Vue.prototype.makeEditor = function (fieldName) {
    CKEDITOR.replace(document.querySelector('textarea[name="' + fieldName + '"]'), {
        height: 260
    });
}

Vue.prototype.preventChangeRoute = function () {
    let vm = this;
    vm.canLeaveRoute = false;
}
