var originalConfirm = Ext.MessageBox.confirm
Ext.MessageBox.confirm = function (title, msg, fn, scope) {
    let result = originalConfirm.call(this, title, msg, fn, scope)

    if (scope.xtype.indexOf($ns) > -1) {
        var dlg = Ext.MessageBox.getDialog()
        if (dlg) {
            dlg.addClass($cls($ns + '.popup'))
            dlg.setWidth(300)
        }
    }

    return result
}
