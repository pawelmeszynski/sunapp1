(function($) {
    $.fn.trigger2 = function(eventName) {
        return this.each(function() {
            var el = $(this).get(0);
            triggerNativeEvent(el, eventName);
        });
    };

    function triggerNativeEvent(el, eventName){
        if (el.fireEvent) {
            (el.fireEvent('on' + eventName));
        } else {
            var evt = document.createEvent('Events');
            evt.initEvent(eventName, true, false);
            el.dispatchEvent(evt);
        }
    }
}(jQuery));

i18next
    .use(window.i18nextXHRBackend)
    .init({
        debug: false,
        lng: document.getElementsByTagName('html')[0].getAttribute('lang'),
        fallbackLng: document.getElementsByTagName('html')[0].getAttribute('lang-fallback'),
        ns: "",
        defaultNS: '',
        nsSeparator: '::',
        keySeparator: '.',
        backend: {
            loadPath: function(lngs, namespaces) {
                var url = adminBaseUrl + "/i18next/fetch/"+lngs;
                if (namespaces.toString() !== "") {
                    url += "/" + namespaces;
                }
                return url;
            }
        },
        returnObjects: true
    }).then(function (t) {
        jqueryI18next.init(i18next, $);
        dateRangePickerConfig.locale.monthNames = [
            i18next.t('core::calendar.months_standalone.0'),
            i18next.t('core::calendar.months_standalone.1'),
            i18next.t('core::calendar.months_standalone.2'),
            i18next.t('core::calendar.months_standalone.3'),
            i18next.t('core::calendar.months_standalone.4'),
            i18next.t('core::calendar.months_standalone.5'),
            i18next.t('core::calendar.months_standalone.6'),
            i18next.t('core::calendar.months_standalone.7'),
            i18next.t('core::calendar.months_standalone.8'),
            i18next.t('core::calendar.months_standalone.9'),
            i18next.t('core::calendar.months_standalone.10'),
            i18next.t('core::calendar.months_standalone.11')
        ];
        dateRangePickerConfig.locale.daysOfWeek = [
            i18next.t('core::calendar.weekdays_min.0'),
            i18next.t('core::calendar.weekdays_min.1'),
            i18next.t('core::calendar.weekdays_min.2'),
            i18next.t('core::calendar.weekdays_min.3'),
            i18next.t('core::calendar.weekdays_min.4'),
            i18next.t('core::calendar.weekdays_min.5'),
            i18next.t('core::calendar.weekdays_min.6'),
        ];
        dateRangePickerConfig.locale.firstDay = parseInt(i18next.t('core::calendar.first_day'));
        dateRangePickerConfig.locale.applyLabel = i18next.t('core::actions.ok');
        dateRangePickerConfig.locale.cancelLabel = i18next.t('core::actions.cancel');

        var $datepicker = $('.datetimepicker.timepicker');
        if ($datepicker.length) {
            $datepicker.daterangepicker(timePickerConfig).on('show.daterangepicker', function (ev, picker) {
                picker.container.find(".calendar-table").hide();
            }).on('showCalendar.daterangepicker', function (ev, picker) {
                picker.container.find('.calendar-date').remove();
            });
            $datepicker.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('HH:mm'));
            });
        }

        $datepicker = $('.datetimepicker.datepicker');
        if ($datepicker.length) {
            $datepicker.daterangepicker(datePickerConfig).on('show.daterangepicker', function (ev, picker) {
                picker.container.find(".calendar-time").hide();
            }).on('showCalendar.daterangepicker', function (ev, picker) {
                picker.container.find('.calendar-time').remove();
            });
            $datepicker.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
                $(this).trigger2('input');
            });
        }

        $datepicker = $('.datetimepicker:not(.timepicker):not(.datepicker)');
        if ($datepicker.length) {
            $datepicker.daterangepicker(dateRangePickerConfig);
            $datepicker.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
                $(this).trigger2('input');
            });
        }
    });

var selectConfig = {
    language: {
        searching: function () {
            return i18next.t('core::actions.searching');
        },
        inputTooShort: function () {
            return i18next.t('core::actions.type_to_search');
        },
        noResults: function() {
            return i18next.t('core::actions.no_results');
        },
    },
    width: '100%',
    escapeMarkup: function (m) {
        return m;
    },
};

var treeConfig = {
    expanderTemplate: '<span class="treegrid-expander fa fa-square-o"></span>',
    expanderExpandedClass: 'fa-minus-square-o',
    expanderCollapsedClass: 'fa-plus-square-o',
    initialState: 'collapsed'
};

var dateRangePickerConfig = {
    autoUpdateInput: false,
    singleDatePicker: true,
    showDropdowns: false,
    timePicker: true,
    timePicker24Hour: true,
    parentEl: '.sg-scroll-area',
    locale: {
        format: 'YYYY-MM-DD HH:mm:ss',
        monthNames: [],
        daysOfWeek: [],
        applyLabel: '',
        cancelLabel: '',
    }
}

var timePickerConfig = {
    singleDatePicker: true,
    timePicker: true,
    timePicker24Hour: true,
    timePickerIncrement: 1,
    timePickerSeconds: false,
    alwaysShowCalendars: false
};
timePickerConfig = $.extend({}, dateRangePickerConfig, timePickerConfig);

var datePickerConfig = {
    singleDatePicker: true,
    alwaysShowCalendars: false,
};
datePickerConfig = $.extend({}, dateRangePickerConfig, datePickerConfig);
datePickerConfig.locale.format = 'YYYY-MM-DD';

var tagsUrl = adminBaseUrl + '/cms/tags';
var selectizeConfig = {
    plugins: ['restore_on_backspace', 'remove_button'],
    valueField: 'value',
    labelField: 'value',
    searchField: 'value',
    sortField: 'value',
    options: [],
    delimiter: ',',
    persist: true,
    create: true,
    closeAfterSelect: true,
    openOnFocus: false,
    render: {
        option_create: function (data, escape) {
            var addString = 'Dodaj';
            return '<div class="create option">' + addString + ' <strong>' + escape(data.input) + '</strong>&hellip;</div>';
        }
    },
    load: function (query, callback) {
        var url = this.$input[0].getAttribute('data-url');

        if (url === null) {
            url = tagsUrl;
        }

        if (url) {
            if (!query.length) return callback();
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                data: {
                    search: query,
                },
                error: function () {
                    callback();
                },
                success: function (res) {
                    callback(res.data);
                }
            });
        } else {
            return null;
        }
    },
    onInitialize: function () {
        var selectize = this;
        var url = this.$input[0].getAttribute('data-url');
        if (url === null) {
            url = tagsUrl;
        }
        if (url) {
            $.get(url, function (data) {
                if (data.data) {
                    $.each(data.data, function (i, obj) {
                        selectize.addOption({
                            text: obj.attributes.name,
                            value: obj.attributes.name
                        });
                    });
                }
            });
        }
    }
};

$(document).ready(function () {
    checkSelectedLang();
    Pace.on('done', function () {
        checkSelectedLang();
    });

    $('select:not(.not-select2):not(.treeselect):not(.select2-hidden-accessible)').select2(selectConfig);
    $('table.tree').treegrid(treeConfig);

    $(document).on('click', '.navbar-langs .content-language-selector .dropdown-menu a', function (e) {
        e.preventDefault();
        changeLang($(this));
    });
});

$(document).on('select2:unselect', 'select', function () {
    var element = this;
    if (typeof app !== 'undefined' && typeof app.changedField === 'function') {
        app.changedField(element.name);
    }
});

$(document).on('select2:select', 'select', function () {
    var element = this;
    if (typeof app !== 'undefined' && typeof app.changedField === 'function') {
        app.changedField(element.name);
    }
});

$(document).on('click', '.modern-nav-toggle', function () {
    if (!Cookies.get('sunapp_menu_open')) {
        Cookies.set('sunapp_menu_open', 0, {expires: 30})
    } else {
        Cookies.set('sunapp_menu_open', 1, {expires: 30})
    }
});

$(document).on('click', '.nav-link', function () {
    $('select').trigger('change');
});

function changeLang(element) {
    Pace.ignore(function () {
        $.get(element.attr('href'));
    });
    $('.content-language-selector .dropdown-menu a').removeClass('active');
    element.addClass('active');
    $('.content-language-selector .dropdown-toggle').html(element.html());
    $('.content-language-selector').data('lang', element.attr('data-lang'));
    if (typeof app !== 'undefined') app.currentLang = element.attr('data-lang');
    checkSelectedLang();
}

function checkSelectedLang() {
    lang = $('.content-language-selector').data('lang');
    if (typeof app !== 'undefined') {
        app.currentLang = lang;
    }
    $('.form-group-translation .form-field-translation').hide();
    $('.form-group-translation .form-field-translation.form-field-translation-' + lang).show();
}

$(document).on('mouseleave', '.sidebar-left', function (e) {
    var $sidebar = $(this);

    var $picker = $('.content-area-wrapper > .daterangepicker');
    if ($picker.length && $picker.is(':visible')) {
        $('.sidebar-left').addClass('show');
    } else {
        $('.sidebar-left').removeClass('show');
    }
});

$(document).on('click', '.toggle-card', function (e) {
    const isOpen = $(this).hasClass('toggle-card--closed'),
        $span = $(this).find('span'),
        $cardBody = $(this).closest('.card').find('.card-body');

    if (!isOpen) {
        $span.text($(this).attr('data-open'));
    } else {
        $span.text($(this).attr('data-close'));
    }

    $cardBody.slideToggle(500);
    $(this).toggleClass('toggle-card--closed');
});

function openModal(url, trans = 'core::actions.show_on_front') {
    this.event.preventDefault();
    if (document.getElementById('showOnFront') == null) {
        const body = document.querySelector('body');
        const modal = document.createElement("div");
        const modalDialog = document.createElement("div");
        const modalContent = document.createElement("div");
        const modalHeader = document.createElement("div");
        const modalBody = document.createElement("div");
        const modalFooter = document.createElement("div");
        const modalTitle = document.createElement("h4");
        const closeButton = document.createElement("button");

        modal.classList.add("modal");
        modal.setAttribute("id", "showOnFront");
        modal.setAttribute("role", "dialog");
        modalDialog.classList.add("modal-dialog");
        modalContent.classList.add("modal-content");
        modalHeader.classList.add("modal-header");
        modalBody.classList.add("modal-body", "spinner-border");
        modalBody.style.margin = 'auto';
        modalBody.setAttribute("id", "modal-body");
        modalFooter.classList.add("modal-footer");
        closeButton.setAttribute("type", "button");
        closeButton.setAttribute("data-dismiss", "modal");
        closeButton.classList.add("btn", "btn-default");

        modalTitle.appendChild(document.createTextNode(i18next.t(trans)));
        modalHeader.appendChild(modalTitle);
        modalContent.appendChild(modalHeader);
        modalContent.appendChild(modalBody);
        closeButton.appendChild(document.createTextNode(i18next.t('core::actions.close')));
        modalFooter.appendChild(closeButton);
        modalContent.appendChild(modalFooter);
        modalDialog.appendChild(modalContent);
        modal.appendChild(modalDialog);
        body.appendChild(modal);
    } else {
        document.getElementById("modal-body").innerHTML='';
        document.getElementById("modal-body").classList.add('spinner-border');
        document.getElementById("modal-body").style.margin = 'auto';
    }

    $('#showOnFront').modal('show');
    $.ajax({
        url: url,
        type: 'get',
        success: function(response){
            document.getElementById("modal-body").classList.remove('spinner-border');
            document.getElementById("modal-body").innerHTML=response;
            document.getElementById("modal-body").style.margin = '1rem';
        }
    });
}

window.addEventListener('beforeunload', function (e) {
    const $app = document.getElementById('app');

    if ($app) {
        $vue = $app.__vue__;

        if ($vue) {
            if (typeof $vue.preventEventBeforeUnload !== 'undefined' && $vue.preventEventBeforeUnload) {
                return false;
            }
            if (!$vue.forceLeaveRoute && $vue.elementToShow) {
                if (
                    (JSON.stringify($vue.elementToShowCopy) !== JSON.stringify($vue.elementToShow))
                    || !$vue.canLeaveRoute
                ) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            }
        }
    }
});
