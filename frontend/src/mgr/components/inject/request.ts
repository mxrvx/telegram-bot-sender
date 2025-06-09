import {ofetch} from 'ofetch'

Ext.override(Ext.Ajax, {
    request: function (config) {
        config = config || {}

        const $api_url = $config.api_url
        if ($api_url && config.url.includes($api_url)) {
            config = this.prepareCmpConfig(config)
        }

        return Ext.Ajax.constructor.prototype.request.call(this, config)
    },

    prepareCmpConfig: function (config) {
        config = config || {}
        config.headers = config.headers || {}

        let method = (config.method || 'GET').toUpperCase()
        let params = config.params || {}
        if (typeof params === 'string') {
            params = this.unserializeParams(params)
        }

        let url = config.url || ''
        let urlParams = this.unserializeParams(url.split('?')[1])
        const action = urlParams.action ?? (params.action || '')

        url = url.split('?')[0] + action
        url = url.replace(/\/{2,}/g, '/')

        delete urlParams.action
        delete params.action

        params = Ext.applyIf(urlParams, params)

        if (config.scope instanceof Ext.form.Action.Submit) {
            let formParams = config.scope.form.getValues()
            params = Ext.applyIf(params, formParams)
            const formUpdate = config.scope?.options?.scope?.update ?? false
            method = formUpdate ? 'PATCH' : 'PUT'
        }

        if (method === 'GET') {
            params = this.serializeParams(params)
        }

        if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            config.jsonData = params
            params = null
        }

        config.url = url
        config.method = method
        config.params = params

        return config
    },

    unserializeParams: function (str: string) {
        const obj = {}
        for (const [key, value] of new URLSearchParams(str).entries()) {
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2)
                if (!obj[cleanKey]) {
                    obj[cleanKey] = []
                }
                obj[cleanKey].push(value)
            } else {
                obj[key] = value
            }
        }

        return obj
    },

    serializeParams: function (params) {
        const serialized = new URLSearchParams()
        for (const [key, value] of Object.entries(params)) {
            if (Array.isArray(value)) {
                value.forEach((v) => serialized.append(`${key}[]`, v))
            } else if (typeof value === 'object' && value !== null) {
                serialized.append(key, this.safeStringify(value))
            } else {
                serialized.append(key, value)
            }
        }

        return serialized.toString()
    },

    safeStringify: function (data) {
        const seen = new WeakSet()
        return JSON.stringify(data, (key, value) => {
            if (typeof value === 'object' && value !== null) {
                if (seen.has(value)) return '[Circular]'
                seen.add(value)
            }
            return value
        })
    },
})

Ext.Ajax.on('requestcomplete', function (conn, response, options) {
    const $api_url = $config.api_url
    if ($api_url && options.url.includes($api_url)) {
        let responseData = null
        try {
            responseData = Ext.decode(response.responseText)
        } catch (e) {}

        if (typeof responseData === 'object') {
            if (!responseData.hasOwnProperty('results')) {
                responseData = {data: responseData}
            }

            if (response.status >= 200 && response.status < 300) {
                responseData.success = true
            } else {
                responseData.success = false
            }
            response.responseText = Ext.encode(responseData)
        }
    }
})

Ext.Ajax.on('requestexception', function (conn, response, options) {
    const $api_url = $config.api_url
    if ($api_url && options.url.includes($api_url)) {
        let responseData = null
        try {
            responseData = Ext.decode(response.responseText)
        } catch (e) {}

        if (!(typeof responseData === 'object')) {
            responseData = {message: responseData}
        }
        responseData.success = false
        response.responseText = Ext.encode(responseData)
    }
})
