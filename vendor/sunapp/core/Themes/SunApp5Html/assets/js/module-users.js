Vue.prototype.switchSearchType = function (type, parameters) {
    let vm = this;

    switch (type) {
        case 'all':
            vm.itemsFilter = type;
            break;
        case 'verified':
            parameters = '?search=email_verified_at:1';
            vm.itemsFilter = type;
            break;
        case 'not_verified':
            parameters = '?search=email_verified_at:0';
            vm.itemsFilter = type;
            break;
        case 'banned':
            parameters = '?search=banned:1';
            vm.itemsFilter = type;
            break;
        case 'superadmin':
            parameters = '?search=superadmin:1';
            vm.itemsFilter = type;
            break;
        case 'ldap':
            parameters = '?search=ldap:1';
            vm.itemsFilter = type;
            break;
        case 'not_ldap':
            parameters = '?search=ldap:0';
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
    }

    return parameters;
};

Vue.prototype.additionalMetaCounters = function (counters) {
    let vm = this;

    if (counters.verified !== "undefined") {
        vm.itemsVerified = counters.verified;
    }
    if (counters.not_verified !== "undefined") {
        vm.itemsNotVerified = counters.not_verified;
    }
    if (counters.banned !== "undefined") {
        vm.itemsBanned = counters.banned;
    }
    if (counters.domain_users !== "undefined") {
        vm.domainItems = counters.domain_users;
    }
    if (counters.ldap !== "undefined") {
        vm.itemsLDAP = counters.ldap;
    }
    if (counters.not_ldap !== "undefined") {
        vm.itemsNotLDAP = counters.not_ldap;
    }
    if (counters.superadmins !== "undefined") {
        vm.itemsSuperadmins = counters.superadmins;
    }
};

Vue.prototype.clearFieldsAfterSavingElement = function() {
    var passF = document.querySelector('[name="password"]'),
        passRF = document.querySelector('[name="password_confirmation"]');

    if (passF) {
        passF.value = "";
    }
    if (passRF) {
        passRF.value = "";
    }
};

Vue.prototype.generatePassword = function (length, field) {
    let vm = this;

    if (Array.isArray(length)) {
        length = Math.floor(Math.random() * (length[1] - length[0] + 1)) + length[0];
    }

    var charset = "abcdefghjkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789",
        password = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        password += charset.charAt(Math.floor(Math.random() * n));
    }

    if (field === 'password') {
        document.querySelector('input[name="password"]').value = password;
        document.querySelector('input[name="password_confirmation"]').value = password;
        vm.copyValue(password);
        toastr.success(i18next.t('core::users.password_copied'), i18next.t('core::users.password') + ": " + password, {
            positionClass: 'toast-bottom-right'
        });
    } else if (field === 'api_token') {
        var tokenField = document.querySelector('textarea[name="api_token"]');
        if (tokenField.readOnly) {
            var enableButton = document.querySelector('label[for="api_token"] .enable-field');
            enableButton.click();
        }
        tokenField.value = password;
    }
};

Vue.prototype.permitElement = function (e, type) {
    let vm = this;
    var form,
        info;

    if (e.target.classList.contains('chip-text')) {
        form = e.target.closest('.chip').nextElementSibling;
    } else {
        form = e.target.parentNode.nextElementSibling;
    }

    switch (type) {
        case 'super':
            info = i18next.t('core::messages.will_be_superadmin');
            break;
        case 'unsuper':
            info = i18next.t('core::messages.will_not_be_superadmin');
            break;
        case 'ban':
            info = i18next.t('core::messages.will_be_banned');
            break;
        case 'enable2fa':
            info = i18next.t('core::messages.will_be_enable2fa');
            break;
        case 'disable2fa':
            info = i18next.t('core::messages.will_be_disable2fa');
            break;
        case 'reset2fa':
            info = i18next.t('core::messages.will_be_reset2fa');
            break;
        case 'unban':
            info = i18next.t('core::messages.will_be_unbanned');
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
            vm.isLoading = true;

            var formData = new FormData(form);
            for (var pair of formData.entries()) {
                var field = document.querySelector('[name="' + pair[0] + '"]');
                if (field.readOnly) {
                    formData.delete(pair[0]);
                }
            }

            axios.post(form.action, formData, {
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

Vue.prototype.copyValue = function (val) {
    let vm = this;

    if (val === 'password') {
        val = document.querySelector('input[name="password"]').value;
        if (val != "") {
            toastr.success(i18next.t('core::users.password_copied'), i18next.t('core::users.password') + ": " + val, {
                positionClass: 'toast-bottom-right'
            });
        } else {
            vm.generatePassword(8, 'password');
        }
    } else if (val === 'api_token') {
        val = document.querySelector('textarea[name="api_token"]').value;
        if (val != "") {
            toastr.success(i18next.t('core::users.token_copied'), "", {
                positionClass: 'toast-bottom-right'
            });
        } else {
            vm.generatePassword([64, 250], 'api_token');
        }
    }

    const input = document.createElement('input');
    document.body.appendChild(input);
    input.value = val;
    input.focus();
    input.select();
    document.execCommand('copy');
    input.remove();
};


Vue.prototype.refreshFieldUser_role = function (htmlField = null, url = false, formDisabled = false) {
    let vm = this;

    axios.get(url, {
        headers: vm.queryHeaders
    }).then(function (response) {
        if (response.data) {
            var fieldValues = [],
                options = htmlField.options,
                opt,
                i;

            if (vm.elementToShow) {
                for (i = 0, iLen = options.length; i < iLen; i++) {
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
                opt.innerHTML = el.attributes.name + " (#" + el.id + ")";
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
}

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
            } else if (currentHref.indexOf('email_verified_at:0') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('not_verified', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('not_verified', "");
                }
            } else if (currentHref.indexOf('email_verified_at:1') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('verified', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('verified', "");
                }
            }
            if (currentHref.indexOf('banned:1') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('banned', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('banned', "");
                }
            } else if (currentHref.indexOf('domain:') > -1) {
                var domainId;
                if (currentHref.indexOf(';') > -1) {
                    domainId = currentHref.substring(currentHref.indexOf("domain:") + 7, currentHref.indexOf(";"));
                    this.getItems('domain:' + domainId, currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    domainId = currentHref.substring(currentHref.indexOf("domain:") + 7);
                    this.getItems('domain:' + domainId, "");
                }
            } else if (currentHref.indexOf('ldap:0') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('not_ldap', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('not_ldap', "");
                }
            } else if (currentHref.indexOf('ldap:1') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('ldap', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('ldap', "");
                }
            } else if (currentHref.indexOf('superadmin:1') > -1) {
                if (currentHref.indexOf(';') > -1) {
                    this.getItems('superadmin', currentHref.substring(currentHref.indexOf("?search=") + 8, currentHref.indexOf(";")));
                } else {
                    this.getItems('superadmin', "");
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
            }
        }
    }
};
