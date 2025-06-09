const Xtype: string = $ns + '.grid.default'
const Cls: string = $cls(Xtype)

import SearchField from '../field/search.ts'

const Default = Ext.define(Xtype, {
    extend: MODx.grid.Grid,
    xtype: Xtype,

    constructor: function (config) {
        config = config || {}
        this.initialConfig = config

        Ext.applyIf(config, {
            ApiPath: '',
            cls: Cls,
            url: $config.api_url || '',
            apiPath: '',
            primaryKey: 'id',
            baseParams: {},
            autoHeight: true,
            paging: true,
            remoteSort: true,
            sortBy: 'id',
            sortDir: 'ASC',
            fields: this.getFields(config),
            columns: this.getColumns(config),
            tbar: this.getTopBar(config),
            listeners: this.getListeners(config),
            viewConfig: {
                forceFit: true,
                enableRowBody: true,
                autoFill: true,
                showPreview: true,
                scrollOffset: -10,
                getRowClass: function (rec) {
                    var cls = []
                    if (rec.json['is_active'] != undefined && rec.json['is_active'] == 0) {
                        cls.push('row-inactive')
                    }
                    return cls.join(' ')
                },
            },
        })

        Default.superclass.constructor.call(this, config)
    },

    initComponent_: function () {
        Default.superclass.initComponent.call(this)
    },

    getFields: function () {
        return ['id', 'actions']
    },

    getColumns: function () {
        return [
            {
                header: _('id'),
                width: 35,
                sortable: true,
            },
            {
                header: '<i class="icon icon-cogs"></i>',
                dataIndex: 'actions',
                id: 'actions',
                renderer: renderActions,
                width: 75,
                sortable: false,
                groupable: false,
                hideable: false,
                menuDisabled: true,
            },
        ]
    },

    getTopBar: function () {
        return ['->', this.getSearchField()]
    },

    getListeners: function () {
        return {}
    },

    getSearchField: function (width) {
        return {
            xtype: SearchField.prototype.self.xtype,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field)
                    },
                    scope: this,
                },
                clear: {
                    fn: function (field) {
                        field.setValue('')
                        this._clearSearch()
                    },
                    scope: this,
                },
            },
        }
    },

    getActions: function () {
        return {}
    },

    getActionByName: function (action?: string) {
        const actions = this.getActions()
        const defaultAction = actions['default'] || null

        if (!action || !actions.hasOwnProperty(action)) {
            if (typeof defaultAction === 'function') {
                return () => defaultAction(action)
            }
            return null
        }

        return actions[action]
    },

    onClick: function (e) {
        const elem = e.getTarget()
        const action = elem.getAttribute('action')
        if (action) {
            const handler = this.getActionByName(action) ?? null
            if (typeof handler === 'function') {
                return handler.call(this, null, e)
            }
        }
        return this.processEvent('click', e)
    },

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds()
        var row = grid.getStore().getAt(rowIndex)

        var menu = getMenu(row.data['actions'], this, ids)

        this.addContextMenuItem(menu)
    },

    addContextMenuItem: function (items) {
        Default.superclass.addContextMenuItem.call(this, items)

        if (this.menu) {
            this.menu.on('show', function (menu) {
                menu.el.addClass($cls($ns + '.menu'))
            })
        }
    },

    createToolbar: function (b, c) {
        var a
        if (Ext.isArray(b)) {
            b = {items: b}
        }
        a = b.events ? Ext.apply(b, c) : this.createComponent(Ext.apply({}, b, c), 'toolbar')
        this.toolbars.push(a)

        if (a.items) {
            a.items.each(function (item) {
                if (item.menu) {
                    item.menu.on('show', function (menu) {
                        menu.el.addClass($cls($ns + '.menu'))
                    })
                }
            })
        }
        return a
    },

    getPrimaryKey(params: object) {
        let primaryKey = this.config.primaryKey || 'id'
        if (!Array.isArray(primaryKey)) {
            primaryKey = [primaryKey]
        }
        const key = {}
        for (const i of primaryKey) {
            if (!params.hasOwnProperty(i)) {
                return null
            }
            key[i] = params[i]
        }

        return key
    },

    _getSelectedIds: function () {
        var ids = []
        var selected = this.getSelectionModel().getSelections()

        selected.forEach((item) => {
            ids.push(this.getPrimaryKey(item.json))
        })

        return ids
    },

    _loadStore: function () {
        this.store = new Ext.data.JsonStore({
            url: this.config.url,
            baseParams: this.config.baseParams || {action: this.config.action || 'getList'},
            fields: this.config.fields,
            root: 'results',
            totalProperty: 'total',
            remoteSort: this.config.remoteSort || false,
            storeId: this.config.storeId || Ext.id(),
            autoDestroy: true,
            sortInfo: {
                field: this.config.sortBy || 'id',
                direction: this.config.sortDir || 'ASC',
            },
            listeners: {
                load: function (store, rows, data) {
                    Ext.getCmp('modx-content').doLayout()
                },
            },
        })
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue()
        this.getBottomToolbar().changePage(1)
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = ''
        this.getBottomToolbar().changePage(1)
    },
})

export default Default

export function renderActions(value, props, row) {
    var res = []
    var cls,
        icon,
        title,
        action,
        item = ''

    for (var i in row.data.actions) {
        if (!row.data.actions.hasOwnProperty(i)) {
            continue
        }
        var a = row.data.actions[i]
        if (!a['button']) {
            if (a == '-') {
                menu.push('-')
            }
            continue
        } else if (/^sep/i.test(a['action'])) {
            if (menu.length > 0) {
                menu.push('-')
            }
            continue
        }

        cls = a['cls'] ? a['cls'] : ''
        icon = a['icon'] ? a['icon'] : ''
        action = a['action'] ? a['action'] : ''

        item = String.format(
            '<li><button class="btn btn-default" action="{2}" title="{3}"><i class="{0} icon {1}" action="{2}"></i></button></li>',
            cls,
            icon,
            action,
            $l('actions.' + action),
        )

        res.push(item)
    }

    return String.format('<ul class="{0}">{1}</ul>', $cls($ns + '.actions'), res.join(''))
}

export function getMenu(actions, grid, selected) {
    var menu = []
    var cls,
        icon,
        title,
        action = ''

    var has_delete = false
    for (var i in actions) {
        if (!actions.hasOwnProperty(i)) {
            continue
        }

        var a = actions[i]
        if (!a['menu']) {
            if (a == '-') {
                menu.push('-')
            }
            continue
        } else if (/^sep/i.test(a['action'])) {
            if (menu.length > 0) {
                menu.push('-')
            }
            continue
        }

        if (selected.length > 1) {
            if (!a['multiple']) {
                continue
            } else if (typeof a['multiple'] == 'string') {
                a['title'] = a['multiple']
            }
        }

        cls = a['cls'] ? a['cls'] : ''
        icon = a['icon'] ? a['icon'] : ''
        action = a['action'] ? a['action'] : ''

        menu.push({
            handler: grid.getActionByName(action) ?? null,
            text: String.format('<i class="{0} icon {1}"></i>{2}', cls, icon, $l('actions.' + action)),
            scope: grid,
        })
    }

    return menu
}
