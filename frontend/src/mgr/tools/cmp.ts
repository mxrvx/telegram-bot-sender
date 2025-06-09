export function getClsByXtype(xtype: string): string {
    const parts = xtype.split('.')

    const classes = []
    const first = parts[0]

    classes.push(first)

    for (let i = 1; i < parts.length; i++) {
        classes.push(`${first}-${parts[i]}`)
        classes.push(parts[i])
    }
    if (parts.length > 2) {
        classes.push(xtype.replace(/\./g, '-'))
    }

    return classes.join(' ')
}

export function ref(initialValue) {
    return {
        _value: initialValue,
        get value() {
            return this._value
        },
        set value(newValue) {
            this._value = newValue
        },
    }
}

export function timestampToDate(timestamp?: number): string {
    if (!timestamp) {
        return ''
    }
    const value = Ext.util.Format.date(
        new Date(timestamp * 1000),
        MODx.config['manager_date_format'] + ' ' + MODx.config['manager_time_format'],
    )
    return value
}
