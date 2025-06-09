const Xtype: string = $ns + '.panel.default'
const Cls: string = $cls(Xtype)

const Default = Ext.define(Xtype, {
    extend: MODx.Panel,
    xtype: Xtype,
    constructor: function (config) {
        config = config || {}
        this.initialConfig = config

        Ext.applyIf(config, {
            cls: Cls,
            baseCls: 'modx-formpanel',
            layout: 'anchor',
            hideMode: 'offsets',
            defaults: {collapsible: false, autoHeight: true},
            items: this.getItems(config),
        })
        Default.superclass.constructor.call(this, config)
    },

    getItems: function (config) {
        return []
    },
})

export default Default
