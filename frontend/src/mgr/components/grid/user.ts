import Default, {renderActions} from './default.ts'
import {timestampToDate} from '../../tools/cmp.ts'
import PostWindow from '../window/post'

const Xtype: string = $ns + '.grid.user'
const Cls: string = $cls(Xtype)
const ApiPath: string = '/mgr/users/'

const User = Ext.define(Xtype, {
    extend: Default,
    xtype: Xtype,
    constructor: function (config) {
        config = config || {}
        this.initialConfig = config

        Ext.applyIf(config, {
            ApiPath: ApiPath,
            baseParams: {
                action: ApiPath,
                expand: ['actions'],
            },
            sortBy: 'created_at',
            sortDir: 'DESC',
        })
        User.superclass.constructor.call(this, config)
    },

    getTopBar: function () {
        return [
            {
                text: '<i class="icon icon-refresh"></i>',
                handler: this.refresh,
                scope: this,
            },
            '->',
            this.getSearchField(),
        ]
    },

    getFields: function (config): array {
        const fields = Array.isArray($config['grid_user_fields']) ? [...$config['grid_user_fields']] : []
        if (!fields.includes('actions')) {
            fields.push('actions')
        }

        return fields
    },

    getColumns: function (config) {
        var all = {
            id: {
                width: 20,
                sortable: true,
            },
            first_name: {
                width: 35,
                sortable: true,
            },
            last_name: {
                width: 35,
                sortable: true,
            },
            username: {
                width: 35,
                sortable: true,
            },
            status: {
                width: 35,
                sortable: true,
                renderer: function (value, metaData, record) {
                    const status = record['json']['status']
                    const allEmpty = status === ''
                    if (allEmpty) {
                        return ''
                    }

                    return String.format(
                        '<span class="badge badge-{1}" ext:qtip="{0}">{1}</span>',
                        $l('models.user.status') + ': ' + status,
                        status,
                    )
                },
            },
            created_at: {
                width: 20,
                sortable: true,
                renderer: timestampToDate,
            },
            updated_at: {
                width: 20,
                sortable: true,
                renderer: timestampToDate,
            },
            actions: {
                header: '<i class="icon icon-cogs"></i>',
                id: 'actions',
                renderer: renderActions,
                width: 10,
                sortable: false,
                groupable: false,
                hideable: false,
                menuDisabled: true,
            },
        }
        const columns = this.getFields()
            .filter((field) => all.hasOwnProperty(field))
            .map((field) => ({
                header: $l(`models.user.${field}`),
                dataIndex: field,
                sortable: false,
                ...all[field],
            }))
        return columns
    },

    /*getActions: function () {
        return {
            default: async (operation?: string) => {
                await $post(ApiPath, {
                    params: {operation: operation, ids: this._getSelectedIds()},
                })
                this.refresh()
            },
            delete: () => {
                Ext.MessageBox.confirm(
                    `${$l('actions.delete')}: ${$l('components.confirm.title')}`,
                    $l('components.confirm.message'),
                    function (e) {
                        if (e == 'yes') {
                            this.getActions()['default'].call(this, 'delete')
                        }
                    },
                    this,
                )
            },
        }
    },*/
})

export default User
