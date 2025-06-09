import Default, {renderActions} from './default.ts'
import PostWindow from '../window/post.ts'
import {timestampToDate} from '../../tools/cmp.ts'

const Xtype: string = $ns + '.grid.post'
const Cls: string = $cls(Xtype)
const ApiPath: string = '/mgr/posts/'

const Post = Ext.define(Xtype, {
    extend: Default,
    xtype: Xtype,
    constructor: function (config) {
        config = config || {}

        Ext.applyIf(config, {
            ApiPath: ApiPath,
            baseParams: {
                action: ApiPath,
                expand: ['actions'],
            },
            sortBy: 'id',
            sortDir: 'DESC',
        })
        Post.superclass.constructor.call(this, config)
    },

    getTopBar: function () {
        return [
            /*{
                text: '<i class="icon icon-cogs"></i> ',
                menu: [
                    {
                        text: '<i class="icon icon-plus"></i>' + $l('actions.create'),
                        handler: this.getActionByName('create') ?? null,
                        scope: this,
                    },
                ],
            },*/
            {
                text: '<i class="icon icon-plus"></i>',
                handler: this.getActionByName('create') ?? null,
                scope: this,
            },
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
        const fields = Array.isArray($config['grid_post_fields']) ? [...$config['grid_post_fields']] : []
        if (!fields.includes('actions')) {
            fields.push('actions')
        }

        return fields
    },

    getColumns: function (config) {
        var all = {
            id: {
                width: 10,
                sortable: true,
            },
            title: {
                width: 35,
                sortable: true,
            },
            status: {
                width: 10,
                renderer: function (value, metaData, record) {
                    const status = {
                        total: record['json']['total'] ?? 0,
                        total_send: record['json']['total_send'] ?? 0,
                        total_success_send: record['json']['total_success_send'] ?? 0,
                    }
                    const allEmpty = Object.values(status).every((value) => value === 0)
                    if (allEmpty) {
                        return ''
                    }

                    return (
                        String.format(
                            '<span class="badge" ext:qtip="{0}">{1}</span>',
                            $l('models.post.total') + ': ' + status.total,
                            status.total,
                        ) +
                        String.format(
                            '<span class="badge badge-primary" ext:qtip="{0}">{1}</span>',
                            $l('models.post.total_send') + ': ' + status.total_send,
                            status.total_send,
                        ) +
                        String.format(
                            '<span class="badge badge-success" ext:qtip="{0}">{1}</span>',
                            $l('models.post.total_success_send') + ': ' + status.total_success_send,
                            status.total_success_send,
                        )
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
            sended_at: {
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
                header: $l(`models.post.${field}`),
                dataIndex: field,
                sortable: false,
                ...all[field],
            }))
        return columns
    },

    getActions: function () {
        return {
            default: async (operation?: string) => {
                await $post(ApiPath, {
                    params: {operation: operation, ids: this._getSelectedIds()},
                })
                this.refresh()
            },
            create: () => {
                const data = {}
                const w = MODx.load({
                    xtype: PostWindow.prototype.self.xtype,
                    record: data,
                    update: false,
                    listeners: {
                        success: this.refresh.bind(this),
                    },
                })
                w.reset()
                w.setValues(data)
                w.show()
            },
            edit: async () => {
                try {
                    const row = this.getSelectionModel().getSelected()
                    const {data} = await $get(ApiPath + `${row.id}/`)
                    const w = MODx.load({
                        xtype: PostWindow.prototype.self.xtype,
                        record: data,
                        update: true,
                        listeners: {
                            success: this.refresh.bind(this),
                        },
                    })
                    w.reset()
                    w.setValues(data)
                    w.show()
                } catch (error) {
                    console.error(error)
                }
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
    },
})

export default Post
