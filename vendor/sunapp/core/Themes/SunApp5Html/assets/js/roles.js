i18next.loadNamespaces(['core']);

var app = new Vue({
    el: '#app',
    data: Object.assign({ moduleUrl: adminBaseUrl + '/roles' }, dataObj),
    watch: {
        searchPhrase(newSearch, oldSearch) {
            this.searchPhraseWatch(newSearch, oldSearch)
        }
    },
    methods: {
        afterGetElement() {
            let vm = this;
            let required_model = '';
            if (vm.elementToShow.attributes && vm.elementToShow.attributes.abilities) {
                var required_abilities = vm.elementToShow.attributes.required_abilities;
                var abilities = vm.elementToShow.attributes.abilities;
                for (var k1 of Object.keys(abilities)) {
                    model = k1.split('\\').join('\\\\');
                    required_model = '\\' + k1;
                    model = '\\\\' + model;

                    for (var k2 of Object.keys(abilities[k1])) {
                        var row = document.querySelector('tr[data-model="' + model + '"]');
                        if (row) {
                            row.querySelector('input[type="checkbox"][data-ability="' + k2 + '"]').checked = true;
                        }
                        if (
                            typeof required_abilities == 'object'
                            && typeof required_abilities[required_model] == 'object'
                            && typeof required_abilities[required_model][k2] != 'undefined'
                        ) {
                            for (const [key, value] of Object.entries(required_abilities[required_model][k2])) {
                                let checkboxId = 'abilities[' + key + '][' + value + ']';
                                let abilityCheckbox = document.getElementById(checkboxId);
                                abilityCheckbox.checked = true;
                                abilityCheckbox.setAttribute("data-force_disabled", 1);
                            }
                        }
                    }
                }
            }
        },
        hideCard() {
            let vm = this;

            vm.prepareToHide();
        },
        showForm() {
            let vm = this;
            var $scroll_area = vm.$refs.form_wrapper.querySelector('.sg-scroll-area');
            new PerfectScrollbar($scroll_area);
            var checkboxes = document.querySelectorAll('[data-force_disabled]');
            if (checkboxes) {
                for (const [key, value] of Object.entries(checkboxes)) {
                    value.disabled = true;
                }
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
                        const e = new Event("change"); 
                        item.dispatchEvent(e);
                    }
                });
            }
        },
        requiredAbilities(e) {
            var checkbox = e.target;
            console.log(checkbox);
            var is_checked = checkbox.checked;
            var ability = checkbox.getAttribute('data-ability');
            var row = checkbox.closest('tr');
            var model = row.getAttribute('data-model');
            let url = adminBaseUrl + '/roles/abilities?model=' + model + '&action=' + ability;
            $.ajax({
                url: url,
                type: 'get',
                success: function(response){
                    if (response != null && response != '') {
                        let abilities = response.data;
                        abilities.forEach((element) => {
                            let checkboxId = 'abilities[' + element.model + '][' + element.ability + ']';
                            let abilityCheckbox = document.getElementById(checkboxId);
                            abilityCheckbox.checked = is_checked;
                            abilityCheckbox.disabled = is_checked;
                        });
                    }
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
