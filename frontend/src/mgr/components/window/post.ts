import Default, {renderActions} from './default.ts'
import {initEditor, destroyEditor, record} from '../editor/default.ts'

const Xtype: string = $ns + '.window.post'
const Cls: string = $cls(Xtype)
const ApiPath: string = '/mgr/posts/'

const Post = Ext.define(Xtype, {
    extend: Default,
    xtype: Xtype,
    constructor: function (config) {
        config = config || {}
        this.initialConfig = config

        Ext.applyIf(config, {
            ApiPath: ApiPath,
            baseParams: {
                action: ApiPath,
            },
            title: $l('models.post.title_one'),
            width: 600,
            height: 500,
        })
        Post.superclass.constructor.call(this, config)
    },

    getFields: function (config) {
        return [
            {
                layout: 'form',
                defaults: {
                    anchor: '99.8%',
                },
                items: [
                    {xtype: 'hidden', name: 'id'},
                    {
                        xtype: 'hidden',
                        name: 'content',
                        setValue: function (value) {
                            if (value && value instanceof Object) {
                                value = Ext.util.JSON.encode(value)
                            }
                            return Ext.form.TextField.superclass.setValue.call(this, value)
                        },
                    },
                    {
                        xtype: 'textfield',
                        name: 'title',
                        fieldLabel: $l('models.post.title'),
                        allowBlank: false,
                    },
                    {
                        xtype: 'panel',
                        layout: 'fit',
                        cls: 'content-editor',
                        bodyCssClass: 'editorjs form-control',
                        listeners: {
                            afterrender: {
                                fn: function (panel) {
                                    this.initEditorContent()
                                    initEditor(panel.body.id, {readOnly: !!(this.record || {}).is_send})
                                },
                                scope: this,
                            },
                            destroy: {
                                fn: function (panel) {
                                    destroyEditor()
                                },
                                scope: this,
                            },
                        },
                    },
                ],
            },
        ]
    },

    getListeners: function (config) {
        return Ext.applyIf(config.listeners, {
            beforeSubmit: {
                fn: function () {
                    this.setEditorContent()
                },
                scope: this,
            },
        })
    },

    setEditorContent: function () {
        let content = this.fp.getForm()?.findField('content')
        if (content) {
            content.setValue(record.value)
        }
    },
    initEditorContent: function () {
        let value = this.fp.getForm()?.findField('content')?.getValue()

        if (typeof value === 'string' && value.trim() !== '') {
            try {
                value = JSON.parse(value)
            } catch (e) {
                console.warn('Failed to parse JSON content:', e)
                value = {}
            }
        } else if (typeof value !== 'object' || value === null) {
            value = {}
        }

        record.value = value
    },
})

export default Post
