const Xtype: string = $ns + '.panel.version'
const Cls: string = $cls(Xtype)

const Vesrion = Ext.define(Xtype, {
    extend: MODx.Panel,
    xtype: Xtype,
    constructor: function (config) {
        config = config || {}
        this.initialConfig = config

        Ext.applyIf(config, {
            cls: Cls,
            layout: 'anchor',
            hideMode: 'offsets',
            defaults: {collapsible: false, autoHeight: true},
            listeners: {
                render: async function (p) {
                    try {
                        const {data} = await $get('mgr/version/')

                        let tpl = new Ext.XTemplate(
                            '<span title="{values.current_desc}">{[Ext.util.Format.htmlEncode(values.current)]}</span>',
                            '<tpl if="available">',
                            ' <span title="{values.available_desc}">{[Ext.util.Format.htmlEncode(values.available)]}</span>',
                            '</tpl>',
                        )

                        const row = {
                            ...data,
                            current_desc: $l('version.current', {version: data.current || ''}),
                            available_desc: $l('version.available', {version: data.available || ''}),
                        }
                        p.update(tpl.apply(row))
                    } catch (e) {
                        p.update('<p>Error loading version</p>')
                        console.error(e)
                    }
                },
            },
        })
        Vesrion.superclass.constructor.call(this, config)
    },
})

export default Vesrion
