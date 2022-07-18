// register the component
if (typeof Vue !== "undefined") {
    Vue.component('treeselect', VueTreeselect.Treeselect);

    $(document).ready(function () {
        Pace.on('done', function () {
            treeselectInit();
        });
    });
}

function treeselectInit() {
    $('select.treeselect').each(function (index) {
        $select = $(this);
        $new_select = $select.clone();
        $new_select.insertBefore($select);
        $new_select.addClass('treeselect_copy');
        $select.removeClass('treeselect');
        $select.remove();

        let select_element = this;

        let vm_element = new Vue({
            el: 'select.treeselect_copy',
            template: `
        <div class="treeselect-container">
          <treeselect
            v-model="value"
            :multiple="multiple"
            :disabled="disabled"
            :options="options"
            :name="name"
            placeholder=""
            :flat="true"
          >
          <div slot="value-label" slot-scope="{ node }">{{ node.raw.full_label ? node.raw.full_label : node.label  }}</div>
          </treeselect>
        </div>
        `,
            watch: {
                value(newValue, oldValue) {
                    $(select_element).val(newValue);
                }
            },
            methods: {
                prepareData(asocArray) {
                    for (const [key, value] of Object.entries(asocArray)) {
                        value.full_label = value.label;
                        if (value.childrenCount === 0) delete value.children;
                        if (value.parent_id !== null) {
                            if (typeof asocArray[value.parent_id] !== "undefined") {
                                value.full_label = asocArray[value.parent_id].full_label + ' → ' + value.full_label;
                                asocArray[value.parent_id].children.push(value);
                            } else {
                                asocArray[value.id].parent_id = null;
                                asocArray[value.id].depth = 0;
                            }
                        } else {
                            asocArray[value.id].parent_id = null;
                            asocArray[value.id].depth = 0;
                        }
                    }

                    for (const [key, value] of Object.entries(asocArray)) {
                        if (asocArray[value.id].depth > 0) delete asocArray[value.id];
                    }
                    this.options = Object.values(asocArray);
                    if (![undefined, null].includes(select_element.getAttribute('data-v-model'))) {
                        this.value = eval('app.' + (select_element.getAttribute('data-v-model')));
                    }
                },
                async fetchData(url) {
                    var actionLinks = document.querySelectorAll('.save-element');
                    if (actionLinks) {
                        actionLinks.forEach(function (element) {
                            element.classList.add('disabled');
                        });
                    }

                    current_disable = this.disabled;
                    this.disabled = true;
                    url += (url.indexOf('?') > -1) ? '&' : '?';
                    url += 'per_page=-1';
                    const data = await $.ajax({
                        url: url,
                        type: 'get',
                        success: function (response) {
                            return response.data
                        }
                    });
                    if (this.$org_el.hasAttribute("data-lang")) {
                        lang = this.$org_el.getAttribute("data-lang");
                    } else {
                        lang = $('.content-language-selector').data('lang');
                    }
                    const asocArray = {};
                    data.data.forEach((element) => {
                        const {attributes} = element;
                        asocArray[element.id] = {
                            id: element.id,
                            depth: attributes.depth,
                            parent_id: attributes.parent_id,
                            label: (attributes.name[lang] ? attributes.name[lang] : attributes.name) + ' (#' + element.id + ')',
                            childrenCount: attributes.children_count,
                            children: [],
                            isDisabled: element.id == this.$org_el.getAttribute("data-disabled_id") ? true : false
                        }
                    });
                    this.prepareData(asocArray);
                    this.disabled = current_disable;
                },
                getDataStatic() {
                    if (this.$org_el.hasAttribute("data-lang")) {
                        lang = this.$org_el.getAttribute("data-lang");
                    } else {
                        lang = $('.content-language-selector').data('lang');
                    }
                    const asocArray = {};
                    const propOptions = this.$org_el.querySelectorAll("option");
                    propOptions.forEach(element => {
                        if (element.hasAttribute("data-id")) {
                            asocArray[element.getAttribute("data-id")] = {
                                id: element.getAttribute("data-id"),
                                depth: element.getAttribute("data-depth"),
                                parent_id: element.getAttribute("data-parent_id"),
                                label: (element.hasAttribute("data-" + lang) ? element.getAttribute("data-" + lang) : (element.hasAttribute("data-name") ? element.getAttribute("data-name") : element.text)),
                                childrenCount: element.getAttribute("data-childercounter"),
                                children: [],
                                isDisabled: element.getAttribute("disabled")
                            }
                        }
                    });
                    this.prepareData(asocArray);
                }
            },
            beforeMount() {
                $refresh = $(this.$el).parent().find('.refresh-field').clone();
                $(this.$el).parent().find('.refresh-field').replaceWith($refresh);
                this.$org_el = this.$el;
                let select_app = this;
                $refresh.on('click', function () {
                    const jsonData = select_app.$org_el.getAttribute('data-url');
                    if (jsonData !== null) {
                        select_app.fetchData(jsonData);
                    }
                });
            },
            mounted() {
                if (
                    this.$org_el?.getAttribute('forceAjax') === "true"
                    && this.$org_el?.getAttribute('data-url') != ''
                ) {
                    this.fetchData(this.$org_el.getAttribute('data-url'))
                } else {
                    this.getDataStatic();
                }
                if (this.$org_el.hasAttribute('name')) {
                    this.name = this.$org_el.getAttribute('name');
                }
                if (this.$org_el.hasAttribute('multiple')) {
                    this.multiple = true;
                }
                if (this.$org_el.value !== null) {
                    if (this.multiple === true) {
                        this.value = $(this.$org_el).val();
                    } else {
                        if (this.$org_el.value === '') {
                            this.value = null;
                        } else {
                            this.value = this.$org_el.value;
                        }
                    }
                }
                if (this.$org_el.hasAttribute('disabled')) {
                    this.disabled = true;
                }
            },
            data: {
                // define the default value
                value: null,
                name: 'select',
                // define options
                options: [],
                multiple: false,
                disabled: false,
                selected: false
            },
        });

        var vueInstance = document.getElementById('app').__vue__;
        vueInstance.$watch('elementToShow', function (newValue, oldValue) {
            if (vm_element.multiple === true) {
                vm_element.value = $(select_element).val();
            } else {
                vm_element.value = select_element.value ? select_element.value : null;
            }
        });
        /*select_element.addEventListener("change", evt => vm_element.value = evt.target.value)
        if (vm_element.multiple === true) {
            vm_element.value = $(select_element).val();
        } else {
            vm_element.value = select_element.value ? select_element.value : null;
        }*/
    });
}

