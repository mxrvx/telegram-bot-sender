import Default from '../grid/default'

const Xtype: string = $ns + '.field.search'
const Cls: string = $cls(Xtype)

const Search = Ext.define(Xtype, {
    extend: Ext.form.TwinTriggerField,
    xtype: Xtype,

    constructor: function (config) {
        config = config || {}
        this.initialConfig = config

        Ext.applyIf(config, {
            xtype: 'twintrigger',
            ctCls: Cls,
            allowBlank: true,
            msgTarget: 'under',
            emptyText: _('search'),
            name: 'query',
            width: 250,
            triggerAction: 'all',
            clearBtnCls: 'x-field-search-clear',
            searchBtnCls: 'x-field-search-go',
            onTrigger1Click: this._triggerSearch,
            onTrigger2Click: this._triggerClear,
        })
        Search.superclass.constructor.call(this, config)

        this.on('render', function () {
            this.getEl().addKeyListener(
                Ext.EventObject.ENTER,
                function () {
                    this._triggerSearch()
                },
                this,
            )
        })
        this.addEvents('clear', 'search')
    },

    initComponent: function () {
        Default.superclass.initComponent.call(this)
        this.triggerConfig = {
            tag: 'span',
            cls: 'x-field-search-btns',
            cn: [
                {tag: 'div', cls: 'x-form-trigger ' + this.searchBtnCls},
                {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls},
            ],
        }
    },

    _triggerSearch: function () {
        this.fireEvent('search', this)
    },

    _triggerClear: function () {
        this.fireEvent('clear', this)
    },
})

export default Search
