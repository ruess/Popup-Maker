var PUMTriggers;
(function ($) {
    "use strict";

    var I10n = pum_admin.I10n,
        defaults = pum_admin.defaults;

    PUMTriggers = {
        new_cookie: null,
        getLabel: function (type) {
            return I10n.labels.triggers[type].name;
        },
        getSettingsDesc: function (type, values) {
            var template = _.template(I10n.labels.triggers[type].settings_column);
            values.I10n = I10n;
            return template(values);
        },
        renumber: function () {
            $('#pum_popup_triggers_list tbody tr').each(function () {
                var $this = $(this),
                    index = $this.parent().children().index($this),
                    originalIndex = $this.data('index');

                $this.data('index', index);

                $this.find('input').each(function () {
                    var replace_with = "[" + index + "]";
                    this.name = this.name.replace("[" + originalIndex + "]", replace_with).replace("[]", replace_with);
                });
            });
        },
        refreshDescriptions: function () {
            $('#pum_popup_triggers_list tbody tr').each(function () {
                var $row = $(this),
                    type = $row.find('.popup_triggers_field_type').val(),
                    values = JSON.parse($row.find('.popup_triggers_field_settings:first').val()),
                    cookie_text = PUMTriggers.cookie_column_value(values.cookie.name);

                $row.find('td.settings-column').html(PUMTriggers.getSettingsDesc(type, values));
                $row.find('td.cookie-column code').text(cookie_text);
            });
        },
        initEditForm: function (data) {
            var $form = $('.trigger-editor .pum-form'),
                $cookie = $('#name', $form),
                trigger_settings = data.trigger_settings;

            $('#pum_popup_cookies_list tbody tr').each(function () {
                var settings = JSON.parse($(this).find('.popup_cookies_field_settings:first').val());
                if (!$cookie.find('option[value="' + settings.name + '"]').length) {
                    $('<option value="' + settings.name + '">' + settings.name + '</option>').appendTo($cookie);
                }
            });

            $cookie.val(trigger_settings.cookie.name);

            $cookie.trigger("chosen:updated");
        },
        cookie_column_value: function (cookie_name) {
            var cookie_text = I10n.no_cookie;

            if (typeof cookie_name === 'array') {
                cookie_text = cookie_name.join(', ');
            } else if (cookie_name !== null) {
                cookie_text = cookie_name;
            }
            return cookie_text;
        }
    };

    PUMTriggers.refreshDescriptions();

    $(document)
        .on('click', '#pum_popup_triggers .add-new', function () {
            var template = _.template($('script#pum_trigger_add_type_templ').html());
            PUMModals.reload('#pum_trigger_add_type_modal', template());
        })
        .on('click', '#pum_popup_triggers_list .edit', function (e) {
            var $this = $(this),
                $row = $this.parents('tr:first'),
                type = $row.find('.popup_triggers_field_type').val(),
                id = '#pum_trigger_settings_' + type,
                template = _.template($('script' + id + '_templ').html()),
                data = {
                    index: $row.parent().children().index($row),
                    type: type,
                    trigger_settings: JSON.parse($row.find('.popup_triggers_field_settings:first').val())
                };

            e.preventDefault();

            data.save_button_text = I10n.save;

            if (!template.length) {
                alert('Something went wrong. Please refresh and try again.');
            }

            PUMModals.reload(id, template(data));
            PUMTriggers.initEditForm(data);
        })
        .on('click', '#pum_popup_triggers_list .remove', function (e) {
            var $this = $(this),
                $row = $this.parents('tr:first');

            e.preventDefault();

            if (window.confirm(I10n.confirm_delete_trigger)) {
                $row.remove();
                PUMTriggers.renumber();
            }
        })
        .on('submit', '#pum_trigger_add_type_modal .pum-form', function (e) {
            var type = $('#popup_trigger_add_type').val(),
                id = '#pum_trigger_settings_' + type,
                template = _.template($('script' + id + '_templ').html()),
                data = {};

            e.preventDefault();

            data.trigger_settings = defaults.triggers[type] !== undefined ? defaults.triggers[type] : {};
            data.save_button_text = I10n.add;
            data.index = null;

            if (!template.length) {
                alert('Something went wrong. Please refresh and try again.');
            }

            PUMModals.reload(id, template(data));
            PUMTriggers.initEditForm(data);
        })
        .on('submit', '.trigger-editor .pum-form', function (e) {
            var $form = $(this),
                type = $form.find('input.type').val(),
                values = $form.serializeObject(),
                index = parseInt(values.index),
                $row = index >= 0 ? $('#pum_popup_triggers_list tbody tr').eq(index) : null,
                template = _.template($('script#pum_trigger_row_templ').html()),
                $new_row;

            e.preventDefault();

            if (!(index >= 0)) {
                values.index = $('#pum_popup_triggers_list tbody tr').length;
            }

            values.I10n = I10n;

            $new_row = template(values);

            if (!$row) {
                $('#pum_popup_triggers_list tbody').append($new_row);
            } else {
                $row.replaceWith($new_row);
            }

            PUMModals.closeAll();
            PUMTriggers.renumber();

            if (values.trigger_settings.cookie.name !== null && values.trigger_settings.cookie.name.indexOf('add_new') >= 0) {
                PUMTriggers.new_cookie = values.index;
                $('#pum_popup_cookie_fields button.add-new').trigger('click');
            }
        })
        .ready(PUMTriggers.refreshDescriptions);

}(jQuery));