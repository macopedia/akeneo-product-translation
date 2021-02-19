'use strict';

define([
        'jquery',
        'underscore',
        'pim/mass-edit-form/product/operation',
        'piotrmus-translator/template/mass-edit/translate',
        'pim/fetcher-registry',
        'pim/initselect2',
        'pim/user-context',
        'pim/i18n',
    ],
    function (
        $,
        _,
        BaseOperation,
        template,
        FetcherRegistry,
        initSelect2,
        UserContext,
        i18n
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            events: {
                'change select.form-field': 'setValue',
                'change #translated-attributes': 'setAttributes',
            },

            render: function () {
                let locales = FetcherRegistry.getFetcher('locale').fetchActivated();
                let channels = FetcherRegistry.getFetcher('channel').fetchAll();
                let attributes = FetcherRegistry.getFetcher('attribute').search({
                    types: 'pim_catalog_text,pim_catalog_textarea',
                    options: {
                        limit: 1000
                    }
                });
                Promise.all([locales, channels, attributes]).then(function (values) {
                    let locales = values[0];
                    let channels = values[1];
                    let attributes = values[2];

                    attributes = attributes.filter(function (attribute) {
                        return attribute.localizable;
                    })

                    let data = this.getFormData();
                    if (data.actions.length === 0) {
                        data.actions[0] = {
                            "sourceChannel": channels[0].code,
                            "sourceLocale": locales[0].code,
                            "targetChannel": channels[0].code,
                            "targetLocale": locales[0].code,
                            "translatedAttributes": [],
                        };
                        this.setData(data);
                    }

                    let model = this.getFormData();

                    this.$el.html(this.template({
                        locales: locales,
                        channels: channels,
                        attributes: attributes,
                        sourceChannel: model.actions[0].sourceChannel,
                        sourceLocale: model.actions[0].sourceLocale,
                        targetChannel: model.actions[0].targetChannel,
                        targetLocale: model.actions[0].targetLocale,
                        i18n: i18n,
                        uiLocale: UserContext.get('uiLocale')
                    }));

                    $('.select2').each(function (key, select) {
                        $(select).select2();
                        if ($(select).attr('readonly')) {
                            $(select).select2('readonly', true);
                        }
                    })
                }.bind(this));
                return this;
            },

            setValue: function (event) {
                this.setFormValue(event.target.name, event.target.value);
            },

            setAttributes: function (event) {
                let data = this.getFormData();
                data.actions[0].translatedAttributes = $(event.target).val();
                this.setData(data);
            },

            setFormValue: function (key, value) {
                let data = this.getFormData();
                data.actions[0][key] = value;
                this.setData(data);
            }
        });
    }
);