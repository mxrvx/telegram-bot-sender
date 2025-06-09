import PostWindow from './post'

const Xtype: string = $ns + '.window.default'
const Cls: string = $cls(Xtype)

const Default = Ext.define(Xtype, {
    extend: MODx.Window,
    xtype: Xtype,

    constructor: function (config) {
        config = config || {}
        this.initialConfig = config

        Ext.applyIf(config, {
            ApiPath: '',
            cls: Cls,
            title: '',
            width: 600,
            url: $config.api_url || '',
            baseParams: {},
            record: {},
            update: false,
            allowDrop: false,
            closeAction: 'destroy',
            fields: this.getFields(config),
            keys: this.getKeys(config),
            buttons: this.getButtons(config),
            listeners: Ext.applyIf(this.getListeners(config), {
                maximize: function () {
                    var size = Ext.getBody().getViewSize()
                    var width = size.width - 20
                    var height = size.height - 20
                    if (width > 0 && height > 0) {
                        this.setSize(width, height)
                        this.center()
                    }
                },
            }),
        })
        Default.superclass.constructor.call(this, config)
    },

    getFields: function (config) {
        return []
    },

    getKeys: function () {
        return [
            {
                key: Ext.EventObject.ENTER,
                shift: true,
                fn: function () {
                    this.submit()
                },
                scope: this,
            },
        ]
    },

    getButtons: function (config) {
        return [
            {
                text: config.cancelBtnText || _('cancel'),
                scope: this,
                handler: function () {
                    if (config.closeAction === 'destroy') {
                        this.destroy()
                    } else if (config.closeAction === 'close') {
                        this.close()
                    } else {
                        this.hide()
                    }
                },
            },
            {
                text: config.saveBtnText || _('save'),
                cls: 'primary-button',
                scope: this,
                handler: this.submit,
            },
        ]
    },

    getListeners: function (config) {
        return {}
    },
})

export default Default
