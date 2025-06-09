import Default from './default.ts'
import Version from './version.ts'
import PostGrid from '../grid/post.ts'
import UserGrid from '../grid/user.ts'

const Xtype: sring = $ns + '.panel.main'
const Cls: sring = $cls(Xtype)

const Main = Ext.define(Xtype, {
    extend: Default,
    xtype: Xtype,
    constructor: function (config) {
        config = config || {}
        Ext.apply(config, {
            cls: Cls,
            items: this.getItems(config),
        })
        Main.superclass.constructor.call(this, config)
    },

    getItems: function (config) {
        return [
            {
                xtype: Version.prototype.self.xtype,
            },
            {
                xtype: 'modx-tabs',
                id: Xtype,
                items: [
                    {
                        title: $l('models.post.title_many'),
                        layout: 'fit',
                        items: [
                            {
                                xtype: PostGrid.prototype.self.xtype,
                                id: PostGrid.prototype.self.xtype,
                            },
                        ],
                    },
                    {
                        title: $l('models.user.title_many'),
                        layout: 'fit',
                        items: [
                            {
                                xtype: UserGrid.prototype.self.xtype,
                                id: UserGrid.prototype.self.xtype,
                            },
                        ],
                    },
                ],
            },
        ]
    },
})

export default Main
