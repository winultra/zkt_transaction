BX.namespace('Custom.ZKTConnect.Transaction.List')

BX.Custom.ZKTConnect.Transaction.List = {
    gridId: null,
    signedParameters: null,
    popupId: 'zkt_update_popup',
    process: {},

    init: function (params) {
        this.gridId = params.gridId;
        this.signedParameters = params.signedParameters;
    },

    showUpdatePopup: function () {
        if ($(`#${this.popupId}`).length > 0) {
            return;
        }

        let content = $('<div>', {
            class: 'crm-entity-popup-item-container',
            'data-id': 'add_stage',
            append: [
                $('<div>', {
                    class: 'ui-entity-editor-content-block ui-entity-editor-field-text',
                    'data-cid': 'DATE_START',
                    append: [
                        $('<div>', {
                            class: 'ui-entity-editor-block-title ui-entity-widget-content-block-title-edit',
                            append: $('<label>', {
                                class: 'ui-entity-editor-block-title-text required',
                                for: 'title_text',
                                text: BX.message('DATE_START')
                            })
                        }),
                        $('<div>', {
                            class: 'ui-entity-editor-content-block',
                            'data-type': 'date',
                            append: $('<div>', {
                                class: 'ui-ctl ui-ctl-after-icon ui-ctl-datetime ui-ctl-w100',
                                append: [
                                    $('<div>', {
                                        class: 'ui-ctl-after ui-ctl-icon-calendar',
                                    }),
                                    $('<input>', {
                                        class: 'ui-ctl-element',
                                        type: 'text'
                                    })
                                ]
                            })
                        }),
                    ]
                }),

                $('<div>', {
                    class: 'ui-entity-editor-content-block ui-entity-editor-content-block-field-custom-file',
                    'data-cid': 'DATE_END',
                    append: [
                        $('<div>', {
                            class: 'ui-entity-editor-block-title ui-entity-widget-content-block-title-edit',
                            append: $('<label>', {
                                class: 'ui-entity-editor-block-title-text required',
                                text: BX.message('DATE_END')
                            })
                        }),
                        $('<div>', {
                            class: 'ui-entity-editor-content-block',
                            'data-type': 'date',
                            append: $('<div>', {
                                class: 'ui-ctl ui-ctl-after-icon ui-ctl-datetime ui-ctl-w100',
                                append: [
                                    $('<div>', {
                                        class: 'ui-ctl-after ui-ctl-icon-calendar',
                                    }),
                                    $('<input>', {
                                        class: 'ui-ctl-element',
                                        type: 'text'
                                    })
                                ]
                            })
                        }),
                    ]
                }),
            ]
        });

        let popup = new BX.PopupWindow(
            this.popupId,
            null,
            {
                content: content[0],
                closeIcon: {right: "20px", top: "10px"},
                titleBar: {
                    content: $('<span>', {
                        class: 'popup-window-titlebar-text',
                        text: BX.message('POPUP_TITLE')
                    })[0]
                },
                zIndex: 0,
                offsetLeft: 0,
                offsetTop: 0,
                width: 440,
                draggable: {restrict: false},
                events: {
                    onPopupClose: () => {
                        popup.destroy()
                    },
                    onPopupShow: () => {
                        this.initCalendar()
                    }
                },
                buttons: [
                    new BX.PopupWindowButton({
                        text: BX.message('POPUP_BUTTON_SAVE'),
                        className: "ui-btn ui-btn-primary",
                        events: {
                            click: async (e) => {
                                let result = await this.handleUpdateClick(e)
                                if (result) {
                                    popup.close()
                                }
                            }
                        }
                    }),
                    new BX.PopupWindowButton({
                        text: BX.message('POPUP_BUTTON_CANCEL'),
                        className: "ui-btn ui-btn-link",
                        events: {
                            click: () => {
                                popup.close()
                            }
                        }
                    })
                ]
            })

        popup.show()
    },

    initCalendar: function () {
        let dateNodes = $(`#${this.popupId} div[data-type="date"]`);

        dateNodes.each((i, node) => {
            let inputNode = $(node).find('input')
            $(inputNode).on('click', function () {
                BX.calendar({node: this, field: this, bTime: false});
            })
        });
    },

    handleUpdateClick: async function (e) {
        if (this.process.update && this.process.update === true) {
            return
        }

        this.process.update = true;
        $(e.target).addClass('ui-btn-wait')

        let result = await this.updateProcess(e)

        $(e.target).removeClass('ui-btn-wait')
        this.process.update = false;

        return result;
    },

    updateProcess: async function (e) {

        const fields = this.getFields();

        if (!this.validateFields(fields)) {
            BX.UI.Dialogs.MessageBox.alert(BX.message('REQUIRED_FIELDS_ERROR'));
            return;
        }

        try {
            await this.update(fields)

            BX.UI.Notification.Center.notify({
                autoHideDelay: 3000,
                content: BX.message('LIST_UPDATE_SUCCESS')
            });

            this.reloadGrid();

            return true;
        } catch (error) {
            BX.UI.Dialogs.MessageBox.alert(reject.errors[0].message);
            return false;
        }

    },

    getFields: function () {
        let fields = {
            DATE_START: '',
            DATE_END: ''
        };

        let fromNode = $(".ui-entity-editor-content-block[data-cid='DATE_START']").find('input')

        if (fromNode.length > 0) {
            fields['DATE_START'] = fromNode.val();
        }

        let toNode = $(".ui-entity-editor-content-block[data-cid='DATE_END']").find('input')

        if (toNode.length > 0) {
            fields['DATE_END'] = toNode.val();
        }

        return fields;
    },

    validateFields: function (fields) {
        let isValid = true

        let requiredFields = this.getRequiredFields();

        requiredFields.forEach((field) => {
            let value = fields[field]
            if (!value || (Array.isArray(value) && value.length === 0)) {
                isValid = false;
            }
        })

        return isValid
    },

    getRequiredFields: function () {
        return ['DATE_START', 'DATE_END'];
    },

    update: function (fields) {
        return BX.ajax.runComponentAction('custom:zktconnect.transaction.list', 'update', {
            mode: 'class',
            signedParameters: this.signedParameters,
            data: {
                fields: fields
            }
        }).then(
            response => Promise.resolve(response.data),
            e => Promise.reject(e.errors[0]));
    },

    reloadGrid: function () {
        let grid = this.getGridInstance();
        if (grid) {
            grid.reloadTable();
        }
    },

    getGridInstance: function () {
        let grid = BX.Main.gridManager.getById(this.gridId);
        return grid.instance;
    }
}
